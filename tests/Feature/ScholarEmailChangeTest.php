<?php

namespace Tests\Feature;

use App\Mail\ScholarAccountCreated;
use App\Mail\ScholarEmailChanged;
use App\Mail\ScholarEmailChangeRequested;
use App\Models\Scholar;
use App\Models\Scholarship;
use App\Models\User;
use Database\Seeders\ProviderAccountSeeder;
use Database\Seeders\ScholarshipSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Owner, 2026-11-23: a provider can change a scholar's sign-in e-mail (e.g. the scholar lost their Gmail). The new
 * address only replaces the old one after the scholar opens the confirmation link sent to it, so the new e-mail is
 * proven to work. The mobile number stays the way to reach them meanwhile (Call / Text).
 */
class ScholarEmailChangeTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'Provider-Test-Pass-2026';

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        foreach (array_keys(config('providers.accounts')) as $key) {
            config(["providers.accounts.{$key}.email" => "{$key}@provider.test", "providers.accounts.{$key}.password" => self::PASSWORD]);
        }
        $this->seed(ScholarshipSeeder::class);
        $this->seed(ProviderAccountSeeder::class);
    }

    private function provider(string $key = 'ched'): User
    {
        return User::where('email', "{$key}@provider.test")->firstOrFail();
    }

    /** A scholar of CHED TDP with a real scholar account that has its own password. */
    private function scholar(): Scholar
    {
        $u = new User;
        $u->forceFill(['name' => 'Maria Santos', 'email' => 'maria@example.test', 'password' => Hash::make('Scholar-Pass-2026'),
            'role' => 'scholar', 'status' => 'active', 'must_change_password' => false])->save();

        return Scholar::create(['user_id' => $u->id, 'full_name' => 'Maria Santos', 'email' => 'maria@example.test', 'scholar_code' => 'T-1',
            'scholarship_id' => Scholarship::where('slug', 'ched-tdp')->value('id'), 'status' => 'active', 'mobile' => '09171234567']);
    }

    /** The confirmation link from the e-mail sent to $to. */
    private function confirmUrl(string $to): string
    {
        $url = null;
        Mail::assertSent(ScholarEmailChangeRequested::class, function ($m) use ($to, &$url) {
            $url = $m->confirmUrl;

            return $m->hasTo($to);
        });

        return $url;
    }

    public function test_the_new_email_changes_only_after_the_scholar_confirms_it(): void
    {
        $s = $this->scholar();
        $this->actingAs($this->provider());

        $this->get("/provider/scholars/{$s->id}")->assertOk()->assertSee('Change e-mail')
            ->assertSee('href="tel:+639171234567"', false)->assertSee('href="sms:+639171234567"', false);
        $this->get("/provider/scholars/{$s->id}?edit=email")->assertOk()->assertSee('name="new_email"', false)->assertSee('Send confirmation link');

        $this->post("/provider/scholars/{$s->id}/email", ['new_email' => ' Maria.New@Gmail.com '])->assertSessionHasNoErrors()->assertSessionHas('sent');

        // Nothing changed yet: the scholar still signs in with the old e-mail.
        $s->refresh();
        $this->assertSame('maria@example.test', $s->contactEmail());
        $this->assertSame('maria.new@gmail.com', $s->pending_email);
        $this->assertNotNull($s->pending_email_token);
        $this->get("/provider/scholars/{$s->id}")->assertSee('Waiting for the scholar to confirm')->assertSee('maria.new@gmail.com');

        // Opening the link (signed in or not) makes the change, everywhere.
        auth()->logout();
        $this->get($this->confirmUrl('maria.new@gmail.com'))->assertOk()->assertSee('Your new e-mail is confirmed')->assertSee('maria.new@gmail.com');
        $s->refresh();
        $this->assertSame('maria.new@gmail.com', $s->user->email);
        $this->assertSame('maria.new@gmail.com', $s->email);
        $this->assertNull($s->pending_email);
        $this->assertNull($s->pending_email_token);
        Mail::assertSent(ScholarEmailChanged::class, fn ($m) => $m->hasTo('maria@example.test'));   // notice to the old address

        // The new e-mail signs in (same password); the old one no longer does.
        $this->post('/scholarLogin', ['email' => 'maria@example.test', 'password' => 'Scholar-Pass-2026']);
        $this->assertGuest();
        $this->post('/scholarLogin', ['email' => 'maria.new@gmail.com', 'password' => 'Scholar-Pass-2026']);
        $this->assertAuthenticatedAs($s->user);

        // The link works once.
        auth()->logout();
        $this->get($this->confirmUrl('maria.new@gmail.com'))->assertOk()->assertSee('This link does not work');
    }

    public function test_a_wrong_or_taken_address_is_refused_and_nothing_changes(): void
    {
        $s = $this->scholar();
        $other = new User;
        $other->forceFill(['name' => 'Ana', 'email' => 'ana@example.test', 'password' => Hash::make('x-12345678'), 'role' => 'student', 'status' => 'active'])->save();
        $this->actingAs($this->provider());
        $url = "/provider/scholars/{$s->id}/email";

        $this->post($url, ['new_email' => ''])->assertSessionHasErrors('new_email');
        $this->post($url, ['new_email' => 'not-an-email'])->assertSessionHasErrors('new_email');
        $this->post($url, ['new_email' => 'MARIA@example.test'])->assertSessionHasErrors('new_email');     // the same address
        $this->post($url, ['new_email' => 'ana@example.test'])->assertSessionHasErrors('new_email');       // another account
        $this->post($url, ['new_email' => 'ched@provider.test'])->assertSessionHasErrors('new_email');     // a provider account
        Mail::assertNothingSent();
        $this->assertNull($s->fresh()->pending_email);

        // A bad token, or an expired link, changes nothing.
        $this->post($url, ['new_email' => 'maria.new@gmail.com'])->assertSessionHasNoErrors();
        $this->get(route('scholar.email.confirm', [$s->id, str_repeat('a', 48)]))->assertOk()->assertSee('This link does not work');
        $this->assertSame('maria@example.test', $s->fresh()->contactEmail());

        $link = $this->confirmUrl('maria.new@gmail.com');
        $this->travel(Scholar::EMAIL_CHANGE_HOURS + 1)->hours();
        $this->get($link)->assertOk()->assertSee('This link has expired');
        $this->assertSame('maria@example.test', $s->fresh()->contactEmail());
        $this->assertNull($s->fresh()->pending_email);
    }

    public function test_the_provider_can_withdraw_a_waiting_change_and_only_for_its_own_scholars(): void
    {
        $s = $this->scholar();
        $this->actingAs($this->provider());
        $this->post("/provider/scholars/{$s->id}/email", ['new_email' => 'maria.new@gmail.com']);
        $link = $this->confirmUrl('maria.new@gmail.com');

        $this->delete("/provider/scholars/{$s->id}/email")->assertRedirect();
        $this->assertNull($s->fresh()->pending_email);
        $this->get($link)->assertOk()->assertSee('This link does not work');
        $this->assertSame('maria@example.test', $s->fresh()->contactEmail());

        // Another provider cannot touch this scholar.
        $this->actingAs($this->provider('dost'))->post("/provider/scholars/{$s->id}/email", ['new_email' => 'x@gmail.com'])->assertNotFound();
        $this->delete("/provider/scholars/{$s->id}/email")->assertNotFound();
    }

    public function test_a_login_never_used_gets_new_details_at_the_confirmed_address(): void
    {
        $s = $this->scholar();
        $s->user->forceFill(['must_change_password' => true])->save();   // temporary password, perhaps never received
        $this->actingAs($this->provider())->post("/provider/scholars/{$s->id}/email", ['new_email' => 'maria.new@gmail.com']);
        auth()->logout();

        $this->get($this->confirmUrl('maria.new@gmail.com'))->assertOk()->assertSee('new login details');
        Mail::assertSent(ScholarAccountCreated::class, fn ($m) => $m->hasTo('maria.new@gmail.com') && $m->password !== null);
    }

    public function test_lost_email_help_points_scholars_to_their_provider(): void
    {
        $this->get('/scholarLogin')->assertOk()->assertSee('Lost access to your e-mail?');
        $this->get('/student/forgot-password?from=scholar')->assertOk()->assertSee('No longer have access to that e-mail?');
        $this->get('/student/forgot-password')->assertOk()->assertDontSee('No longer have access to that e-mail?');
    }
}
