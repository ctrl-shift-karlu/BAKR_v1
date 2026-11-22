<?php

namespace App\Http\Controllers;

use App\Http\Requests\FinderCheckRequest;
use App\Matching\AnswerSanitizer;
use App\Matching\ApplicationSchema;
use App\Matching\MatchingService;
use App\Matching\ProgramCatalog;
use App\Models\Campus;
use App\Models\Program;
use App\Models\Scholarship;
use App\Models\ScholarshipMatch;
use App\Services\ExplanationService;
use Illuminate\View\View;

/**
 * Public Scholarship Finder: try BAKR without an account.
 *
 * Uses the SAME questions (ApplicationSchema) and the SAME rule engine (MatchingService, with the
 * rules from the `scholarships` table) as the student portal, so the result here is exactly what
 * a student would see after registering, and provider edits apply here too.
 * Nothing is saved: answers are matched in memory and only shown back to the visitor.
 */
class ScholarshipFinderController extends Controller
{
    public function show(): View
    {
        return view('finder.show', $this->formData());
    }

    public function check(FinderCheckRequest $request, MatchingService $matcher, ExplanationService $explainer): View
    {
        $answers = ProgramCatalog::enrich(AnswerSanitizer::clean($request->answers()));
        $scholarships = Scholarship::where('is_active', true)->orderBy('sort_order')->get();

        $rows = $matcher->evaluate($answers, $scholarships->map(fn ($s) => ['id' => $s->id, 'rules' => $s->rules]));

        // Unsaved match objects, so the page can reuse the portal's labels and built-in explanations.
        $order = array_flip(ScholarshipMatch::STATUS_ORDER);
        $matches = collect($rows)
            ->map(function (array $row) use ($scholarships) {
                $m = new ScholarshipMatch($row);
                $m->setRelation('scholarship', $scholarships->firstWhere('id', $row['scholarship_id']));

                return $m;
            })
            ->sortBy([fn ($a, $b) => $order[$a->status] <=> $order[$b->status], fn ($a, $b) => $b->score <=> $a->score])
            ->values();

        // Only matches are listed; when nothing matches, show the one-requirement-away scholarships.
        $listed = $matches->whereIn('status', ScholarshipMatch::LISTED)->values();

        return view('finder.results', [
            'matches' => $listed,
            'total' => $matches->count(),
            'counts' => collect(ScholarshipMatch::LISTED)->mapWithKeys(fn ($s) => [$s => $listed->where('status', $s)->count()]),
            'nearMisses' => $listed->isEmpty() ? ScholarshipMatch::nearMisses($matches) : collect(),
            'explainer' => $explainer,
        ]);
    }

    private function formData(): array
    {
        return [
            'sections' => ApplicationSchema::sections(),
            'campuses' => Campus::orderBy('campus_name')->get(['id', 'campus_name']),
            'programs' => Program::orderBy('program_name')->get(['id', 'program_name', 'campus_id']),
            'saved' => [],
        ];
    }
}
