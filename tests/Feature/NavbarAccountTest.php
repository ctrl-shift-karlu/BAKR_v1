<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/** The navbar's account area: Register for guests, a plain dashboard link when signed in. */
class NavbarAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_see_sign_in_with_a_register_option_but_no_register_button(): void
    {
        $this->get('/')->assertOk()
            ->assertDontSee('nav-item nav-register', false)
            ->assertSee(route('student.register'), false)   // "New student?" item in the Sign in menu
            ->assertSee('New student?')
            ->assertSee('Sign in');
    }

    public function test_signed_in_students_get_a_dashboard_link_not_an_account_dropdown(): void
    {
        $u = new User;
        $u->name = 'Rochelle Abella';
        $u->email = 'student@example.test';
        $u->password = Hash::make('StudentPass123');
        $u->role = 'student';
        $u->status = 'active';
        $u->save();

        $this->actingAs($u)->get('/')->assertOk()
            ->assertSee('My dashboard')
            ->assertSee(route('student.dashboard'), false)
            ->assertSee(route('student.logout'), false)
            ->assertDontSee('My account')
            ->assertDontSee('nav-item nav-register', false);
    }
}
