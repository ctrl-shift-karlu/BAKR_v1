<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScholarshipApplicationRequest;
use App\Matching\ApplicationSchema;
use App\Models\Campus;
use App\Models\Program;
use App\Models\ScholarshipApplication;
use App\Models\ScholarshipMatch;
use App\Models\StudentConsent;
use App\Services\ScholarshipApplicationService;
use App\Support\AcademicTerm;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentApplicationController extends Controller
{
    public function show(Request $request): View
    {
        $current = ScholarshipApplication::where('user_id', $request->user()->id)->where('is_current', true)->first();
        $consent = StudentConsent::where('user_id', $request->user()->id)->where('consent_type', StudentConsent::TYPE_DATA_PRIVACY)->first();

        // "Use these answers again" (My profile › Saved versions): fill the form in with an EARLIER version of
        // the student's own answers. Nothing is saved until they submit, which makes it a new version.
        $from = null;
        if ($current && $request->filled('version')) {
            $from = ScholarshipApplication::where('user_id', $request->user()->id)
                ->where('version', (int) $request->query('version'))->where('is_current', false)->first();
        }

        return view('student.application', [
            'sections' => ApplicationSchema::sections(),
            'campuses' => Campus::orderBy('campus_name')->get(['id', 'campus_name']),
            'programs' => Program::orderBy('program_name')->get(['id', 'program_name', 'campus_id']),
            'saved' => ($from ?? $current)?->answers ?? [],
            'fromVersion' => $from,
            'application' => $current,
            'user' => $request->user(),
            'term' => AcademicTerm::currentSemester(),
            // The Privacy Policy agreement is asked on every submit; the last date is shown under it.
            'hasConsent' => $consent !== null,
            'consentAt' => $consent?->accepted_at,
        ]);
    }

    /**
     * Printable copy of the current form: the answers, the certification and the documents
     * listed by the scholarships the student matched. Only the signed-in student's own form.
     */
    public function printable(Request $request): View|RedirectResponse
    {
        $application = ScholarshipApplication::where('user_id', $request->user()->id)->where('is_current', true)->first();
        if (! $application) {
            return redirect()->route('student.application.show');
        }

        $matches = $application->matches()->with('scholarship')
            ->whereIn('status', ScholarshipMatch::LISTED)->orderByDesc('score')->get()
            ->filter(fn ($m) => $m->scholarship !== null);

        return view('student.application-print', [
            'application' => $application,
            'sections' => ApplicationSchema::sections(),
            'user' => $request->user(),
            'matches' => $matches,
        ]);
    }

    public function store(ScholarshipApplicationRequest $request, ScholarshipApplicationService $service): RedirectResponse
    {
        $application = $service->submit($request->user(), $request->answers(), $request->boolean('privacy_consent'));

        $message = $application->wasRecentlyCreated
            ? 'Your matching form is saved and your matches are ready.'
            : 'You didn\'t change anything, so your results stay the same.';

        return redirect()->route('student.results')->with('status', $message);
    }
}
