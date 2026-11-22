<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * DNSC academic terms as plain labels, e.g. "AY 2026–2027 · 1st Semester".
 * DNSC has two semesters and NO Midyear term (owner's decision 2026-11-18):
 *   1st Semester  August–December
 *   2nd Semester  January–May (June–July, the break, still counts as the 2nd Semester that just ended)
 * Requirements, stipends and "scholar since" all use these same labels.
 */
class AcademicTerm
{
    public static function current(?CarbonInterface $date = null): string
    {
        return self::label(self::index(Carbon::instance($date ?? Carbon::now())));
    }

    /** The semester before the current one. */
    public static function previous(?CarbonInterface $date = null): string
    {
        return self::label(self::index(Carbon::instance($date ?? Carbon::now())) - 1);
    }

    /** Two semesters ahead, the current one and three before it (newest first). */
    public static function options(?CarbonInterface $date = null): array
    {
        $now = self::index(Carbon::instance($date ?? Carbon::now()));

        return array_map(fn (int $k) => self::label($now + $k), [2, 1, 0, -1, -2, -3]);
    }

    public static function isValid(?string $term): bool
    {
        return $term !== null && in_array($term, self::options(), true);
    }

    /**
     * The semesters a scholar can have started in ("Scholar since", owner 2026-11-21): the next one (an incoming
     * scholar), the current one and the eight before it (a full four-year program), newest first.
     */
    public static function startOptions(?CarbonInterface $date = null): array
    {
        $now = self::index(Carbon::instance($date ?? Carbon::now()));

        return array_map(fn (int $k) => self::label($now - $k), range(-1, 8));
    }

    /**
     * Where a semester label sits in time (bigger = later), or null when it is not a semester label.
     * Use it to compare two semesters; never compare the labels as text.
     */
    public static function indexOf(?string $term): ?int
    {
        if ($term === null || ! preg_match('/^AY (\d{4})–(\d{4}) · (1st|2nd) Semester$/u', $term, $m) || (int) $m[2] !== (int) $m[1] + 1) {
            return null;
        }

        return (int) $m[1] * 2 + ($m[3] === '2nd' ? 1 : 0);
    }

    /** $term and the $back semesters before it, newest first ([] for a label that is not a semester). */
    public static function upTo(string $term, int $back = 40): array
    {
        $index = self::indexOf($term);

        return $index === null ? [] : array_map(fn (int $k) => self::label($index - $k), range(0, $back));
    }

    /** The semester a date falls in (e.g. when a scholar row was created). */
    public static function of(CarbonInterface $date): string
    {
        return self::current($date);
    }

    /** Same as current(): every term is a semester. Kept so stipend code reads naturally. */
    public static function currentSemester(?CarbonInterface $date = null): string
    {
        return self::current($date);
    }

    /** Same as options() (there is no Midyear to leave out any more). */
    public static function semesterOptions(?CarbonInterface $date = null): array
    {
        return self::options($date);
    }

    public static function isValidSemester(?string $term): bool
    {
        return self::isValid($term);
    }

    /**
     * The calendar months of a semester as "YYYY-MM" (1st: Aug–Dec, 2nd: Jan–May).
     * Empty for an unrecognised label.
     */
    public static function months(string $term): array
    {
        if (! preg_match('/^AY (\d{4})–(\d{4}) · (1st|2nd) Semester$/u', $term, $m)) {
            return [];
        }
        [$year, $from, $to] = $m[3] === '1st' ? [(int) $m[1], 8, 12] : [(int) $m[2], 1, 5];

        return array_map(fn ($month) => sprintf('%04d-%02d', $year, $month), range($from, $to));
    }

    /** Last day of the semester's last month (Dec 31 / May 31): the default due date for its requirements. */
    public static function endDate(string $term): ?Carbon
    {
        $months = self::months($term);

        return $months ? Carbon::createFromFormat('Y-m-d', end($months).'-01')->endOfMonth()->startOfDay() : null;
    }

    /** Semesters counted from year 0: AY start year × 2, + 1 for the 2nd Semester. */
    private static function index(CarbonInterface $date): int
    {
        $m = (int) $date->format('n');
        $y = (int) $date->format('Y');

        // August onwards: 1st Semester of AY y–y+1. January–July: 2nd Semester of AY (y-1)–y.
        return $m >= 8 ? $y * 2 : ($y - 1) * 2 + 1;
    }

    private static function label(int $index): string
    {
        $start = intdiv($index, 2);

        return sprintf('AY %d–%d · %s', $start, $start + 1, $index % 2 === 0 ? '1st Semester' : '2nd Semester');
    }
}
