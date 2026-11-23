<?php

namespace Tests\Feature;

use App\Models\Scholar;
use App\Models\Scholarship;
use App\Models\User;
use App\Support\AcademicTerm;
use App\Support\Spreadsheet;
use Database\Seeders\ProviderAccountSeeder;
use Database\Seeders\ScholarshipSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Owner, 2026-11-20: every scholar has a Scholar ID, typed freely by the provider (no format; unique
 * within the scholarship), which the provider searches and filters by; and the provider records the
 * scholar's birthday, gender (Male / Female), mobile number and address, all required. The provider's
 * scholar profile is edited one card at a time (School / Scholarship / Personal / Contact).
 */
class ScholarIdAndDetailsTest extends TestCase
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

    private function scholarship(string $slug = 'ched-tdp'): Scholarship
    {
        return Scholarship::where('slug', $slug)->firstOrFail();
    }

    private function person(array $over = []): array
    {
        return $over + [
            'scholarship_id' => $this->scholarship()->id, 'scholar_code' => 'SCID-2026-0001',
            'first_name' => 'Maria', 'last_name' => 'Santos', 'student_number' => '2023-0001', 'email' => 'maria@example.test',
            'campus' => 'Main (Panabo Campus)', 'program' => 'BS Information Technology', 'year_level' => '2',
            'birthdate' => '2005-03-14', 'gender' => 'female', 'mobile' => '0917 123 4567', 'address_street' => 'Purok 3, Gredu', 'address_city' => 'Panabo City', 'address_province' => 'Davao del Norte',
        ];
    }

    private function xlsx(array $rows): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'roster');
        file_put_contents($path, Spreadsheet::xlsx($rows));

        return new UploadedFile($path, 'roster.xlsx', null, null, true);
    }

    public function test_a_new_scholar_gets_a_scholar_id_and_personal_details_shown_to_provider_and_scholar(): void
    {
        $this->actingAs($this->provider());

        $html = $this->get('/provider/scholars/new')->assertOk()->getContent();
        foreach (['name="scholar_code"', 'name="birthdate"', 'name="gender"', 'name="mobile"', 'name="address_street"', 'name="address_city"', 'name="address_province"'] as $needle) {
            $this->assertStringContainsString($needle, $html);
        }
        // Only the middle name and the suffix are optional (owner, 2026-11-23); every personal detail is required.
        $this->assertSame(2, substr_count($html, '(optional)'));
        $this->assertStringContainsString('Middle name <small class="pv-optional">(optional)</small>', $html);
        $this->assertStringContainsString('Suffix <small class="pv-optional">(optional)</small>', $html);
        $this->assertStringNotContainsString('every 0 is one digit', $html);
        $this->assertStringNotContainsString('Prefer not to say', $html);

        $this->post('/provider/scholars', $this->person([
            'scholar_code' => ' DOST 2026/0001 ', 'mobile' => '+63 917-123-4567', 'address_street' => '  Purok 3,   Gredu ', 'address_city' => ' Panabo  City', 'address_province' => 'Davao del Norte ',
        ]))->assertSessionHasNoErrors()->assertRedirect();

        $s = Scholar::firstOrFail();
        $this->assertSame('DOST 2026/0001', $s->scholar_code);   // as typed (only trimmed)
        $this->assertSame('2005-03-14', $s->birthdate->toDateString());
        $this->assertSame('female', $s->gender);
        $this->assertSame('09171234567', $s->mobile);
        $this->assertSame('Purok 3, Gredu, Panabo City, Davao del Norte', $s->address);   // the parts, joined
        $this->assertSame(['Purok 3, Gredu', 'Panabo City', 'Davao del Norte'], [$s->address_street, $s->address_city, $s->address_province]);

        $this->get('/provider/scholars')->assertSee('DOST 2026/0001');
        $this->get("/provider/scholars/{$s->id}")->assertOk()
            ->assertSee('DOST 2026/0001 · ', false)                       // in the page heading line
            ->assertSee('Mar 14, 2005')->assertSee('Female')->assertSee('0917 123 4567')->assertSee('Purok 3, Gredu, Panabo City, Davao del Norte')
            ->assertSee('href="tel:+639171234567"', false)->assertSee('href="sms:+639171234567"', false)   // reachable when e-mail fails
            ->assertDontSee('name="birthdate"', false);                   // read-only until Edit

        // The scholar sees their own details (read-only) on Profile and My scholarship.
        $s->user->forceFill(['must_change_password' => false])->save();
        $this->actingAs($s->user)->get('/scholar/profile')->assertOk()
            ->assertSee('Mar 14, 2005')->assertSee('Female')->assertSee('0917 123 4567')->assertSee('Purok 3, Gredu, Panabo City, Davao del Norte')->assertSee('DOST 2026/0001')
            ->assertDontSee('name="mobile"', false);
        $this->get('/scholar/scholarship')->assertOk()->assertSee('DOST 2026/0001');
    }

    public function test_personal_details_are_required_and_checked(): void
    {
        $this->actingAs($this->provider());

        $this->post('/provider/scholars', $this->person(['mobile' => '12345', 'birthdate' => now()->toDateString(), 'gender' => 'robot']))
            ->assertSessionHasErrors(['mobile', 'birthdate', 'gender' => 'Choose Male or Female.']);
        $this->post('/provider/scholars', $this->person(['birthdate' => '14/03/2005']))->assertSessionHasErrors('birthdate');
        foreach (['other', 'undisclosed'] as $gone) {
            $this->post('/provider/scholars', $this->person(['gender' => $gone]))->assertSessionHasErrors('gender');
        }
        // None of them may be left out.
        $missing = $this->person();
        unset($missing['birthdate'], $missing['gender'], $missing['mobile'], $missing['address_street'], $missing['address_city'], $missing['address_province']);
        $this->post('/provider/scholars', $missing)->assertSessionHasErrors([
            'birthdate' => 'Enter the scholar\'s birthday.', 'gender' => 'Choose Male or Female.',
            'mobile' => 'Enter the scholar\'s mobile number.', 'address_street' => 'Enter the street: house no., street, purok or barangay.',
            'address_city' => 'Enter the city or municipality.', 'address_province' => 'Enter the province.',
        ]);
        $this->assertSame(0, Scholar::count());

        $this->post('/provider/scholars', $this->person(['gender' => 'male']))->assertSessionHasNoErrors();
        $this->assertSame('male', Scholar::firstOrFail()->gender);
    }

    public function test_the_scholar_id_is_typed_freely_but_unique_in_the_scholarship(): void
    {
        $this->actingAs($this->provider());

        $this->post('/provider/scholars', $this->person(['scholar_code' => '']))->assertSessionHasErrors('scholar_code');
        $this->post('/provider/scholars', $this->person(['scholar_code' => str_repeat('A', Scholar::SCHOLAR_CODE_MAX + 1)]))->assertSessionHasErrors('scholar_code');
        $this->assertSame(0, Scholar::count());

        // Any shape is accepted: there is no format.
        $this->post('/provider/scholars', $this->person(['scholar_code' => 'SCID-26-1']))->assertSessionHasNoErrors();
        // Same ID (any capitals), another person, same scholarship: refused.
        $this->post('/provider/scholars', $this->person(['scholar_code' => 'scid-26-1', 'first_name' => 'Ana', 'last_name' => 'Reyes', 'student_number' => '2023-0002', 'email' => 'ana@example.test']))
            ->assertSessionHasErrors(['scholar_code' => 'Scholar ID scid-26-1 already belongs to another scholar of TDP.']);
        // The same ID in ANOTHER scholarship is fine (each scholarship has its own IDs).
        $this->post('/provider/scholars', $this->person(['scholarship_id' => $this->scholarship('bpmsp')->id, 'scholar_code' => 'SCID-26-1', 'first_name' => 'Ana', 'last_name' => 'Reyes', 'student_number' => '2023-0002', 'email' => 'ana@example.test']))
            ->assertSessionHasNoErrors();
        $this->assertSame(2, Scholar::count());
    }

    public function test_the_scholar_id_format_is_gone_from_my_scholarships(): void
    {
        $this->assertFalse(Schema::hasColumn('scholarships', 'scholar_id_format'));
        $tdp = $this->scholarship();
        $this->actingAs($this->provider());

        $this->get("/provider/scholarships?s={$tdp->id}")->assertOk()->assertDontSee('Scholar ID format');
        // An old "scholar_id" section link just shows the read view; its save is refused.
        $this->get("/provider/scholarships?s={$tdp->id}&edit=scholar_id")->assertOk()->assertDontSee('name="scholar_id_format"', false);
        $this->put("/provider/scholarships/{$tdp->id}", ['section' => 'scholar_id', 'scholar_id_format' => 'X-0000'])->assertStatus(422);
    }

    public function test_the_profile_is_edited_one_card_at_a_time(): void
    {
        $this->actingAs($this->provider());
        $this->post('/provider/scholars', $this->person())->assertSessionHasNoErrors();
        $this->post('/provider/scholars', $this->person(['scholar_code' => 'B-2', 'first_name' => 'Ana', 'last_name' => 'Reyes', 'student_number' => '2023-0002', 'email' => 'ana@example.test']))->assertSessionHasNoErrors();
        $s = Scholar::where('full_name', 'Maria Santos')->firstOrFail();
        $url = "/provider/scholars/{$s->id}";

        // Each card opens its own form; the others stay read-only.
        $this->get("{$url}?edit=school")->assertOk()->assertSee('name="scholar_code"', false)->assertDontSee('name="birthdate"', false)->assertDontSee('name="mobile"', false);
        // Personal: the name is editable; birthday and gender are recorded, so they are shown locked (owner, 2026-11-23).
        $this->get("{$url}?edit=personal")->assertOk()->assertSee('name="first_name"', false)->assertSee('name="last_name"', false)->assertSee('value="Maria"', false)->assertDontSee('name="birthdate"', false)->assertDontSee('name="gender"', false)
            ->assertSee('Cannot be changed')->assertDontSee('name="scholar_code"', false);
        $this->get("{$url}?edit=contact")->assertOk()->assertSee('value="0917 123 4567"', false)->assertDontSee('name="gender"', false);

        // School: every field required; the ID is free text but not another scholar's.
        $this->patch($url, ['section' => 'school', 'scholar_code' => '', 'student_number' => '', 'year_level' => '', 'campus' => '', 'program' => ''])
            ->assertSessionHasErrors(['scholar_code', 'student_number', 'year_level', 'campus', 'program']);
        $school = ['section' => 'school', 'student_number' => '2023-0001', 'year_level' => '3', 'campus' => 'Main (Panabo Campus)', 'program' => 'BS Information Technology'];
        $this->patch($url, $school + ['scholar_code' => 'b-2'])->assertSessionHasErrors('scholar_code');   // Ana's, any capitals
        $this->patch($url, $school + ['scholar_code' => 'TDP/2026 0001'])->assertSessionHasNoErrors()->assertRedirect("{$url}#school");
        $s->refresh();
        $this->assertSame('TDP/2026 0001', $s->scholar_code);
        $this->assertSame('3', $s->year_level);
        $this->assertSame('female', $s->gender);                         // other cards untouched
        $this->assertSame('Bachelor of Science in Information Technology', $s->program);

        // Personal: the name is required; a recorded birthday / gender is never changed; other fields are ignored.
        // The name is in parts (2026-11-23): first name + surname required, middle name + suffix optional.
        $this->patch($url, ['section' => 'personal', 'first_name' => '', 'last_name' => ''])
            ->assertSessionHasErrors(['first_name' => 'Type the scholar\'s first name.', 'last_name' => 'Type the scholar\'s surname.']);
        $this->patch($url, ['section' => 'personal', 'first_name' => 'Maria', 'last_name' => 'Santos', 'suffix' => 'Esq.'])->assertSessionHasErrors('suffix');
        $this->patch($url, ['section' => 'personal', 'first_name' => 'Maria1', 'last_name' => 'Santos'])->assertSessionHasErrors('first_name');
        $this->patch($url, ['section' => 'personal', 'first_name' => '  Maria  ', 'middle_name' => 'Luz', 'last_name' => 'Santos-Cruz', 'suffix' => 'III', 'birthdate' => '2004-01-02', 'gender' => 'male', 'scholar_code' => 'HACK', 'mobile' => '09999999999'])->assertSessionHasNoErrors();
        $s->refresh();
        $this->assertSame('Maria Luz Santos-Cruz III', $s->full_name);
        $this->assertSame(['Maria', 'Luz', 'Santos-Cruz', 'III'], [$s->first_name, $s->middle_name, $s->last_name, $s->suffix]);
        $this->assertSame('Maria Luz Santos-Cruz III', $s->user->name);                 // the account's name follows
        $this->assertSame(['Maria', 'Santos-Cruz'], [$s->user->first_name, $s->user->last_name]);
        $this->assertSame('Maria Luz Santos-Cruz III', $s->displayName());
        $this->assertSame('MS', $s->initials());
        // Middle name and suffix can be cleared again.
        $this->patch($url, ['section' => 'personal', 'first_name' => 'Maria', 'middle_name' => '', 'last_name' => 'Santos-Cruz', 'suffix' => ''])->assertSessionHasNoErrors();
        $this->assertSame('Maria Santos-Cruz', $s->fresh()->displayName());
        $this->assertNull($s->fresh()->suffix);
        $this->assertSame('female', $s->gender);                               // locked
        $this->assertSame('2005-03-14', $s->birthdate->toDateString());        // locked
        $this->assertSame('TDP/2026 0001', $s->scholar_code);
        $this->assertSame('09171234567', $s->mobile);

        // Contact: required, mobile checked and stored as 09…
        $this->patch($url, ['section' => 'contact', 'mobile' => '', 'address_street' => '', 'address_city' => '', 'address_province' => ''])
            ->assertSessionHasErrors(['mobile', 'address_street', 'address_city', 'address_province']);
        $this->patch($url, ['section' => 'contact', 'mobile' => '+63 918 765 4321', 'address_street' => ' Purok 1,  Visayan Village ', 'address_city' => ' Tagum   City ', 'address_province' => 'Davao del Norte', 'email' => 'hack@example.test'])->assertSessionHasNoErrors();
        $s->refresh();
        $this->assertSame('09187654321', $s->mobile);
        $this->assertSame('Purok 1, Visayan Village, Tagum City, Davao del Norte', $s->address);
        $this->assertSame('Tagum City', $s->address_city);
        $this->assertSame('maria@example.test', $s->contactEmail());           // the e-mail has its own confirmed flow

        // No section, or an unknown one, saves nothing.
        $this->patch($url, ['scholar_code' => 'X'])->assertStatus(422);
        $this->patch($url, ['section' => 'status', 'status' => 'terminated'])->assertStatus(422);
        $this->assertSame('active', $s->fresh()->status);
    }

    public function test_every_scholarship_card_edits_scholar_since_and_only_its_own_fields(): void
    {
        // DNSC has neither grant types nor Disbursements: its Scholarship card edits only "Scholar since" (2026-11-21).
        $dnsc = $this->scholarship('dnsc-academic');
        $this->assertFalse($dnsc->hasGrantTypes());
        $s = Scholar::create(['full_name' => 'Lee Uy', 'scholar_code' => 'D-1', 'scholarship_id' => $dnsc->id, 'status' => 'active', 'started_term' => AcademicTerm::current()]);
        $this->actingAs($this->provider('dnsc'));

        $this->get("/provider/scholars/{$s->id}")->assertOk()->assertSee('edit=scholarship', false)->assertSee('Not recorded yet');
        $this->get("/provider/scholars/{$s->id}?edit=scholarship")->assertOk()->assertSee('name="started_term"', false)->assertDontSee('name="stipend_amount"', false);

        $earlier = AcademicTerm::previous();
        $this->patch("/provider/scholars/{$s->id}", ['section' => 'scholarship', 'started_term' => $earlier, 'stipend_amount' => '5'])->assertSessionHasNoErrors();
        $this->assertSame($earlier, $s->fresh()->started_term);
        $this->assertNull($s->fresh()->stipend_amount);   // no Disbursements: never saved

        $this->patch("/provider/scholars/{$s->id}", ['section' => 'scholarship', 'started_term' => ''])->assertSessionHasErrors('started_term');
        $this->patch("/provider/scholars/{$s->id}", ['section' => 'scholarship', 'started_term' => 'AY 1990–1991 · 1st Semester'])->assertSessionHasErrors('started_term');
        $this->assertSame($earlier, $s->fresh()->started_term);
    }

    public function test_providers_search_and_filter_by_scholar_id(): void
    {
        $tdp = $this->scholarship();
        Scholar::create(['full_name' => 'Ana Reyes', 'scholar_code' => 'SCID-2026-0001', 'scholarship_id' => $tdp->id, 'status' => 'active']);
        Scholar::create(['full_name' => 'Ben Cruz', 'scholar_code' => 'SCID-2025-0042', 'scholarship_id' => $tdp->id, 'status' => 'active']);
        Scholar::create(['full_name' => 'Cara Lim', 'scholarship_id' => $tdp->id, 'status' => 'active']);   // no ID yet
        $this->actingAs($this->provider());

        $this->get('/provider/scholars?q=scid-2025-0042')->assertSee('Ben Cruz')->assertDontSee('Ana Reyes');     // search box
        $this->get('/provider/scholars?scholar_id=SCID-2026')->assertSee('Ana Reyes')->assertDontSee('Ben Cruz')->assertDontSee('Cara Lim');   // starts with
        $this->get('/provider/scholars?scholar_id=0042')->assertDontSee('Ben Cruz');                               // "starts with", not "contains"
        $this->get('/provider/scholars?scholar_id=None')->assertSee('Cara Lim')->assertDontSee('Ana Reyes')->assertSee('No Scholar ID')->assertSee('value="none"', false);
        $this->get('/provider/scholars?scholar_id=%25')->assertDontSee('Ana Reyes');                               // % is not a wildcard
    }

    public function test_import_reads_scholar_ids_and_personal_details(): void
    {
        $this->actingAs($this->provider());

        $this->post('/provider/scholars/import', ['scholarship_id' => $this->scholarship()->id, 'file' => $this->xlsx([
            ['Scholar ID', 'First name', 'Surname', 'Student number', 'Email', 'Contact number', 'Street', 'City / Municipality', 'Province', 'Date of birth', 'Sex', 'Campus', 'Program', 'Year level'],
            ['SCID-2026-0001', 'Juan', 'Dela Cruz', '2023-0001', 'juan@example.test', '9171234567', 'Purok 3, Gredu', 'Panabo City', 'Davao del Norte', '38425', 'M', 'Main', 'BS Information Technology', '1'],
            ['T-2', 'Ana', 'Reyes', '2023-0002', 'ana@example.test', '09181234567', 'Purok 1', 'Tagum City', 'Davao del Norte', '03/14/2005', 'F', 'Main', 'BS Information Technology', '2'],
            ['scid-2026-0001', 'Twin', 'ID', '2023-0003', 'twin@example.test', '09181234568', 'Purok 1', 'Carmen', 'Davao del Norte', '03/14/2005', 'F', 'Main', 'BS Information Technology', '2'],   // ID twice in the file
            ['SCID-2026-0004', 'No', 'Details', '2023-0004', 'nod@example.test', '', '', '', '', '', '', 'Main', 'BS Information Technology', '2'],                         // details missing
            ['', 'No', 'ID', '2023-0005', 'noid@example.test', '09181234569', 'Purok 1', 'Carmen', 'Davao del Norte', '03/14/2005', 'F', 'Main', 'BS Information Technology', '2'],               // ID missing
            ['SCID-2026-0006', 'Bad', 'Phone', '2023-0006', 'phone@example.test', '555', 'Purok 1', 'Carmen', 'Davao del Norte', '03/14/2005', 'F', 'Main', 'BS Information Technology', '2'],
            ['SCID-2026-0007', 'Other', 'Gender', '2023-0007', 'og@example.test', '09181234570', 'Purok 1', 'Carmen', 'Davao del Norte', '03/14/2005', 'Prefer not to say', 'Main', 'BS Information Technology', '2'],
        ])])->assertSessionHas('import_report', fn ($r) => $r['added'] === 2 && count($r['skipped']) === 5
            && str_contains($r['skipped'][0], 'appears twice') && str_contains($r['skipped'][4], 'Choose Male or Female.'));

        $juan = Scholar::where('full_name', 'Juan Dela Cruz')->firstOrFail();
        $this->assertSame('09171234567', $juan->mobile);
        $this->assertSame('2005-03-14', $juan->birthdate->toDateString());
        $this->assertSame('male', $juan->gender);
        $this->assertSame('Purok 3, Gredu, Panabo City, Davao del Norte', $juan->address);
        $this->assertSame('female', Scholar::where('full_name', 'Ana Reyes')->value('gender'));
        $this->assertSame('T-2', Scholar::where('full_name', 'Ana Reyes')->value('scholar_code'));   // any shape

        // A file without the required columns is refused as a whole.
        $this->post('/provider/scholars/import', ['scholarship_id' => $this->scholarship()->id, 'file' => $this->xlsx([
            ['First name', 'Surname', 'Student number', 'Email', 'Campus', 'Program', 'Year level'], ['X', 'Y', '1', 'x@example.test', 'Main', 'BS Information Technology', '1'],
        ])])->assertSessionHasErrors(['file' => 'The first row must be the column titles. Missing: "Scholar ID", "Mobile number", "Street", "City / Municipality", "Province", "Birthday", "Gender". Download the template to see the expected columns.']);
    }
}
