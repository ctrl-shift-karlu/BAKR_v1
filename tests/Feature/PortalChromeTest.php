<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\ProviderAccountSeeder;
use Database\Seeders\ScholarshipSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Shared portal chrome (2026-11-18): working "Forgot password" for every real account,
 * mobile burger drawer, collapsible provider Account section, red sign-out confirmation.
 */
class PortalChromeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        foreach (array_keys(config('providers.accounts')) as $key) {
            config([
                "providers.accounts.{$key}.email" => "{$key}@provider.test",
                "providers.accounts.{$key}.password" => 'Provider-Test-Pass-2026',
            ]);
        }
        $this->seed(ScholarshipSeeder::class);
        $this->seed(ProviderAccountSeeder::class);
    }

    public function test_a_provider_can_reset_a_forgotten_password(): void
    {
        Notification::fake();
        $provider = User::where('email', 'ched@provider.test')->firstOrFail();

        $this->get('/providerLogin')->assertOk()->assertSee(route('student.password.request', ['from' => 'provider']), false);
        $this->get('/student/forgot-password?from=provider')->assertOk()->assertSee(route('provider.login'), false);

        $this->post('/student/forgot-password', ['email' => 'CHED@provider.test'])->assertSessionHas('status');

        $token = null;
        Notification::assertSentTo($provider, ResetPassword::class, function ($n) use (&$token) {
            $token = $n->token;

            return true;
        });

        $this->post('/student/reset-password', ['token' => $token, 'email' => 'ched@provider.test', 'password' => 'NewProvider123', 'password_confirmation' => 'NewProvider123'])
            ->assertRedirect(route('provider.login'));
        $this->assertTrue(Hash::check('NewProvider123', $provider->fresh()->password));
        $this->post('/providerLogin', ['email' => 'ched@provider.test', 'password' => 'NewProvider123'])->assertRedirect(route('provider.dashboard'));
    }

    public function test_unknown_or_inactive_emails_get_the_same_reply_and_no_mail(): void
    {
        Notification::fake();
        User::where('email', 'dost@provider.test')->update(['status' => 'inactive']);

        foreach (['nobody@example.test', 'dost@provider.test'] as $email) {
            $this->post('/student/forgot-password', ['email' => $email])
                ->assertSessionHas('status', fn ($m) => str_contains($m, 'If that e-mail belongs to a BAKR account'));
        }
        Notification::assertNothingSent();
    }

    public function test_the_old_staff_forgot_password_address_goes_to_the_real_flow(): void
    {
        $this->get('/forgotPassword?from=scholar')->assertRedirect(route('student.password.request', ['from' => 'scholar']));
        $this->get('/scholarLogin')->assertOk()->assertSee(route('student.password.request', ['from' => 'scholar']), false);
        $this->get('/adminLogin')->assertOk()->assertSee(route('student.password.request', ['from' => 'admin']), false);
    }

    public function test_provider_sidebar_has_a_collapsible_help_section_and_a_red_sign_out(): void
    {
        $html = $this->actingAs(User::where('email', 'ched@provider.test')->first())->get('/provider/dashboard')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<details class="dash-nav-group"[^>]*>\s*<summary>Help &amp; info/s', $html);
        $this->assertSignOutIsTheSharedOne($html);
        $this->assertStringContainsString('data-dash-burger', $html);
        $this->assertStringContainsString('id="dash-side"', $html);
        $this->assertStringNotContainsString('Applicants', $html);

        // Help opens the group by itself.
        $this->assertMatchesRegularExpression('/<details class="dash-nav-group"\s+open\b/', $this->get('/provider/help')->getContent());
    }

    /**
     * Every portal signs out the same way (owner, 2026-11-18): the sidebar foot's "Sign out" (never inside a
     * collapsible group), the red hero icon, and the one shared red confirmation dialog.
     */
    private function assertSignOutIsTheSharedOne(string $html): void
    {
        $this->assertSame(1, substr_count($html, 'class="dash-signout" data-open-dialog="confirm-signout"'));
        $this->assertSame(1, substr_count($html, 'class="dash-icon-btn is-danger" href="#confirm-signout"'));
        $this->assertSame(1, substr_count($html, 'id="confirm-signout"'));
        $this->assertMatchesRegularExpression('/id="confirm-signout".*?class="dash-btn is-danger"/s', $html);
        preg_match_all('/<details class="dash-nav-group".*?<\/details>/s', $html, $groups);
        foreach ($groups[0] as $group) {
            $this->assertStringNotContainsString('confirm-signout', $group);
        }
    }

    public function test_every_portal_signs_out_the_same_way(): void
    {
        $make = function (string $role): User {
            $u = new User;
            $u->forceFill(['name' => ucfirst($role).' User', 'email' => $role.'@example.test', 'password' => Hash::make('x'), 'role' => $role, 'status' => 'active'])->save();

            return $u;
        };

        $this->assertSignOutIsTheSharedOne($this->actingAs($make('student'))->get('/student/help')->assertOk()->getContent());
        $this->assertSignOutIsTheSharedOne($this->actingAs($make('scholar'))->get('/scholar/help')->assertOk()->getContent());
        $this->assertSignOutIsTheSharedOne($this->actingAs($make('admin'))->get('/admin')->assertOk()->getContent());

        // Signed in on a public page: the navbar opens the same confirmation (no instant sign-out form).
        $public = $this->actingAs(User::where('email', 'student@example.test')->first())->get('/about')->assertOk()->getContent();
        $this->assertStringContainsString('href="#confirm-signout" data-open-dialog="confirm-signout"', $public);
        $this->assertMatchesRegularExpression('/id="confirm-signout".*?action="'.preg_quote(route('student.logout'), '/').'"/s', $public);
    }

    public function test_a_new_student_gets_a_modal_not_a_banner_until_the_form_is_done(): void
    {
        $u = new User;
        $u->forceFill(['name' => 'Ana', 'email' => 'ana@example.test', 'password' => Hash::make('AnaPass123'), 'role' => 'student', 'status' => 'active'])->save();
        $this->actingAs($u);

        // The locked links open the modal directly (no page load needed)…
        $html = $this->get('/student/application')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/href="'.preg_quote(route('student.results'), '/').'"[^>]*data-open-dialog="finish-form"/', $html);
        $this->assertStringContainsString('id="finish-form"', $html);
        $this->assertDoesNotMatchRegularExpression('/id="finish-form"[^>]*data-auto-open/', $html);

        // …and opening the address anyway lands on the form with the modal already open.
        foreach (['/student/dashboard' => 'Dashboard', '/student/results' => 'My matches', '/student/profile' => 'My profile'] as $page => $name) {
            $this->get($page)->assertRedirect(route('student.application.show'))->assertSessionHas('form_first', $name);
            $html = $this->get('/student/application')->assertOk()->assertSee($name.'</strong> opens after you submit the form once', false)->getContent();
            $this->assertMatchesRegularExpression('/<dialog[^>]*id="finish-form"[^>]*data-auto-open/', $html);
            $this->assertStringNotContainsString('sp-flash', $html);   // no banner any more
        }
    }

    public function test_scholars_get_their_own_about_text(): void
    {
        $u = new User;
        $u->forceFill(['name' => 'Sam', 'email' => 'sam@example.test', 'password' => Hash::make('x'), 'role' => 'scholar', 'status' => 'active'])->save();

        $this->actingAs($u)->get('/scholar/help')->assertOk()
            ->assertSee('Submit requirements')->assertSee('Follow your stipends')
            ->assertDontSee('See your matches')->assertDontSee('Answer once');
    }

    public function test_the_student_portal_also_has_the_burger_and_red_sign_out(): void
    {
        $u = new User;
        $u->forceFill(['name' => 'Ana', 'email' => 'ana@example.test', 'password' => Hash::make('AnaPass123'), 'role' => 'student', 'status' => 'active'])->save();

        $html = $this->actingAs($u)->get('/student/help')->assertOk()->getContent();
        $this->assertStringContainsString('data-dash-burger', $html);
        $this->assertSignOutIsTheSharedOne($html);
    }
}
