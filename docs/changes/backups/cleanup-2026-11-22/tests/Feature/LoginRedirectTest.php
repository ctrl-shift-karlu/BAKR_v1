<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * After signing in, a person goes back to the page they were sent away from only when it belongs to the
 * portal they signed in to (audit 2026-11-21). Another portal's page would only answer 403.
 */
class LoginRedirectTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $email, string $role): User
    {
        $user = new User;
        $user->name = 'Test Person';
        $user->email = $email;
        $user->password = Hash::make('Secret123');
        $user->role = $role;
        $user->status = 'active';
        $user->save();

        return $user;
    }

    public function test_student_returns_to_their_own_page_after_login(): void
    {
        $this->makeUser('student@example.test', 'student');

        $this->get('/student/scholarships')->assertRedirect(url('studentLogin'));
        $this->post('/studentLogin', ['email' => 'student@example.test', 'password' => 'Secret123'])
            ->assertRedirect(url('student/scholarships'));
    }

    public function test_student_is_not_sent_to_another_portals_page(): void
    {
        $this->makeUser('student@example.test', 'student');

        $this->get('/provider/scholars')->assertRedirect(url('providerLogin'));
        $this->post('/studentLogin', ['email' => 'student@example.test', 'password' => 'Secret123'])
            ->assertRedirect(route('student.dashboard'));
    }

    public function test_scholar_is_not_sent_to_an_admin_page(): void
    {
        $this->makeUser('scholar@example.test', 'scholar');

        $this->get('/admin/accounts')->assertRedirect(url('adminLogin'));
        $this->post('/scholarLogin', ['email' => 'scholar@example.test', 'password' => 'Secret123'])
            ->assertRedirect(route('scholar.overview'));
    }

    public function test_admin_returns_to_admin_page(): void
    {
        $this->makeUser('admin@example.test', 'admin');

        $this->get('/admin/messages')->assertRedirect(url('adminLogin'));
        $this->post('/adminLogin', ['email' => 'admin@example.test', 'password' => 'Secret123'])
            ->assertRedirect(url('admin/messages'));
    }
}
