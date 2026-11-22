<?php

namespace Tests\Unit;

use App\Matching\AnswerNormalizer;
use App\Matching\AnswerSanitizer;
use App\Matching\ApplicationSchema;
use PHPUnit\Framework\TestCase;

/**
 * Only the questions that apply to a student are asked and kept (owner, 2026-11-19: "if a student is already
 * a college student, why ask for senior high school information?").
 *   first_year  Grade 12 grade, Philippine SHS, SHS conduct: incoming AND current 1st-years (CMSP and
 *               GrabScholar accept current first-years and read their Grade 12 grade)
 *   incoming    strand, Top 5, DOST exam
 *   upper       college GWA, "within the normal length": 2nd year and up
 */
class QuestionLogicTest extends TestCase
{
    private const SHS_FIRST_YEAR = ['shs_gwa' => '95', 'shs_country' => 'ph', 'disciplinary' => 'no'];

    private const SHS_INCOMING = ['shs_strand' => 'stem', 'top_five' => 'yes', 'dost_exam' => 'not_taken'];

    private const COLLEGE = ['college_gwa' => '1.75', 'college_gwa_scale' => 'dnsc', 'within_years' => 'yes'];

    private function keys(array $in): array
    {
        return array_keys(AnswerSanitizer::clean($in));
    }

    public function test_an_incoming_student_keeps_the_shs_questions_and_no_college_grades(): void
    {
        $kept = $this->keys(['enrollment_status' => 'incoming_first_year'] + self::SHS_FIRST_YEAR + self::SHS_INCOMING + self::COLLEGE);

        foreach ([...array_keys(self::SHS_FIRST_YEAR), ...array_keys(self::SHS_INCOMING)] as $k) {
            $this->assertContains($k, $kept, $k);
        }
        foreach (array_keys(self::COLLEGE) as $k) {
            $this->assertNotContains($k, $kept, $k);
        }
    }

    public function test_a_current_first_year_keeps_the_grade_12_grade_but_not_strand_top_five_or_exam(): void
    {
        $kept = $this->keys(['enrollment_status' => 'currently_enrolled', 'year_level' => '1'] + self::SHS_FIRST_YEAR + self::SHS_INCOMING + self::COLLEGE);

        foreach (array_keys(self::SHS_FIRST_YEAR) as $k) {
            $this->assertContains($k, $kept, "$k: CMSP and GrabScholar read a current first-year's Grade 12 grade");
        }
        foreach (array_keys(self::SHS_INCOMING) as $k) {
            $this->assertNotContains($k, $kept, $k);
        }
        $this->assertNotContains('within_years', $kept);   // nobody has overstayed in their first year
    }

    public function test_a_second_year_student_keeps_no_senior_high_school_answers_at_all(): void
    {
        foreach (['2', '3', '4', '5'] as $year) {
            $kept = $this->keys(['enrollment_status' => 'currently_enrolled', 'year_level' => $year] + self::SHS_FIRST_YEAR + self::SHS_INCOMING + self::COLLEGE);

            foreach ([...array_keys(self::SHS_FIRST_YEAR), ...array_keys(self::SHS_INCOMING)] as $k) {
                $this->assertNotContains($k, $kept, "year $year: $k");
            }
            foreach (array_keys(self::COLLEGE) as $k) {
                $this->assertContains($k, $kept, "year $year: $k");
            }
        }
    }

    public function test_a_first_year_counts_as_within_the_normal_length(): void
    {
        // Not asked, so the rule for TDP / TES must not turn it into "needs info".
        $this->assertSame('yes', AnswerNormalizer::facts(['enrollment_status' => 'currently_enrolled', 'year_level' => '1'])['within_years']);
        $this->assertSame('yes', AnswerNormalizer::facts(['enrollment_status' => 'currently_enrolled', 'year_level' => '2', 'within_years' => 'yes'])['within_years']);
        $this->assertNull(AnswerNormalizer::facts(['enrollment_status' => 'currently_enrolled', 'year_level' => '2'])['within_years']);
    }

    public function test_applies_and_required_conditions_agree(): void
    {
        $incoming = ['enrollment_status' => 'incoming_first_year'];
        $second = ['enrollment_status' => 'currently_enrolled', 'year_level' => '2'];

        $this->assertTrue(ApplicationSchema::applies('incoming', $incoming));
        $this->assertTrue(ApplicationSchema::applies('first_year', $incoming));
        $this->assertFalse(ApplicationSchema::applies('upper', $incoming));
        $this->assertFalse(ApplicationSchema::applies('first_year', $second));
        $this->assertTrue(ApplicationSchema::applies('upper', $second));
        $this->assertTrue(ApplicationSchema::applies(null, $second));   // no 'when' = always asked

        // The validator requires exactly what applies() asks.
        $rules = ApplicationSchema::rules();
        $this->assertContains('required_if:year_level,1', $rules['shs_gwa']);
        $this->assertContains('required_if:enrollment_status,incoming_first_year', $rules['shs_strand']);
        $this->assertContains('required_if:year_level,2,3,4,5', $rules['college_gwa']);
        $this->assertContains('required', $rules['failing_grade']);   // asked of everyone
    }
}
