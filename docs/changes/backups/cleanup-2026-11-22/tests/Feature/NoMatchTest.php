<?php

namespace Tests\Feature;

use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use App\Models\ScholarshipMatch;
use App\Models\User;
use Database\Seeders\ScholarshipSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * "Not a match right now" scholarships are left out of the results. When nothing matches,
 * the page says so and lists the scholarships that miss by exactly one required rule.
 */
class NoMatchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ScholarshipSeeder::class);
    }

    private function check(string $label, string $result, bool $required = true, ?string $detail = null): array
    {
        return ['key' => md5($label), 'label' => $label, 'result' => $result, 'required' => $required, 'detail' => $detail, 'src' => 'page'];
    }

    /** A student whose current answers produced the given [slug => [status, score, checks]] matches. */
    private function studentWith(array $matches): User
    {
        $u = new User;
        $u->name = 'Test Student';
        $u->email = 'student@example.test';
        $u->password = 'secret-password';
        $u->role = 'student';
        $u->status = 'active';
        $u->save();

        $app = ScholarshipApplication::create(['user_id' => $u->id, 'version' => 1, 'is_current' => true, 'submitted_at' => now(), 'answers' => ['citizenship' => 'filipino']]);
        foreach ($matches as $slug => [$status, $score, $checks]) {
            ScholarshipMatch::create([
                'scholarship_application_id' => $app->id,
                'scholarship_id' => Scholarship::where('slug', $slug)->value('id'),
                'status' => $status, 'score' => $score, 'checks' => $checks,
            ]);
        }

        return $u;
    }

    public function test_results_leave_out_scholarships_that_are_not_a_match(): void
    {
        $u = $this->studentWith([
            'ched-tdp' => ['eligible', 95, [$this->check('Filipino citizen', 'pass')]],
            'ched-tes' => ['not_eligible', 40, [$this->check('Priority group', 'fail')]],
        ]);

        $html = $this->actingAs($u)->get('/student/results')->assertOk()->getContent();
        $this->assertStringContainsString('CHED Tulong Dunong', $html);
        $this->assertStringNotContainsString('Tertiary Education Subsidy', $html);
        $this->assertStringNotContainsString('Not eligible', $html);
        $this->assertStringNotContainsString('closeness', $html);
        $this->assertStringContainsString('>Eligible</span>', $html);
        $this->assertStringNotContainsString('No scholarship matches you right now', $html);

        // Each match is a slim row whose details open in a dialog.
        $tdp = ScholarshipMatch::whereHas('scholarship', fn ($q) => $q->where('slug', 'ched-tdp'))->value('id');
        $this->assertStringContainsString('id="match-'.$tdp.'" data-hash-dialog="match-dialog-'.$tdp.'"', $html);
        $this->assertStringContainsString('data-open-dialog="match-dialog-'.$tdp.'"', $html);
        $this->assertStringContainsString('<dialog class="dash-dialog sp-mdialog" id="match-dialog-'.$tdp.'"', $html);

        // The old "not a match" filter no longer lists anything; it falls back to all matches.
        $this->actingAs($u)->get('/student/results?status=not_eligible')->assertOk()
            ->assertSee('CHED Tulong Dunong')->assertDontSee('Tertiary Education Subsidy');
    }

    public function test_when_nothing_matches_the_one_requirement_away_scholarships_are_shown(): void
    {
        $u = $this->studentWith([
            // One required rule fails: a near miss. The optional fail does not count.
            'ched-tdp' => ['not_eligible', 80, [
                $this->check('Filipino citizen', 'pass'),
                $this->check('Combined parent / guardian gross income of ₱400,000 or less', 'fail', true, 'Your answer: above ₱400,000.'),
                $this->check('DNSC has TDP slots', 'fail', false),
            ]],
            // Two required rules fail: not a near miss.
            'ched-tes' => ['not_eligible', 70, [$this->check('Priority group', 'fail'), $this->check('No previous degree', 'fail')]],
        ]);

        $html = $this->actingAs($u)->get('/student/results')->assertOk()->getContent();
        $this->assertStringContainsString('No scholarship matches you right now', $html);
        $this->assertStringContainsString('Closest: missing just one requirement', $html);
        $this->assertStringContainsString('CHED Tulong Dunong', $html);
        $this->assertStringContainsString('₱400,000 or less', $html);
        $this->assertStringContainsString('Your answer: above ₱400,000.', $html);
        $this->assertStringNotContainsString('Tertiary Education Subsidy', $html);

        $this->actingAs($u)->get('/student/dashboard')->assertOk()
            ->assertSee('No scholarship matches you right now')
            ->assertSee('Closest: missing just one requirement')
            ->assertSee('CHED Tulong Dunong');
    }

    public function test_near_misses_are_not_shown_while_something_matches(): void
    {
        $u = $this->studentWith([
            'ched-tes' => ['possible', 75, [$this->check('Priority group', 'pass')]],
            'ched-tdp' => ['not_eligible', 80, [$this->check('Income', 'fail')]],
        ]);

        $this->actingAs($u)->get('/student/results')->assertOk()
            ->assertDontSee('Closest: missing just one requirement')
            ->assertDontSee('CHED Tulong Dunong');
    }

    public function test_when_nothing_is_one_requirement_away_the_page_says_so(): void
    {
        $u = $this->studentWith([
            'ched-tes' => ['not_eligible', 30, [$this->check('Priority group', 'fail'), $this->check('No previous degree', 'fail')]],
        ]);

        $this->actingAs($u)->get('/student/results')->assertOk()
            ->assertSee('No scholarship matches you right now')
            ->assertSee('No scholarship is just one requirement away from you either');
    }

    public function test_the_public_finder_is_gone_and_guests_are_sent_to_sign_up(): void
    {
        // Owner, 2026-11-19: guests only browse scholarships; matching needs a student account.
        $this->get('/scholarship-finder')->assertRedirect('/find-my-matches');
        $this->get('/find-my-matches')->assertRedirect(route('student.register'));
        $this->post('/scholarship-finder', [])->assertRedirect('/find-my-matches');   // nothing is checked or saved
    }
}
