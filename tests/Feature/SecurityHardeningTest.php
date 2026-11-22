<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

/** Security audit 2026-11-22: response headers, sessions ended on password change / reset / deactivation, web-root guard. */
class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $email, string $role = 'student'): User
    {
        $user = new User;
        $user->name = 'Test Person';
        $user->email = $email;
        $user->password = Hash::make('OldPass123');
        $user->role = $role;
        $user->status = 'active';
        $user->save();

        return $user;
    }

    /** A session row for $user as if they were signed in on another device. */
    private function otherSession(User $user, string $id): void
    {
        DB::table('sessions')->insert(['id' => $id, 'user_id' => $user->id, 'ip_address' => '10.0.0.9', 'user_agent' => 'Other', 'payload' => '', 'last_activity' => time()]);
    }

    public function test_pages_send_security_headers(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy');
    }

    public function test_changing_the_password_signs_out_other_sessions(): void
    {
        config(['session.driver' => 'database']);
        $user = $this->makeUser('student@example.test');
        $stranger = $this->makeUser('someone@example.test');
        $this->otherSession($user, 'old-device');
        $this->otherSession($stranger, 'stranger-device');

        $this->actingAs($user)->put(route('student.password.change.update'), [
            'current_password' => 'OldPass123',
            'password' => 'NewPass456',
            'password_confirmation' => 'NewPass456',
        ])->assertRedirect();

        $this->assertDatabaseMissing('sessions', ['id' => 'old-device']);
        $this->assertDatabaseHas('sessions', ['id' => 'stranger-device']);   // nobody else is touched
    }

    public function test_resetting_the_password_signs_out_every_session(): void
    {
        config(['session.driver' => 'database']);
        $user = $this->makeUser('scholar@example.test', 'scholar');
        $this->otherSession($user, 'stolen-session');
        $token = Password::broker()->createToken($user);

        $this->post(route('student.password.update'), [
            'token' => $token,
            'email' => 'scholar@example.test',
            'password' => 'NewPass456',
            'password_confirmation' => 'NewPass456',
        ])->assertRedirect(route('scholar.login'));

        $this->assertDatabaseMissing('sessions', ['id' => 'stolen-session']);
    }

    public function test_deactivating_an_account_signs_it_out(): void
    {
        config(['session.driver' => 'database']);
        $admin = $this->makeUser('admin@example.test', 'admin');
        $student = $this->makeUser('student@example.test');
        $this->otherSession($student, 'student-device');

        $this->actingAs($admin)->patch(route('admin.accounts.status', $student->id), ['status' => 'inactive'])->assertRedirect();

        $this->assertDatabaseMissing('sessions', ['id' => 'student-device']);
    }

    public function test_project_root_htaccess_keeps_everything_outside_public_private(): void
    {
        $rules = file_get_contents(base_path('.htaccess'));

        $this->assertStringContainsString('RewriteRule ^(?!public(?:/|$)) - [F,L]', $rules);
        $this->assertStringContainsString('Options -Indexes', $rules);
    }
}
