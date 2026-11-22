<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class StudentPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $email, string $role = 'student', string $status = 'active'): User
    {
        $user = new User;
        $user->name = 'Test Person';
        $user->email = $email;
        $user->password = Hash::make('OldPass123');
        $user->role = $role;
        $user->status = $status;
        $user->save();

        return $user;
    }

    public function test_login_page_links_to_forgot_password(): void
    {
        $this->get('/studentLogin')
            ->assertOk()
            ->assertSee(route('student.password.request'), false);
    }

    public function test_student_receives_reset_link(): void
    {
        Notification::fake();
        $user = $this->makeUser('student@example.test');

        $this->post(route('student.password.email'), ['email' => 'student@example.test'])
            ->assertRedirect()
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_unknown_email_gets_the_same_reply_and_no_mail(): void
    {
        Notification::fake();

        $this->post(route('student.password.email'), ['email' => 'nobody@example.test'])
            ->assertRedirect()
            ->assertSessionHas('status');

        Notification::assertNothingSent();
    }

    public function test_staff_and_inactive_accounts_are_not_sent_a_student_link(): void
    {
        Notification::fake();
        $this->makeUser('officer@example.test', 'officer');
        $this->makeUser('inactive@example.test', 'student', 'inactive');

        $this->post(route('student.password.email'), ['email' => 'officer@example.test'])->assertSessionHas('status');
        $this->post(route('student.password.email'), ['email' => 'inactive@example.test'])->assertSessionHas('status');

        Notification::assertNothingSent();
    }

    public function test_valid_token_changes_the_password(): void
    {
        $user = $this->makeUser('student@example.test');
        $token = Password::createToken($user);

        $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))->assertOk();

        $this->post(route('student.password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewPass456',
            'password_confirmation' => 'NewPass456',
        ])->assertRedirect(route('student.login'))->assertSessionHas('status');

        $this->assertTrue(Hash::check('NewPass456', $user->fresh()->password));
    }

    public function test_invalid_token_is_rejected(): void
    {
        $user = $this->makeUser('student@example.test');

        $this->from(route('password.reset', ['token' => 'bad']))
            ->post(route('student.password.update'), [
                'token' => 'not-a-real-token',
                'email' => $user->email,
                'password' => 'NewPass456',
                'password_confirmation' => 'NewPass456',
            ])->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('OldPass123', $user->fresh()->password));
    }
}
