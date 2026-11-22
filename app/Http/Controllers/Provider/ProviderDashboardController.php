<?php

namespace App\Http\Controllers\Provider;

use App\Models\Scholar;
use App\Models\ScholarRequirement;
use App\Models\StipendDisbursement;
use App\Support\AcademicTerm;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Overview: the provider's scholarships, this term's stipends and requirements at a glance. */
class ProviderDashboardController extends ProviderController
{
    /**
     * Overview page: per scholarship, the current scholars, this semester's stipends and documents, plus the five
     * newest scholars, the Active / Inactive / Terminated totals and the five uploads waiting for review.
     */
    public function index(Request $request): View
    {
        $scope = $this->scope($request);
        $term = AcademicTerm::current();
        $scholarships = $scope->scholarships();

        $scholars = $scope->scholars()->selectRaw('scholarship_id, status, COUNT(*) as n')->groupBy('scholarship_id', 'status')->get();
        $pay = $scope->disbursements()->where('term', AcademicTerm::current())
            ->join('scholars', 'scholars.id', '=', 'stipend_disbursements.scholar_id')
            ->selectRaw('scholars.scholarship_id as sid, stipend_disbursements.status as st, COUNT(*) as n, SUM(stipend_disbursements.amount) as total')
            ->groupBy('scholars.scholarship_id', 'stipend_disbursements.status')->get();
        $reqs = $scope->requirements()->owed()->where('term', $term)
            ->join('scholars', 'scholars.id', '=', 'scholar_requirements.scholar_id')
            ->selectRaw('scholars.scholarship_id as sid, scholar_requirements.status as st, COUNT(*) as n')
            ->groupBy('scholars.scholarship_id', 'scholar_requirements.status')->get();

        $perScholarship = $scholarships->mapWithKeys(fn ($s) => [$s->id => [
            'scholars' => (int) $scholars->where('scholarship_id', $s->id)->whereIn('status', Scholar::CURRENT)->sum('n'),
            'pay' => collect(array_keys(StipendDisbursement::STATUSES))->mapWithKeys(fn ($st) => [$st => (int) $pay->where('sid', $s->id)->where('st', $st)->sum('n')])->all(),
            'released_total' => (float) $pay->where('sid', $s->id)->where('st', 'released')->sum('total'),
            'reqs' => collect(array_keys(ScholarRequirement::STATUSES))->mapWithKeys(fn ($st) => [$st => (int) $reqs->where('sid', $s->id)->where('st', $st)->sum('n')])->all(),
        ]]);

        return $this->page($scope, 'provider.dashboard', [
            'term' => $term,
            'scholarships' => $scholarships,
            'perScholarship' => $perScholarship,
            'releasedTotal' => (float) $pay->where('st', 'released')->sum('total'),
            'recentScholars' => $scope->scholars()->with(['user', 'scholarship'])->withStanding()->latest('id')->take(5)->get(),
            'standing' => collect(array_keys(Scholar::STATUSES))->mapWithKeys(fn ($k) => [$k => $scope->scholars()->whereStanding($k)->count()]),
            'awaitingReview' => $scope->has('requirements')
                ? $scope->requirements()->with(['scholar.user', 'scholar.scholarship'])->where('status', 'submitted')->latest('submitted_at')->take(5)->get()
                : collect(),
        ]);
    }
}
