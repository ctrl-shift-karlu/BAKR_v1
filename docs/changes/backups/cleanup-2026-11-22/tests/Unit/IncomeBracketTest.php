<?php

namespace Tests\Unit;

use App\Matching\AnswerNormalizer;
use App\Matching\ApplicationSchema;
use App\Matching\MatchingService;
use PHPUnit\Framework\TestCase;

/**
 * Household income brackets (owner, 2026-11-19: "a better income bracket based on each provider's
 * eligibility"). Every income cap a scholarship publishes must sit exactly on a bracket edge, so the
 * student gets a clear pass or fail and never "your range overlaps the limit".
 */
class IncomeBracketTest extends TestCase
{
    private function scholarships(): array
    {
        return (require __DIR__.'/../../config/scholarships.php')['scholarships'];
    }

    public function test_brackets_are_contiguous_and_cover_every_income(): void
    {
        $prev = null;
        foreach (ApplicationSchema::INCOME as $b) {
            $prev === null ? $this->assertSame(0, $b['min']) : $this->assertSame($prev + 1, $b['min']);
            $prev = $b['max'];
        }
        $this->assertSame(PHP_INT_MAX, $prev);
    }

    public function test_every_published_income_cap_is_a_bracket_edge(): void
    {
        $edges = array_column(ApplicationSchema::INCOME, 'max');
        $caps = [];
        foreach ($this->scholarships() as $s) {
            foreach ($s['rules'] as $r) {
                if (($r['type'] ?? null) === 'income_lte') {
                    $caps[$s['slug']] = $r['value'];
                    $this->assertContains($r['value'], $edges, "{$s['slug']}: the ₱{$r['value']} cap is not a bracket edge");
                }
            }
        }
        // Sources: CMSP 500k, BPMSP 2M, Cebuana 350k, LANDBANK 300k, CoScho 300k.
        $this->assertSame(['ched-cmsp' => 500000, 'bpmsp' => 2000000, 'cebuana-clfi' => 350000, 'landbank-ilp' => 300000, 'coscho' => 300000], $caps);
        // Simple on purpose: one edge per real limit and nothing else (BPMSP's finer steps only change its ranking points).
        $this->assertCount(5, ApplicationSchema::INCOME);
    }

    public function test_each_provider_cap_gives_a_clear_answer_at_its_own_edge(): void
    {
        $ev = new MatchingService;
        $status = function (string $slug, string $bracket) use ($ev): string {
            $s = array_values(array_filter($this->scholarships(), fn ($x) => $x['slug'] === $slug))[0];
            $facts = AnswerNormalizer::facts(['household_income' => $bracket]);
            $check = array_column($ev->evaluate(['household_income' => $bracket], [['id' => 1, 'rules' => array_values(array_filter($s['rules'], fn ($r) => ($r['type'] ?? '') === 'income_lte'))]])[0]['checks'], 'result', 'key');

            return $check['income'] ?? 'none';
        };

        $this->assertSame('pass', $status('landbank-ilp', 'up_to_300k'));   // up to ₱300,000
        $this->assertSame('fail', $status('landbank-ilp', '300k_350k'));
        $this->assertSame('pass', $status('cebuana-clfi', '300k_350k'));   // up to ₱350,000
        $this->assertSame('fail', $status('cebuana-clfi', '350k_500k'));
        $this->assertSame('pass', $status('ched-cmsp', '350k_500k'));      // up to ₱500,000
        $this->assertSame('fail', $status('ched-cmsp', '500k_750k'));
        $this->assertSame('pass', $status('bpmsp', '500k_2m'));           // up to ₱2,000,000
        $this->assertSame('fail', $status('bpmsp', 'over_2m'));
    }

    public function test_forms_saved_with_an_old_bracket_still_read(): void
    {
        $facts = AnswerNormalizer::facts(['household_income' => '250k_350k']);
        $this->assertSame(250001, $facts['income_min']);
        $this->assertSame(350000, $facts['income_max']);
        $this->assertSame('₱250,001 – ₱350,000', ApplicationSchema::optionLabel('household_income', '250k_350k'));
        $this->assertArrayNotHasKey('250k_350k', ApplicationSchema::fields()['household_income']['options']);   // not offered any more
    }

    public function test_age_and_province_answer_bfar_and_landbank_rules(): void
    {
        $this->assertSame(18, ApplicationSchema::age('2008-06-01', new \DateTimeImmutable('2026-11-19')));
        $this->assertSame(17, ApplicationSchema::age('2008-12-01', new \DateTimeImmutable('2026-11-19')));   // birthday not reached yet
        $facts = AnswerNormalizer::facts(['birth_date' => '2008-06-01', 'home_province' => 'davao_del_norte']);
        $this->assertIsInt($facts['age']);
        $this->assertSame('yes', $facts['lives_in_davao_del_norte']);
        $this->assertSame('no', AnswerNormalizer::facts(['home_province' => 'davao_de_oro'])['lives_in_davao_del_norte']);
        $this->assertNull(AnswerNormalizer::facts([])['lives_in_davao_del_norte']);   // an older form: "needs info", not a fail
        $this->assertNull(AnswerNormalizer::facts(['top_five' => 'unsure'])['top_five']);   // "not sure" is not "no"
    }
}
