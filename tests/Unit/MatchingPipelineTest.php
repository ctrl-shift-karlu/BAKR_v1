<?php

namespace Tests\Unit;

use App\Ai\ExplanationPrompt;
use App\Ai\TemplateExplainer;
use App\Matching\AnswerNormalizer;
use App\Matching\AnswerSanitizer;
use App\Matching\ApplicationSchema;
use App\Matching\MatchingService;
use App\Matching\ProgramCatalog;
use PHPUnit\Framework\TestCase;

/** Pure PHP: no database, framework, or network. */
class MatchingPipelineTest extends TestCase
{
    private function scholarships(): array
    {
        $out = [];
        foreach ((require __DIR__.'/../../config/scholarships.php')['scholarships'] as $i => $s) {
            $out[] = ['id' => $i + 1, 'slug' => $s['slug'], 'rules' => $s['rules']] + $s;
        }

        return $out;
    }

    private function input(array $over = []): array
    {
        return $over + [
            'citizenship' => 'filipino', 'enrollment_status' => 'incoming_first_year', 'year_level' => '1', 'campus_id' => '1', 'program_id' => '5',
            'previous_degree' => 'no', 'previous_units' => 'no', 'top_five' => 'no',
            'shs_gwa' => '96.5', 'failing_grade' => 'no', 'disciplinary' => 'no', 'dost_exam' => 'not_taken',
            'household_income' => '300k_350k', 'shs_country' => 'ph', 'shs_strand' => 'stem', 'home_province' => 'davao_del_norte', 'special_groups' => ['listahanan'], 'existing_assistance' => ['none'], 'farm_fisher_family' => ['none'],
            'talent_categories' => [], 'leadership_roles' => [], 'willing_student_assistant' => 'yes',
            'cebuana_client' => 'self', 'cebuana_microsavings' => 'yes', 'evil_field' => 'x', 'user_id' => 99,
        ];
    }

    /** Sanitized answers plus the field the service derives from the program in the database. */
    private function cleaned(array $input, string $field = 'it'): array
    {
        // program_name comes from ProgramCatalog in the app; the IT fixture uses the real DNSC name.
        return AnswerSanitizer::clean($input) + ['program_field' => $field] + ($field === 'it' ? ['program_name' => 'Bachelor of Science in Information Technology (BSIT)'] : []);
    }

    private function statuses(array $input): array
    {
        $rows = (new MatchingService)->evaluate($this->cleaned($input), $this->scholarships());
        $by = [];
        foreach ($this->scholarships() as $i => $s) {
            $by[$s['slug']] = $rows[$i]['status'];
        }

        return $by;
    }

    public function test_sanitizer_drops_unknown_fields_and_trims(): void
    {
        $c = AnswerSanitizer::clean($this->input());
        $this->assertSame(false, array_key_exists('evil_field', $c));
        $this->assertSame(false, array_key_exists('user_id', $c));
        $this->assertSame('5', $c['program_id']);
        $this->assertSame(96.5, $c['shs_gwa']);
    }

    public function test_incoming_student_has_no_college_fields_and_is_year_one(): void
    {
        $c = AnswerSanitizer::clean($this->input(['year_level' => '3', 'college_gwa' => '1.5', 'within_years' => 'yes']));
        $this->assertSame('1', $c['year_level']);
        $this->assertSame(false, array_key_exists('college_gwa', $c));
        $this->assertSame(false, array_key_exists('within_years', $c));
    }

    public function test_none_of_these_is_dropped_when_mixed_with_real_assistance(): void
    {
        $c = AnswerSanitizer::clean($this->input(['existing_assistance' => ['none', 'tes_tdp_ched']]));
        $this->assertSame(['tes_tdp_ched'], $c['existing_assistance']);
    }

    public function test_awards_answer_is_dropped_without_a_talent(): void
    {
        $c = AnswerSanitizer::clean($this->input(['talent_categories' => [], 'has_awards' => 'yes']));
        $this->assertSame(false, array_key_exists('has_awards', $c));
    }

    public function test_every_scholarship_gets_a_result_and_score_is_in_range(): void
    {
        $rows = (new MatchingService)->evaluate(AnswerSanitizer::clean($this->input()), $this->scholarships());
        $this->assertCount(count($this->scholarships()), $rows);
        $this->assertCount(14, $rows);   // 11 + LANDBANK ILP, CoScho, BFAR FSP (2026-11-18)
        foreach ($rows as $r) {
            $this->assertTrue($r['score'] >= 0 && $r['score'] <= 100, 'score range');
            $this->assertTrue(in_array($r['status'], ['eligible', 'possible', 'needs_info', 'not_eligible'], true), 'status value');
        }
    }

    public function test_top_student_pipeline(): void
    {
        $s = $this->statuses($this->input());
        $this->assertSame('eligible', $s['bpmsp']);        // 96.5% passes; BSIT is a CHED priority program
        $this->assertSame('eligible', $s['ched-cmsp']);    // a Grade 12 student with 96.5%, income within ₱500,000 (the owner's case, 2026-11-19)
        $this->assertSame('possible', $s['dost-sei']);     // STEM strand + BSIT pass; the DOST exam is not taken yet (fixture)
        $this->assertSame('eligible', $s['grabscholar']);
        $this->assertSame('eligible', $s['cebuana-clfi']);
        $this->assertSame('eligible', $s['ched-tes']);     // Listahanan; the admission note is optional
        $this->assertSame('not_eligible', $s['dnsc-gia-b']);
    }

    public function test_changing_answers_changes_results(): void
    {
        $s = $this->statuses($this->input(['household_income' => 'over_2m', 'existing_assistance' => ['dnsc_internal']]));
        $this->assertSame('not_eligible', $s['bpmsp']);        // income above 2,000,000
        $this->assertSame('not_eligible', $s['grabscholar']);  // already has an assistance
    }

    public function test_template_explainer_never_contradicts_the_result(): void
    {
        $t = new TemplateExplainer;
        $ctx = fn ($status, $checks) => ['scholarship' => 'Test Grant', 'overview' => '', 'result' => $status, 'closeness_percent' => 50, 'checks' => $checks, 'documents_to_prepare' => ['COR'], 'application_window' => null];
        $fail = [['requirement' => 'Income of 400,000 or less', 'outcome' => 'fail', 'note' => '', 'required' => true]];
        $text = $t->explainMatch($ctx('not_eligible', $fail));
        $this->assertStringContainsString('not a match', $text);
        $this->assertStringContainsString('Income of 400,000 or less', $text);
        $this->assertStringNotContainsString('strong match', $text);
        $this->assertStringNotContainsString('COR', $text);   // no document prep for a non-match
        $ok = $t->explainMatch($ctx('eligible', [['requirement' => 'Filipino citizen', 'outcome' => 'pass', 'note' => '', 'required' => true]]));
        $this->assertStringContainsString('strong match', $ok);
        $this->assertStringContainsString('does not collect documents', $ok);
    }

    public function test_ai_prompt_contains_no_personal_data(): void
    {
        $answers = $this->cleaned($this->input(['campus_id' => 'Maria Santos private note']));
        $rows = (new MatchingService)->evaluate($answers, $this->scholarships());
        $s = $this->scholarships()[0];
        $ctx = ExplanationPrompt::matchContext($s, $rows[0]['status'], $rows[0]['checks']);
        $msg = ExplanationPrompt::matchMessage($ctx).ExplanationPrompt::summaryMessage([$ctx]);
        $this->assertStringNotContainsString('Maria', $msg);
        $this->assertStringNotContainsString('@', $msg);
        $this->assertStringNotContainsString('closeness', $msg);   // results are described by status only, never a percentage
        $this->assertStringContainsString('<data>', $msg);
    }

    public function test_reply_cleaner_strips_markup_and_limits_length(): void
    {
        $c = ExplanationPrompt::cleanReply("<b>Hello</b> **there** #1\n\n\n\nBye");
        $this->assertSame("Hello there 1\n\nBye", $c);
        $this->assertSame(null, ExplanationPrompt::cleanReply('   '));
        $this->assertSame(1400, mb_strlen(ExplanationPrompt::cleanReply(str_repeat('a', 3000))));
    }

    public function test_every_form_field_has_a_validation_rule(): void
    {
        $rules = ApplicationSchema::rules();
        foreach (ApplicationSchema::fields() as $key => $f) {
            $this->assertTrue(isset($rules[$key]), "rule for $key");
        }
    }

    public function test_every_rule_fact_the_scholarships_use_is_collected_or_derived(): void
    {
        $known = array_merge(array_keys(ApplicationSchema::fields()), array_keys(AnswerNormalizer::facts([])), ['program_field', 'program_name']);   // both are derived from the chosen program by ProgramCatalog
        $walk = function (array $rules) use (&$walk, $known) {
            foreach ($rules as $r) {
                if (isset($r['rules'])) {
                    $walk($r['rules']);
                }
                if (isset($r['fact'])) {
                    $this->assertTrue(in_array($r['fact'], $known, true), 'fact '.$r['fact']);
                }
            }
        };
        foreach ($this->scholarships() as $s) {
            $walk($s['rules']);
        }
    }

    public function test_program_names_map_to_fields(): void
    {
        $map = require __DIR__.'/../../config/program_fields.php';
        $f = fn ($n) => ProgramCatalog::fieldFor($n, $map);
        $this->assertSame('it', $f('Bachelor of Science in Information Technology'));
        $this->assertSame('it', $f('BSIS'));
        $this->assertSame('criminology', $f('BS Criminology'));
        $this->assertSame('education', $f('Bachelor of Physical Education'));
        $this->assertSame('education', $f('Bachelor of Secondary Education major in Math'));
        $this->assertSame('agriculture_forestry', $f('BS Agroforestry'));
        $this->assertSame('stem_other', $f('BS Marine Biology'));
        $this->assertSame('hospitality', $f('BS Hospitality Management'));
        $this->assertSame('tourism', $f('BS Tourism Management'));
        $this->assertSame('business', $f('BS Business Administration'));
        $this->assertSame('engineering_civil', $f('BS Civil Engineering'));
        $this->assertSame('environment_sustainability', $f('BS Environmental Science'));
        $this->assertSame('social_science_humanities', $f('BS Political Science'));
        $this->assertSame('social_science_humanities', $f('Bachelor of Public Administration (BPA)'));
        $this->assertSame('disaster_resilience', $f('Bachelor of Science in Disaster Resilience and Management (BSDRM)'));
        $this->assertSame('business', $f('Bachelor of Science in Entrepreneurship (BS ENTREP)'));
        $this->assertSame('stem_other', $f('Bachelor of Science in Fisheries and Aquatic Sciences (BSFAS)'));
        $this->assertSame('unrecognized', $f('Underwater Basket Weaving'));
        $this->assertSame('unrecognized', $f(null));
    }

    /** Every program_in rule must sort every DNSC program into pass / maybe / not (no silent confirms). */
    public function test_program_lists_cover_every_dnsc_program(): void
    {
        $programs = array_merge(...array_values((require __DIR__.'/../../config/dnsc_campuses.php')['institutes']));
        $this->assertCount(18, $programs);
        $walk = function (array $rules, string $slug) use (&$walk, $programs) {
            foreach ($rules as $r) {
                if (isset($r['rules'])) {
                    $walk($r['rules'], $slug);
                }
                if (($r['type'] ?? null) === 'program_in' && empty($r['open_list'])) {   // open lists (CHED priority programs) never fail an unlisted program
                    $listed = array_merge($r['pass'] ?? [], $r['maybe'] ?? [], $r['not'] ?? []);
                    foreach ($programs as $p) {
                        $this->assertContains($p, $listed, "$slug: $p is not sorted");
                    }
                }
            }
        };
        foreach ($this->scholarships() as $s) {
            $walk($s['rules'], $s['slug']);
        }
    }

    /** The farmer/fisherfolk question was added on 2026-11-18: older answers must not fail on it. */
    public function test_farm_question_missing_from_an_old_form_is_unknown_not_a_fail(): void
    {
        $old = $this->input();
        unset($old['farm_fisher_family']);
        $rows = (new MatchingService)->evaluate($this->cleaned($old), $this->scholarships());
        $i = array_search('coscho', array_column($this->scholarships(), 'slug'), true);
        $coconut = array_values(array_filter($rows[$i]['checks'], fn ($c) => $c['key'] === 'coconut'))[0];
        $this->assertSame('unknown', $coconut['result']);
    }

    public function test_new_scholarships_follow_the_farm_answer(): void
    {
        // Answered "None of these": the coconut / RSBSA family rules fail.
        $none = $this->statuses($this->input(['household_income' => 'up_to_300k']));
        $this->assertSame('not_eligible', $none['coscho']);
        $this->assertSame('not_eligible', $none['landbank-ilp']);

        // Coconut farmer family, income within ₱300,000, IT (a CoScho priority program): a candidate.
        $coco = $this->statuses($this->input(['household_income' => 'up_to_300k', 'farm_fisher_family' => ['coconut']]));
        $this->assertNotSame('not_eligible', $coco['coscho']);

        // RSBSA farmer family, incoming BSIT student, income within ₱300,000: a LANDBANK candidate.
        $rsbsa = $this->statuses($this->input(['household_income' => 'up_to_300k', 'farm_fisher_family' => ['rsbsa'], 'special_groups' => []]));
        $this->assertNotSame('not_eligible', $rsbsa['landbank-ilp']);

        // BFAR is for BS Fisheries only: a BSIT student never qualifies.
        $this->assertSame('not_eligible', $this->statuses($this->input(['farm_fisher_family' => ['fishr']]))['bfar-fsp']);
    }

    public function test_unrecognized_program_asks_to_confirm_instead_of_failing(): void
    {
        $by = (new MatchingService)->evaluate($this->cleaned($this->input(), 'unrecognized'), $this->scholarships());
        $dost = array_values(array_filter($by, fn ($r) => $r['scholarship_id'] === 7))[0];
        $course = array_values(array_filter($dost['checks'], fn ($c) => $c['key'] === 'course'))[0];
        $this->assertSame('confirm', $course['result']);
    }
}
