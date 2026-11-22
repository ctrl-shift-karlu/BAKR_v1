<?php

namespace Tests\Feature;

use App\Ai\GeminiExplainer;
use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use App\Models\ScholarshipMatch;
use App\Models\User;
use App\Services\ExplanationService;
use Database\Seeders\ScholarshipSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** Gemini writes the explanations; the rule engine still decides. No real API call is made. */
class GeminiExplainerTest extends TestCase
{
    use RefreshDatabase;

    private const GEMINI = 'generativelanguage.googleapis.com/*';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ScholarshipSeeder::class);
        config(['bakr_ai.provider' => 'gemini', 'bakr_ai.gemini.key' => 'test-gemini-key', 'bakr_ai.gemini.model' => 'gemini-3.5-flash-lite']);
    }

    private function reply(string $text): array
    {
        return ['candidates' => [['content' => ['parts' => [['text' => $text]]]]]];
    }

    /** @return array{0: User, 1: ScholarshipMatch} */
    private function studentWithMatch(string $email = 'maria.santos@example.test', string $aiMatch = ScholarshipApplication::AI_FAILED): array
    {
        // The explainer is the fallback when the AI matcher did not write the text (failed / off);
        // while the matcher is pending it writes the explanations itself (AiMatcherTest).
        $u = new User;
        $u->name = 'Maria Santos';
        $u->email = $email;
        $u->password = 'secret-password';
        $u->role = 'student';
        $u->status = 'active';
        $u->save();

        $app = ScholarshipApplication::create(['user_id' => $u->id, 'version' => 1, 'is_current' => true, 'submitted_at' => now(), 'answers' => ['citizenship' => 'filipino'], 'ai_match_status' => $aiMatch]);
        $m = ScholarshipMatch::create([
            'scholarship_application_id' => $app->id,
            'scholarship_id' => Scholarship::where('slug', 'ched-tdp')->value('id'),
            'status' => 'possible', 'score' => 90,
            'checks' => [
                ['key' => 'income', 'label' => 'Combined parent / guardian gross income of ₱400,000 or less', 'result' => 'pass', 'required' => true, 'detail' => 'Your income range (Up to ₱250,000) is within the limit.', 'src' => 'web'],
                ['key' => 'slots', 'label' => 'DNSC has TDP slots this semester', 'result' => 'confirm', 'required' => false, 'detail' => 'Ask the DNSC Scholarship and Grants Unit.', 'src' => 'web'],
            ],
        ]);

        return [$u, $m];
    }

    public function test_gemini_request_has_the_key_in_a_header_and_no_personal_data(): void
    {
        Http::fake([self::GEMINI => Http::response($this->reply('**You may qualify** for TDP.'))]);
        [$u, $m] = $this->studentWithMatch();

        $text = app(GeminiExplainer::class)->explainMatch(ExplanationService::context($m->load('scholarship')));

        $this->assertSame('You may qualify for TDP.', $text);   // markdown stripped
        Http::assertSent(function (HttpRequest $r) {
            $body = json_encode($r->data(), JSON_UNESCAPED_UNICODE);

            return str_contains($r->url(), '/models/gemini-3.5-flash-lite:generateContent')
                && ! str_contains($r->url(), 'test-gemini-key')                 // key never in the URL
                && $r->header('x-goog-api-key') === ['test-gemini-key']
                && str_contains($body, 'A rule engine has ALREADY decided')     // same safety prompt as Claude
                && str_contains($body, 'Up to ₱250,000')                         // the answer per requirement (chosen by the owner)
                && ! str_contains($body, 'Maria') && ! str_contains($body, '@') // never the name or e-mail
                && ! str_contains($body, 'closeness');                          // never a percentage
        });
    }

    public function test_opening_a_match_gets_and_saves_the_gemini_explanation_once(): void
    {
        Http::fake([self::GEMINI => Http::response($this->reply('You may qualify for the CHED TDP. Confirm the slots with DNSC.'))]);
        [$u, $m] = $this->studentWithMatch();

        $this->actingAs($u)->postJson(route('student.matches.explain', $m->id))
            ->assertOk()->assertJson(['ok' => true, 'text' => 'You may qualify for the CHED TDP. Confirm the slots with DNSC.']);
        $this->assertSame('gemini', $m->fresh()->explanation_source);
        $this->assertSame('possible', $m->fresh()->status);   // the AI never changes the result

        // Cached: asking again does not call Gemini again, and the page shows the AI text.
        $this->actingAs($u)->postJson(route('student.matches.explain', $m->id))->assertOk();
        Http::assertSentCount(1);
        $this->actingAs($u)->get('/student/results')->assertOk()
            ->assertSee('You may qualify for the CHED TDP. Confirm the slots with DNSC.')
            ->assertSee('AI explanation');
    }

    public function test_when_gemini_fails_the_built_in_explanation_stays(): void
    {
        Http::fake([self::GEMINI => Http::response(['error' => ['message' => 'quota']], 429)]);
        [$u, $m] = $this->studentWithMatch();

        $this->actingAs($u)->postJson(route('student.matches.explain', $m->id))->assertStatus(503)->assertJson(['ok' => false]);
        $this->assertNull($m->fresh()->explanation);
        $this->actingAs($u)->get('/student/results')->assertOk()
            ->assertSee('Quick explanation')
            ->assertSee('data-ai-url=', false);   // it will try again next time
    }

    public function test_a_student_cannot_ask_for_another_students_explanation(): void
    {
        Http::fake();
        [, $m] = $this->studentWithMatch('one@example.test');
        [$other] = $this->studentWithMatch('two@example.test');

        $this->actingAs($other)->postJson(route('student.matches.explain', $m->id))->assertNotFound();
        Http::assertNothingSent();
    }

    public function test_dashboard_overview_is_written_by_gemini(): void
    {
        Http::fake([self::GEMINI => Http::response($this->reply('The CHED TDP looks promising. Confirm the slots with DNSC.'))]);
        [$u] = $this->studentWithMatch();

        $this->actingAs($u)->get('/student/dashboard')->assertOk()->assertSee('data-ai-auto', false);
        $this->actingAs($u)->postJson(route('student.results.explain'))->assertOk()->assertJson(['ok' => true]);
        $this->actingAs($u)->get('/student/dashboard')->assertOk()
            ->assertSee('The CHED TDP looks promising.')->assertSee('AI overview')->assertDontSee('data-ai-auto', false);
    }

    public function test_without_a_key_nothing_is_sent_and_pages_use_the_built_in_text(): void
    {
        config(['bakr_ai.gemini.key' => null]);
        Http::fake();
        [$u, $m] = $this->studentWithMatch(aiMatch: ScholarshipApplication::AI_OFF);

        $this->assertFalse(ExplanationService::aiAvailable());
        $this->actingAs($u)->get('/student/results')->assertOk()
            ->assertDontSee('data-ai-url=', false)
            ->assertDontSee('data-ai-match-url=', false)
            ->assertSee('The AI is turned off on this server')
            ->assertSee('The AI matcher is turned off on this server');
        $this->actingAs($u)->postJson(route('student.matches.explain', $m->id))->assertStatus(503);
        Http::assertNothingSent();
    }
}
