<?php

namespace Tests\Feature;

use App\Models\ScholarshipApplication;
use App\Models\User;
use Database\Seeders\DnscCatalogSeeder;
use Database\Seeders\ScholarshipSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Version history of the matching form (2026-11-21): every changed submit is a new version, an unchanged
 * one is not; My profile lists the versions with what changed; an earlier version opens with the answers
 * that are different now marked; "Use these answers again" fills the form in with it (saved only on submit).
 * Only the student's own versions can be opened.
 */
class StudentVersionHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ScholarshipSeeder::class);
        $this->seed(DnscCatalogSeeder::class);
    }

    private function student(string $name = 'Juana Dela Cruz', string $email = 'juana@example.test'): User
    {
        $u = new User;
        $u->forceFill(['name' => $name, 'email' => $email, 'password' => 'secret-password', 'role' => 'student', 'status' => 'active'])->save();

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
            'special_groups' => ['none'], 'talent_categories' => ['none'], 'leadership_roles' => ['none'],
            'privacy_consent' => '1', 'certify_name' => 'Juana Dela Cruz',
        ], $extra);
    }

    public function test_only_changed_answers_make_a_new_version(): void
    {
        $u = $this->student();
        $this->actingAs($u);

        $this->post('/student/application', $this->answers())->assertSessionHasNoErrors();
        $this->post('/student/application', $this->answers())->assertSessionHas('status', 'You didn\'t change anything, so your results stay the same.');
        // Same choices in another order, a number written differently: still no change.
        $this->post('/student/application', $this->answers(['college_gwa' => '88.0']))->assertSessionHasNoErrors();
        $this->assertSame(1, ScholarshipApplication::where('user_id', $u->id)->count());

        $this->post('/student/application', $this->answers(['college_gwa' => '91', 'home_province' => 'davao_del_sur']))->assertSessionHasNoErrors();
        $versions = ScholarshipApplication::where('user_id', $u->id)->orderBy('version')->get();
        $this->assertSame([1, 2], $versions->pluck('version')->all());
        $this->assertSame([false, true], $versions->pluck('is_current')->all());
        $this->assertSame(91.0, (float) $versions[1]->answers['college_gwa']);
        $this->assertSame(88.0, (float) $versions[0]->answers['college_gwa']);   // the earlier version is kept as it was
    }

    public function test_profile_lists_versions_and_an_earlier_one_shows_what_is_different_now(): void
    {
        $u = $this->student();
        $this->actingAs($u);
        $this->post('/student/application', $this->answers());
        $this->post('/student/application', $this->answers(['college_gwa' => '91', 'home_province' => 'davao_del_sur']));

        $this->get('/student/profile')->assertOk()
            ->assertSee('Version 2 (current)')
            ->assertSee(route('student.profile.version', 1), false)
            ->assertSee('2 answers changed')
            ->assertSee('First form');

        $this->get(route('student.profile.version', 1))->assertOk()
            ->assertSee('Version <em>1</em> of your answers', false)
            ->assertSee('2 answers different from your current version 2')
            ->assertSee('Different now')
            ->assertSee('Now: 91%')
            ->assertSee(route('student.application.show', ['version' => 1]), false);

        // The current version is the profile itself; an unknown version is not found.
        $this->get(route('student.profile.version', 2))->assertRedirect(route('student.profile'));
        $this->get(route('student.profile.version', 9))->assertNotFound();
    }

    public function test_use_these_answers_again_fills_the_form_and_saves_only_on_submit(): void
    {
        $u = $this->student();
        $this->actingAs($u);
        $this->post('/student/application', $this->answers());
        $this->post('/student/application', $this->answers(['home_province' => 'davao_del_sur']));

        $this->get(route('student.application.show', ['version' => 1]))->assertOk()
            ->assertSee('Filled in with your answers from version 1');
        $this->assertSame(2, ScholarshipApplication::where('user_id', $u->id)->count());   // nothing saved yet

        // Submitting the old answers again makes version 3 (version 1 stays as it was).
        $this->post('/student/application', $this->answers())->assertSessionHasNoErrors();
        $current = ScholarshipApplication::where('user_id', $u->id)->where('is_current', true)->first();
        $this->assertSame(3, (int) $current->version);
        $this->assertSame('davao_del_norte', $current->answers['home_province']);
    }

    public function test_another_students_versions_cannot_be_opened_or_used(): void
    {
        $owner = $this->student();
        $this->actingAs($owner);
        $this->post('/student/application', $this->answers());
        $this->post('/student/application', $this->answers(['home_province' => 'davao_del_sur']));

        $other = $this->student('Pedro Santos', 'pedro@example.test');
        $this->actingAs($other);
        $this->post('/student/application', $this->answers(['certify_name' => 'Pedro Santos', 'home_province' => 'davao_oriental']));

        // Pedro has only version 1 (his current one): version 1 is his own profile, Juana's do not exist for him.
        $this->get(route('student.profile.version', 1))->assertRedirect(route('student.profile'));
        $this->get(route('student.profile.version', 2))->assertNotFound();
        $this->get(route('student.application.show', ['version' => 1]))->assertOk()->assertDontSee('Filled in with your answers from version');
    }
}
