<?php

namespace App\Http\Controllers\Provider;

use App\Models\ScholarshipRevision;
use App\Services\Provider\RequirementChecklist;
use App\Services\Provider\ScholarshipEditor;
use App\Support\MaintainingDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * "My scholarships": the full scholarship information, editable by the provider.
 * The public Scholarships page reads the same rows, so a save shows up there at once.
 */
class ProviderScholarshipController extends ProviderController
{
    /** Each editable section of My scholarships and the fields its own form may change. */
    private const SECTION_FIELDS = [
        'overview' => ['overview'],
        'qualifications' => [],   // rules only
        'benefits' => ['benefits'],
        'documents' => ['documents'],
        'period' => ['application_opens_on', 'application_closes_on', 'results_on', 'window_note'],
        'apply' => ['application_route', 'official_url'],
    ];

    public function index(Request $request): View
    {
        $scope = $this->scope($request);
        $scholarships = $scope->scholarships();
        $selected = (int) $request->query('s') ?: $scholarships->first()?->id;

        return $this->page($scope, 'provider.scholarships', [
            'scholarships' => $scholarships,
            'selected' => $selected,
            'revisions' => $scope->scholarships()->isEmpty() ? collect() : ScholarshipRevision::query()
                ->whereIn('scholarship_id', $scope->scholarshipIds())
                ->with(['user', 'scholarship'])->latest('created_at')->take(10)->get(),
            'missing' => 'Not published by provider. Confirm with '.$scope->name().'.',
        ]);
    }

    public function update(Request $request, int $scholarship, ScholarshipEditor $editor): RedirectResponse
    {
        $scope = $this->scope($request);
        $s = $scope->scholarship($scholarship);

        $data = $request->validate([
            'overview' => ['nullable', 'string', 'max:2000'],
            'benefits' => ['nullable', 'string', 'max:4000'],
            'documents' => ['nullable', 'string', 'max:6000'],
            'application_route' => ['nullable', 'string', 'max:500'],
            'window_note' => ['nullable', 'string', 'max:500'],
            'application_opens_on' => ['nullable', 'date_format:Y-m-d'],
            'application_closes_on' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:application_opens_on'],
            'results_on' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:application_opens_on', 'after_or_equal:application_closes_on'],
            'official_url' => ['nullable', 'url:https,http', 'max:255'],
            'rules' => ['nullable', 'array'],
        ], [
            'application_closes_on.after_or_equal' => 'The closing date cannot be before the opening date.',
            'results_on.after_or_equal' => 'The results date cannot be before the opening or closing date.',
        ]);

        $lines = fn (?string $text) => array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $text)), 'strlen'));
        $text = [
            'overview' => $data['overview'] ?? null,
            'benefits' => $lines($data['benefits'] ?? ''),
            'documents' => $lines($data['documents'] ?? ''),
            'application_route' => $data['application_route'] ?? null,
            'window_note' => $data['window_note'] ?? null,
            'application_opens_on' => $data['application_opens_on'] ?? null,
            'application_closes_on' => $data['application_closes_on'] ?? null,
            'results_on' => $data['results_on'] ?? null,
            'official_url' => $data['official_url'] ?? null,
        ];
        // Maintaining documents have their own form (updateDocuments); leaving the key out keeps them (Arr::only).

        // A section's own form sends "section": only THAT section's fields are saved, everything else stays
        // exactly as it is (even if the request carries other fields). No "section" = the whole form (legacy).
        $section = $request->input('section');
        if ($section !== null) {
            abort_unless(is_string($section) && isset(self::SECTION_FIELDS[$section]), 422);
            $text = Arr::only($text, self::SECTION_FIELDS[$section]);
            if ($section !== 'qualifications') {
                $data['rules'] = [];
            }
        }

        $result = $editor->apply($s, $text, $data['rules'] ?? [], $request->user());

        $msg = match (true) {
            ! $result['changed'] => 'No changes to save.',
            $result['rules_changed'] => "Saved. Qualifications changed, so BAKR re-matched {$result['rematched']} ".str('student')->plural($result['rematched']).'. The public Scholarships page is updated.',
            default => 'Saved. The public Scholarships page is updated.',
        };

        $redirect = redirect()->route('provider.scholarships', ['s' => $s->id]);
        if ($section !== null) {
            $redirect->withFragment($section);
        }

        return $redirect->with('status', $msg);
    }

    /**
     * My scholarships › Maintaining documents: the documents scholars submit every semester, each with
     * its own name, instructions, accepted files, size limit and "every scholar / only if it applies".
     * Only for providers that collect requirements in BAKR (404 otherwise, like the Requirements page).
     * Open checklist items that are not approved yet take the new settings at once.
     */
    public function updateDocuments(Request $request, int $scholarship, ScholarshipEditor $editor): RedirectResponse
    {
        $scope = $this->scope($request);
        abort_unless($scope->has('requirements'), 404);
        $s = $scope->scholarship($scholarship);

        $request->validate([
            'docs' => ['nullable', 'array', 'max:'.(MaintainingDocument::MAX_DOCUMENTS + 5)],
            'docs.*.name' => ['nullable', 'string', 'max:'.MaintainingDocument::NAME_MAX],
            'docs.*.note' => ['nullable', 'string', 'max:'.MaintainingDocument::NOTE_MAX],
            'docs.*.types' => ['nullable', 'array'],
            'docs.*.types.*' => ['string', Rule::in(array_keys(MaintainingDocument::TYPES))],
            'docs.*.max_mb' => ['nullable', 'integer', Rule::in(MaintainingDocument::SIZES)],
            'docs.*.required' => ['nullable', 'boolean'],
            'docs.*.remove' => ['nullable', 'boolean'],
        ], [
            'docs.*.name.max' => 'A document name can be up to '.MaintainingDocument::NAME_MAX.' characters.',
            'docs.*.note.max' => 'Instructions can be up to '.MaintainingDocument::NOTE_MAX.' characters.',
        ]);

        // Rows ticked "Remove", and blank new rows, are left out.
        $docs = [];
        $errors = [];
        $seen = [];
        foreach ((array) $request->input('docs', []) as $i => $row) {
            $name = trim(preg_replace('/\s+/u', ' ', (string) ($row['name'] ?? '')));
            if (! empty($row['remove']) || $name === '') {
                continue;
            }
            $key = mb_strtolower($name);
            if (isset($seen[$key])) {
                $errors["docs.$i.name"] = "“{$name}” is listed twice.";
            }
            $seen[$key] = true;
            if (empty($row['types'])) {
                $errors["docs.$i.types"] = "Choose at least one accepted file type for “{$name}”.";
            }
            $docs[] = MaintainingDocument::normalize([
                'name' => $name,
                'note' => $row['note'] ?? null,
                'types' => $row['types'] ?? [],
                'max_mb' => $row['max_mb'] ?? MaintainingDocument::DEFAULT_MB,
                'required' => (bool) ($row['required'] ?? false),
            ]);
        }
        if (count($docs) > MaintainingDocument::MAX_DOCUMENTS) {
            $errors['docs'] = 'List up to '.MaintainingDocument::MAX_DOCUMENTS.' documents.';
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        $result = $editor->apply($s, ['renewal_requirements' => $docs], [], $request->user());
        $synced = $result['changed'] ? $editor->syncChecklistSettings($s) : 0;
        // A new document joins the checklists of the current and coming semesters that have a deadline.
        $added = $result['changed'] ? app(RequirementChecklist::class)->syncScholarship($s->fresh()) : 0;

        return redirect()->route('provider.scholarships', ['s' => $s->id])->withFragment('maintaining')->with('status', match (true) {
            ! $result['changed'] => 'No changes to the maintaining documents.',
            $synced > 0 || $added > 0 => 'Maintaining documents saved.'
                .($synced ? ' '.$synced.' open checklist '.str('item')->plural($synced).' now use the new settings.' : '')
                .($added ? ' '.$added.' new checklist '.str('item')->plural($added).' added to semesters that already have a deadline.' : ''),
            default => 'Maintaining documents saved. They apply to every semester you set a deadline for on the Requirements page.',
        });
    }
}
