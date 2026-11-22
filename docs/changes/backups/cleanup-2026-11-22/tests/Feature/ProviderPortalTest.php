<?php

namespace Tests\Feature;

use App\Models\Scholarship;
use App\Models\User;
use Database\Seeders\ProviderAccountSeeder;
use Database\Seeders\ScholarshipSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProviderPortalTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'Provider-Test-Pass-2026';

    protected function setUp(): void
    {
        parent::setUp();

        // Fake .env credentials for the 5 accounts, then run the real seeders.
        foreach (array_keys(config('providers.accounts')) as $key) {
            config([
                "providers.accounts.{$key}.email" => "{$key}@provider.test",
                "providers.accounts.{$key}.password" => self::PASSWORD,
            ]);
        }

        $this->seed(ScholarshipSeeder::class);
        $this->seed(ProviderAccountSeeder::class);
    }

    public static function providers(): array
    {
        return array_map(fn ($key) => [$key], array_combine(
            ['ched', 'dost', 'cebuana', 'dnsc', 'grab'],
            ['ched', 'dost', 'cebuana', 'dnsc', 'grab'],
        ));
    }

    private function login(string $key)
    {
        return $this->post('/providerLogin', ['email' => "{$key}@provider.test", 'password' => self::PASSWORD]);
    }

    private function makeStudent(string $email): User
    {
        $user = new User;
        $user->name = 'Student '.$email;
        $user->email = $email;
        $user->password = Hash::make('StudentPass123');
        $user->role = 'student';
        $user->status = 'active';
        $user->save();

        return $user;
    }

    public function test_navbar_lists_the_provider_login(): void
    {
        $this->get('/')->assertOk()->assertSee('Scholarship Provider')->assertSee(url('providerLogin'), false);
        $this->get('/providerLogin')->assertOk()->assertSee('Provider login');
    }

    public function test_seeder_sets_role_and_provider_key_and_hashes_the_password(): void
    {
        $user = User::where('email', 'dost@provider.test')->firstOrFail();

        $this->assertSame('provider', $user->role);
        $this->assertSame('dost', $user->provider_key);
        $this->assertNotSame(self::PASSWORD, $user->password);
        $this->assertTrue(Hash::check(self::PASSWORD, $user->password));
        $this->assertSame(count(config('providers.accounts')), User::where('role', 'provider')->count());   // 8 since 2026-11-18
    }

    #[DataProvider('providers')]
    public function test_each_provider_logs_in_and_sees_only_their_own_scholarships(string $key): void
    {
        $this->login($key)->assertRedirect(route('provider.dashboard'));

        $own = config("providers.accounts.{$key}.slugs");
        $response = $this->get('/provider/dashboard')->assertOk()
            ->assertSee(config("providers.accounts.{$key}.name"));

        foreach (Scholarship::all() as $s) {
            if (in_array($s->slug, $own, true)) {
                $response->assertSee('id="sch-'.$s->slug.'"', false)->assertSee($s->name);
            } else {
                $response->assertDontSee('id="sch-'.$s->slug.'"', false)->assertDontSee($s->name);
            }
        }
    }

    public function test_every_provider_account_has_its_own_official_website(): void
    {
        $sites = array_map(fn ($p) => $p['website'] ?? null, config('providers.accounts'));
        foreach ($sites as $key => $url) {
            $this->assertIsString($url, "$key has no website");
            $this->assertStringStartsWith('https://', $url);
        }
        $this->assertSame(count($sites), count(array_unique($sites)));   // never one shared link
    }

    #[DataProvider('providers')]
    public function test_the_sidebar_opens_the_providers_own_official_website(string $key): void
    {
        $this->login($key);

        $this->get('/provider/dashboard')->assertOk()
            ->assertSee('href="'.config("providers.accounts.{$key}.website").'"', false)
            ->assertSee('Official website')
            ->assertDontSee('Public page');
    }

    public function test_student_gets_403_on_the_provider_dashboard(): void
    {
        $this->actingAs($this->makeStudent('student@example.test'))
            ->get('/provider/dashboard')->assertForbidden();
    }

    public function test_student_credentials_cannot_sign_in_as_a_provider(): void
    {
        $this->makeStudent('student@example.test');

        $this->post('/providerLogin', ['email' => 'student@example.test', 'password' => 'StudentPass123'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_wrong_password_fails(): void
    {
        $this->post('/providerLogin', ['email' => 'ched@provider.test', 'password' => 'wrong-password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_guest_is_sent_to_the_provider_login(): void
    {
        $this->get('/provider/dashboard')->assertRedirect(url('providerLogin'));
    }

    public function test_a_provider_cannot_reach_another_providers_data(): void
    {
        $this->login('dost');

        // Request input is ignored: the provider always comes from the account.
        $this->get('/provider/dashboard?provider=ched&provider_key=ched')->assertOk()
            ->assertSee('id="sch-dost-sei"', false)
            ->assertDontSee('id="sch-ched-tdp"', false);

        // There are no per-provider URLs to guess.
        $this->get('/provider/ched')->assertNotFound();
        $this->get('/provider/dashboard/ched')->assertNotFound();
    }

    public function test_provider_account_with_unknown_key_is_refused(): void
    {
        $user = User::where('email', 'grab@provider.test')->firstOrFail();
        $user->provider_key = 'not-a-provider';
        $user->save();

        $this->actingAs($user)->get('/provider/dashboard')->assertForbidden();

        auth()->logout();
        $this->login('grab')->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_qualifications_render_from_the_rules_json(): void
    {
        $this->login('dost');
        $response = $this->get('/provider/scholarships')->assertOk();

        $dost = Scholarship::where('slug', 'dost-sei')->firstOrFail();
        foreach ($dost->rules as $rule) {
            $response->assertSee($rule['label']);
            if (isset($rule['confirm_note'])) {
                $response->assertSee($rule['confirm_note']);
            }
        }

        // Text comes from the table, not the view: change a label and the page follows.
        $rules = $dost->rules;
        $rules[0]['label'] = 'Edited label from the rules JSON';
        $dost->rules = $rules;
        $dost->save();

        $this->get('/provider/scholarships')->assertSee('Edited label from the rules JSON');
    }

    public function test_any_of_rules_list_their_sub_rules(): void
    {
        $this->login('ched');

        $this->get('/provider/scholarships?s='.Scholarship::where('slug', 'bpmsp')->value('id'))->assertOk()
            ->assertSee('Top 5 of the graduating class, or a Grade 12 GWA of at least 95%')
            ->assertSee('Top 5 of the graduating class')
            ->assertSee('Grade 12 GWA of at least 95%');
    }

    public function test_empty_fields_show_the_not_published_notice(): void
    {
        $this->login('dnsc');

        // dnsc-academic has no window_note in config/scholarships.php.
        $this->get('/provider/scholarships')->assertOk()
            ->assertSee('Not published by provider. Confirm with DNSC Scholarship and Grants Unit.');
    }

    public function test_provider_can_log_out(): void
    {
        $this->login('grab');

        $this->post(route('provider.logout'))->assertRedirect(route('provider.login'));
        $this->assertGuest();
    }
}
