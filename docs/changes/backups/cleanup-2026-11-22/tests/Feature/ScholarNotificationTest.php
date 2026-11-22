<?php

namespace Tests\Feature;

use App\Models\Scholar;
use App\Models\ScholarMessage;
use App\Models\Scholarship;
use App\Models\User;
use App\Notifications\ScholarUpdate;
use App\Services\PublicAnalyticsService;
use App\Support\AcademicTerm;
use Database\Seeders\ScholarshipSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Updates from the provider to the scholar (owner, 2026-11-21): status changes, corrections made after a
 * request, and replies show on the scholar's Overview (sidebar badge until seen) and status / correction
 * notices are e-mailed. Also: the folded Profile cards and the live About totals.
 */
class ScholarNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->seed(ScholarshipSeeder::class);
    }

    private function account(string $role, string $email, ?string $providerKey = null): User
    {
        $u = new User;
        $u->forceFill(['name' => $role === 'scholar' ? 'Ana Reyes' : 'CHED', 'email' => $email, 'password' => Hash::make('Pass-12345678'),
            'role' => $role, 'status' => 'active', 'provider_key' => $providerKey])->save();

        return $u;
    }

    private function scholar(User $u): Scholar
    {
        return Scholar::create(['user_id' => $u->id, 'scholarship_id' => Scholarship::where('slug', 'ched-tdp')->value('id'),
            'status' => 'active', 'started_term' => AcademicTerm::current(), 'scholar_code' => 'TDP-1']);
    }

    public function test_a_status_change_reaches_the_scholar_in_the_portal_and_by_email(): void
    {
        Notification::fake();
        $ana = $this->account('scholar', 'ana@example.test');
        $s = $this->scholar($ana);
        $ched = $this->account('provider', 'ched@provider.test', 'ched');

        $this->actingAs($ched)->patch(route('provider.scholars.status', $s->id), ['status' => 'terminated'])->assertSessionHas('status');

        Notification::assertSentTo($ana, ScholarUpdate::class, fn ($n, $channels) => $channels === ['database'] && $n->kind === 'status'
            && $n->title === 'Your scholarship was terminated');
        Notification::assertSentTo(new AnonymousNotifiable, ScholarUpdate::class, fn ($n, $channels, $to) => $channels === ['mail']
            && $to->routes['mail'] === 'ana@example.test');

        // Same status again: nothing changes, nobody is told.
        $this->patch(route('provider.scholars.status', $s->id), ['status' => 'terminated']);
        Notification::assertSentToTimes($ana, ScholarUpdate::class, 1);
    }

    public function test_the_overview_lists_updates_and_the_badge_clears_once_seen(): void
    {
        $ana = $this->account('scholar', 'ana@example.test');
        $s = $this->scholar($ana);
        $ched = $this->account('provider', 'ched@provider.test', 'ched');

        $this->actingAs($ched)->patch(route('provider.scholars.status', $s->id), ['status' => 'terminated']);
        $this->assertSame(1, $ana->unreadNotifications()->count());

        $this->actingAs($ana)->get('/scholar/profile')->assertSee('1 new update');   // sidebar badge on Overview
        $this->get('/scholar')->assertOk()->assertSee('Updates from your provider')->assertSee('Your scholarship was terminated')->assertSee('New');
        $this->assertSame(0, $ana->unreadNotifications()->count());
        $this->get('/scholar/profile')->assertDontSee('new update');
    }

    public function test_a_correction_made_after_a_request_is_announced_and_unrequested_edits_are_not(): void
    {
        $ana = $this->account('scholar', 'ana@example.test');
        $s = $this->scholar($ana);
        $ched = $this->account('provider', 'ched@provider.test', 'ched');
        $personal = ['section' => 'personal', 'birthdate' => '2004-06-01', 'gender' => 'female'];

        // No request: the provider's own edit sends nothing.
        $this->actingAs($ched)->patch(route('provider.scholars.update', $s->id), $personal)->assertSessionHasNoErrors();
        $this->assertSame(0, $ana->notifications()->count());

        // An open request about the Personal card, then the provider changes it.
        ScholarMessage::create(['scholar_id' => $s->id, 'user_id' => $ana->id, 'topic' => 'personal', 'message' => 'My birthday should be June 2, 2004.']);
        $this->patch(route('provider.scholars.update', $s->id), ['birthdate' => '2004-06-02'] + $personal)->assertSessionHasNoErrors();
        $n = $ana->notifications()->sole();
        $this->assertSame(['correction', 'Your Personal information was updated'], [$n->data['kind'], $n->data['title']]);
        $this->assertStringEndsWith('/scholar/profile#personal', $n->data['url']);

        // Saving the same values again (nothing changed) adds nothing; the request stays for the provider to close.
        $this->patch(route('provider.scholars.update', $s->id), ['birthdate' => '2004-06-02'] + $personal);
        $this->assertSame(1, $ana->notifications()->count());
        $this->assertNull(ScholarMessage::sole()->handled_at);
    }

    public function test_a_reply_adds_an_in_portal_note(): void
    {
        $ana = $this->account('scholar', 'ana@example.test');
        $s = $this->scholar($ana);
        $ched = $this->account('provider', 'ched@provider.test', 'ched');
        $m = ScholarMessage::create(['scholar_id' => $s->id, 'user_id' => $ana->id, 'topic' => 'school', 'message' => 'I am in 3rd year now, not 2nd.']);

        $this->actingAs($ched)->post(route('provider.messages.reply', $m->id), ['reply' => 'Fixed: your year level is now 3rd year.']);
        $this->assertSame('reply', $ana->notifications()->sole()->data['kind']);
    }

    public function test_profile_cards_are_plain_cards_that_links_can_name(): void
    {
        $ana = $this->account('scholar', 'ana@example.test');
        $this->scholar($ana);

        $this->actingAs($ana)->get('/scholar/profile')->assertOk()
            ->assertSee('<section class="dash-card" id="personal"', false)
            ->assertSee('<section class="dash-card" id="requests"', false)
            ->assertDontSee('ms-fold', false)
            ->assertSee('id="corrections"', false)->assertSee('href="#corrections"', false);
    }

    public function test_about_totals_follow_the_database(): void
    {
        $totals = fn () => collect(app(PublicAnalyticsService::class)->summary()['totals'])->pluck('value', 'label');

        $before = $totals();
        $this->assertSame(Scholarship::where('is_active', true)->count(), $before['Scholarships listed']);
        $this->assertSame(0, $before['Scholars monitored']);

        $this->scholar($this->account('scholar', 'ana@example.test'));
        $this->assertSame(1, $totals()['Scholars monitored']);
        $this->get('/about')->assertOk()->assertSee('Scholars monitored')->assertSee('Not enough students yet');
    }
}
