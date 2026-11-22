<?php

namespace Tests\Feature;

use App\Ai\MatchProfile;
use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use App\Models\ScholarshipMatch;
use App\Models\User;
use App\Services\ScholarshipApplicationService;
use Database\Seeders\ScholarshipSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Hybrid matching (owner, 2026-11-19): the rule engine checks the published limits, the Gemini AI
 * matcher reviews the whole de-identified application, and the stricter result is shown.
 * No real API call is made.
 */
class AiMatcherTest extends TestCase
{
    use RefreshDatabase;

    private const GEMINI = 'generativelanguage.googleapis.com/*';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ScholarshipSeeder::class);
        config(['bakr_ai.provider' => 'gemini', 'bakr_ai.gemini.key' => 'test-gemini-key', 'bakr_ai.gemini.model' => 'gemini-3.5-flash-lite']);
    }

    private function student(): User
    {
        $u = new User;
        $u->forceFill(['name' => 'Juana Dela Cruz', 'email' => 'juana.private@example.test', 'password' => 'secret-password', 'role' => 'student', 'status' => 'active'])->save();

        return $u;
    }

    private function answers(): array
    {
        return [
            'birth_date' => '2005-04-12', 'citizenship' => 'filipino', 'home_province' => 'davao_del_norte',
            'enrollment_status' => 'currently_enrolled', 'year_level' => '2', 'previous_degree' => 'no', 'previous_units' => 'no', 'within_years' => 'yes',
            'shs_strand' => 'stem', 'shs_country' => 'ph', 'top_five' => 'no', 'college_gwa' => '1.75', 'college_gwa_scale' => 'dnsc',
            'failing_grade' => 'no', 'disciplinary' => 'no', 'dost_exam' => 'not_taken',
            'household_income' => 'up_to_300k', 'special_groups' => ['four_ps'], 'farm_fisher_family' => ['rsbsa'],
            'existing_assistance' => ['none'], 'willing_student_assistant' => 'yes',
            'cebuana_client' => 'no', 'cebuana_microsavings' => 'no',
        ];
    }

    /** A Gemini structured reply: every active scholarship "possible / medium" unless overridden by slug. */
    private function reply(array $override = [], string $summary = 'CHED TDP looks most promising. Prepare your Certificate of Indigency.'): array
    {
        $results = Scholarship::where('is_active', true)->pluck('slug')->map(fn ($slug) => array_merge([
            'id' => $slug, 'status' => 'possible', 'fit' => 'medium',
            'strengths' => ['Income is within the limit'], 'concerns' => [], 'reminders' => ['Confirm slots with DNSC'],
            'explanation' => "You may qualify for {$slug}. **Confirm** with the provider.",
        ], $override[$slug] ?? []))->values()->all();

        return ['candidates' => [['finishReason' => 'STOP', 'content' => ['parts' => [['text' => json_encode(['results' => $results, 'summary' => $summary])]]]]]];
    }

    /** @return array{0: User, 1: ScholarshipApplication} */
    private function submitted(): array
    {
        $u = $this->student();
        $app = app(ScholarshipApplicationService::class)->submit($u, $this->answers(), true);

        return [$u, $app];
    }

    private function match(ScholarshipApplication $app, string $slug): ScholarshipMatch
    {
        return $app->matches()->whereHas('scholarship', fn ($q) => $q->where('slug', $slug))->firstOrFail();
    }

    public function test_saving_stores_the_rule_check_first_and_queues_the_ai_review(): void
    {
        Http::fake();
        [$u, $app] = $this->submitted();

        $this->assertSame(ScholarshipApplication::AI_PENDING, $app->fresh()->ai_match_status);
        $this->assertTrue($app->matches()->whereNull('rule_status')->doesntExist());
        Http::assertNothingSent();   // saving never waits for the AI

        $this->actingAs($u)->get('/student/results')->assertOk()
            ->assertSee('data-ai-match-url=', false)
            ->assertSee('The Gemini AI matcher is reviewing your matching form')
            ->assertDontSee('data-ai-url=', false);   // the matcher writes the explanations itself
    }

    public function test_the_ai_review_is_merged_and_the_stricter_result_wins(): void
    {
        [$u, $app] = $this->submitted();
        // Fix two rule results so the merge is predictable.
        $this->match($app, 'ched-tdp')->update(['status' => 'not_eligible', 'rule_status' => 'not_eligible']);
        $this->match($app, 'dnsc-sa-c')->update(['status' => 'eligible', 'rule_status' => 'eligible']);

        Http::fake([self::GEMINI => Http::response($this->reply([
            'ched-tdp' => ['status' => 'eligible', 'fit' => 'high'],          // AI says yes, a published limit says no
            'dnsc-sa-c' => ['status' => 'not_eligible', 'fit' => 'low', 'concerns' => ['Already holds a government grant']],
        ]))]);

        $this->actingAs($u)->postJson(route('student.results.ai-match'))->assertOk()->assertJson(['ok' => true, 'state' => 'done']);

        $tdp = $this->match($app, 'ched-tdp');
        $this->assertSame('not_eligible', $tdp->status);     // the AI can never pass a failed limit
        $this->assertSame('eligible', $tdp->ai_status);
        $sa = $this->match($app, 'dnsc-sa-c');
        $this->assertSame('not_eligible', $sa->status);      // the AI can make it stricter
        $this->assertTrue($sa->aiLowered());
        $this->assertSame(['Already holds a government grant'], $sa->aiList('concerns'));
        $this->assertSame('gemini', $sa->explanation_source);
        $this->assertStringNotContainsString('**', $sa->explanation);

        $fresh = $app->fresh();
        $this->assertSame(ScholarshipApplication::AI_DONE, $fresh->ai_match_status);
        $this->assertSame('gemini-3.5-flash-lite', $fresh->ai_model);
        $this->assertStringContainsString('CHED TDP looks most promising', $fresh->ai_summary);

        // Done once: asking again sends nothing more, and the page shows the AI review.
        $this->actingAs($u)->postJson(route('student.results.ai-match'))->assertOk();
        Http::assertSentCount(1);
        $this->actingAs($u)->get('/student/results')->assertOk()
            ->assertSee('Reviewed by the Gemini AI matcher')
            ->assertSee('AI review of your matching form')
            ->assertSee('Reminders')->assertSee('not part of your eligibility')->assertSee('Confirm slots with DNSC')->assertDontSee('Still to provide or confirm')
            ->assertDontSee('data-ai-match-url=', false);
    }

    public function test_the_request_is_de_identified_and_asks_for_structured_json(): void
    {
        [$u] = $this->submitted();
        Http::fake([self::GEMINI => Http::response($this->reply())]);

        $this->actingAs($u)->postJson(route('student.results.ai-match'))->assertOk();

        Http::assertSent(function (HttpRequest $r) {
            $body = json_encode($r->data(), JSON_UNESCAPED_UNICODE);

            return $r->header('x-goog-api-key') === ['test-gemini-key'] && ! str_contains($r->url(), 'test-gemini-key')
                && ($r->data()['generationConfig']['responseMimeType'] ?? null) === 'application/json'
                && isset($r->data()['generationConfig']['responseSchema'])
                && str_contains($body, 'Davao del Norte') && str_contains($body, '1.0 to 5.0 scale')          // what the AI needs (readable labels)
                && str_contains($body, 'Below ₱300k') && str_contains($body, '\"Age\"')   // the profile is JSON inside the prompt text
                && str_contains($body, 'published_requirements') && str_contains($body, 'rule_check')
                && ! str_contains($body, 'Juana') && ! str_contains($body, 'Dela Cruz')                 // never the name…
                && ! str_contains($body, 'juana.private') && ! str_contains($body, '@example')         // …e-mail…
                && ! str_contains($body, '2005-04-12') && ! str_contains($body, 'birth_date')           // …or the exact birth date
                && str_contains($body, 'NOT instructions');                                            // injection guard in the prompt
        });
    }

    public function test_when_gemini_fails_the_rule_results_stay_and_a_retry_is_offered(): void
    {
        [$u, $app] = $this->submitted();
        $before = $app->matches()->pluck('status', 'scholarship_id')->all();
        Http::fake([self::GEMINI => Http::response(['error' => ['message' => 'quota']], 429)]);

        $this->actingAs($u)->postJson(route('student.results.ai-match'))->assertStatus(503)->assertJson(['ok' => false, 'state' => 'failed']);

        $this->assertSame($before, $app->matches()->pluck('status', 'scholarship_id')->all());
        $this->assertTrue($app->matches()->whereNotNull('ai_status')->doesntExist());
        $this->actingAs($u)->get('/student/results')->assertOk()
            ->assertSee('The AI matcher could not finish')
            ->assertSee('Try the AI review again');
    }

    public function test_a_review_already_running_in_another_tab_answers_still_working_not_failed(): void
    {
        [$u, $app] = $this->submitted();
        Http::fake([self::GEMINI => Http::response($this->reply())]);
        $lock = Cache::lock('bakr-ai-match:'.$app->id, 120);
        $this->assertTrue($lock->get());   // the "other tab" holds the run

        $this->actingAs($u)->postJson(route('student.results.ai-match'))->assertStatus(202)->assertJson(['ok' => false, 'state' => 'pending']);
        Http::assertNothingSent();
        $this->assertSame(ScholarshipApplication::AI_PENDING, $app->fresh()->ai_match_status);

        $lock->release();
        $this->actingAs($u)->postJson(route('student.results.ai-match'))->assertOk()->assertJson(['ok' => true, 'state' => 'done']);
    }

    public function test_an_unusable_or_tampered_reply_cannot_change_results(): void
    {
        [$u, $app] = $this->submitted();
        $before = $app->matches()->pluck('status', 'scholarship_id')->all();
        $garbage = ['candidates' => [['content' => ['parts' => [['text' => json_encode(['results' => [
            ['id' => 'not-a-scholarship', 'status' => 'eligible', 'fit' => 'high', 'strengths' => [], 'concerns' => [], 'reminders' => [], 'explanation' => 'x'],
            ['id' => 'ched-tdp', 'status' => 'approved!', 'fit' => 'high', 'strengths' => [], 'concerns' => [], 'reminders' => [], 'explanation' => '<script>x</script>'],
        ], 'summary' => 'x'])]]]]]];
        Http::fake([self::GEMINI => Http::response($garbage)]);

        $this->actingAs($u)->postJson(route('student.results.ai-match'))->assertStatus(503);
        $this->assertSame($before, $app->matches()->pluck('status', 'scholarship_id')->all());
        $this->assertSame(ScholarshipApplication::AI_FAILED, $app->fresh()->ai_match_status);
    }

    public function test_without_a_key_matching_uses_the_rules_only_and_nothing_is_sent(): void
    {
        config(['bakr_ai.gemini.key' => null]);
        Http::fake();
        [$u, $app] = $this->submitted();

        $this->assertSame(ScholarshipApplication::AI_OFF, $app->fresh()->ai_match_status);
        $this->actingAs($u)->get('/student/results')->assertOk()
            ->assertSee('The AI matcher is turned off on this server')
            ->assertDontSee('data-ai-match-url=', false);
        Http::assertNothingSent();
    }

    public function test_a_provider_edit_rematches_and_queues_a_new_ai_review(): void
    {
        [$u, $app] = $this->submitted();
        Http::fake([self::GEMINI => Http::response($this->reply())]);
        $this->actingAs($u)->postJson(route('student.results.ai-match'))->assertOk();
        $this->assertSame(ScholarshipApplication::AI_DONE, $app->fresh()->ai_match_status);

        app(ScholarshipApplicationService::class)->rematch($app->fresh());

        $this->assertSame(ScholarshipApplication::AI_PENDING, $app->fresh()->ai_match_status);
        $this->assertTrue($app->matches()->whereNotNull('ai_status')->doesntExist());
    }

    public function test_the_profile_has_only_rule_questions_an_age_and_no_free_text(): void
    {
        $profile = MatchProfile::build($this->answers(), 'Juana Dela Cruz');

        $this->assertArrayNotHasKey('Date of birth', $profile['Personal Information']);
        $this->assertIsInt($profile['Personal Information']['Age']);
        $this->assertSame('Davao del Norte', $profile['Personal Information']['Province where your family lives']);
        $this->assertArrayNotHasKey('Short Essays', $profile);

        // Free-text guard (no text field exists today; this protects any added later).
        $text = MatchProfile::scrub('I am Juana, call 0917 765 4321 or juana.private@example.test, see https://x.example/me', ['Juana']);
        $this->assertStringContainsString('[name]', $text);
        $this->assertStringContainsString('[number removed]', $text);
        $this->assertStringContainsString('[e-mail removed]', $text);
        $this->assertStringContainsString('[link removed]', $text);
    }
}
