<?php

namespace Tests\Feature;

use App\Models\Scholar;
use App\Models\Scholarship;
use App\Models\User;
use App\Support\Spreadsheet;
use Database\Seeders\ScholarshipSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Owner, 2026-11-23: the full name is separated everywhere a student or scholar name is typed: first name and surname
 * required, middle name and suffix optional. The one-line name is composed from the parts.
 */
class PersonNameFormsTest extends TestCase
{
    use RefreshDatabase;

    public function test_students_register_with_their_name_in_parts(): void
    {
        $html = $this->get('/studentRegister')->assertOk()->getContent();
        foreach (['name="first_name"', 'name="middle_name"', 'name="last_name"', 'name="suffix"'] as $field) {
            $this->assertStringContainsString($field, $html);
        }
        $this->assertStringNotContainsString('name="name"', $html);

        // First name and surname are required; the suffix must be one from the list.
        $this->post('/studentRegister', ['email' => 'juan@example.test', 'password' => 'Secret-123', 'password_confirmation' => 'Secret-123'])
            ->assertSessionHasErrors(['first_name' => 'Type your first name.', 'last_name' => 'Type your surname.']);
        $this->post('/studentRegister', ['first_name' => 'Juan', 'last_name' => 'Cruz', 'suffix' => 'Esq.', 'email' => 'juan@example.test', 'password' => 'Secret-123', 'password_confirmation' => 'Secret-123'])
            ->assertSessionHasErrors('suffix');
        $this->post('/studentRegister', ['first_name' => 'Juan2', 'last_name' => 'Cruz', 'email' => 'juan@example.test', 'password' => 'Secret-123', 'password_confirmation' => 'Secret-123'])
            ->assertSessionHasErrors('first_name');
        $this->assertGuest();

        $this->post('/studentRegister', ['first_name' => ' Juan ', 'middle_name' => 'Andres', 'last_name' => 'Dela Cruz', 'suffix' => 'jr',
            'email' => 'juan@example.test', 'password' => 'Secret-123', 'password_confirmation' => 'Secret-123'])
            ->assertSessionHasNoErrors()->assertRedirect(route('student.application.show'));

        $u = User::where('email', 'juan@example.test')->firstOrFail();
        $this->assertSame('student', $u->role);
        $this->assertSame('Juan Andres Dela Cruz Jr.', $u->name);
        $this->assertSame(['Juan', 'Andres', 'Dela Cruz', 'Jr.'], [$u->first_name, $u->middle_name, $u->last_name, $u->suffix]);
        $this->assertSame('Juan', $u->firstName());
        $this->assertSame('JD', $u->initials());

        // Without a middle name or suffix.
        auth()->logout();
        $this->post('/studentRegister', ['first_name' => 'Ana', 'last_name' => 'Reyes', 'email' => 'ana@example.test', 'password' => 'Secret-123', 'password_confirmation' => 'Secret-123'])
            ->assertSessionHasNoErrors();
        $ana = User::where('email', 'ana@example.test')->firstOrFail();
        $this->assertSame('Ana Reyes', $ana->name);
        $this->assertNull($ana->middle_name);
        $this->assertNull($ana->suffix);
    }

    public function test_the_scholar_sees_their_name_in_parts_and_a_one_column_file_is_refused(): void
    {
        Mail::fake();
        $this->seed(ScholarshipSeeder::class);
        $u = new User;
        $u->forceFill(['email' => 'maria@example.test', 'password' => Hash::make('Scholar-Pass-2026'), 'role' => 'scholar', 'status' => 'active', 'must_change_password' => false])
            ->fillName(['first_name' => 'Maria', 'middle_name' => 'Luz', 'last_name' => 'Santos', 'suffix' => 'II'])->save();
        $tdp = Scholarship::where('slug', 'ched-tdp')->firstOrFail();
        Scholar::create(['user_id' => $u->id, 'full_name' => $u->name, 'scholarship_id' => $tdp->id, 'status' => 'active']);

        $this->actingAs($u)->get('/scholar/profile')->assertOk()
            ->assertSeeInOrder(['First name', 'Maria', 'Middle name', 'Luz', 'Surname', 'Santos', 'Suffix', 'II'])
            ->assertDontSee('name="first_name"', false);   // read-only: the provider keeps it
        $this->get('/scholar')->assertOk()->assertSee('Maria');

        // Provider import: a file with only a "Full name" column is refused (the parts are required).
        $p = new User;
        $p->forceFill(['name' => 'CHED', 'email' => 'ched@provider.test', 'password' => Hash::make('x-12345678'), 'role' => 'provider', 'status' => 'active', 'provider_key' => 'ched'])->save();
        $path = tempnam(sys_get_temp_dir(), 'roster');
        file_put_contents($path, Spreadsheet::xlsx([['Scholar ID', 'Full name', 'Student number', 'Email'], ['T-1', 'Juan Dela Cruz', '1', 'j@example.test']]));
        $this->actingAs($p)->post('/provider/scholars/import', ['scholarship_id' => $tdp->id, 'file' => new UploadedFile($path, 'r.xlsx', null, null, true)])
            ->assertSessionHasErrors('file');
        $this->assertStringContainsString('"First name", "Surname"', session('errors')->first('file'));
    }
}
