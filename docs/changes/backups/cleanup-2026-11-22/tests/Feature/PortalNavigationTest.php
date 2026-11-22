<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use App\Models\ScholarshipMatch;
use App\Models\User;
use Database\Seeders\ScholarshipSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** Routing logic and the in-portal pages that keep people inside their dashboard. */
class PortalNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->seed(ScholarshipSeeder::class);
    }

    private function user(string $role = 'student', ?string $providerKey = null): User
    {
        $u = new User;
        $u->name = 'Rochelle Abella';
        $u->email = $role.'@example.test';
        $u->password = Hash::make('Pass-12345678');
        $u->role = $role;
        $u->provider_key = $providerKey;
        $u->status = 'active';
        $u->save();

        return $u;
    }

    private function withMatches(User $u): ScholarshipApplication
    {
        $app = ScholarshipApplication::create(['user_id' => $u->id, 'version' => 1, 'is_current' => true, 'answers' => [], 'submitted_at' => now()]);
        ScholarshipMatch::create([
            'scholarship_application_id' => $app->id,
            'scholarship_id' => Scholarship::where('slug', 'ched-tdp')->value('id'),
            'status' => 'eligible', 'score' => 96, 'checks' => [],
        ]);

        return $app;
    }

    // ---- "Find a Scholarship" sends everyone to the right step ----

    public function test_find_sends_guests_to_register(): void
    {
        $this->get('/find-my-matches')->assertRedirect(route('student.register'));
    }

    public function test_find_sends_a_new_student_to_the_questionnaire(): void
    {
        $this->actingAs($this->user())->get('/find-my-matches')->assertRedirect(route('student.application.show'));
    }

    public function test_find_sends_a_student_with_answers_to_their_matches(): void
    {
        $u = $this->user();
        $this->withMatches($u);
        $this->actingAs($u)->get('/find-my-matches')->assertRedirect(route('student.results'));
    }

    public function test_find_sends_a_provider_to_their_dashboard(): void
    {
        $this->actingAs($this->user('provider', 'ched'))->get('/find-my-matches')->assertRedirect(route('provider.dashboard'));
    }

    public function test_find_sends_the_admin_to_the_admin_overview(): void
    {
        $this->actingAs($this->user('admin'))->get('/find-my-matches')->assertRedirect(route('admin.overview'));
    }

    // ---- Signed-in users on login/register pages go to their own dashboard ----

    public function test_signed_in_users_are_sent_to_their_dashboard_from_login_pages(): void
    {
        $this->actingAs($this->user())->get('/studentLogin')->assertRedirect(route('student.dashboard'));
        $this->get('/studentRegister')->assertRedirect(route('student.dashboard'));

        auth()->logout();
        $this->actingAs($this->user('provider', 'ched'))->get('/providerLogin')->assertRedirect(route('provider.dashboard'));
    }

    // ---- Student portal: help and catalogue stay inside the portal ----

    public function test_student_help_page_and_message_use_the_account_details(): void
    {
        $u = $this->user();
        $this->actingAs($u)->get('/student/help')->assertOk()
            ->assertSee('About BAKR')->assertSee('Message the BAKR team');

        $this->post('/student/help', ['topic' => 'question', 'message' => 'Short'])->assertSessionHasErrors('message');

        $this->post('/student/help', [
            'topic' => 'question',
            'message' => 'How do I share a match with a provider?',
            'email' => 'attacker@example.test',     // ignored: comes from the account
            'name' => 'Someone Else',
        ])->assertRedirect(route('student.help'))->assertSessionHas('sent', fn ($s) => $s['title'] === 'Message sent');

        // The next page opens the success modal (not a green alert), with a no-JavaScript fallback.
        $html = $this->get('/student/help')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/<dialog[^>]*id="sent-dialog"[^>]*data-auto-open/', $html);
        $this->assertStringContainsString('The BAKR team will reply to student@example.test.', $html);
        $this->assertStringContainsString('<noscript>', $html);
        $this->get('/student/help')->assertDontSee('id="sent-dialog"', false);   // shown once only

        $msg = ContactMessage::firstOrFail();
        $this->assertSame('student@example.test', $msg->email);
        $this->assertSame('Rochelle Abella', $msg->name);
    }

    public function test_student_catalogue_shows_their_own_result(): void
    {
        $u = $this->user();
        $this->withMatches($u);

        $response = $this->actingAs($u)->get('/student/scholarships')->assertOk()
            ->assertSee('sch-mine', false)
            ->assertDontSee('closeness')
            ->assertSee('See my result');
        foreach (Scholarship::all() as $s) {
            $response->assertSee('id="modal-'.$s->slug.'"', false);
        }
    }

    public function test_student_pages_no_longer_send_people_to_public_help_pages(): void
    {
        $u = $this->user();
        $this->withMatches($u);

        $this->actingAs($u)->get('/student/dashboard')->assertOk()
            ->assertDontSee('href="'.url('/contact').'"', false)
            ->assertDontSee('href="'.url('/scholarships').'"', false)
            ->assertSee(route('student.help'), false)
            ->assertSee(route('student.scholarships'), false)
            ->assertSee('id="about-bakr"', false);
    }

    // ---- Provider portal help ----

    public function test_provider_help_page_and_message(): void
    {
        $this->actingAs($this->user('provider', 'ched'));
        $this->get('/provider/help')->assertOk()->assertSee('Questions providers ask')->assertSee('id="about-bakr"', false);

        // Providers edit their own scholarships, so "Scholarship info needs updating" is not offered (nor accepted).
        $this->get('/provider/help')->assertDontSee('Scholarship info needs updating')->assertDontSee('For corrections, say which scholarship');
        $this->post('/provider/help', ['topic' => 'info', 'message' => 'Please update the TDP application window.'])->assertSessionHasErrors('topic');
        $this->assertSame(0, ContactMessage::count());

        $this->post('/provider/help', ['topic' => 'problem', 'message' => 'The import page shows an error.'])
            ->assertRedirect(route('provider.help'))->assertSessionHas('sent', fn ($s) => $s['title'] === 'Message sent');
        $this->get('/provider/help')->assertSee('id="sent-dialog"', false)->assertSee('Message sent');

        $msg = ContactMessage::firstOrFail();
        $this->assertSame('problem', $msg->topic);
        $this->assertStringContainsString('(provider)', $msg->name);
        $this->assertSame('provider@example.test', $msg->email);
    }

    public function test_students_cannot_use_the_provider_help_form(): void
    {
        $this->actingAs($this->user())->post('/provider/help', ['topic' => 'info', 'message' => 'Hello there provider'])->assertForbidden();
        $this->assertSame(0, ContactMessage::count());
    }
}
