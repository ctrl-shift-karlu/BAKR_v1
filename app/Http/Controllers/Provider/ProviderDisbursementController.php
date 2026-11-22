<?php

namespace App\Http\Controllers\Provider;

use App\Models\Scholar;
use App\Models\StipendDisbursement;
use App\Support\AcademicTerm;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Stipend disbursement per semester: who has been released and who has not yet.
 * Stipends are released per semester, or per month within a semester (DNSC has no Midyear).
 * A record is "Not yet released" or "Released"; the Release button is the only change (owner, 2026-11-22:
 * no On hold, no status dropdown). It works only when Scholar::releaseBlocker() is null: every maintaining
 * requirement of that semester has been approved (owner's rule 2026-11-18).
 */
class ProviderDisbursementController extends ProviderController
{
    public function index(Request $request): View
    {
        $scope = $this->scope($request);
        $term = $this->semester($request);
        $status = $request->query('status');
        $status = array_key_exists((string) $status, StipendDisbursement::STATUSES) ? $status : null;

        $base = $scope->disbursements()->where('term', $term);
        $summary = (clone $base)->selectRaw('status, COUNT(*) as n, SUM(amount) as total')->groupBy('status')->get()->keyBy('status');

        // Requirements of this semester: they decide whether a row may be released (releaseBlocker).
        $rows = (clone $base)->with(['scholar.user', 'scholar.scholarship', 'scholar.requirements' => fn ($q) => $q->where('term', $term)])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByRaw("CASE status WHEN 'pending' THEN 0 ELSE 1 END")
            ->orderBy('scholar_id')->orderBy('period_month')
            ->paginate(30)->withQueryString();

        // Good-standing scholars who had started by this semester, with a stipend set but no row for it yet.
        $missing = $scope->scholars()->whereIn('status', Scholar::GOOD_STANDING)->startedBy($term)
            ->whereNotNull('stipend_amount')
            ->whereDoesntHave('disbursements', fn ($q) => $q->where('term', $term))
            ->count();
        $noAmount = $scope->scholars()->whereIn('status', Scholar::GOOD_STANDING)->startedBy($term)->whereNull('stipend_amount')->count();

        return $this->page($scope, 'provider.disbursements', [
            'term' => $term,
            'terms' => AcademicTerm::semesterOptions(),
            'releaseTypes' => StipendDisbursement::RELEASE_TYPES,
            'months' => AcademicTerm::months($term),
            'status' => $status,
            'summary' => $summary,
            'rows' => $rows,
            'missing' => $missing,
            'noAmount' => $noAmount,
        ]);
    }

    /**
     * Creates "not yet released" records for every good-standing scholar who had started by the semester
     * ("Scholar since", owner 2026-11-21), has a stipend amount and no record for the semester yet: one record
     * (per semester) or one per month of the semester (per month). Each record's amount is the scholar's
     * stipend amount per release. A semester may be prepared late (stipends are often released after it ends).
     */
    public function generate(Request $request): RedirectResponse
    {
        $scope = $this->scope($request);
        $data = $request->validate([
            'term' => ['required', Rule::in(AcademicTerm::semesterOptions())],
            'release_type' => ['required', Rule::in(array_keys(StipendDisbursement::RELEASE_TYPES))],
        ]);
        $term = $data['term'];
        $periods = $data['release_type'] === 'monthly' ? AcademicTerm::months($term) : [''];

        $created = 0;
        $scholars = 0;
        DB::transaction(function () use ($scope, $term, $periods, $data, $request, &$created, &$scholars) {
            $scope->scholars()->whereIn('status', Scholar::GOOD_STANDING)->startedBy($term)->whereNotNull('stipend_amount')
                ->whereDoesntHave('disbursements', fn ($q) => $q->where('term', $term))
                ->each(function (Scholar $s) use ($term, $periods, $data, $request, &$created, &$scholars) {
                    foreach ($periods as $month) {
                        $s->disbursements()->create([
                            'term' => $term, 'release_type' => $data['release_type'], 'period_month' => $month,
                            'amount' => $s->stipend_amount, 'status' => 'pending', 'recorded_by' => $request->user()->id,
                        ]);
                        $created++;
                    }
                    $scholars++;
                });
        });

        $how = strtolower(StipendDisbursement::RELEASE_TYPES[$data['release_type']]);

        return redirect()->route('provider.disbursements', ['term' => $term])
            ->with('status', $created
                ? "Prepared {$created} stipend ".str('record')->plural($created)." ({$how}) for {$scholars} ".str('scholar')->plural($scholars)." in {$term}."
                : 'Every eligible scholar already has a record for this semester.');
    }

    /** The row's Release button: marks one record released (dated now), if nothing blocks it. */
    public function releaseOne(Request $request, int $disbursement): RedirectResponse
    {
        $row = $this->scope($request)->disbursement($disbursement)->load('scholar.scholarship');
        $data = $request->validate(['reference' => ['nullable', 'string', 'max:100']]);
        $name = $row->scholar->displayName();

        if ($row->status === 'released') {
            return back()->with('notice', "{$name}'s stipend was already released.");
        }
        if ($block = $row->scholar->releaseBlock($row->term)) {
            [$kind, $blocker] = $block;

            return back()->withErrors(['release' => match ($kind) {
                'terminated' => "{$name}'s stipend can't be released: {$blocker}. Reinstate the scholar first.",
                'inactive' => "{$name}'s stipend can't be released yet: {$blocker}. Mark them active on their profile first.",
                'not_started' => "{$name}'s stipend can't be released: {$blocker}. Check \"Scholar since\" on their profile.",
                'no_deadline' => "{$name}'s stipend can't be released yet: {$blocker}. Set it on the Requirements page first.",
                'not_listed' => "{$name}'s stipend can't be released yet: {$blocker}. Save the {$row->term} deadline again on the Requirements page to add them.",
                default => "{$name}'s stipend can't be released yet: {$blocker}. Approve every requirement on the Requirements page first.",
            }]);
        }

        $row->update($this->released($row, $data['reference'] ?? $row->reference, $request->user()->id));

        return back()->with('status', "{$name}: stipend released.");
    }

    /** Marks several selected rows as released in one go. */
    public function release(Request $request): RedirectResponse
    {
        $scope = $this->scope($request);
        $data = $request->validate([
            'ids' => ['required', 'array', 'max:200'],
            'ids.*' => ['integer'],
            'reference' => ['nullable', 'string', 'max:100'],
        ]);

        $blocked = 0;
        $count = DB::transaction(function () use ($scope, $data, $request, &$blocked) {
            $rows = $scope->disbursements()->whereIn('stipend_disbursements.id', $data['ids'])->where('status', '!=', 'released')
                ->with(['scholar.scholarship', 'scholar.requirements'])->get();
            $done = 0;
            foreach ($rows as $row) {
                // Only scholars whose requirements for that semester are all approved.
                if ($row->scholar->releaseBlocker($row->term)) {
                    $blocked++;

                    continue;
                }
                $row->update($this->released($row, $data['reference'] ?? $row->reference, $request->user()->id));
                $done++;
            }

            return $done;
        });

        $message = "Marked {$count} stipend ".str('record')->plural($count).' as released.';
        if ($blocked) {
            return back()->with('notice', $message." {$blocked} ".str('record')->plural($blocked).' skipped: the scholar\'s requirements for that semester are not all approved yet.');
        }

        return back()->with('status', $message);
    }

    private function released(StipendDisbursement $row, ?string $reference, int $userId): array
    {
        return ['status' => 'released', 'reference' => $reference, 'released_at' => $row->released_at ?? now(), 'recorded_by' => $userId];
    }

    /** The semester picked in the query string if it is offered; else the current semester. */
    private function semester(Request $request): string
    {
        $term = $request->query('term');

        return AcademicTerm::isValidSemester($term) ? $term : AcademicTerm::currentSemester();
    }
}
