<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/** Signed in with the wrong kind of account: still refused (403), but the page explains why. */
class WrongRolePageTest extends TestCase
{
    use RefreshDatabase;

    private function provider(): User
    {
        $u = new User;
        $u->name = 'CHED / UniFAST';
        $u->email = 'ched@provider.test';
        $u->password = Hash::make('Provider-Test-Pass-2026');
        $u->role = 'provider';
        $u->provider_key = 'ched';
        $u->status = 'active';
        $u->save();

        return $u;
    }

    public function test_provider_on_the_student_questionnaire_gets_an_explained_403(): void
    {
        $this->actingAs($this->provider())
            ->get('/student/application')
            ->assertForbidden()
            ->assertSee('scholarship provider account')
            ->assertSee('only for student accounts')
            ->assertSee(route('provider.logout'), false)
            ->assertSee(route('provider.dashboard'), false);
    }

    public function test_navbar_shows_the_provider_account_menu_and_hides_find_a_scholarship(): void
    {
        $this->actingAs($this->provider())
            ->get('/')
            ->assertOk()
            ->assertSee('Provider dashboard')
            ->assertDontSee('nav-item find-button', false);
    }
}
