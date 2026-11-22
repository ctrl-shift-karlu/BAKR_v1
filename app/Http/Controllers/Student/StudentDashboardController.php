<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Matching\ApplicationSchema;
use App\Models\ScholarshipApplication;
use App\Models\ScholarshipMatch;
use App\Services\AiMatchService;
use App\Services\ExplanationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Student portal pages built on the current answers: Dashboard, My matches, My profile (with saved versions),
 * and the background AI requests (Gemini matcher, AI explanations). Always scoped to the signed-in student.
 */
class StudentDashboardController extends Controller
{
    /**
     * @param  ExplanationService  $explainer  built-in and AI explanations of the matches
     */
    public function __construct(private ExplanationService $explainer) {}

    /** Dashboard: match counts, the top four matches, the matches that need more information and the overview text. */
    public function index(Request $request): View|RedirectResponse
    {
        $application = $this->current($request);
        if (! $application) {
            return $this->formFirst('Dashboard');
        }

        $matches = $this->sortedMatches($application);

        return view('student.dashboard', [
            'user' => $request->user(),
            'application' => $application,
            'counts' => collect(ScholarshipMatch::STATUS_ORDER)->mapWithKeys(fn ($s) => [$s => $matches->where('status', $s)->count()]),
            'top' => $matches->whereIn('status', ['eligible', 'possible'])->take(4)->values(),
            'needsInfo' => $matches->where('status', 'needs_info')->values(),
            'nearMisses' => $matches->whereIn('status', ScholarshipMatch::LISTED)->isEmpty() ? ScholarshipMatch::nearMisses($matches)->take(4) : collect(),
            'summary' => $application->ai_summary ?: $this->explainer->quickSummary($application),
            'summaryIsAi' => (bool) $application->ai_summary,
            'aiAvailable' => ExplanationService::aiAvailable(),
        ]);
    }

    /** My matches: every listed match (optionally ?status=), or the near misses when nothing matches. */
    public function results(Request $request): View|RedirectResponse
    {
        $application = $this->current($request);
        if (! $application) {
            return $this->formFirst('My matches');
        }

        // Only matches are listed; "not a match right now" is left out. When nothing matches,
        // the page shows the scholarships that miss by exactly one requirement instead.
        $filter = $request->query('status');
        $filter = in_array($filter, ScholarshipMatch::LISTED, true) ? $filter : null;
        $sorted = $this->sortedMatches($application);
        $all = $sorted->whereIn('status', ScholarshipMatch::LISTED)->values();

        return view('student.results', [
            'application' => $application,
            'matches' => $filter ? $all->where('status', $filter)->values() : $all,
            'counts' => collect(ScholarshipMatch::LISTED)->mapWithKeys(fn ($s) => [$s => $all->where('status', $s)->count()]),
            'nearMisses' => $all->isEmpty() ? ScholarshipMatch::nearMisses($sorted) : collect(),
            'filter' => $filter,
            'explainer' => $this->explainer,
            'aiAvailable' => ExplanationService::aiAvailable(),
        ]);
    }

    /** My profile: the current answers and the list of saved versions. */
    public function profile(Request $request): View|RedirectResponse
    {
        $application = $this->current($request);
        if (! $application) {
            return $this->formFirst('My profile');
        }

        return view('student.profile', [
            'user' => $request->user(),
            'application' => $application,
            'sections' => ApplicationSchema::sections(),
            'history' => $this->history($request),
        ]);
    }

    /**
     * One saved version of the student's OWN answers (looked up by version number inside their account,
     * else 404), with the answers that differ from the current version marked. From here the student can
     * reuse these answers: the matching form opens filled in with them (StudentApplicationController::show).
     */
    public function version(Request $request, int $version): View|RedirectResponse
    {
        $current = $this->current($request);
        if (! $current) {
            return $this->formFirst('My profile');
        }

        $saved = ScholarshipApplication::where('user_id', $request->user()->id)->where('version', $version)->firstOrFail();
        if ($saved->is_current) {
            return redirect()->route('student.profile');
        }

        return view('student.profile-version', [
            'user' => $request->user(),
            'saved' => $saved,
            'current' => $current,
            'sections' => ApplicationSchema::sections(),
            'changed' => ApplicationSchema::changedFields($saved->answers, $current->answers),
            'history' => $this->history($request),
        ]);
    }

    /**
     * Every saved version of the signed-in student's answers, newest first, each with how many answers
     * changed from the version before it (the first version: null).
     *
     * @return Collection<int, array{version:int, is_current:bool, submitted_at:mixed, changes:?int}>
     */
    private function history(Request $request): Collection
    {
        $versions = ScholarshipApplication::where('user_id', $request->user()->id)->orderBy('version')
            ->get(['id', 'version', 'is_current', 'answers', 'submitted_at']);

        $rows = [];
        $previous = null;
        foreach ($versions as $v) {
            $rows[] = [
                'version' => (int) $v->version,
                'is_current' => (bool) $v->is_current,
                'submitted_at' => $v->submitted_at,
                'changes' => $previous ? count(ApplicationSchema::changedFields($previous, $v->answers)) : null,
            ];
            $previous = $v->answers;
        }

        return collect(array_reverse($rows));
    }

    /**
     * Writes (or returns the cached) AI explanation of one match. JSON for the results page, a redirect otherwise.
     *
     * @param  int  $match  the scholarship_matches id from the URL (must belong to the student's current answers)
     */
    public function explainMatch(Request $request, int $match): RedirectResponse|JsonResponse
    {
        // Only a match that belongs to the signed-in student's current answers can be found (otherwise 404).
        $m = ScholarshipMatch::with('scholarship')
            ->whereHas('application', fn ($q) => $q->where('user_id', $request->user()->id)->where('is_current', true))
            ->findOrFail($match);

        $ok = $this->explainer->forMatch($m);

        // The results page asks in the background when a match is opened (student-ai.js).
        if ($request->expectsJson()) {
            return response()->json($ok ? ['ok' => true, 'text' => ExplanationService::aiText($m->refresh())] : ['ok' => false], $ok ? 200 : 503);
        }

        return redirect(route('student.results', array_filter(['status' => $request->query('status')])).'#match-'.$m->id)
            ->with($ok ? 'status' : 'notice', $ok ? 'Here is the AI explanation.' : 'The AI explainer is not available right now, so the built-in explanation is shown instead.');
    }

    /**
     * Run the Gemini AI matcher on the signed-in student's current answers (asked for in the
     * background by student-ai.js while the results say "AI review in progress"). Never takes an
     * id from the URL; a finished review is not run again.
     */
    public function aiMatch(Request $request, AiMatchService $ai): RedirectResponse|JsonResponse
    {
        $application = $this->current($request);
        abort_unless($application, 404);

        $ok = $ai->run($application);
        $state = $application->refresh()->ai_match_status;
        // Not done but still pending: another request (a second tab, a reload) is running the review right now.
        // 202 = "still working", so the page waits and asks again instead of showing a failure.
        $busy = ! $ok && $state === ScholarshipApplication::AI_PENDING;

        if ($request->expectsJson()) {
            return response()->json(['ok' => $ok, 'state' => $state], $ok ? 200 : ($busy ? 202 : 503));
        }
        if ($busy) {
            return redirect()->route('student.results')->with('status', 'The AI matcher is still reviewing your matching form. This page updates when it is done.');
        }

        return redirect()->route('student.results')
            ->with($ok ? 'status' : 'notice', $ok
                ? 'The AI matcher has reviewed your matching form.'
                : 'The AI matcher could not be reached, so these results come from BAKR\'s rule check only. Try again in a minute.');
    }

    /** Writes (or returns the cached) AI overview of all matches. JSON for the dashboard, a redirect otherwise. */
    public function explainAll(Request $request): RedirectResponse|JsonResponse
    {
        $application = $this->current($request);
        abort_unless($application, 404);

        $ok = $this->explainer->forApplication($application);

        if ($request->expectsJson()) {
            return response()->json($ok ? ['ok' => true, 'text' => $application->refresh()->ai_summary] : ['ok' => false], $ok ? 200 : 503);
        }

        return redirect()->route('student.dashboard')
            ->with($ok ? 'status' : 'notice', $ok ? 'Here is the AI overview.' : 'The AI explainer is not available right now, so the built-in overview is shown instead.');
    }

    /**
     * Dashboard / My matches / My profile need answers first: back to the matching form, which then
     * shows the "Finish your matching form first" modal (student.layout, <dialog data-auto-open>)
     * instead of a banner. $page names what the student tried to open.
     */
    private function formFirst(string $page): RedirectResponse
    {
        return redirect()->route('student.application.show')->with('form_first', $page);
    }

    /**
     * The signed-in student's current answers, or null. An application that never had an AI review (saved
     * before the matcher existed, or while it was off and a key was added since) is queued for one here.
     */
    private function current(Request $request): ?ScholarshipApplication
    {
        // Always scoped to the signed-in student; no ID is ever read from the URL.
        $application = ScholarshipApplication::currentFor($request->user()->id)->first();

        // Saved before the AI matcher existed (2026-11-19), or saved while it was off and a key was
        // added since: queue it for an AI review on this visit.
        if ($application && ($application->ai_match_status === null
            || ($application->ai_match_status === ScholarshipApplication::AI_OFF && AiMatchService::available()))) {
            $application->update(['ai_match_status' => AiMatchService::initialState()]);
        }

        return $application;
    }

    /** @return Collection<int,ScholarshipMatch> best matches first */
    private function sortedMatches(ScholarshipApplication $application): Collection
    {
        $order = array_flip(ScholarshipMatch::STATUS_ORDER);

        return $application->matches()->with('scholarship')->get()
            ->sortBy([fn ($a, $b) => $order[$a->status] <=> $order[$b->status], fn ($a, $b) => $b->score <=> $a->score])
            ->values();
    }
}
