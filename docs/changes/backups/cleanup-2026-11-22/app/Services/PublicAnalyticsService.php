<?php

namespace App\Services;

use App\Models\Scholar;
use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use App\Models\ScholarshipMatch;
use App\Models\User;

/**
 * Public, aggregate-only statistics for the About page.
 *
 * Every number is read live from the database (fromDatabase()).
 *
 * Privacy rules enforced here, not in the view:
 *  - counts only, never individual rows, names, contact details, grades,
 *    income, documents, or free text;
 *  - any group smaller than MIN_GROUP_SIZE is hidden ("Fewer than 10").
 */
class PublicAnalyticsService
{
    public const MIN_GROUP_SIZE = 10;

    /** One colour per meaning (same as the portals' --viz-* tones): match status → tone class suffix. */
    public const OUTCOME_TONES = ['eligible' => 'good', 'possible' => 'warn', 'needs_info' => 'blue', 'not_eligible' => 'gray'];

    public function summary(): array
    {
        return $this->fromDatabase();
    }

    /** Heights for the column chart (relative to the tallest visible group). */
    public function scaleRows(array $rows): array
    {
        $max = max(array_map(fn ($r) => $r['count'] ?? 0, $rows) ?: [0]);
        foreach ($rows as &$r) {
            $r['height'] = $max > 0 && ! $r['hidden'] ? (int) round($r['count'] / $max * 100) : 0;
        }

        return $rows;
    }

    /**
     * Add stroke-dasharray geometry for a donut drawn on a circle of circumference 100.
     * Hidden (small) groups are left out of the ring and listed as "Fewer than N".
     */
    public function donut(array $rows): array
    {
        $shown = array_sum(array_map(fn ($r) => $r['count'] ?? 0, $rows));
        $offset = 0.0;
        foreach ($rows as &$r) {
            $len = $shown > 0 && ! $r['hidden'] ? $r['count'] / $shown * 100 : 0;
            $r['dash'] = round(max($len - 0.6, 0), 2);   // small gap between slices
            $r['offset'] = round(-$offset, 2);
            $offset += $len;
        }

        return $rows;
    }

    /** Points for the trend chart on a 600x220 canvas (padding 40/20/30/20). */
    private function trendGeometry(array $points): array
    {
        $n = count($points);
        $max = max(1, ...(array_column($points, 'value') ?: [1]));   // never 0 (no accounts yet)
        $coords = [];
        foreach ($points as $i => $p) {
            $x = $n > 1 ? 40 + $i * (540 / ($n - 1)) : 310;
            $y = 190 - ($p['value'] / $max) * 160;
            $coords[] = $p + ['x' => round($x, 1), 'y' => round($y, 1)];
        }
        $line = implode(' ', array_map(fn ($c, $i) => ($i ? 'L' : 'M').$c['x'].' '.$c['y'], $coords, array_keys($coords)));
        $area = $coords ? $line.' L'.end($coords)['x'].' 190 L'.$coords[0]['x'].' 190 Z' : '';

        return [
            'points' => $coords, 'line' => $line, 'area' => $area, 'max' => $max,
            'first' => $coords[0]['value'] ?? 0, 'last' => $coords ? end($coords)['value'] : 0,
        ];
    }

    /**
     * Fixed About-page blocks. The office details are still to be supplied (nothing invented).
     * Team photos (owner, 2026-11-21): drop a square photo at the path and it appears; until then a placeholder shows.
     */
    public function placeholders(): array
    {
        return [
            'office' => [
                'photo' => 'images/img/scholarship-office.jpg',
                'details' => ['Location' => 'To be added', 'Office hours' => 'To be added', 'Contact person' => 'To be added', 'Email / phone' => 'To be added'],
            ],
            'team' => [
                ['name' => 'Karl Angelo T. Gabaes', 'photo' => 'images/img/team/member-1.jpg'],
                ['name' => 'Rochelle D. Abella', 'photo' => 'images/img/team/member-2.jpg'],
                ['name' => 'Marielle Joie Javier', 'photo' => 'images/img/team/member-3.jpg'],
            ],
        ];
    }

    /**
     * Live numbers, read from the database on every page load (no cache, so the page
     * follows the data). Answers are encrypted, so campus / program / year are counted
     * in PHP after decryption; only counts ever leave this method.
     */
    private function fromDatabase(): array
    {
        $students = User::where('role', 'student')->where('status', 'active');

        $accounts = (clone $students)->count();

        // Running total of accounts at the end of each of the last 6 months.
        $trend = [];
        for ($i = 5; $i >= 0; $i--) {
            $end = now()->subMonthsNoOverflow($i)->endOfMonth();
            $trend[$end->format('M')] = (clone $students)->where('created_at', '<=', $end)->count();
        }

        $current = ScholarshipApplication::query()
            ->where('is_current', true)
            ->whereIn('user_id', (clone $students)->select('id'))
            ->get(['id', 'answers']);

        $campus = $program = $year = [];
        foreach ($current as $app) {
            $a = (array) $app->answers;
            if (! empty($a['campus_name'])) {
                $campus[$a['campus_name']] = ($campus[$a['campus_name']] ?? 0) + 1;
            }
            if (! empty($a['program_name'])) {
                $program[$a['program_name']] = ($program[$a['program_name']] ?? 0) + 1;
            }
            if (! empty($a['year_level'])) {
                $year[(string) $a['year_level']] = ($year[(string) $a['year_level']] ?? 0) + 1;
            }
        }
        arsort($campus);
        arsort($program);

        $yearLabels = ['1' => '1st year', '2' => '2nd year', '3' => '3rd year', '4' => '4th year', '5' => '5th year or higher'];
        $years = [];
        foreach ($yearLabels as $k => $label) {
            $years[$label] = $year[$k] ?? 0;
        }

        // Matching results (fixed 2026-11-21): every result of a current profile against an ACTIVE scholarship,
        // all four outcomes. Before, "Not eligible" was left out, so the shares did not describe the results.
        $matches = ScholarshipMatch::query()
            ->whereIn('scholarship_application_id', $current->pluck('id'))
            ->whereHas('scholarship', fn ($q) => $q->where('is_active', true));

        $matchCounts = (clone $matches)->selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status');
        $outcomes = [];
        foreach (ScholarshipMatch::STATUS_ORDER as $s) {
            $outcomes[ScholarshipMatch::STATUS_LABELS[$s]] = (int) ($matchCounts[$s] ?? 0);
        }
        $outcomeRows = $this->prepare($outcomes);
        foreach ($outcomeRows as $i => &$row) {
            $row['tone'] = self::OUTCOME_TONES[ScholarshipMatch::STATUS_ORDER[$i]];
        }
        unset($row);

        // Profiles that can apply somewhere: at least one Eligible or Nearly eligible result.
        $profilesWithMatch = (clone $matches)->whereIn('status', ['eligible', 'possible'])
            ->distinct()->count('scholarship_application_id');

        // Scholarships students match most: profiles with an Eligible / Nearly eligible result, per scholarship.
        $perScholarship = (clone $matches)->whereIn('status', ['eligible', 'possible'])
            ->selectRaw('scholarship_id, count(distinct scholarship_application_id) as n')
            ->groupBy('scholarship_id')->orderByDesc('n')->limit(5)
            ->pluck('n', 'scholarship_id');
        $names = Scholarship::whereIn('id', $perScholarship->keys())->get()->keyBy('id');
        $topScholarships = [];
        foreach ($perScholarship as $id => $n) {
            if ($names->has($id)) {
                $topScholarships[$names[$id]->shortName()] = (int) $n;
            }
        }
        $profiles = $current->count();
        $topRows = $this->prepare($topScholarships);
        foreach ($topRows as &$row) {   // share of ALL profiles, not of this top five
            $row['percent'] = $row['hidden'] || ! $profiles ? null : (int) round($row['count'] / $profiles * 100);
        }
        unset($row);

        $min = self::MIN_GROUP_SIZE;

        // Totals (2026-11-21): whole-system counts straight from the tables, so the page follows the data even
        // while every campus / program / year group is still under the privacy minimum. A total is not a group:
        // it names no campus, program or person.
        $scholarshipsListed = Scholarship::where('is_active', true)->count();
        $scholarsMonitored = Scholar::where('status', '!=', 'terminated')->count();

        return [
            'as_of' => now()->format('M j, Y g:i A'),
            'totals' => [
                ['label' => 'Student accounts', 'value' => $accounts, 'icon' => 'bi-people-fill'],
                ['label' => 'Profiles ready for matching', 'value' => $current->count(), 'icon' => 'bi-journal-check'],
                ['label' => 'Scholarships listed', 'value' => $scholarshipsListed, 'icon' => 'bi-award-fill'],
                ['label' => 'Scholars monitored', 'value' => $scholarsMonitored, 'icon' => 'bi-mortarboard-fill'],
            ],
            'charts' => [
                'trend' => [
                    'title' => 'Student accounts over time',
                    'note' => 'Running total of student accounts at the end of each month.',
                    // Running totals of ALL accounts (the latest equals the headline number), so none is hidden.
                    'geo' => $this->trendGeometry(array_map(fn ($label, $value) => ['label' => $label, 'value' => $value], array_keys($trend), $trend)),
                ],
                'campus' => [
                    'title' => 'Where students study',
                    'note' => 'Share of students by campus. Small groups are not shown.',
                    'rows' => $this->donut($this->prepare($campus)),
                ],
                'outcomes' => [
                    'title' => 'How profiles match scholarships',
                    'note' => 'Every profile is checked against every scholarship. This is the share of all those results by outcome.',
                    'rows' => $outcomeRows,
                    'total' => array_sum($outcomes),
                    'profiles' => $profiles,
                    'with_match' => $profilesWithMatch >= $min ? $profilesWithMatch : null,
                ],
                'top_scholarships' => [
                    'title' => 'Scholarships students match most',
                    'note' => 'Share of profiles that are eligible or nearly eligible for each scholarship.',
                    'rows' => $topRows,
                ],
                'year_levels' => [
                    'title' => 'By year level',
                    'note' => 'Number of students in each year.',
                    'rows' => $this->scaleRows($this->prepare($years)),
                ],
                'programs' => [
                    'title' => 'Most common programs',
                    'note' => 'Share of students in the five largest programs.',
                    'rows' => $this->prepare(array_slice($program, 0, 5, true)),
                ],
            ],
            'min_group' => $min,
        ];
    }

    /**
     * Trend points [label => running total]. Points below the minimum group size
     * are dropped so the earliest, tiny totals are never published.
     *
     * @param  array<string,int>  $series
     * @return array<int,array{label:string,value:int}>
     */
    public function prepareTrend(array $series): array
    {
        $points = [];
        foreach ($series as $label => $value) {
            if ($value >= self::MIN_GROUP_SIZE) {
                $points[] = ['label' => $label, 'value' => $value];
            }
        }

        return $points;
    }

    /**
     * Turn [label => count] into rows with a percentage, hiding small groups so a
     * handful of students can never be singled out.
     *
     * @param  array<string,int>  $counts
     * @return array<int,array{label:string,count:?int,percent:?int,hidden:bool}>
     */
    public function prepare(array $counts): array
    {
        $total = array_sum($counts);
        $rows = [];
        foreach ($counts as $label => $count) {
            $hidden = $count < self::MIN_GROUP_SIZE;
            $rows[] = [
                'label' => $label,
                'count' => $hidden ? null : $count,
                'percent' => $hidden || $total === 0 ? null : (int) round($count / $total * 100),
                'hidden' => $hidden,
            ];
        }

        return $rows;
    }
}
