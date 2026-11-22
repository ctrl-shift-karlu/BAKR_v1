<?php

namespace Tests\Unit;

use App\Matching\AnswerNormalizer;
use App\Matching\ApplicationSchema;
use App\Matching\RuleEvaluator;
use PHPUnit\Framework\TestCase;

/** Pure PHP: no database or framework needed. Run with: php artisan test --filter=RuleEvaluatorTest */
class RuleEvaluatorTest extends TestCase
{
    private function config(): array
    {
        return require __DIR__.'/../../config/scholarships.php';
    }

    private function run_all(array $answers): array
    {
        $facts = AnswerNormalizer::facts($answers);
        $ev = new RuleEvaluator;
        $out = [];
        foreach ($this->config()['scholarships'] as $s) {
            $out[$s['slug']] = $ev->evaluate($s['rules'], $facts);
        }

        return $out;
    }

    private function base(array $over = []): array
    {
        return $over + [
            'citizenship' => 'filipino', 'enrollment_status' => 'incoming_first_year', 'year_level' => '1',
            'program_field' => 'it', 'program_name' => 'Bachelor of Science in Information Technology (BSIT)', 'previous_degree' => 'no', 'previous_units' => 'no', 'within_years' => 'yes',
            'shs_gwa' => '94', 'top_five' => 'no', 'failing_grade' => 'no', 'disciplinary' => 'no', 'dost_exam' => 'not_taken',
            'household_income' => '300k_350k', 'shs_country' => 'ph', 'shs_strand' => 'stem', 'home_province' => 'davao_del_norte', 'special_groups' => [], 'existing_assistance' => ['none'], 'farm_fisher_family' => ['none'],
            'talent_categories' => [], 'leadership_roles' => [], 'willing_student_assistant' => 'no',
            'cebuana_client' => 'no', 'cebuana_microsavings' => 'no',
        ];
    }

    public function test_strong_first_year_matches_merit_programs(): void
    {
        $r = $this->run_all($this->base());
        $this->assertSame('eligible', $r['grabscholar']['status']);
        $this->assertSame('eligible', $r['ched-cmsp']['status']);      // BSIT is on CHED's national priority list; Philippine SHS answered
        $this->assertSame('not_eligible', $r['bpmsp']['status']);      // 94% is below 95% and not top 5
        $this->assertSame('not_eligible', $r['cebuana-clfi']['status']); // not a Cebuana client
    }

    public function test_top_five_qualifies_for_bpmsp_even_below_95(): void
    {
        $r = $this->run_all($this->base(['top_five' => 'yes', 'household_income' => '500k_2m']));
        $this->assertSame('eligible', $r['bpmsp']['status']);
        $this->assertSame('not_eligible', $r['ched-cmsp']['status']);   // income above 500k
    }

    /** GrabScholar SY 2026-2030: the course list is "included but not limited", so only clearly outside programs fail. */
    public function test_grab_course_list_is_not_exhaustive(): void
    {
        $grab = fn (string $program) => $this->run_all($this->base(['program_name' => $program]))['grabscholar'];
        $course = fn (string $program) => array_column($grab($program)['checks'], 'result', 'key')['course'];

        $this->assertSame('eligible', $grab('Bachelor of Science in Information Technology (BSIT)')['status']);
        $this->assertSame('pass', $course('Bachelor of Science in Entrepreneurship (BS ENTREP)'));      // "Entrepreneurship"
        $this->assertSame('pass', $course('Bachelor of Science in Agro-Forestry (BSAF)'));              // "Agroforestry"
        $this->assertSame('confirm', $course('Bachelor of Science in Marine Biology (BSMB)'));          // not named; may be STEM
        $this->assertSame('fail', $course('Bachelor of Science in Criminology (BSCrim)'));
        $this->assertSame('not_eligible', $this->run_all($this->base(['shs_gwa' => '89.9']))['grabscholar']['status']);
        $this->assertSame('not_eligible', $this->run_all($this->base(['existing_assistance' => ['private_foundation']]))['grabscholar']['status']);
    }

    /** DOST-SEI brochure + official priority S&T list: matched by the exact DNSC program, not the broad field. */
    public function test_dost_checks_the_exact_program_against_the_official_list(): void
    {
        $course = fn (string $program) => array_column($this->run_all($this->base(['program_name' => $program]))['dost-sei']['checks'], 'result', 'key')['course'];

        $this->assertSame('pass', $course('Bachelor of Secondary Education (BSeD) - Mathematics'));
        $this->assertSame('fail', $course('Bachelor of Secondary Education (BSeD) - English'));      // same "education" field, not on the list
        $this->assertSame('pass', $course('Bachelor of Science in Agro-Forestry (BSAF)'));          // listed as "BS Agroforestry"
        $this->assertSame('confirm', $course('Bachelor of Technology and Livelihood Education (BTLEd)'));   // ICT specialization only
        $this->assertSame('fail', $course('Bachelor of Science in Criminology (BSCrim)'));
        $this->assertSame('confirm', $course('Bachelor of Science in Something New'));              // not sorted yet: never a silent fail

        // Grade 12 graduates only: a student already in college is not covered.
        $enrolled = $this->run_all($this->base(['program_name' => 'Bachelor of Science in Information Technology (BSIT)', 'enrollment_status' => 'currently_enrolled', 'year_level' => '2']));
        $this->assertSame('not_eligible', $enrolled['dost-sei']['status']);
        $this->assertSame('possible', $this->run_all($this->base(['program_name' => 'Bachelor of Science in Information Technology (BSIT)']))['dost-sei']['status']);   // every requirement passes; the exam is not taken yet
        $this->assertSame('eligible', $this->run_all($this->base(['program_name' => 'Bachelor of Science in Information Technology (BSIT)', 'dost_exam' => 'passed']))['dost-sei']['status']);
        $nonStem = $this->run_all($this->base(['shs_strand' => 'humss']))['dost-sei'];
        $this->assertSame('possible', $nonStem['status']);   // top 5% of a non-STEM class: only the principal can certify it
        $this->assertSame('confirm', array_column($nonStem['checks'], 'result', 'key')['strand']);
    }

    /** BPMSP Joint Memorandum Circular, Sections 2-3: merit, income, no units/degree, no national grant, no disciplinary record. */
    public function test_bpmsp_follows_the_joint_memorandum_circular(): void
    {
        $top5 = $this->base(['top_five' => 'yes', 'shs_gwa' => '90']);
        $this->assertSame('eligible', $this->run_all($top5)['bpmsp']['status']);                     // BSIT is a CHED priority program
        $this->assertSame('not_eligible', $this->run_all($this->base(['shs_gwa' => '94.9']))['bpmsp']['status']);
        $this->assertSame('not_eligible', $this->run_all(['disciplinary' => 'yes'] + $top5)['bpmsp']['status']);
        $this->assertSame('not_eligible', $this->run_all(['existing_assistance' => ['other_government']] + $top5)['bpmsp']['status']);
        $this->assertSame('eligible', $this->run_all(['existing_assistance' => ['private_foundation']] + $top5)['bpmsp']['status']);   // private grants do not count
        $this->assertSame('not_eligible', $this->run_all(['previous_degree' => 'yes'] + $top5)['bpmsp']['status']);

        $equity = $this->run_all(['special_groups' => ['first_generation']] + $top5)['bpmsp'];
        $this->assertSame('pass', array_column($equity['checks'], 'result', 'key')['equity']);
        $this->assertGreaterThan($this->run_all($top5)['bpmsp']['score'], $equity['score']);
    }

    /** CMO No. 13, s. 2025, Section 5: second-course takers and transferees/shiftees with credited units cannot apply. */
    public function test_cmsp_excludes_degree_holders_and_students_with_credited_units(): void
    {
        $this->assertSame('eligible', $this->run_all($this->base())['ched-cmsp']['status']);
        // A program CHED's national list does not name is a confirm with CHEDRO, never a silent fail.
        $other = $this->run_all($this->base(['program_name' => 'Bachelor of Science in Criminology (BSCrim)']))['ched-cmsp'];
        $this->assertSame('possible', $other['status']);
        $this->assertSame('confirm', array_column($other['checks'], 'result', 'key')['priority']);
        $this->assertSame('not_eligible', $this->run_all($this->base(['shs_country' => 'abroad']))['ched-cmsp']['status']);   // Sec. 4.2: Philippine SHS graduates only
        $this->assertSame('needs_info', $this->run_all($this->base(['shs_country' => null]))['ched-cmsp']['status']);
        $this->assertSame('not_eligible', $this->run_all($this->base(['previous_degree' => 'yes']))['ched-cmsp']['status']);
        $this->assertSame('not_eligible', $this->run_all($this->base(['previous_units' => 'yes']))['ched-cmsp']['status']);
        $this->assertSame('not_eligible', $this->run_all($this->base(['shs_gwa' => '92.9']))['ched-cmsp']['status']);
    }

    /** Revised TDP Guidelines of 2026 publish no income cap: need is shown with a Certificate of Indigency. */
    public function test_tdp_has_no_invented_income_cap(): void
    {
        $r = $this->run_all($this->base(['household_income' => '350k_500k']));
        $this->assertSame('possible', $r['ched-tdp']['status']);
        $this->assertSame('eligible', $r['ched-cmsp']['status']);   // exactly at the CMSP cap: the bracket ends at 500,000
        $keys = array_column($r['ched-tdp']['checks'], 'result', 'key');
        $this->assertArrayNotHasKey('income', $keys);
        $this->assertSame('confirm', $keys['indigency']);
    }

    public function test_existing_tes_blocks_tdp_cmsp_and_grab(): void
    {
        $r = $this->run_all($this->base(['existing_assistance' => ['tes_tdp_ched']]));
        foreach (['ched-tdp', 'ched-cmsp', 'grabscholar'] as $slug) {
            $this->assertSame('not_eligible', $r[$slug]['status'], $slug);
        }
    }

    /**
     * Revised TES Guidelines of 2026, Section 3: new slots are only for (1) Listahanan/4Ps households, then
     * (2.1) dependents of solo parents and (2.2) Indigenous Peoples. Anyone else is not covered.
     */
    public function test_tes_is_only_for_its_priority_groups(): void
    {
        $this->assertSame('not_eligible', $this->run_all($this->base())['ched-tes']['status']);
        $this->assertSame('not_eligible', $this->run_all($this->base(['special_groups' => ['pwd']]))['ched-tes']['status']);   // PWD alone is not a group

        foreach (['four_ps', 'listahanan', 'solo_parent', 'indigenous_people'] as $group) {
            $this->assertSame('eligible', $this->run_all($this->base(['special_groups' => [$group]]))['ched-tes']['status'], $group);   // the admission note is optional: it does not hide a clear match
        }

        // Already a TDP / CHED grantee: not eligible for a new TES slot (Section 7-A.5).
        $this->assertSame('not_eligible', $this->run_all($this->base(['special_groups' => ['four_ps'], 'existing_assistance' => ['tes_tdp_ched']]))['ched-tes']['status']);
    }

    public function test_cebuana_full_match_for_enrolled_it_student(): void
    {
        $r = $this->run_all($this->base([
            'enrollment_status' => 'currently_enrolled', 'year_level' => '2', 'shs_gwa' => null,
            'college_gwa' => '1.75', 'college_gwa_scale' => 'dnsc', 'within_years' => 'yes',
            'cebuana_client' => 'self', 'cebuana_microsavings' => 'yes',
        ]));
        $this->assertSame('eligible', $r['cebuana-clfi']['status']);    // 1.75 is about 88%
    }

    /** CLFI FAQ: a client with a 24K card (Q2); listed courses only, no exceptions (Q9); public colleges only (Q3). */
    public function test_cebuana_follows_the_clfi_faq(): void
    {
        $enrolled = ['enrollment_status' => 'currently_enrolled', 'year_level' => '3', 'college_gwa' => '88', 'college_gwa_scale' => 'percent', 'cebuana_client' => 'self'];
        $check = fn (array $over, string $key) => array_column($this->run_all($this->base($over + $enrolled))['cebuana-clfi']['checks'], 'result', 'key')[$key];

        $this->assertSame('confirm', $check(['cebuana_client' => 'relative'], 'client'));   // FAQ names the client only
        $this->assertSame('fail', $check(['program_name' => 'Bachelor of Science in Marine Biology (BSMB)'], 'course'));
        $this->assertSame('confirm', $check(['program_name' => 'Bachelor of Science in Entrepreneurship (BS ENTREP)'], 'course'));
        $this->assertSame('not_eligible', $this->run_all($this->base(['college_gwa' => '84'] + $enrolled))['cebuana-clfi']['status']);
    }

    public function test_gia_programs_use_talent_and_roles(): void
    {
        $r = $this->run_all($this->base(['enrollment_status' => 'currently_enrolled', 'year_level' => '2', 'talent_categories' => ['music'], 'has_awards' => 'yes', 'leadership_roles' => ['ssc_officer']]));
        $this->assertSame('eligible', $r['dnsc-gia-a']['status']);
        $this->assertSame('eligible', $r['dnsc-gia-b']['status']);
        $this->assertSame('not_eligible', $this->run_all($this->base())['dnsc-gia-a']['status']);
    }

    public function test_student_assistant_requires_willingness_and_confirms_need(): void
    {
        $r = $this->run_all($this->base(['willing_student_assistant' => 'yes']));
        $this->assertSame('possible', $r['dnsc-sa-c']['status']);
        $this->assertSame('not_eligible', $this->run_all($this->base())['dnsc-sa-c']['status']);
    }

    public function test_missing_answers_mean_needs_info_not_failure(): void
    {
        $r = $this->run_all($this->base(['shs_gwa' => null]));
        $this->assertSame('needs_info', $r['grabscholar']['status']);
    }

    public function test_dnsc_scale_conversion_is_monotonic(): void
    {
        $prev = 101;
        foreach ([1.0, 1.25, 1.5, 1.75, 2.0, 2.5, 3.0, 4.0, 5.0] as $g) {
            $p = ApplicationSchema::dnscToPercent($g);
            $this->assertLessThanOrEqual($prev, $p);
            $prev = $p;
        }
        $this->assertSame(85.0, ApplicationSchema::dnscToPercent(2.0));
    }

    public function test_dost_course_rules(): void
    {
        $this->assertSame('not_eligible', $this->run_all($this->base(['program_name' => 'Bachelor of Science in Criminology (BSCrim)']))['dost-sei']['status']);
        $this->assertSame('possible', $this->run_all($this->base(['program_name' => 'Bachelor of Science in Marine Biology (BSMB)']))['dost-sei']['status']);
    }
}
