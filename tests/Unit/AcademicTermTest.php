<?php

namespace Tests\Unit;

use App\Support\AcademicTerm;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

/** DNSC has two semesters and no Midyear (owner, 2026-11-18). */
class AcademicTermTest extends TestCase
{
    public function test_every_date_falls_in_a_semester_and_june_july_is_the_2nd_semester(): void
    {
        $this->assertSame('AY 2026–2027 · 1st Semester', AcademicTerm::current(Carbon::create(2026, 8, 1)));
        $this->assertSame('AY 2026–2027 · 1st Semester', AcademicTerm::current(Carbon::create(2026, 11, 13)));
        $this->assertSame('AY 2026–2027 · 1st Semester', AcademicTerm::current(Carbon::create(2026, 12, 31)));
        $this->assertSame('AY 2026–2027 · 2nd Semester', AcademicTerm::current(Carbon::create(2027, 1, 1)));
        $this->assertSame('AY 2026–2027 · 2nd Semester', AcademicTerm::current(Carbon::create(2027, 6, 15)));   // the break: no Midyear
        $this->assertSame('AY 2026–2027 · 2nd Semester', AcademicTerm::current(Carbon::create(2027, 7, 31)));
        $this->assertSame(AcademicTerm::current(Carbon::create(2027, 6, 15)), AcademicTerm::currentSemester(Carbon::create(2027, 6, 15)));
    }

    public function test_options_are_consecutive_semesters_without_midyear(): void
    {
        $this->assertSame([
            'AY 2027–2028 · 1st Semester',
            'AY 2026–2027 · 2nd Semester',
            'AY 2026–2027 · 1st Semester',   // current (Nov 2026)
            'AY 2025–2026 · 2nd Semester',
            'AY 2025–2026 · 1st Semester',
            'AY 2024–2025 · 2nd Semester',
        ], AcademicTerm::options(Carbon::create(2026, 11, 13)));

        foreach ([Carbon::create(2026, 11, 13), Carbon::create(2027, 6, 15), Carbon::create(2027, 3, 1)] as $date) {
            $terms = AcademicTerm::options($date);
            $this->assertCount(6, array_unique($terms));
            $this->assertSame([], array_values(array_filter($terms, fn ($t) => str_contains($t, 'Midyear'))));
            $this->assertContains(AcademicTerm::current($date), $terms);
            $this->assertSame($terms, AcademicTerm::semesterOptions($date));
        }
    }

    public function test_previous_semester(): void
    {
        $this->assertSame('AY 2025–2026 · 2nd Semester', AcademicTerm::previous(Carbon::create(2026, 11, 13)));
        $this->assertSame('AY 2026–2027 · 1st Semester', AcademicTerm::previous(Carbon::create(2027, 6, 15)));
    }

    public function test_months_of_each_semester(): void
    {
        $this->assertSame(['2026-08', '2026-09', '2026-10', '2026-11', '2026-12'], AcademicTerm::months('AY 2026–2027 · 1st Semester'));
        $this->assertSame(['2027-01', '2027-02', '2027-03', '2027-04', '2027-05'], AcademicTerm::months('AY 2026–2027 · 2nd Semester'));
        $this->assertSame([], AcademicTerm::months('AY 2026–2027 · Midyear'));
        $this->assertSame([], AcademicTerm::months('nonsense'));
    }
}
