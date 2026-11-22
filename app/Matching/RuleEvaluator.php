<?php

namespace App\Matching;

/**
 * Rule-based eligibility check. Deterministic: the same answers always give the same result.
 * The AI never decides eligibility; it only explains what this class produced.
 *
 * Per-rule result:  pass | fail | unknown (a needed answer is missing) | confirm (only the provider can decide) | na
 * Scholarship status:
 *   not_eligible  a required rule failed
 *   needs_info    a required rule has no answer yet
 *   possible      all required rules pass but something must be confirmed or a soft rule failed
 *   eligible      every rule passes
 */
final class RuleEvaluator
{
    public const ELIGIBLE = 'eligible';

    public const POSSIBLE = 'possible';

    public const NEEDS_INFO = 'needs_info';

    public const NOT_ELIGIBLE = 'not_eligible';

    /**
     * @param  array<int,array>  $rules
     * @param  array<string,mixed>  $facts  from AnswerNormalizer::facts()
     * @return array{status:string,score:int,checks:array<int,array>}
     */
    public function evaluate(array $rules, array $facts): array
    {
        $checks = [];
        $earned = 0.0;
        $total = 0.0;
        $hardFail = $unknownRequired = $needsConfirm = $softIssue = false;

        foreach ($rules as $rule) {
            $r = $this->check($rule, $facts);
            $required = (bool) ($rule['required'] ?? false);
            $weight = (float) ($rule['weight'] ?? 1);

            $checks[] = [
                'key' => $rule['key'], 'label' => $rule['label'], 'result' => $r['result'],
                'detail' => $r['detail'], 'required' => $required,
            ];
            if ($r['result'] === 'na') {
                continue;
            }

            $total += $weight;
            $earned += match ($r['result']) {
                'pass' => $weight, 'confirm', 'unknown' => $weight * 0.5, default => 0.0
            };

            // A 'bonus' rule (e.g. "special equity group: +10 ranking points") only helps the ranking. Not
            // meeting it is no reason to say "nearly eligible", so it never changes the status (2026-11-19).
            if (! empty($rule['bonus'])) {
                continue;
            }

            if ($r['result'] === 'fail') {
                $required ? $hardFail = true : $softIssue = true;
            } elseif ($r['result'] === 'unknown') {
                $required ? $unknownRequired = true : $softIssue = true;
            } elseif ($r['result'] === 'confirm') {
                // Only a REQUIRED confirmation keeps a student at "nearly eligible". An optional one (a note such
                // as "ask about open slots") is shown in the checks but must not hide a clear match (2026-11-19).
                if ($required) {
                    $needsConfirm = true;
                }
            }
        }

        $status = match (true) {
            $hardFail => self::NOT_ELIGIBLE,
            $unknownRequired => self::NEEDS_INFO,
            $needsConfirm || $softIssue => self::POSSIBLE,
            default => self::ELIGIBLE,
        };

        return ['status' => $status, 'score' => $total > 0 ? (int) round($earned / $total * 100) : 0, 'checks' => $checks];
    }

    /** @return array{result:string,detail:string} */
    private function check(array $rule, array $facts): array
    {
        foreach ($rule['skip_if'] ?? [] as $fact => $value) {
            if (($facts[$fact] ?? null) === $value) {
                return $this->result('na', '');
            }
        }

        $type = $rule['type'];
        if ($type === 'any_of') {
            return $this->anyOf($rule, $facts);
        }

        $fact = $rule['fact'] ?? '';
        $v = $facts[$fact] ?? null;

        if ($type === 'confirm') {
            return $this->confirm($rule, $facts);
        }

        // A program was chosen but its name could not be read: ask the provider, do not tell the student to answer again.
        if ($type === 'program_in' && ($v === null || $v === '') && ! empty($facts['program_field'])) {
            return $this->result('confirm', 'BAKR could not tell which program you chose. Ask the provider if it is on their list.');
        }

        $missing = $v === null || $v === '' || ($type === 'income_lte' && ($facts['income_max'] ?? null) === null);
        // Tick-box groups: an empty list is an answer ("none of these"), but null means the question was never
        // asked (e.g. a form saved before the question existed), which must be "unknown", never a fail.
        if ($missing && (! in_array($type, ['contains_any', 'contains_none'], true) || $v === null)) {
            return $this->result('unknown', 'Answer this in your matching form so BAKR can check it.');
        }

        switch ($type) {
            case 'eq':
                return $this->bool($v == $rule['value'], $this->describeAnswer($fact, $v), $this->describeAnswer($fact, $v));
            case 'in':
                return $this->bool(in_array($v, $rule['value'], true), $this->describeAnswer($fact, $v), $this->describeAnswer($fact, $v));
            case 'not_in':
                return $this->bool(! in_array($v, $rule['value'], true), $this->describeAnswer($fact, $v), $this->describeAnswer($fact, $v));
            case 'gte':
                $est = ! empty($facts['college_gwa_estimated']) && str_starts_with($fact, 'last_') ? ' (estimated from your 1.0–5.0 grade)' : '';

                return $this->bool((float) $v >= (float) $rule['value'], 'You entered '.$this->formatNumber($v).'%'.$est.'. The minimum is '.$this->formatNumber($rule['value']).'%.', 'You entered '.$this->formatNumber($v).'%'.$est.', below the minimum of '.$this->formatNumber($rule['value']).'%.');
            case 'lte':
                return $this->bool((float) $v <= (float) $rule['value'], 'Your answer is within the limit.', 'Your answer is above the limit.');
            case 'income_lte':
                $cap = (float) $rule['value'];
                $label = $facts['income_label'] ?? 'your range';
                if ($facts['income_max'] <= $cap) {
                    return $this->result('pass', "Your income range ($label) is within the limit.");
                }
                if ($facts['income_min'] > $cap) {
                    return $this->result('fail', "Your income range ($label) is above the limit of ₱".number_format($cap).'.');
                }

                return $this->result('confirm', "Your income range ($label) overlaps the limit. Bring proof of income.");
            case 'contains_any':
                $have = (array) $v;
                $hit = array_values(array_intersect($have, $rule['value']));

                return $hit
                    ? $this->result('pass', 'You selected: '.ApplicationSchema::optionLabel($fact, $hit).'.')
                    : $this->result('fail', 'None of the qualifying options were selected.');
            case 'contains_none':
                $hit = array_values(array_intersect((array) $v, $rule['value']));
                if (! $hit && (array) $v === []) {
                    return $this->result('unknown', 'Tell BAKR what scholarships you already receive.');
                }

                return $hit
                    ? $this->result('fail', 'You said you already receive: '.ApplicationSchema::optionLabel($fact, $hit).'.')
                    : $this->result('pass', 'You do not receive a conflicting scholarship.');
            case 'maybe_in':
                if ($v === 'unrecognized') {
                    return $this->result('confirm', 'BAKR could not tell which field your program belongs to. Ask the provider if it is on their list.');
                }
                if (in_array($v, $rule['pass'], true)) {
                    return $this->result('pass', 'Your answer: '.ApplicationSchema::optionLabel($fact, $v).'.');
                }
                if (in_array($v, $rule['maybe'] ?? [], true)) {
                    return $this->result('confirm', 'Your answer: '.ApplicationSchema::optionLabel($fact, $v).'. This may qualify. Confirm with the provider.');
                }

                return $this->result('fail', 'Your answer: '.ApplicationSchema::optionLabel($fact, $v).'. It is not on the provider\'s list.');
            case 'program_in':
                // Exact DNSC program against a provider's official program list (fact: program_name).
                // 'pass' / 'maybe' / 'not' hold DNSC program names; one BAKR has not sorted yet is a confirm, never a fail.
                $name = self::programKey((string) $v);
                $in = fn (string $list) => in_array($name, array_map([self::class, 'programKey'], $rule[$list] ?? []), true);
                if ($in('pass')) {
                    return $this->result('pass', "Your program ($v) is on the provider's list.");
                }
                if ($in('maybe')) {
                    return $this->result('confirm', "Your program ($v) is on the list only for some specializations. Confirm with the provider.");
                }
                if ($in('not')) {
                    return $this->result('fail', "Your program ($v) is not on the provider's list.");
                }

                // A list marked 'open_list' is not the provider's full list (newer or regional lists exist): it carries its own note.
                return $this->result('confirm', $rule['confirm_note'] ?? "BAKR has not checked your program ($v) against the provider's list yet. Ask the provider.");
        }

        return $this->result('unknown', 'This rule type is not supported.');
    }

    private function anyOf(array $rule, array $facts): array
    {
        $details = [];
        $sawUnknown = false;
        foreach ($rule['rules'] as $sub) {
            $r = $this->check($sub, $facts);
            if ($r['result'] === 'pass') {
                return $this->result('pass', $sub['label'].': yes.');
            }
            if ($r['result'] === 'unknown') {
                $sawUnknown = true;
            }
            $details[] = $sub['label'].': '.($r['result'] === 'unknown' ? 'no answer yet' : 'no').'.';
        }

        return $this->result($sawUnknown ? 'unknown' : 'fail', implode(' ', $details));
    }

    private function confirm(array $rule, array $facts): array
    {
        $note = $rule['confirm_note'] ?? 'Only the provider can confirm this.';
        // Optional numeric threshold, filled in once a source publishes one.
        if (isset($rule['min_percent']) && is_numeric($rule['min_percent']) && isset($facts[$rule['fact']])) {
            return $this->bool((float) $facts[$rule['fact']] >= (float) $rule['min_percent'], 'Your grade meets the minimum.', 'Your grade is below the minimum.');
        }

        return $this->result('confirm', $note);
    }

    /** "Your answer: …" for an eq / in / not_in rule. */
    private function describeAnswer(string $fact, mixed $v): string
    {
        if (! isset(ApplicationSchema::fields()[$fact])) {
            return $fact === 'dnsc_student' ? 'Your BAKR account is for a DNSC student.' : 'OK.';
        }

        return 'Your answer: '.ApplicationSchema::optionLabel($fact, is_scalar($v) ? (string) $v : $v).'.';
    }

    private function bool(bool $ok, string $pass, string $fail): array
    {
        return $this->result($ok ? 'pass' : 'fail', $ok ? $pass : $fail);
    }

    /** "Bachelor of Science in Agro-Forestry (BSAF)" → "bachelor of science in agro forestry": case, abbreviations and dashes ignored. */
    public static function programKey(string $name): string
    {
        $s = preg_replace('/\([^)]*\)/u', ' ', mb_strtolower($name));
        $s = preg_replace('/[-–—\/]+/u', ' ', $s);

        return trim(preg_replace('/\s+/u', ' ', $s));
    }

    /** One rule's outcome: pass | fail | unknown | confirm | na, plus the sentence shown to the student. */
    private function result(string $result, string $detail): array
    {
        return ['result' => $result, 'detail' => $detail];
    }

    /** 85.50 → "85.5", 90.00 → "90" (for the sentences shown to the student). */
    private function formatNumber(mixed $v): string
    {
        return rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.');
    }
}
