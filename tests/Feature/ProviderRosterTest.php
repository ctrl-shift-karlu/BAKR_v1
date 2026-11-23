<?php

namespace Tests\Feature;

use App\Models\Scholar;
use App\Models\ScholarRequirement;
use App\Models\Scholarship;
use App\Models\User;
use App\Support\AcademicTerm;
use App\Support\Spreadsheet;
use Database\Seeders\ProviderAccountSeeder;
use Database\Seeders\ScholarshipSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Per-provider pages (DNSC has no Disbursements / Requirements) and the official scholar
 * roster: add one scholar, import many from Excel (.xlsx) or CSV.
 */
class ProviderRosterTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'Provider-Test-Pass-2026';

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Mail::fake();

        foreach (array_keys(config('providers.accounts')) as $key) {
            config([
                "providers.accounts.{$key}.email" => "{$key}@provider.test",
                "providers.accounts.{$key}.password" => self::PASSWORD,
            ]);
        }
        $this->seed(ScholarshipSeeder::class);
        $this->seed(ProviderAccountSeeder::class);
    }

    private function provider(string $key): User
    {
        return User::where('email', "{$key}@provider.test")->firstOrFail();
    }

    private function sid(string $slug): int
    {
        return (int) Scholarship::where('slug', $slug)->value('id');
    }

    private function xlsxUpload(array $rows, string $name = 'roster.xlsx'): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'roster');
        file_put_contents($path, Spreadsheet::xlsx($rows));

        return new UploadedFile($path, $name, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    // ---------- Pages per provider ----------

    public function test_dnsc_has_no_disbursements_or_requirements_pages(): void
    {
        $this->actingAs($this->provider('dnsc'));

        foreach (['disbursements', 'requirements'] as $page) {
            $this->get("/provider/{$page}")->assertNotFound();
        }
        $this->post('/provider/disbursements/generate', ['term' => 'x', 'release_type' => 'semester'])->assertNotFound();
        $this->post('/provider/requirements/deadline', ['term' => 'x'])->assertNotFound();
        $this->post('/provider/requirements/open', ['term' => 'x'])->assertNotFound();   // the old checklist button is gone

        $html = $this->get('/provider/dashboard')->assertOk()->getContent();
        $this->assertStringNotContainsString('/provider/disbursements', $html);
        $this->assertStringNotContainsString('/provider/requirements', $html);

        foreach (['scholars', 'scholars/new', 'scholarships', 'help'] as $page) {
            $this->get("/provider/{$page}")->assertOk();
        }
        $this->get('/provider/scholarships')->assertDontSee('Renewal requirements per term');
    }

    public function test_other_providers_keep_every_page(): void
    {
        foreach (['ched', 'dost', 'cebuana', 'grab'] as $key) {
            $this->actingAs($this->provider($key));
            foreach (['dashboard', 'scholars', 'scholars/new', 'disbursements', 'requirements', 'scholarships', 'help'] as $page) {
                $this->get("/provider/{$page}")->assertOk();
            }
            $this->get('/provider/dashboard')->assertSee('/provider/disbursements', false);
        }
    }

    public function test_dnsc_saving_my_scholarships_keeps_the_stored_renewal_list(): void
    {
        $s = Scholarship::where('slug', 'dnsc-academic')->firstOrFail();
        $s->update(['renewal_requirements' => ['COR']]);
        $this->actingAs($this->provider('dnsc'));

        $this->put("/provider/scholarships/{$s->id}", ['overview' => 'Updated overview.', 'renewal_requirements' => 'Hacked'])->assertRedirect();

        $s->refresh();
        $this->assertSame('Updated overview.', $s->overview);
        $this->assertSame(['COR'], $s->renewal_requirements);
    }

    public function test_a_dnsc_scholar_cannot_upload_renewal_documents(): void
    {
        $student = $this->student('dnsc-scholar@example.test');   // a scholar account (see student())
        $scholar = Scholar::create(['user_id' => $student->id, 'scholarship_id' => $this->sid('dnsc-academic'), 'status' => 'active']);
        $req = $scholar->requirements()->create(['term' => '1st Semester 2026-2027', 'name' => 'COR', 'status' => 'missing']);

        $this->actingAs($student)
            ->post("/scholar/requirements/{$req->id}/upload", ['file' => UploadedFile::fake()->create('cor.pdf', 10, 'application/pdf')])
            ->assertNotFound();
        $this->assertSame('missing', $req->fresh()->status);
        $this->actingAs($student)->get('/scholar/scholarship')->assertOk()->assertSee('handles stipends directly with you');
        $this->get('/scholar/requirements')->assertOk()->assertSee('in person at the official DNSC scholarship office')->assertDontSee('name="file"', false);
    }

    // ---------- Add one scholar ----------

    /** The scholar's own details, as the form sends them (birthday, gender, mobile and address are required since 2026-11-20). */
    private function person(array $over = []): array
    {
        return $over + ['scholar_code' => 'SCID-2026-0001', 'first_name' => 'Maria', 'last_name' => 'Santos', 'student_number' => '2023-0001', 'email' => 'maria@example.test', 'campus' => 'Main (Panabo Campus)', 'program' => 'BS Information Technology', 'year_level' => '2',
            'birthdate' => '2005-03-14', 'gender' => 'female', 'mobile' => '0917 123 4567', 'address_street' => 'Purok 3, Gredu', 'address_city' => 'Panabo City', 'address_province' => 'Davao del Norte'];
    }

    /** Adds the required personal columns (Mobile number, Street, City / Municipality, Province, Birthday, Gender) with valid values to a sheet. */
    private function withDetails(array $rows): array
    {
        $header = array_shift($rows);

        return [[...$header, 'Mobile number', 'Street', 'City / Municipality', 'Province', 'Birthday', 'Gender'], ...array_map(fn ($r) => [...$r, '09171234567', 'Purok 3, Gredu', 'Panabo City', 'Davao del Norte', '2005-03-14', 'F'], $rows)];
    }

    public function test_a_provider_adds_one_new_scholar_and_the_rest_is_filled_in(): void
    {
        $this->actingAs($provider = $this->provider('ched'));

        $this->post('/provider/scholars', $this->person([
            'scholarship_id' => $this->sid('ched-tdp'), 'first_name' => '  Maria  ', 'last_name' => 'Santos', 'email' => 'Maria@Example.test',
            // Not on the form: ignored if someone sends them.
            'status' => 'graduated', 'stipend_amount' => '1',
        ]))->assertRedirect();

        $s = Scholar::firstOrFail();
        $this->assertNotNull($s->user_id);                                // the account is created on add
        $this->assertSame('Maria Santos', $s->full_name);
        $this->assertSame('maria@example.test', $s->email);
        $this->assertSame($provider->id, $s->added_by);
        $this->assertSame('active', $s->status);                          // a new scholar
        $this->assertSame(AcademicTerm::current(), $s->started_term);     // nothing picked: this semester
        $this->assertSame('regular', $s->grant_type);                     // TDP has one published option
        $this->assertSame('7500.00', $s->stipend_amount);                 // ₱7,500 per semester (published)

        $this->get('/provider/scholars')->assertSee('Maria Santos')->assertSee('Not signed in yet');
        $this->get("/provider/scholars/{$s->id}")->assertOk()->assertSee('maria@example.test');
        $this->get('/provider/scholars?q=maria')->assertSee('Maria Santos');
    }

    public function test_the_form_asks_scholar_since_but_not_status_or_stipend(): void
    {
        $this->actingAs($this->provider('ched'));

        $html = $this->get('/provider/scholars/new')->assertOk()->getContent();
        foreach (['name="status"', 'name="stipend_amount"'] as $input) {
            $this->assertStringNotContainsString($input, $html);
        }
        // "Scholar since" on both forms (owner, 2026-11-21), starting on the current semester.
        $this->assertSame(2, substr_count($html, 'name="started_term"'));
        $this->assertStringContainsString('<option value="'.AcademicTerm::current().'" selected', $html);
        foreach (['name="first_name"', 'name="middle_name"', 'name="last_name"', 'name="suffix"', 'name="student_number"', 'name="email"', 'name="program"', 'name="year_level"', 'name="grant_type"'] as $input) {
            $this->assertStringContainsString($input, $html);
        }
        $this->assertStringContainsString('Full scholarship (Full-SSP)', $html);   // CMSP Full / Half choice
    }

    public function test_cmsp_needs_full_or_half_and_takes_that_stipend(): void
    {
        $this->actingAs($this->provider('ched'));
        $cmsp = $this->sid('ched-cmsp');

        $this->post('/provider/scholars', $this->person(['scholarship_id' => $cmsp]))->assertSessionHasErrors('grant_type');
        $this->post('/provider/scholars', $this->person(['scholarship_id' => $cmsp, 'grant_type' => 'quarter']))->assertSessionHasErrors('grant_type');
        $this->assertSame(0, Scholar::count());

        $this->post('/provider/scholars', $this->person(['scholarship_id' => $cmsp, 'grant_type' => 'half']))->assertRedirect();
        $s = Scholar::firstOrFail();
        $this->assertSame('half', $s->grant_type);
        $this->assertSame('20000.00', $s->stipend_amount);
        $this->assertSame('Half scholarship (Half-SSP)', $s->grantLabel());
        $this->get('/provider/scholars')->assertSee('Half scholarship (Half-SSP)');

        // Changing the type on the profile brings that type's published stipend.
        $this->patch("/provider/scholars/{$s->id}", ['section' => 'scholarship', 'status' => 'terminated', 'grant_type' => 'full', 'stipend_amount' => '20000'])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('active', $s->fresh()->status);   // status is never set from this form
        $this->assertSame('40000.00', $s->fresh()->stipend_amount);
    }

    public function test_a_scholarship_with_one_option_ignores_a_sent_grant_type(): void
    {
        $this->actingAs($this->provider('dost'));

        $this->post('/provider/scholars', $this->person(['scholarship_id' => $this->sid('dost-sei'), 'grant_type' => 'full']))->assertRedirect();

        $s = Scholar::firstOrFail();
        $this->assertSame('regular', $s->grant_type);
        $this->assertSame('8000.00', $s->stipend_amount);   // ₱8,000 per month (published)
    }

    public function test_no_published_amount_means_no_stipend(): void
    {
        $this->actingAs($this->provider('cebuana'));   // "up to ₱50,000": not a fixed amount

        $this->post('/provider/scholars', $this->person(['scholarship_id' => $this->sid('cebuana-clfi')]))->assertRedirect();

        $s = Scholar::firstOrFail();
        $this->assertNull($s->grant_type);
        $this->assertNull($s->stipend_amount);
    }

    public function test_required_details_and_year_levels(): void
    {
        $this->actingAs($this->provider('ched'));
        $sid = $this->sid('ched-tdp');

        $this->post('/provider/scholars', ['scholarship_id' => $sid, 'first_name' => 'Maria', 'last_name' => 'Santos'])
            ->assertSessionHasErrors(['scholar_code', 'student_number', 'campus', 'program', 'year_level', 'birthdate', 'gender', 'mobile', 'address_street', 'address_city', 'address_province']);
        $this->post('/provider/scholars', $this->person(['scholarship_id' => $sid, 'year_level' => '9']))->assertSessionHasErrors('year_level');
        $this->post('/provider/scholars', $this->person(['scholarship_id' => $sid, 'year_level' => '5']))->assertSessionHasErrors('year_level');   // no 5th year
        $this->post('/provider/scholars', $this->person(['scholarship_id' => $sid, 'program' => 'BS Nursing']))->assertSessionHasErrors('program');   // not a DNSC program
        $this->assertSame(0, Scholar::count());

        // The form offers a campus select and the programs of each campus (plain selects, no browser datalist), and only 1st–4th year.
        $html = $this->get('/provider/scholars/new')->getContent();
        $this->assertStringNotContainsString('dnsc-programs', $html);
        $this->assertStringContainsString('data-campus-select', $html);
        $this->assertStringContainsString('<option value="Samal Campus"', $html);
        $this->assertStringContainsString('value="Bachelor of Science in Agro-Forestry" data-campus="Talaingod Campus"', $html);
        $this->assertStringNotContainsString('5th year', $html);

        $this->post('/provider/scholars', $this->person(['scholarship_id' => $sid, 'program' => 'bs information technology']))->assertRedirect();
        $this->assertSame('Bachelor of Science in Information Technology', Scholar::firstOrFail()->program);
    }

    public function test_the_campus_is_saved_shown_everywhere_and_counted_in_the_admin_analytics(): void
    {
        $this->actingAs($provider = $this->provider('ched'));
        $sid = $this->sid('ched-tdp');

        // "Samal" is understood and stored under the official campus name.
        $this->post('/provider/scholars', $this->person(['scholarship_id' => $sid, 'campus' => 'samal']))->assertRedirect();
        $s = Scholar::firstOrFail();
        $this->assertSame('Samal Campus', $s->campus);

        $admin = (new User)->forceFill(['name' => 'Admin', 'email' => 'admin@example.test', 'password' => Hash::make('Pass12345'), 'role' => 'admin', 'status' => 'active']);
        $admin->save();

        $this->get('/provider/scholars')->assertSee('Samal Campus');                   // provider's list
        $this->get("/provider/scholars/{$s->id}")->assertSee('Samal Campus');                           // profile (read)
        $this->get("/provider/scholars/{$s->id}?edit=school")->assertSee('value="Samal Campus" selected', false);   // profile form
        $s->user->forceFill(['must_change_password' => false])->save();
        $this->actingAs($s->user)->get('/scholar/profile')->assertSee('Samal Campus');   // the scholar's own profile
        $this->actingAs($admin)->get('/admin/analytics?scholars=campus')->assertSee('Samal Campus · Active: 1');

        // Changing it on the profile moves the scholar in the analytics straight away.
        $this->actingAs($provider)->patch("/provider/scholars/{$s->id}", ['section' => 'school', 'scholar_code' => 'SCID-2026-0001', 'student_number' => '2023-0001', 'campus' => 'Asuncion Campus', 'program' => 'Bachelor of Science in Information Technology', 'year_level' => '2'])->assertSessionHasNoErrors();
        $this->assertSame('Asuncion Campus', $s->fresh()->campus);
        $this->actingAs($admin)->get('/admin/analytics?scholars=campus')->assertSee('Asuncion Campus · Active: 1')->assertDontSee('Samal Campus · Active: 1');
    }

    public function test_a_program_must_be_offered_at_the_chosen_campus(): void
    {
        $this->actingAs($this->provider('ched'));
        $sid = $this->sid('ched-tdp');

        // Agro-Forestry is only taught at Talaingod; Carmen offers PE and Criminology.
        $this->post('/provider/scholars', $this->person(['scholarship_id' => $sid, 'campus' => 'Carmen Campus', 'program' => 'BS Agroforestry']))
            ->assertSessionHasErrors('program');
        $this->post('/provider/scholars', $this->person(['scholarship_id' => $sid, 'campus' => 'Atlantis']))->assertSessionHasErrors('campus');
        $this->post('/provider/scholars', $this->person(['scholarship_id' => $sid, 'campus' => '']))->assertSessionHasErrors('campus');
        $this->assertSame(0, Scholar::count());

        $this->post('/provider/scholars', $this->person(['scholarship_id' => $sid, 'campus' => 'Talaingod', 'program' => 'BS Agroforestry']))->assertRedirect();
        $this->assertSame('Talaingod Campus', Scholar::firstOrFail()->campus);
    }

    public function test_an_import_row_needs_a_campus_when_several_campuses_offer_the_program(): void
    {
        $this->actingAs($this->provider('ched'));

        $this->post('/provider/scholars/import', ['scholarship_id' => $this->sid('ched-tdp'), 'file' => $this->xlsxUpload($this->withDetails([
            ['Scholar ID', 'First name', 'Surname', 'Student number', 'Email', 'Program', 'Year level'],
            ['SCID-2026-0001', 'Juan', 'Dela Cruz', '2023-0001', 'juan@example.test', 'BS Information Technology', '1'],   // IT: Main, Samal, Asuncion
            ['SCID-2026-0002', 'Ana', 'Reyes', '2023-0002', 'ana@example.test', 'BS Criminology', '2'],                     // Carmen offers it (Main does not)
        ]))])->assertSessionHas('import_report', fn ($r) => $r['added'] === 1 && str_contains($r['skipped'][0], 'more than one campus'));

        $this->assertSame('Carmen Campus', Scholar::where('full_name', 'Ana Reyes')->value('campus'));
    }

    public function test_the_same_student_number_is_not_added_twice(): void
    {
        $this->actingAs($this->provider('ched'));
        $row = $this->person(['scholarship_id' => $this->sid('ched-tdp')]);

        $this->post('/provider/scholars', $row)->assertRedirect();
        $this->post('/provider/scholars', ['first_name' => 'Someone', 'last_name' => 'Else'] + $row)->assertSessionHasErrors('student_number');

        $this->assertSame(1, Scholar::count());
    }

    public function test_a_provider_cannot_add_to_another_providers_scholarship(): void
    {
        $this->actingAs($this->provider('ched'));

        $this->post('/provider/scholars', $this->person(['scholarship_id' => $this->sid('dost-sei')]))->assertNotFound();
        $this->assertSame(0, Scholar::count());
    }

    public function test_dnsc_scholars_get_no_stipend(): void
    {
        $this->actingAs($this->provider('dnsc'));

        $this->post('/provider/scholars', $this->person(['scholarship_id' => $this->sid('dnsc-academic')]))->assertRedirect();
        $this->assertNull(Scholar::firstOrFail()->stipend_amount);
        $this->get('/provider/scholars/new')->assertDontSee('Stipend per release');
    }

    // ---------- Import from Excel / CSV ----------

    public function test_import_from_an_xlsx_file_adds_valid_rows_and_reports_the_rest(): void
    {
        $this->actingAs($this->provider('ched'));
        Scholar::create(['full_name' => 'Already There', 'student_number' => '2023-0009', 'scholarship_id' => $this->sid('ched-tdp'), 'status' => 'active']);

        $file = $this->xlsxUpload($this->withDetails([
            ['First name', 'Surname', 'E-mail', 'Student No', 'Course', 'Year', 'Status', 'Notes', 'Campus', 'Scholar ID'],
            ['Juan', 'Dela Cruz', 'juan@example.test', '2023-0001', 'BS Agroforestry', '1st year', 'Graduated', 'ignored', '', 'scid-2026-0001'],   // Talaingod is the only campus: inferred
            ['Ana', 'Reyes', 'ana@example.test', '2023-0002', 'Bachelor of Science in Social Work', 'Third Year', '', '', 'Main', 'SCID-2026-0002'],
            ['', '', 'no-name@example.test', '2023-0003', 'BS Information Technology', '2', '', '', 'Samal', 'SCID-2026-0003'],          // no name
            ['Bad', 'Email', 'not-an-email', '2023-0004', 'BS Information Technology', '2', '', '', 'Samal', 'SCID-2026-0004'],        // invalid e-mail
            ['Dup In', 'File', 'dup@example.test', '2023-0001', 'BS Information Technology', '2', '', '', 'Samal', 'SCID-2026-0005'],                  // same number twice in the file
            ['Old', 'Scholar', 'old@example.test', '2023-0009', 'BS Information Technology', '2', '', '', 'Samal', 'SCID-2026-0006'],                  // already a scholar
            ['No', 'Program', 'np@example.test', '2023-0005', '', '2', '', '', 'Samal', 'SCID-2026-0007'],                       // program missing
            ['Bad', 'Year', 'by@example.test', '2023-0006', 'BS Information Technology', 'seventh', '', '', 'Samal', 'SCID-2026-0008'],               // unknown year level
        ]));

        $this->post('/provider/scholars/import', ['scholarship_id' => $this->sid('ched-tdp'), 'file' => $file])
            ->assertRedirect('/provider/scholars/new')
            ->assertSessionHas('import_report', fn ($r) => $r['added'] === 2 && count($r['skipped']) === 6);

        $juan = Scholar::where('full_name', 'Juan Dela Cruz')->firstOrFail();
        $this->assertSame('Bachelor of Science in Agro-Forestry', $juan->program);   // short form saved as the official name
        $this->assertSame('scid-2026-0001', $juan->scholar_code);                     // Scholar ID saved as typed (no format)
        $this->assertSame('female', $juan->gender);                                   // "F"
        $this->assertSame('1', $juan->year_level);
        $this->assertSame('Talaingod Campus', $juan->campus);   // left empty: the only campus that offers Agro-Forestry
        $this->assertSame('Main (Panabo Campus)', Scholar::where('full_name', 'Ana Reyes')->value('campus'));   // "Main" understood
        $this->assertSame('active', $juan->status);             // the Status column is ignored: new scholars are Active
        $this->assertSame('7500.00', $juan->stipend_amount);    // published TDP amount
        $this->assertSame(AcademicTerm::current(), $juan->started_term);
        $this->assertSame('3', Scholar::where('full_name', 'Ana Reyes')->value('year_level'));
        $this->assertSame(3, Scholar::count());

        $this->get('/provider/scholars/new')->assertSee('Row 4:')->assertSee('Row 9:');
    }

    public function test_import_into_cmsp_needs_a_grant_type_column(): void
    {
        $this->actingAs($this->provider('ched'));
        $cmsp = $this->sid('ched-cmsp');

        $this->post('/provider/scholars/import', ['scholarship_id' => $cmsp, 'file' => $this->xlsxUpload($this->withDetails([
            ['Scholar ID', 'First name', 'Surname', 'Student number', 'Email', 'Campus', 'Program', 'Year level'], ['SCID-2026-0001', 'Juan', 'Dela Cruz', '2023-0001', 'juan@example.test', 'Main', 'BS Information Technology', '1'],
        ]))])->assertSessionHasErrors(['file' => 'CMSP has more than one grant type, so the file needs a "Grant type" column (Full scholarship (Full-SSP) or Half scholarship (Half-SSP)).']);

        $this->post('/provider/scholars/import', ['scholarship_id' => $cmsp, 'file' => $this->xlsxUpload($this->withDetails([
            ['Scholar ID', 'First name', 'Surname', 'Student number', 'Email', 'Campus', 'Program', 'Year level', 'Grant type'],
            ['SCID-2026-0001', 'Juan', 'Dela Cruz', '2023-0001', 'juan@example.test', 'Main', 'BS Information Technology', '1', 'Full'],
            ['SCID-2026-0002', 'Ana', 'Reyes', '2023-0002', 'ana@example.test', 'Main', 'BS Information Technology', '2', 'half scholarship (half-ssp)'],
            ['SCID-2026-0003', 'No', 'Type', '2023-0003', 'notype@example.test', 'Main', 'BS Information Technology', '2', ''],
        ]))])->assertSessionHas('import_report', fn ($r) => $r['added'] === 2 && count($r['skipped']) === 1);

        $this->assertSame('40000.00', Scholar::where('full_name', 'Juan Dela Cruz')->value('stipend_amount'));
        $this->assertSame('half', Scholar::where('full_name', 'Ana Reyes')->value('grant_type'));
    }

    public function test_importing_the_same_file_twice_adds_nobody_twice(): void
    {
        $this->actingAs($this->provider('dost'));
        $rows = $this->withDetails([['Scholar No', 'Given name', 'Last name', 'Student ID', 'Email', 'Campus', 'Course', 'Year'], ['SCID-2026-0001', 'Juan', 'Dela Cruz', '2023-0001', 'juan@example.test', 'Samal', 'BS Information Technology', '1'], ['SCID-2026-0002', 'Ana', 'Reyes', '2023-0002', 'ana@example.test', 'Samal', 'BS Information Technology', '2']]);

        $this->post('/provider/scholars/import', ['scholarship_id' => $this->sid('dost-sei'), 'file' => $this->xlsxUpload($rows)]);
        $this->post('/provider/scholars/import', ['scholarship_id' => $this->sid('dost-sei'), 'file' => $this->xlsxUpload($rows)]);

        $this->assertSame(2, Scholar::count());
    }

    public function test_import_from_a_semicolon_csv_with_a_bom(): void
    {
        $this->actingAs($this->provider('grab'));
        $csv = "\xEF\xBB\xBFFirst name;Middle name;Surname;Suffix;Email;Student number;Program;Year level;Scholar ID;Contact number;Street address;Municipality;Province;Date of birth;Sex\n"
            ."Juan;Andres;Dela Cruz;jr;juan@example.test;2023-0001;bachelor of science in entrepreneurship;1;SCID-2026-0001;0917 123 4567;Purok 3, Gredu;Panabo City;Davao del Norte;03/14/2005;Male\n";
        $file = UploadedFile::fake()->createWithContent('roster.csv', $csv);

        $this->post('/provider/scholars/import', ['scholarship_id' => $this->sid('grabscholar'), 'file' => $file])->assertRedirect();

        $s = Scholar::firstOrFail();
        $this->assertSame('juan@example.test', $s->email);
        $this->assertSame('8000.00', $s->stipend_amount);
        $this->assertSame('Juan Andres Dela Cruz Jr.', $s->full_name);   // parts in separate columns; "jr" → "Jr."
        $this->assertSame(['Juan', 'Andres', 'Dela Cruz', 'Jr.'], [$s->first_name, $s->middle_name, $s->last_name, $s->suffix]);
        $this->assertSame('Juan Andres Dela Cruz Jr.', $s->user->name);
        $this->assertSame('Purok 3, Gredu, Panabo City, Davao del Norte', $s->address);   // the three parts, joined
        $this->assertSame('Davao del Norte', $s->address_province);
    }

    public function test_import_needs_the_required_columns_and_rejects_old_xls(): void
    {
        $this->actingAs($this->provider('ched'));

        $this->post('/provider/scholars/import', ['scholarship_id' => $this->sid('ched-tdp'), 'file' => $this->xlsxUpload([['Full name', 'Email'], ['A B', 'a@example.test']])])
            ->assertSessionHasErrors('file');
        $this->post('/provider/scholars/import', ['scholarship_id' => $this->sid('ched-tdp'), 'file' => UploadedFile::fake()->create('old.xls', 5)])
            ->assertSessionHasErrors('file');
        $this->post('/provider/scholars/import', ['scholarship_id' => $this->sid('ched-tdp'), 'file' => UploadedFile::fake()->createWithContent('fake.xlsx', 'not a zip')])
            ->assertSessionHasErrors('file');

        $this->assertSame(0, Scholar::count());
    }

    public function test_the_template_downloads_as_xlsx_and_reads_back(): void
    {
        $read = function (string $key): array {
            $this->actingAs($this->provider($key));
            $res = $this->get('/provider/scholars/template')->assertOk()
                ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            $path = tempnam(sys_get_temp_dir(), 'tpl');
            file_put_contents($path, $res->getContent());

            return Spreadsheet::read($path, 'xlsx')[0];
        };

        $base = ['Scholar ID', 'First name', 'Middle name', 'Surname', 'Suffix', 'Student number', 'Email', 'Mobile number', 'Street', 'City / Municipality', 'Province', 'Birthday', 'Gender', 'Campus', 'Program', 'Year level'];
        $this->assertSame($base, $read('dnsc'));                        // no types
        $this->assertSame([...$base, 'Grant type'], $read('ched'));     // CMSP Full / Half
    }

    // ---------- Roster + applicants ----------

    public function test_roster_scholars_work_on_the_operations_pages(): void
    {
        $s = Scholar::create(['full_name' => 'Roster Person', 'scholarship_id' => $this->sid('ched-tdp'), 'status' => 'active', 'stipend_amount' => 5000]);
        $s->requirements()->create(['term' => '1st Semester 2026-2027', 'name' => 'COR', 'status' => 'submitted']);
        $this->actingAs($this->provider('ched'));

        $this->post('/provider/disbursements/generate', ['term' => AcademicTerm::current(), 'release_type' => 'semester'])->assertRedirect();
        $this->get('/provider/disbursements')->assertOk()->assertSee('Roster Person');
        $this->get('/provider/requirements?term='.urlencode('1st Semester 2026-2027'))->assertOk();
        $this->get('/provider/dashboard')->assertOk();
        $this->assertSame(1, ScholarRequirement::count());
    }

    /** A scholar account (scholars have their own role since 2026-11-18). */
    private function student(string $email): User
    {
        $u = new User;
        $u->name = 'Juan Dela Cruz';
        $u->email = $email;
        $u->password = Hash::make('StudentPass123');
        $u->role = 'scholar';
        $u->status = 'active';
        $u->save();

        return $u;
    }
}
