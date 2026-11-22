<?php

namespace App\Services\Provider;

use App\Models\ScholarRequirement;
use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use App\Models\User;
use App\Services\ScholarshipApplicationService;
use App\Support\MaintainingDocument;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Applies a provider's edits to one of their scholarships.
 *
 * Text fields are replaced as given. For the qualifications, a provider may change each
 * EXISTING rule's wording (label), its "confirm with provider" note, whether it is required,
 * and the number for threshold rules (gte / lte / income_lte, including inside any_of).
 * A provider can never add or remove rules, or change a rule's type or the answer it checks,
 * because those are tied to the questionnaire in App\Matching\ApplicationSchema.
 *
 * Every save writes a ScholarshipRevision (full before/after). When the rules change, every
 * student's current answers are matched again so results never use stale rules.
 */
class ScholarshipEditor
{
    public const TEXT_FIELDS = ['overview', 'benefits', 'documents', 'renewal_requirements', 'application_route', 'window_note', 'application_opens_on', 'application_closes_on', 'results_on', 'official_url'];

    public const NUMERIC_TYPES = ['gte', 'lte', 'income_lte'];

    /**
     * @param  ScholarshipApplicationService  $applications  re-matches students after a rule change
     */
    public function __construct(private ScholarshipApplicationService $applications) {}

    /**
     * @param  array  $text  validated text fields (lists already split into arrays)
     * @param  array  $ruleInput  rules[index][label|required|confirm_note|value|sub][…]
     * @return array{changed: bool, rules_changed: bool, rematched: int}
     */
    public function apply(Scholarship $scholarship, array $text, array $ruleInput, User $editor): array
    {
        $rules = $this->editedRules($scholarship->rules ?? [], $ruleInput);

        $before = $this->snapshot($scholarship);
        $scholarship->fill(Arr::only($text, self::TEXT_FIELDS));
        $scholarship->rules = $rules;
        $after = $this->snapshot($scholarship);

        if ($before === $after) {
            return ['changed' => false, 'rules_changed' => false, 'rematched' => 0];
        }

        $rulesChanged = $before['rules'] !== $after['rules'];

        DB::transaction(function () use ($scholarship, $editor, $before, $after, $rulesChanged) {
            $scholarship->updated_by = $editor->id;
            $scholarship->save();
            $scholarship->revisions()->create([
                'user_id' => $editor->id,
                'before' => $before,
                'after' => $after,
                'rules_changed' => $rulesChanged,
            ]);
        });

        $rematched = 0;
        if ($rulesChanged) {
            ScholarshipApplication::where('is_current', true)->lazyById(100)->each(function ($application) use (&$rematched) {
                $this->applications->rematch($application);
                $rematched++;
            });
        }

        return ['changed' => true, 'rules_changed' => $rulesChanged, 'rematched' => $rematched];
    }

    /**
     * After the maintaining documents change: every checklist item of this scholarship that is not
     * approved yet takes the new settings of the document with the same name (accepted files, size,
     * instructions, required). Approved items keep what they were reviewed under. Returns the count.
     */
    public function syncChecklistSettings(Scholarship $scholarship): int
    {
        $count = 0;
        foreach ($scholarship->maintainingDocuments() as $doc) {
            $settings = ScholarRequirement::settingsFrom($doc);
            $settings['accepted_types'] = json_encode($settings['accepted_types']);   // query update: no casts
            $count += ScholarRequirement::query()
                ->whereHas('scholar', fn ($q) => $q->where('scholarship_id', $scholarship->id))
                ->where('name', $doc['name'])->where('status', '!=', 'approved')
                ->update($settings);
        }

        return $count;
    }

    /** Returns the rules with the allowed edits applied, or throws a validation error. */
    private function editedRules(array $rules, array $input): array
    {
        $errors = [];

        foreach ($rules as $i => $rule) {
            $in = $input[$i] ?? null;
            if (! is_array($in)) {
                continue;   // rule not on the form: keep as is
            }

            $label = trim((string) ($in['label'] ?? ''));
            if ($label === '' || mb_strlen($label) > 255) {
                $errors["rules.$i.label"] = 'Each qualification needs a description (up to 255 characters).';
            } else {
                $rule['label'] = $label;
            }

            $rule['required'] = filter_var($in['required'] ?? false, FILTER_VALIDATE_BOOLEAN);

            if (($rule['type'] ?? null) === 'confirm') {
                $note = trim((string) ($in['confirm_note'] ?? ''));
                if (mb_strlen($note) > 500) {
                    $errors["rules.$i.confirm_note"] = 'The note can be up to 500 characters.';
                } elseif ($note !== '' || array_key_exists('confirm_note', $rule)) {
                    $rule['confirm_note'] = $note === '' ? null : $note;
                }
            }

            if (in_array($rule['type'] ?? null, self::NUMERIC_TYPES, true) && array_key_exists('value', $in)) {
                $value = $this->number($in['value'], $rule, "rules.$i.value", $errors);
                if ($value !== null) {
                    $rule['value'] = $value;
                }
            }

            if (($rule['type'] ?? null) === 'any_of') {
                foreach ($rule['rules'] ?? [] as $j => $sub) {
                    $subIn = $in['sub'][$j] ?? null;
                    if (! is_array($subIn)) {
                        continue;
                    }
                    $subLabel = trim((string) ($subIn['label'] ?? ''));
                    if ($subLabel === '' || mb_strlen($subLabel) > 255) {
                        $errors["rules.$i.sub.$j.label"] = 'Each option needs a description (up to 255 characters).';
                    } else {
                        $sub['label'] = $subLabel;
                    }
                    if (in_array($sub['type'] ?? null, self::NUMERIC_TYPES, true) && array_key_exists('value', $subIn)) {
                        $value = $this->number($subIn['value'], $sub, "rules.$i.sub.$j.value", $errors);
                        if ($value !== null) {
                            $sub['value'] = $value;
                        }
                    }
                    $rule['rules'][$j] = $sub;
                }
            }

            $rules[$i] = $rule;
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        return $rules;
    }

    /** Percent facts: 0–100. Income caps: whole pesos up to 100 million. Anything else: 0–1,000,000. */
    private function number(mixed $raw, array $rule, string $key, array &$errors): int|float|null
    {
        $isPercent = str_contains((string) ($rule['fact'] ?? ''), 'percent');
        $isIncome = ($rule['type'] ?? null) === 'income_lte';
        [$min, $max] = match (true) {
            $isPercent => [0, 100],
            $isIncome => [0, 100_000_000],
            default => [0, 1_000_000],
        };

        if (! is_numeric($raw) || $raw < $min || $raw > $max || ($isIncome && floor((float) $raw) != (float) $raw)) {
            $errors[$key] = $isIncome
                ? 'Enter the income cap in whole pesos.'
                : "Enter a number from {$min} to ".number_format($max).'.';

            return null;
        }

        return $raw + 0;   // int when whole, float otherwise
    }

    /** The editable fields of a scholarship in a comparable form (used for the revision log and change detection). */
    private function snapshot(Scholarship $s): array
    {
        return [
            'overview' => $s->overview,
            'benefits' => array_values($s->benefits ?? []),
            'documents' => array_values($s->documents ?? []),
            'renewal_requirements' => MaintainingDocument::list($s->renewal_requirements),
            'application_route' => $s->application_route,
            'window_note' => $s->window_note,
            'application_opens_on' => $s->application_opens_on?->format('Y-m-d'),
            'application_closes_on' => $s->application_closes_on?->format('Y-m-d'),
            'results_on' => $s->results_on?->format('Y-m-d'),
            'official_url' => $s->official_url,
            'rules' => $s->rules ?? [],
        ];
    }
}
