<?php

namespace App\Http\Controllers\Provider;

use App\Matching\ProgramCatalog;
use App\Models\Scholar;
use App\Models\ScholarMessage;
use App\Models\ScholarRequirement;
use App\Models\StipendDisbursement;
use App\Services\Provider\RequirementChecklist;
use App\Services\Provider\ScholarRoster;
use App\Services\ScholarNotifier;
use App\Support\AcademicTerm;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** The provider's scholars: list, profile, and the data the provider maintains for each. */
class ProviderScholarController extends ProviderController
{
    public function index(Request $request): View
    {
        $scope = $this->scope($request);
        $term = AcademicTerm::current();

        $query = $scope->scholars()->with(['user', 'scholarship'])->withStanding()
            ->withCount([
                'requirements as reqs_total' => fn ($q) => $q->where('term', $term)->owed(),
                'requirements as reqs_approved' => fn ($q) => $q->where('term', $term)->where('status', 'approved'),
                'requirements as reqs_waiting' => fn ($q) => $q->where('term', $term)->where('status', 'submitted'),
            ])
            ->with(['disbursements' => fn ($q) => $q->where('term', AcademicTerm::currentSemester())]);

        if ($sid = (int) $request->query('scholarship')) {
            $query->where('scholarship_id', $scope->scholarship($sid)->id);
        }
        $status = $request->query('status');
        if (array_key_exists((string) $status, Scholar::STATUSES)) {
            $query->whereStanding($status);
        } else {
            $status = null;
        }
        if ($q = trim((string) $request->query('q'))) {
            $query->where(fn ($w) => $w->where('student_number', 'like', "%{$q}%")
                ->orWhere('scholar_code', 'like', '%'.Scholar::normalizeCode($q).'%')
                ->orWhere('full_name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%")));
        }
        // Scholar ID filter: IDs that START with what was typed ("SCID-2026" = that whole batch), or "none" = no ID yet.
        $code = Scholar::normalizeCode((string) $request->query('scholar_id'));
        if ($code !== null && mb_strtolower($code) === 'none') {
            $code = 'none';
            $query->whereNull('scholar_code');
        } elseif ($code !== null) {
            $query->where('scholar_code', 'like', addcslashes($code, '%_\\').'%');
        }

        return $this->page($scope, 'provider.scholars.index', [
            'scholars' => $query->orderBy('status')->orderByDesc('id')->paginate(25)->withQueryString(),
            'scholarships' => $scope->scholarships(),
            'term' => $term,
            'statusCounts' => collect(array_keys(Scholar::STATUSES))->mapWithKeys(fn ($k) => [$k => $scope->scholars()
                ->when($sid, fn ($q) => $q->where('scholarship_id', $sid))->whereStanding($k)->count()]),
            'filters' => ['scholarship' => $sid ?: null, 'status' => $status, 'q' => $q, 'scholar_id' => $code],
        ]);
    }

    public function show(Request $request, int $scholar): View
    {
        $scope = $this->scope($request);
        $s = $scope->scholar($scholar)->load(['user', 'scholarship', 'requirements']);

        return $this->page($scope, 'provider.scholars.show', [
            's' => $s,
            'requirements' => $s->requirements->sortBy('name')->sortByDesc('term')->groupBy('term'),
            'disbursements' => $s->disbursements()->orderByDesc('term')->orderBy('period_month')->get(),
            'campusPrograms' => ProgramCatalog::byCampus(),
            'genders' => Scholar::GENDERS,
            'term' => AcademicTerm::current(),
            'startTerms' => AcademicTerm::startOptions(),
        ]);
    }

    /**
     * Save ONE section of the scholar's profile (owner, 2026-11-20; same pattern as My scholarships):
     * the hidden "section" input names it and only that section's fields are validated and saved, so
     * editing one card never touches another. Every field of a section is required, except the stipend.
     */
    public function update(Request $request, int $scholar): RedirectResponse
    {
        $scope = $this->scope($request);
        $s = $scope->scholar($scholar)->load('scholarship');
        $options = $s->scholarship->grantOptions();
        $hasTypes = count($options) > 1;
        $hasPay = $scope->has('disbursements');

        $section = $request->input('section');
        abort_unless(is_string($section) && array_key_exists($section, self::SECTIONS), 422);

        // Status is NOT edited here: terminating / reinstating is its own confirmed action (status()).
        $rules = match ($section) {
            'school' => [
                // Typed freely (no format); unique within the scholarship (checked below).
                'scholar_code' => ['required', 'string', 'max:'.Scholar::SCHOLAR_CODE_MAX],
                'student_number' => ['required', 'string', 'max:30'],
                // 1st–4th year; a value saved before (e.g. an approved applicant's "5") may stay as it is.
                'year_level' => ['required', Rule::in([...array_keys(Scholar::YEAR_LEVELS), (string) $s->year_level])],
                'campus' => ['required', 'string', 'max:80', ScholarRoster::campusRule()],
                'program' => ['required', 'string', 'max:150', ScholarRoster::programRule()],
            ],
            'personal' => Arr::only(ScholarRoster::personalRules(), ['birthdate', 'gender']),
            'contact' => Arr::only(ScholarRoster::personalRules(), ['mobile', 'address']),
            'scholarship' => array_filter([
                // "Scholar since" (owner, 2026-11-21): decides their first checklist and first stipend.
                // (left out of the request = unchanged; sent empty = an error).
                'started_term' => ['sometimes', ...ScholarRoster::startedTermRule($s->started_term, true)],
                // Only scholarships with more than one published type (e.g. CMSP Full / Half) have this field.
                'grant_type' => $hasTypes ? ['required', Rule::in(array_keys($options))] : null,
                // Providers without Disbursements have no stipend field.
                'stipend_amount' => $hasPay ? ['nullable', 'numeric', 'min:0', 'max:1000000'] : null,
            ]),
        };

        $data = $request->validate($rules, ScholarRoster::personalMessages() + ProviderScholarRosterController::startedTermMessages() + [
            'student_number.required' => 'Type the scholar\'s student number.',
            'year_level.required' => 'Choose the scholar\'s year level.',
            'campus.required' => 'Choose the campus the scholar studies at.',
            'program.required' => 'Choose the scholar\'s program.',
        ], ['scholar_code' => 'Scholar ID', 'student_number' => 'student number', 'year_level' => 'year level', 'grant_type' => 'grant type',
            'stipend_amount' => 'stipend', 'birthdate' => 'birthday', 'mobile' => 'mobile number', 'started_term' => 'Scholar since']);

        if ($section === 'school') {
            $data['scholar_code'] = Scholar::normalizeCode($data['scholar_code']);
            if ($codeError = ScholarRoster::scholarCodeError($s->scholarship, $data['scholar_code'], $s->id)) {
                return back()->withInput()->withErrors(['scholar_code' => $codeError]);
            }
            $data['student_number'] = trim(preg_replace('/\s+/u', ' ', $data['student_number']));
            $data['program'] = ProgramCatalog::canonicalName($data['program']) ?? $data['program'];
            $data['campus'] = ProgramCatalog::canonicalCampus($data['campus']) ?? $data['campus'];
            // The campus and program must go together (a program the campus does not offer is refused).
            if ($mismatch = ScholarRoster::campusProgramError($data['campus'], $data['program'])) {
                return back()->withInput()->withErrors(['program' => $mismatch]);
            }
        }
        if ($section === 'contact') {
            $data['mobile'] = ScholarRoster::normalizeMobile($data['mobile']);
            $data['address'] = trim(preg_replace('/\s+/u', ' ', $data['address']));
        }
        if ($section === 'scholarship') {
            // A new grant type brings its published stipend, unless the provider also typed a different amount.
            $newGrant = $data['grant_type'] ?? null;
            if ($newGrant && $newGrant !== $s->grant_type && $hasPay
                && (float) ($data['stipend_amount'] ?? 0) === (float) ($s->stipend_amount ?? 0)) {
                $data['stipend_amount'] = $options[$newGrant]['amount'] ?? $data['stipend_amount'] ?? null;
            }
        }

        $s->update($data);
        $this->tellAboutCorrection($s, $section);
        $note = $s->wasChanged('started_term') ? ' '.$this->followStart($s) : '';

        return redirect()->route('provider.scholars.show', $s->id)->withFragment($section)
            ->with('status', self::SECTIONS[$section].' saved.'.$note);
    }

    /**
     * "Scholar since" changed: checklist items of semesters BEFORE the new start that are still empty (not submitted,
     * no file) are removed; anything with a file or a review stays. The semesters since the start that already have a
     * deadline get their checklist. Stipend records are never removed (one before the start stays locked).
     */
    private function followStart(Scholar $s): string
    {
        $before = array_slice(AcademicTerm::upTo((string) $s->started_term), 1);
        $removed = $before ? $s->requirements()->whereIn('term', $before)->where('status', 'missing')->whereNull('file_path')->delete() : 0;
        $added = app(RequirementChecklist::class)->syncScholar($s);

        return collect([
            $removed ? "{$removed} empty checklist ".str('item')->plural($removed).' from before '.$s->started_term.' removed.' : null,
            $added ? "{$added} checklist ".str('item')->plural($added).' added for the semesters since then.' : null,
        ])->filter()->implode(' ');
    }

    /**
     * The scholar asked for a correction of this card (an open request in Messages) and the provider just changed
     * it: tell the scholar (owner, 2026-11-21). Nothing changed, or nothing was asked → no notice. The request
     * itself stays open until the provider replies or marks it handled.
     */
    private function tellAboutCorrection(Scholar $s, string $section): void
    {
        if (! $s->wasChanged()) {
            return;
        }
        // The Scholar ID is on the School card, but scholars ask about it under "Scholarship information".
        $topics = $s->wasChanged('scholar_code') ? [$section, 'scholarship'] : [$section];
        $asked = ScholarMessage::where('scholar_id', $s->id)->whereNull('handled_at')->whereIn('topic', $topics)->exists();
        if (! $asked) {
            return;
        }
        $card = self::SECTIONS[$section];
        ScholarNotifier::send($s, 'correction', "Your {$card} was updated",
            "{$s->scholarship->provider} updated your ".mb_strtolower($card).' after your correction request. Check that it is right on your Profile.',
            route('scholar.profile').'#'.$section);
    }

    /** The editable cards of a scholar's profile (?edit=<key>) and their titles. */
    public const SECTIONS = [
        'school' => 'School information',
        'personal' => 'Personal information',
        'contact' => 'Contact information',
        'scholarship' => 'Scholarship details',
    ];

    /**
     * Change a scholar's status (owner's rules 2026-11-18/19; scope() makes another provider's scholar a 404):
     *   terminated  every provider, for its own scholars
     *   inactive    only providers that do NOT collect documents in BAKR (they check documents in person
     *               at the DNSC office); for the others Inactive is automatic (overdue documents)
     *   active      reinstate / mark active again
     */
    public function status(Request $request, int $scholar): RedirectResponse
    {
        $scope = $this->scope($request);
        $s = $scope->scholar($scholar);
        $allowed = $scope->has('requirements') ? ['active', 'terminated'] : Scholar::STORED_STATUSES;
        $data = $request->validate(['status' => ['required', Rule::in($allowed)]], [
            'status.in' => 'Inactive is set automatically for your scholars: it follows from maintaining documents past their due date.',
        ]);

        if ($data['status'] === $s->status) {
            return back();
        }
        $s->update(['status' => $data['status']]);
        ScholarNotifier::statusChanged($s->loadMissing('scholarship'), $data['status']);   // in-portal note + e-mail (2026-11-21)
        if ($s->isInGoodStanding()) {
            app(RequirementChecklist::class)->syncScholar($s);   // reinstated: the checklists opened in the meantime
        }

        return back()->with('status', match ($data['status']) {
            'terminated' => $s->displayName().'\'s scholarship was terminated. They no longer get stipends or requirement checklists.',
            'inactive' => $s->displayName().' is now marked Inactive. Mark them active again once their documents are in order at the DNSC office.',
            default => $s->displayName().' is now an active scholar.',
        });
    }

    /** Labels for the views (kept here so the views don't reach into models). */
    public static function labels(): array
    {
        return [
            'scholar' => Scholar::STATUSES,
            'requirement' => ScholarRequirement::STATUSES,
            'disbursement' => StipendDisbursement::STATUSES,
        ];
    }
}
