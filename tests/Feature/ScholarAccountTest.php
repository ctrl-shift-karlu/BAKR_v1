<?php

namespace Tests\Feature;

use App\Mail\ScholarAccountCreated;
use App\Models\Scholar;
use App\Models\Scholarship;
use App\Models\User;
use App\Support\Spreadsheet;
use Database\Seeders\ProviderAccountSeeder;
use Database\Seeders\ScholarshipSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\TestResponse;
use RuntimeException;
use Tests\TestCase;

/**
 * Adding a scholar creates their BAKR SCHOLAR login (separate from student accounts): the typed
 * e-mail is the sign-in at /scholarLogin, a temporary password is e-mailed, and the scholar must
 * change it at first sign-in. A student e-mail is converted to a scholar account.
 */
class ScholarAccountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        foreach (array_keys(config('providers.accounts')) as $key) {
            config([
                "providers.accounts.{$key}.email" => "{$key}@provider.test",
                "providers.accounts.{$key}.password" => 'Provider-Test-Pass-2026',
            ]);
        }
        $this->seed(ScholarshipSeeder::class);
        $this->seed(ProviderAccountSeeder::class);
    }

    private function provider(string $key = 'ched'): User
    {
        return User::where('email', "{$key}@provider.test")->firstOrFail();
    }

    private function addMaria(array $over = []): TestResponse
    {
        return $this->actingAs($this->provider())->post('/provider/scholars', $over + [
            'scholarship_id' => Scholarship::where('slug', 'ched-tdp')->value('id'),
            'scholar_code' => 'SCID-2026-0001', 'first_name' => 'Maria', 'last_name' => 'Santos', 'student_number' => '2023-0001', 'email' => 'Maria@Example.test',
            'campus' => 'Main (Panabo Campus)', 'program' => 'BS Information Technology', 'year_level' => '2',
            'birthdate' => '2005-03-14', 'gender' => 'female', 'mobile' => '0917 123 4567', 'address_street' => 'Purok 3, Gredu', 'address_city' => 'Panabo City', 'address_province' => 'Davao del Norte',
        ]);
    }

    /** The temporary password from the e-mail BAKR sent. */
    private function mailedPassword(): string
    {
        $password = null;
        Mail::assertSent(ScholarAccountCreated::class, function ($m) use (&$password) {
            $password = $m->password;

            return true;
        });

        return (string) $password;
    }

    public function test_adding_a_scholar_creates_a_scholar_account_and_emails_a_temporary_password(): void
    {
        $res = $this->addMaria()->assertRedirect()->assertSessionHas('status', fn ($m) => str_contains($m, 'temporary password was e-mailed'));

        $user = User::where('email', 'maria@example.test')->firstOrFail();
        $this->assertSame('scholar', $user->role);
        $this->assertSame('active', $user->status);
        $this->assertSame('Maria Santos', $user->name);
        $this->assertTrue($user->must_change_password);
        $this->assertSame($user->id, Scholar::firstOrFail()->user_id);
        $this->assertNotNull(Scholar::firstOrFail()->credentials_sent_at);

        Mail::assertSent(ScholarAccountCreated::class, fn ($m) => $m->hasTo('maria@example.test') && strlen((string) $m->password) === 12);
        $password = $this->mailedPassword();
        $this->assertTrue(Hash::check($password, $user->password));

        // The provider never sees the password.
        $this->followRedirects($res)->assertDontSee($password);
        $this->get('/provider/scholars/'.Scholar::first()->id)->assertDontSee($password)->assertSee('Resend login details');
    }

    public function test_the_scholar_must_change_the_temporary_password_at_first_sign_in(): void
    {
        $this->addMaria();
        $temp = $this->mailedPassword();
        Auth::logout();

        // A scholar account is not a student account: the Student login refuses it.
        $this->post('/studentLogin', ['email' => 'maria@example.test', 'password' => $temp])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post('/scholarLogin', ['email' => 'maria@example.test', 'password' => $temp])
            ->assertRedirect(route('scholar.password.change'));

        // Every scholar page sends them back until the password is changed.
        foreach (['/scholar', '/scholar/scholarship', '/scholar/requirements', '/scholar/profile', '/scholar/settings', '/scholar/help'] as $page) {
            $this->get($page)->assertRedirect(route('scholar.password.change'));
        }
        $this->get('/scholar/password')->assertOk()->assertSee('You signed in with a temporary password')
            ->assertSee('action="'.route('scholar.password.change.update').'"', false)
            ->assertSee('action="'.route('scholar.logout').'"', false);
        $this->get('/student/password')->assertForbidden();   // no student pages for a scholar account

        $this->put('/scholar/password', ['current_password' => 'wrong-one-1', 'password' => 'MyOwnPass123', 'password_confirmation' => 'MyOwnPass123'])
            ->assertSessionHasErrors('current_password');
        $this->put('/scholar/password', ['current_password' => $temp, 'password' => $temp, 'password_confirmation' => $temp])
            ->assertSessionHasErrors('password');

        $this->put('/scholar/password', ['current_password' => $temp, 'password' => 'MyOwnPass123', 'password_confirmation' => 'MyOwnPass123'])
            ->assertRedirect(route('scholar.overview'));

        $user = User::where('email', 'maria@example.test')->firstOrFail();
        $this->assertFalse($user->must_change_password);
        $this->assertTrue(Hash::check('MyOwnPass123', $user->password));
        $this->get('/scholar/scholarship')->assertOk()->assertSee('CHED Tulong Dunong Program');

        Auth::logout();
        $this->post('/scholarLogin', ['email' => 'maria@example.test', 'password' => $temp])->assertSessionHasErrors('email');
        $this->post('/scholarLogin', ['email' => 'maria@example.test', 'password' => 'MyOwnPass123'])->assertRedirect(route('scholar.overview'));

        // Provider now sees an active account, and resending is no longer offered.
        $this->actingAs($this->provider())->get('/provider/scholars/'.Scholar::first()->id)->assertSee('Active: the scholar has their own password')->assertDontSee('Resend login details');
    }

    public function test_an_existing_student_account_becomes_a_scholar_account(): void
    {
        $student = new User;
        $student->forceFill(['name' => 'Maria S.', 'email' => 'maria@example.test', 'password' => Hash::make('Existing123'), 'role' => 'student', 'status' => 'active'])->save();

        $this->addMaria()->assertSessionHas('status', fn ($m) => str_contains($m, 'it is now a scholar account'));

        $this->assertSame(1, User::where('email', 'maria@example.test')->count());   // same account, not a second one
        $this->assertSame('scholar', $student->fresh()->role);
        $this->assertSame($student->id, Scholar::firstOrFail()->user_id);
        $this->assertTrue(Hash::check('Existing123', $student->fresh()->password));   // password untouched
        $this->assertFalse((bool) $student->fresh()->must_change_password);
        Mail::assertSent(ScholarAccountCreated::class, fn ($m) => $m->password === null && $m->converted && $m->hasTo('maria@example.test')
            && str_contains($m->render(), 'is now a <strong>scholar account</strong>') && str_contains($m->render(), route('scholar.login')));

        // From now on: Scholar login (scholar portal), not the Student login (matching).
        Auth::logout();
        $this->post('/studentLogin', ['email' => 'maria@example.test', 'password' => 'Existing123'])->assertSessionHasErrors('email');
        $this->post('/scholarLogin', ['email' => 'maria@example.test', 'password' => 'Existing123'])->assertRedirect(route('scholar.overview'));
        $this->get('/student/dashboard')->assertForbidden();
    }

    public function test_an_existing_scholar_account_gets_the_extra_scholarship(): void
    {
        $this->addMaria();
        $user = User::where('email', 'maria@example.test')->firstOrFail();

        $this->addMaria(['scholarship_id' => Scholarship::where('slug', 'bpmsp')->value('id')])
            ->assertSessionHas('status', fn ($m) => str_contains($m, 'already had a BAKR scholar account'));

        $this->assertSame(1, User::where('email', 'maria@example.test')->count());
        $this->assertSame([$user->id, $user->id], Scholar::orderBy('id')->pluck('user_id')->all());
        Mail::assertSent(ScholarAccountCreated::class, fn ($m) => $m->password === null && ! $m->converted);
    }

    public function test_a_provider_or_staff_email_cannot_become_a_scholar_login(): void
    {
        $this->addMaria(['email' => 'dost@provider.test'])->assertSessionHasErrors('email');

        $this->assertSame(0, Scholar::count());
        $this->assertSame('provider', User::where('email', 'dost@provider.test')->value('role'));
        Mail::assertNothingSent();
    }

    public function test_email_is_required(): void
    {
        $this->addMaria(['email' => ''])->assertSessionHasErrors('email');
        $this->assertSame(0, Scholar::count());
    }

    public function test_resend_gives_a_new_password_and_the_old_one_stops_working(): void
    {
        $this->addMaria();
        $first = $this->mailedPassword();
        $scholar = Scholar::firstOrFail();

        $this->post("/provider/scholars/{$scholar->id}/login")->assertSessionHas('sent', fn ($s) => $s['title'] === 'Login details sent');

        $passwords = [];
        Mail::assertSent(ScholarAccountCreated::class, function ($m) use (&$passwords) {
            $passwords[] = $m->password;

            return true;
        });
        $this->assertCount(2, $passwords);
        $this->assertNotSame($first, $passwords[1]);
        $user = $scholar->user->fresh();
        $this->assertFalse(Hash::check($first, $user->password));
        $this->assertTrue(Hash::check($passwords[1], $user->password));
    }

    public function test_another_provider_cannot_resend_or_see_the_scholar(): void
    {
        $this->addMaria();
        $id = Scholar::firstOrFail()->id;

        $this->actingAs($this->provider('dost'))->post("/provider/scholars/{$id}/login")->assertNotFound();
        $this->get("/provider/scholars/{$id}")->assertNotFound();
    }

    public function test_a_failed_email_keeps_the_scholar_and_offers_a_resend(): void
    {
        Mail::shouldReceive('to')->andThrow(new RuntimeException('SMTP down'));

        $this->addMaria()->assertRedirect()->assertSessionHas('notice', fn ($m) => str_contains($m, 'could not be sent'));

        $s = Scholar::with('user')->firstOrFail();
        $this->assertNull($s->credentials_sent_at);
        $this->assertSame('not_sent', $s->accountState());
        $this->get('/provider/scholars')->assertSee('Login not sent');
        $this->get("/provider/scholars/{$s->id}")->assertSee('Resend login details');
    }

    public function test_log_mailer_is_not_counted_as_sent(): void
    {
        config(['mail.default' => 'log']);   // e-mails only written to the log: nobody receives them

        $this->actingAs($this->provider())->get('/provider/scholars/new')->assertSee('E-mail is in test mode');
        $this->addMaria()->assertSessionHas('notice', fn ($m) => str_contains($m, 'test mode'));

        $s = Scholar::with('user')->firstOrFail();
        $this->assertNull($s->credentials_sent_at);
        $this->assertSame('not_sent', $s->accountState());
        $this->get('/provider/scholars')->assertSee('Login not sent');
    }

    public function test_import_creates_an_account_and_sends_a_login_email_for_each_scholar(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'roster');
        file_put_contents($path, Spreadsheet::xlsx([
            ['Scholar ID', 'First name', 'Surname', 'Student number', 'Email', 'Campus', 'Program', 'Year level', 'Mobile number', 'Street', 'City / Municipality', 'Province', 'Birthday', 'Gender'],
            ['SCID-2026-0001', 'Juan', 'Dela Cruz', '2023-0001', 'juan@example.test', 'Main', 'BS Information Technology', '1', '09171234567', 'Purok 3, Gredu', 'Panabo City', 'Davao del Norte', '2005-03-14', 'M'],
            ['SCID-2026-0002', 'Ana', 'Reyes', '2023-0002', 'ana@example.test', 'Main', 'BS Information Technology', '2', '09171234568', 'Purok 3, Gredu', 'Panabo City', 'Davao del Norte', '2005-03-14', 'F'],
            ['SCID-2026-0003', 'Staff', 'Email', '2023-0003', 'grab@provider.test', 'Main', 'BS Information Technology', '2', '09171234569', 'Purok 3, Gredu', 'Panabo City', 'Davao del Norte', '2005-03-14', 'F'],   // a provider account: skipped
        ]));
        $file = new UploadedFile($path, 'roster.xlsx', null, null, true);

        $this->actingAs($this->provider())->post('/provider/scholars/import', ['scholarship_id' => Scholarship::where('slug', 'ched-tdp')->value('id'), 'file' => $file])
            ->assertSessionHas('import_report', fn ($r) => $r['added'] === 2 && $r['new_accounts'] === 2 && count($r['skipped']) === 1);

        $this->assertSame(2, User::where('role', 'scholar')->where('must_change_password', true)->count());
        Mail::assertSent(ScholarAccountCreated::class, 2);
        Mail::assertSent(ScholarAccountCreated::class, fn ($m) => $m->hasTo('juan@example.test') && $m->password);
    }

    public function test_the_login_email_says_what_to_do(): void
    {
        $this->addMaria();
        Mail::assertSent(ScholarAccountCreated::class, function ($m) {
            $html = $m->render();

            return str_contains($html, 'maria@example.test')
                && str_contains($html, $m->password)
                && str_contains($html, 'Change this password right away')
                && str_contains($html, route('scholar.login'))
                && ! str_contains($html, route('student.login'));
        });
    }
}
