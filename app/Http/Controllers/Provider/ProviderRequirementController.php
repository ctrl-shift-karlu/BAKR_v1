<?php

namespace App\Http\Controllers\Provider;

use App\Models\Scholar;
use App\Models\ScholarshipDeadline;
use App\Services\Provider\RequirementChecklist;
use App\Support\AcademicTerm;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Maintaining documents per semester: each scholarship's deadline (opens the checklist), review uploads, preview / download files. */
class ProviderRequirementController extends ProviderController
{
    /**
     * Requirements page for one semester (?term=, optional ?scholarship=): each scholarship's deadline, the scholars
     * with a checklist (25 per page), status totals, overdue items and the uploads waiting for review.
     */
    public function index(Request $request): View
    {
        $scope = $this->scope($request);
        $term = $this->term($request);
        $sid = (int) $request->query('scholarship');
        $scholarship = $sid ? $scope->scholarship($sid) : null;

        $scholars = $scope->scholars()->with(['user', 'scholarship'])
            ->when($scholarship, fn ($q) => $q->where('scholarship_id', $scholarship->id))
            ->whereHas('requirements', fn ($q) => $q->where('term', $term))
            ->with(['requirements' => fn ($q) => $q->where('term', $term)->orderBy('name')])
            ->orderBy('scholarship_id')->orderBy('id')
            ->paginate(25)->withQueryString();

        $base = $scope->requirements()->where('term', $term)
            ->when($scholarship, fn ($q) => $q->whereHas('scholar', fn ($s) => $s->where('scholarship_id', $scholarship->id)));

        // Each scholarship's own deadline for this semester (owner, 2026-11-21), with how many scholars owe it.
        $listed = $scholarship ? collect([$scholarship]) : $scope->scholarships();
        $deadlines = ScholarshipDeadline::where('term', $term)->whereIn('scholarship_id', $listed->pluck('id'))->get()->keyBy('scholarship_id');
        $owing = $listed->mapWithKeys(fn ($s) => [$s->id => RequirementChecklist::scholarsFor($s, $term)->count()]);

        return $this->page($scope, 'provider.requirements', [
            'term' => $term,
            'terms' => AcademicTerm::options(),
            'scholarships' => $scope->scholarships(),
            'scholarship' => $scholarship,
            'listed' => $listed,
            'deadlines' => $deadlines,
            'owing' => $owing,
            'suggested' => AcademicTerm::endDate($term),
            'scholars' => $scholars,
            'summary' => (clone $base)->owed()->selectRaw('status, COUNT(*) as n')->groupBy('status')->pluck('n', 'status'),
            'overdue' => (clone $base)->where('is_optional', false)->whereIn('status', Scholar::OVERDUE_REQUIREMENT_STATES)->whereDate('due_on', '<', now()->toDateString())->count(),
            'waiting' => (clone $base)->with(['scholar.user', 'scholar.scholarship'])->where('status', 'submitted')->orderBy('submitted_at')->get(),
        ]);
    }

    /**
     * Sets (or changes) ONE scholarship's maintaining-documents deadline for ONE semester (owner, 2026-11-21).
     * Saving it opens that semester's checklist for the scholarship's scholars and e-mails them the date
     * (RequirementChecklist::setDeadline). After the date, a required document still not submitted makes
     * the scholar Inactive.
     */
    public function deadline(Request $request, RequirementChecklist $checklist): RedirectResponse
    {
        $scope = $this->scope($request);
        $data = $request->validate([
            'scholarship_id' => ['required', 'integer'],
            'term' => ['required', Rule::in(AcademicTerm::options())],
            'due_on' => ['required', 'date_format:Y-m-d', 'after_or_equal:2000-01-01', 'before_or_equal:2100-12-31'],
        ], [
            'due_on.required' => 'Choose the date the documents are due.',
            'due_on.date_format' => 'Choose the deadline as a date.',
        ], ['due_on' => 'deadline']);
        $scholarship = $scope->scholarship((int) $data['scholarship_id']);   // another provider's scholarship = 404
        $term = $data['term'];
        $back = redirect()->route('provider.requirements', ['term' => $term]);

        if (! $scholarship->maintainingDocuments()) {
            return $back->withErrors(['due_on' => "{$scholarship->shortName()} has no maintaining documents yet. Add them under My scholarships first."]);
        }

        $result = $checklist->setDeadline($scholarship, $term, $data['due_on'], $request->user());
        $date = Carbon::parse($data['due_on'])->format('M j, Y');
        $told = $result['notified'] ? " {$result['notified']} ".str('scholar')->plural($result['notified']).' notified by e-mail.' : '';

        return $back->with('status', match (true) {
            $result['changed'] => "{$scholarship->shortName()}: {$term} documents are due {$date}.{$told}",
            $result['created'] > 0 => "{$scholarship->shortName()}: the deadline stays {$date}; {$result['created']} new checklist ".str('item')->plural($result['created']).' added.'.$told,
            default => "{$scholarship->shortName()}: the deadline is already {$date}. Nothing changed.",
        });
    }

    /**
     * Approves an uploaded document or sends it back ("needs revision", with a required note).
     * An item without an uploaded file cannot be reviewed, because approving it would unlock the stipend.
     *
     * @param  int  $requirement  the scholar_requirements id from the URL (looked up inside the provider's scope)
     */
    public function review(Request $request, int $requirement): RedirectResponse
    {
        $row = $this->scope($request)->requirement($requirement);
        $data = $request->validate([
            // The two actions the review form offers (provider/partials/review-form).
            'status' => ['required', Rule::in(['approved', 'needs_revision'])],
            'review_note' => ['nullable', 'string', 'max:500', Rule::requiredIf($request->input('status') === 'needs_revision')],
        ]);
        // Only an uploaded document can be reviewed: approving an empty item would unlock the stipend
        // (Scholar::releaseBlocker) for a document the scholar never sent.
        if (! $row->hasFile()) {
            return back()->withErrors(['status' => "{$row->name} has not been uploaded yet, so there is nothing to review."]);
        }

        $row->update([
            'status' => $data['status'],
            'review_note' => $data['review_note'] ?? null,
            'reviewed_at' => now(),
        ]);

        return back()->with('status', "{$row->name}: {$row->statusLabel()}.");
    }

    /**
     * Downloads a scholar's uploaded file, or with ?inline=1 shows it in the preview so the provider need
     * not download it just to look. Same rules as the scholar side (ScholarRequirement::fileResponse()).
     *
     * @param  int  $requirement  the scholar_requirements id from the URL (looked up inside the provider's scope)
     */
    public function download(Request $request, int $requirement): StreamedResponse
    {
        return $this->scope($request)->requirement($requirement)->fileResponse($request->boolean('inline'));
    }
}
