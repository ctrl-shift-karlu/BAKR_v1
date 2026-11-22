<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\Program;
use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use App\Models\StudentConsent;
use App\Models\User;
use Database\Seeders\DnscCatalogSeeder;
use Database\Seeders\ScholarshipSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The student's matching form reads like a formal form: numbered parts and items, a reference
 * number, a certification signed by typing the account name (checked, never stored) and a
 * printable copy that lists documents only from the matched scholarships.
 */
class MatchingFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ScholarshipSeeder::class);
        $this->seed(DnscCatalogSeeder::class);   // campus 1 = Main; program 1 (BS Information Systems) belongs to it
    }

    private function student(string $name = 'Juana Dela Cruz', string $email = 'juana@example.test'): User
    {
        $u = new User;
        $u->name = $name;
        $u->email = $email;
        $u->password = 'secret-password';
        $u->role = 'student';
        $u->status = 'active';
        $u->save();

        return $u;
    }

    private function answers(array $extra = []): array
    {
        return array_merge([
            'birth_date' => '2005-04-12', 'home_province' => 'davao_del_norte',
            'citizenship' => 'filipino', 'enrollment_status' => 'currently_enrolled', 'year_level' => '2',
            'campus_id' => '1', 'program_id' => '1', 'previous_degree' => 'no', 'previous_units' => 'no',
            'within_years' => 'yes', 'college_gwa' => '88', 'college_gwa_scale' => 'percent',
            'failing_grade' => 'no', 'household_income' => 'up_to_300k',
            'existing_assistance' => ['none'], 'farm_fisher_family' => ['none'], 'willing_student_assistant' => 'yes', 'cebuana_client' => 'no', 'cebuana_microsavings' => 'no',
            // Every tick-box group is required since 2026-11-21 ("None of these" when nothing applies).
            'special_groups' => ['none'], 'talent_categories' => ['none'], 'leadership_roles' => ['none'],
            'privacy_consent' => '1',
        ], $extra);
    }

    public function test_form_has_formal_header_numbered_parts_and_certification(): void
    {
        $html = $this->actingAs($this->student())->get('/student/application')->assertOk()->getContent();

        $this->assertStringContainsString('Scholarship Matching Form', $html);
        foreach (['Family and Finances', 'Other Scholarships and Grants', 'Achievements and Activities', 'Provider-Specific Questions', 'type="date"', 'name="household_income"', 'name="shs_country"'] as $part) {
            $this->assertStringContainsString($part, $html);
        }
        // Only questions a scholarship rule reads (owner, 2026-11-19): no essays, contact details or family-detail questions.
        foreach (['Short Essays', 'essay_', 'name="mobile_number"', 'name="home_address"', 'name="sex"', 'name="civil_status"', 'name="household_size"', 'name="father_occupation"', 'name="shs_school_type"', '<textarea'] as $gone) {
            $this->assertStringNotContainsString($gone, $html);
        }
        $this->assertStringContainsString('Part VIII</span> Certification', $html);
        $this->assertStringContainsString('not the official application form of any scholarship provider', $html);
        $this->assertStringContainsString('Juana Dela Cruz', $html);
        $this->assertStringContainsString('Part I</small>Personal Information', $html);
        $this->assertStringContainsString('<span class="bf-num">1.1</span>', $html);
        $this->assertStringContainsString('name="certify_name"', $html);
        $this->assertStringContainsString('id="reviewDialog"', $html);
        $this->assertStringNotContainsString('bi-stars', $html);
    }

    public function test_submitting_without_the_signature_is_refused(): void
    {
        $this->actingAs($this->student())->from('/student/application')
            ->post('/student/application', $this->answers())
            ->assertSessionHasErrors('certify_name');

        $this->assertSame(0, ScholarshipApplication::count());
    }

    public function test_a_different_name_is_refused(): void
    {
        $this->actingAs($this->student())->from('/student/application')
            ->post('/student/application', $this->answers(['certify_name' => 'Someone Else']))
            ->assertSessionHasErrors('certify_name');

        $this->assertSame(0, ScholarshipApplication::count());
    }

    public function test_the_account_name_certifies_and_is_not_stored_in_the_answers(): void
    {
        $u = $this->student();

        $this->actingAs($u)->post('/student/application', $this->answers(['certify_name' => '  juana   dela CRUZ ']))
            ->assertSessionHasNoErrors()->assertRedirect(route('student.results'));

        $app = ScholarshipApplication::where('user_id', $u->id)->firstOrFail();
        $this->assertArrayNotHasKey('certify_name', $app->answers);
        $this->assertNotNull($app->submitted_at);
        $this->assertTrue(StudentConsent::where('user_id', $u->id)->exists());
        $this->assertMatchesRegularExpression('/^BAKR-\d{4}-\d{6}$/', $app->referenceNumber());
    }

    public function test_print_redirects_to_the_form_when_nothing_was_submitted(): void
    {
        $this->actingAs($this->student())->get('/student/application/print')
            ->assertRedirect(route('student.application.show'));
    }

    public function test_print_shows_only_the_signed_in_students_form_and_documents_from_the_table(): void
    {
        $juana = $this->student();
        $other = $this->student('Pedro Penduko', 'pedro@example.test');
        $this->actingAs($juana)->post('/student/application', $this->answers(['certify_name' => 'Juana Dela Cruz']))->assertSessionHasNoErrors();
        $this->actingAs($other)->post('/student/application', $this->answers(['certify_name' => 'Pedro Penduko', 'citizenship' => 'other']))->assertSessionHasNoErrors();

        $app = ScholarshipApplication::where('user_id', $juana->id)->firstOrFail();
        $html = $this->actingAs($juana)->get('/student/application/print')->assertOk()->getContent();

        $this->assertStringContainsString($app->referenceNumber(), $html);
        $this->assertStringContainsString('Juana Dela Cruz', $html);
        $this->assertStringNotContainsString('Pedro Penduko', $html);
        $this->assertStringContainsString('Part VIII</small> Certification', $html);

        // Every document shown comes from a listed match's scholarship row.
        $listed = $app->matches()->whereIn('status', ['eligible', 'possible', 'needs_info'])->pluck('scholarship_id');
        $this->assertNotEmpty($listed, 'fixture should match at least one scholarship');
        $doc = Scholarship::whereIn('id', $listed)->get()->pluck('documents')->flatten()->first();
        $this->assertStringContainsString(e($doc), $html);
    }

    public function test_the_application_fields_are_validated_and_removed_questions_are_never_stored(): void
    {
        $u = $this->student();

        $this->actingAs($u)->from('/student/application')
            ->post('/student/application', $this->answers(['certify_name' => 'Juana Dela Cruz', 'birth_date' => now()->subYears(5)->toDateString(), 'shs_country' => 'mars', 'home_province' => 'atlantis']))
            ->assertSessionHasErrors(['birth_date', 'shs_country', 'home_province']);
        $this->assertSame(0, ScholarshipApplication::count());

        // A browser (or an old form) that still sends the removed questions: they are dropped, not stored.
        $this->actingAs($u)->post('/student/application', $this->answers([
            'certify_name' => 'Juana Dela Cruz',
            'mobile_number' => '09171234567', 'home_address' => 'Purok 3', 'sex' => 'female', 'essay_need' => 'x', 'household_size' => '6',
        ]))->assertSessionHasNoErrors();

        $answers = ScholarshipApplication::where('user_id', $u->id)->firstOrFail()->answers;
        foreach (['mobile_number', 'home_address', 'sex', 'essay_need', 'household_size'] as $gone) {
            $this->assertArrayNotHasKey($gone, $answers);
        }
        $this->assertSame('davao_del_norte', $answers['home_province']);
    }

    public function test_a_program_must_be_offered_at_the_chosen_campus(): void
    {
        // Talaingod offers only Agro-Forestry (an example list: the real offerings come from config/dnsc_campuses.php).
        $campuses = config('dnsc_campuses.campuses');
        $campuses['Talaingod Campus']['programs'] = ['Bachelor of Science in Agro-Forestry'];
        config(['dnsc_campuses.campuses' => $campuses]);
        $this->seed(DnscCatalogSeeder::class);

        $talaingod = Campus::where('campus_name', 'Talaingod Campus')->firstOrFail();
        $agro = Program::where('campus_id', $talaingod->id)->firstOrFail();
        $mainIt = Program::where('program_name', 'Bachelor of Science in Information Technology')->firstOrFail();
        $u = $this->student();

        // The form lists only that campus' programs for it (the script filters by data-campus).
        $html = $this->actingAs($u)->get('/student/application')->assertOk()->getContent();
        $this->assertSame(1, substr_count($html, 'data-campus="'.$talaingod->id.'"'));

        // A hand-made request cannot pair the campus with a program it does not offer.
        $this->actingAs($u)->from('/student/application')
            ->post('/student/application', $this->answers(['certify_name' => 'Juana Dela Cruz', 'campus_id' => (string) $talaingod->id, 'program_id' => (string) $mainIt->id]))
            ->assertSessionHasErrors('program_id');
        $this->assertSame(0, ScholarshipApplication::count());

        $this->actingAs($u)->post('/student/application', $this->answers(['certify_name' => 'Juana Dela Cruz', 'campus_id' => (string) $talaingod->id, 'program_id' => (string) $agro->id]))
            ->assertSessionHasNoErrors();
        $this->assertSame('Bachelor of Science in Agro-Forestry', ScholarshipApplication::firstOrFail()->answers['program_name']);
    }

    /** 2026-11-21: every question is answered; tick-box groups offer "None of these"; awards only after a real skill. */
    public function test_every_question_must_be_answered_and_none_of_these_is_exclusive(): void
    {
        $u = $this->student();
        $sign = ['certify_name' => 'Juana Dela Cruz'];

        $this->actingAs($u)->from('/student/application')
            ->post('/student/application', array_diff_key($this->answers($sign), array_flip(['special_groups', 'talent_categories', 'leadership_roles', 'college_gwa_scale'])))
            ->assertSessionHasErrors(['special_groups', 'talent_categories', 'leadership_roles', 'college_gwa_scale']);

        // A real skill → "Do you have awards in that skill?" must be answered too.
        $this->post('/student/application', $this->answers($sign + ['talent_categories' => ['music']]))->assertSessionHasErrors('has_awards');
        $this->assertSame(0, ScholarshipApplication::count());

        // "None of these" ticked with a real choice keeps the real choice; with only "None", awards are not stored.
        $this->post('/student/application', $this->answers($sign + ['special_groups' => ['none', 'pwd'], 'has_awards' => 'yes']))->assertSessionHasNoErrors();
        $answers = ScholarshipApplication::firstOrFail()->answers;
        $this->assertSame(['pwd'], $answers['special_groups']);
        $this->assertSame(['none'], $answers['talent_categories']);
        $this->assertArrayNotHasKey('has_awards', $answers);
    }

    public function test_unfamiliar_questions_have_an_info_note_and_obvious_ones_do_not(): void
    {
        $html = $this->actingAs($this->student())->get('/student/application')->assertOk()->getContent();

        foreach (['farm_fisher_family', 'special_groups', 'previous_units', 'college_gwa_scale', 'dost_exam'] as $key) {
            $this->assertStringContainsString('aria-controls="info-'.$key.'"', $html);
            $this->assertStringContainsString('id="info-'.$key.'"', $html);
        }
        $this->assertStringContainsString('Registry System for Basic Sectors in Agriculture', $html);
        foreach (['citizenship', 'year_level', 'campus_id', 'birth_date'] as $obvious) {
            $this->assertStringNotContainsString('info-'.$obvious.'"', $html);
        }
    }

    public function test_the_privacy_policy_is_agreed_to_on_every_submit(): void
    {
        // The public policy page, linked from the footer and the sign-in pages (as a guest).
        $this->get('/privacy')->assertOk()->assertSee('Data Privacy Act of 2012')->assertSee('National Privacy Commission')->assertSee('de-identified');
        $this->get('/studentLogin')->assertSee(route('privacy'), false);
        $this->get('/about')->assertSee(route('privacy'), false);

        $u = $this->student();
        $sign = ['certify_name' => 'Juana Dela Cruz'];

        $this->actingAs($u)->post('/student/application', $this->answers($sign))->assertSessionHasNoErrors();
        // Already agreed once: still asked again, every time.
        $html = $this->get('/student/application')->getContent();
        $this->assertStringContainsString('name="privacy_consent"', $html);
        $this->assertStringContainsString(route('privacy'), $html);
        $this->assertStringContainsString('Republic Act No. 10173', $html);
        $this->from('/student/application')->post('/student/application', array_diff_key($this->answers($sign), ['privacy_consent' => 1]))
            ->assertSessionHasErrors('privacy_consent');
    }

    public function test_guests_cannot_open_the_printable_form(): void
    {
        $this->get('/student/application/print')->assertRedirect('/studentLogin');
    }
}
