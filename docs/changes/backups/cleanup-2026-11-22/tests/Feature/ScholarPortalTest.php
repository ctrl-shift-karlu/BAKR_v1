<?php

namespace Tests\Feature;

use App\Models\Scholar;
use App\Models\Scholarship;
use App\Models\User;
use App\Support\AcademicTerm;
use Database\Seeders\ScholarshipSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Scholar portal: SCHOLAR accounts (role "scholar", /scholarLogin), separate from student accounts
 * (matching). Overview, My scholarship, Requirements, Explore (collapsible), Profile and Settings
 * (after the scholar prototype). No matching.
 */
class ScholarPortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->seed(ScholarshipSeeder::class);
    }

    private function user(string $email = 'ana@example.test', string $password = 'AnaPass123', string $role = 'scholar'): User
    {
        $u = new User;
        $u->forceFill(['name' => 'Ana Reyes', 'email' => $email, 'password' => Hash::make($password), 'role' => $role, 'status' => 'active'])->save();

        return $u;
    }

    private function scholarship(string $slug, array $renewals = []): Scholarship
    {
        $s = Scholarship::where('slug', $slug)->firstOrFail();
        if ($renewals) {
            $s->update(['renewal_requirements' => $renewals]);
        }

        return $s;
    }

    private function scholar(User $u, string $slug, array $attrs = []): Scholar
    {
        return Scholar::create($attrs + ['user_id' => $u->id, 'scholarship_id' => $this->scholarship($slug)->id, 'status' => 'active', 'started_term' => AcademicTerm::current()]);
    }

    public function test_a_scholar_signs_in_at_the_scholar_login_to_the_scholar_portal(): void
    {
        $u = $this->user();
        $this->scholar($u, 'ched-tdp', ['grant_type' => 'regular', 'stipend_amount' => 7500]);

        $this->get('/scholarLogin')->assertOk()->assertSee('Scholar login')->assertSee('action="'.route('scholar.login').'"', false)
            ->assertDontSee('bakr-staff-login.js');   // real sign-in, not the old prototype script
        $this->post('/scholarLogin', ['email' => 'ana@example.test', 'password' => 'AnaPass123'])->assertRedirect(route('scholar.overview'));

        $html = $this->get('/scholar')->assertOk()->getContent();
        foreach (['Overview', 'My scholarship', 'Requirements', 'Explore', 'All scholarships', 'Help &amp; contact', 'Profile', 'Settings', 'Scholar Portal'] as $text) {
            $this->assertStringContainsString($text, $html);
        }
        foreach (['My matches', 'My matching form', 'Student Portal', route('student.results'), route('student.application.show'), route('student.help')] as $gone) {
            $this->assertStringNotContainsString($gone, $html);
        }
        $this->assertStringContainsString('class="dash-nav-group"', $html);   // Explore is collapsible
        $this->assertStringNotContainsString('class="is-danger" data-open-dialog="confirm-signout"', $html);   // no sidebar sign-out: it lives in Settings
        $this->assertMatchesRegularExpression('/<dialog[^>]*id="confirm-signout".*?action="'.preg_quote(route('scholar.logout'), '/').'"/s', $html);
        $this->assertStringContainsString('data-dash-burger', $html);   // mobile burger
    }

    public function test_a_scholar_account_cannot_use_the_student_login_or_matching(): void
    {
        $u = $this->user();
        $this->scholar($u, 'ched-tdp');

        $this->post('/studentLogin', ['email' => 'ana@example.test', 'password' => 'AnaPass123'])
            ->assertSessionHasErrors(['email' => 'These credentials do not match a student account. Scholars sign in at the Scholar login.']);
        $this->assertGuest();

        $this->actingAs($u);
        foreach (['/student/dashboard', '/student/results', '/student/application', '/student/profile', '/student/help'] as $page) {
            $this->get($page)->assertForbidden()->assertSee('Go to my scholar portal');
        }
        $this->post('/student/application', [])->assertForbidden();
        $this->get('/find-my-matches')->assertRedirect(route('scholar.overview'));
        $this->get('/studentLogin')->assertRedirect(route('scholar.overview'));   // already signed in
        $this->get('/scholarDashboardF')->assertRedirect('/scholar');           // old prototype address

        // Explore pages are the scholar's own, in the scholar layout.
        $this->get('/scholar/scholarships')->assertOk()->assertSee('Scholar Portal')->assertDontSee('See my result');
        $this->get('/scholar/help')->assertOk()->assertSee('Questions scholars ask')->assertDontSee('My matching form');
    }

    public function test_a_student_account_keeps_matching_and_cannot_open_the_scholar_portal(): void
    {
        $student = $this->user('stu@example.test', 'StuPass123', 'student');

        $this->post('/scholarLogin', ['email' => 'stu@example.test', 'password' => 'StuPass123'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->post('/studentLogin', ['email' => 'stu@example.test', 'password' => 'StuPass123'])->assertRedirect(route('student.dashboard'));

        foreach (['/scholar', '/scholar/scholarship', '/scholar/requirements', '/scholar/profile', '/scholar/settings', '/scholar/help'] as $page) {
            $this->get($page)->assertForbidden();
        }
        // A student starts with matching (dashboard → questionnaire), in the student layout.
        $this->get('/student/dashboard')->assertRedirect(route('student.application.show'));
        $this->get('/student/application')->assertOk()->assertSee('My matches')->assertSee('Student Portal')->assertDontSee('Scholar Portal');
        $this->assertSame('student', $student->fresh()->role);
    }

    public function test_overview_lists_what_needs_doing(): void
    {
        $u = $this->user();
        $sc = $this->scholar($u, 'ched-tdp');
        $term = AcademicTerm::current();
        $sc->requirements()->create(['term' => $term, 'name' => 'Certificate of Registration', 'status' => 'missing']);
        $sc->requirements()->create(['term' => $term, 'name' => 'Grades', 'status' => 'needs_revision', 'review_note' => 'Blurry scan']);
        $sc->requirements()->create(['term' => $term, 'name' => 'ID', 'status' => 'approved']);
        $sc->disbursements()->create(['term' => $term, 'amount' => 7500, 'status' => 'pending']);
        $this->actingAs($u);

        $this->get('/scholar')->assertOk()
            ->assertSee('Actions needed')
            ->assertSee('Upload: Certificate of Registration')
            ->assertSee('Revise and re-upload: Grades')
            ->assertSee('Blurry scan')
            ->assertSee('stipend is waiting for your requirements')
            ->assertSee('1/3');   // approved this semester

        $sc->requirements()->update(['status' => 'approved']);
        $this->get('/scholar')->assertSee('all set')->assertDontSee('stipend is waiting for your requirements');
    }

    public function test_a_stipend_held_by_the_provider_side_is_not_blamed_on_the_scholar(): void
    {
        $u = $this->user();
        $this->scholarship('ched-tdp', ['Certificate of Registration (COR)']);
        $sc = $this->scholar($u, 'ched-tdp', ['grant_type' => 'regular', 'stipend_amount' => 7500]);
        $sc->disbursements()->create(['term' => AcademicTerm::current(), 'release_type' => 'semester', 'period_month' => '', 'amount' => 7500, 'status' => 'pending']);
        $this->actingAs($u);

        // No deadline yet: there is nothing to upload, so it is the provider's turn.
        $this->get('/scholar')->assertOk()
            ->assertSee('stipend is waiting for your provider')
            ->assertDontSee('stipend is waiting for your requirements');
        $this->get('/scholar/scholarship')->assertOk()
            ->assertSee('Waiting for CHED / UniFAST to open your '.AcademicTerm::current().' maintaining documents')
            ->assertDontSee('the deadline for the');   // the provider-facing reason is not shown to the scholar
    }

    public function test_my_scholarship_shows_info_benefits_documents_and_stipends(): void
    {
        $u = $this->user();
        $this->scholarship('ched-tdp', ['Certificate of Registration (COR)', 'Grades for the previous semester']);
        $sc = $this->scholar($u, 'ched-tdp', ['grant_type' => 'regular', 'stipend_amount' => 7500, 'student_number' => '2023-0001', 'program' => 'Bachelor of Science in Information Technology', 'year_level' => '2']);
        $sc->disbursements()->create(['term' => AcademicTerm::previous(), 'release_type' => 'semester', 'period_month' => '', 'amount' => 7500, 'status' => 'released', 'released_at' => now(), 'reference' => 'REF-1']);
        $sc->disbursements()->create(['term' => AcademicTerm::current(), 'release_type' => 'semester', 'period_month' => '', 'amount' => 7500, 'status' => 'pending']);
        $this->actingAs($u);

        $this->get('/scholar/scholarship')->assertOk()
            ->assertSee('Scholarship information')->assertSee('2023-0001')->assertSee('2nd year')
            ->assertSee('Benefits')->assertSee('₱7,500 per semester')                 // from the published benefits
            ->assertSee('Documents to keep your scholarship')->assertSee('Grades for the previous semester')
            ->assertSee('Stipends')->assertSee('REF-1')->assertSee('Every semester')
            ->assertSee('only after')                                                   // the release rule
            ->assertSee('Waiting for CHED / UniFAST to open your');                     // no deadline yet: the provider's turn
    }

    public function test_my_scholarship_never_shows_another_persons_record(): void
    {
        $other = $this->scholar($this->user('other@example.test'), 'dost-sei', ['student_number' => 'SECRET-99']);
        $u = $this->user();
        $this->scholar($u, 'ched-tdp');
        $this->actingAs($u);

        $this->get('/scholar/scholarship?s='.$other->id)->assertOk()->assertDontSee('SECRET-99')->assertSee('CHED Tulong Dunong Program');
    }

    public function test_a_scholar_account_without_a_scholarship_gets_empty_states_not_errors(): void
    {
        $this->actingAs($this->user());

        $this->get('/scholar')->assertOk()->assertSee('No scholarship is linked to your account yet');
        $this->get('/scholar/scholarship')->assertRedirect(route('scholar.overview'));
        $this->get('/scholar/requirements')->assertOk();
        $this->get('/scholar/profile')->assertOk()->assertSee('No scholarship is linked to your account yet');
        $this->get('/scholar/settings')->assertOk();
    }

    public function test_requirements_show_each_scholarships_own_documents_and_uploads_go_to_that_provider(): void
    {
        $u = $this->user();
        $this->scholarship('ched-tdp', ['TDP grades form']);
        $this->scholarship('dost-sei', ['DOST scholar report']);
        $tdp = $this->scholar($u, 'ched-tdp');
        $dost = $this->scholar($u, 'dost-sei');
        $r = $dost->requirements()->create(['term' => AcademicTerm::current(), 'name' => 'DOST scholar report', 'status' => 'missing']);
        $this->actingAs($u);

        $html = $this->get('/scholar/requirements')->assertOk()->getContent();
        $this->assertStringContainsString('id="sch-'.$tdp->id.'"', $html);
        $this->assertStringContainsString('TDP grades form', $html);                   // TDP: its own list (checklist not opened yet)
        $this->assertStringContainsString('has not opened the', $html);
        $this->assertStringContainsString('Goes to DOST', $html);                       // DOST: its own provider
        $this->assertStringContainsString('data-upload-url="'.route('scholar.requirements.upload', $r->id).'"', $html);   // upload dialog
        $this->assertStringContainsString('action="'.route('scholar.requirements.upload', $r->id).'"', $html);            // no-JS fallback form
        $this->assertStringContainsString('id="sc-upload"', $html);
        $this->assertStringContainsString('js/bakr-scholar-requirements.js', $html);

        $this->post(route('scholar.requirements.upload', $r->id), ['file' => UploadedFile::fake()->create('report.pdf', 50, 'application/pdf')])->assertRedirect();
        $this->assertSame('submitted', $r->fresh()->status);
        $this->assertSame($dost->id, $r->fresh()->scholar_id);   // received by DOST (their review queue reads scholar → scholarship)
    }

    public function test_the_upload_dialog_submits_and_the_provider_receives_it(): void
    {
        $u = $this->user();
        $sc = $this->scholar($u, 'ched-tdp');
        $r = $sc->requirements()->create(['term' => AcademicTerm::current(), 'name' => 'Grades', 'status' => 'needs_revision', 'review_note' => 'Blurry']);
        $this->actingAs($u);

        // The dialog posts with Accept: application/json and gets JSON back (then reloads the page).
        $this->postJson(route('scholar.requirements.upload', $r->id), ['file' => UploadedFile::fake()->create('grades.pdf', 200, 'application/pdf')])
            ->assertOk()->assertJson(['id' => $r->id, 'status' => 'submitted'])
            ->assertJsonPath('message', fn ($m) => str_contains($m, 'CHED / UniFAST'));
        $this->assertSame('submitted', $r->fresh()->status);
        $this->assertNull($r->fresh()->review_note);   // the old note is cleared with the new file

        // The success message waits for the reload, and the row is now "Waiting for review".
        $this->get('/scholar/requirements')->assertSee('was sent to CHED / UniFAST')->assertSee('Waiting for review');

        // The provider sees it in their review queue.
        $provider = new User;
        $provider->forceFill(['name' => 'CHED', 'email' => 'ched@provider.test', 'password' => Hash::make('x'), 'role' => 'provider', 'status' => 'active', 'provider_key' => 'ched'])->save();
        $this->actingAs($provider)->get('/provider/requirements')->assertOk()->assertSee('grades.pdf');
    }

    public function test_the_upload_dialog_gets_readable_errors(): void
    {
        $u = $this->user();
        $sc = $this->scholar($u, 'ched-tdp');
        $r = $sc->requirements()->create(['term' => AcademicTerm::current(), 'name' => 'COR', 'status' => 'missing']);
        $this->actingAs($u);

        $this->postJson(route('scholar.requirements.upload', $r->id), ['file' => UploadedFile::fake()->create('cor.docx', 10)])
            ->assertStatus(422)->assertJsonPath('errors.file.0', 'Upload a PDF, JPG or PNG file.');
        $this->postJson(route('scholar.requirements.upload', $r->id), ['file' => UploadedFile::fake()->create('cor.pdf', 6000, 'application/pdf')])
            ->assertStatus(422)->assertJsonPath('errors.file.0', 'The file must be 5 MB or smaller.');
        $this->assertSame('missing', $r->fresh()->status);

        $r->update(['status' => 'approved']);
        $this->postJson(route('scholar.requirements.upload', $r->id), ['file' => UploadedFile::fake()->create('cor.pdf', 10, 'application/pdf')])->assertStatus(409);
    }

    public function test_without_javascript_the_form_posts_and_returns_to_the_document(): void
    {
        $u = $this->user();
        $sc = $this->scholar($u, 'ched-tdp');
        $r = $sc->requirements()->create(['term' => AcademicTerm::current(), 'name' => 'COR', 'status' => 'missing']);
        $this->actingAs($u);

        $this->from('/scholar/requirements')->post(route('scholar.requirements.upload', $r->id), ['file' => UploadedFile::fake()->create('cor.pdf', 50, 'application/pdf')])
            ->assertRedirect(url('/scholar/requirements').'#req-'.$r->id);
        $this->assertSame('submitted', $r->fresh()->status);
    }

    public function test_preview_shows_pdf_and_images_inline_and_safely(): void
    {
        $u = $this->user();
        $sc = $this->scholar($u, 'ched-tdp');
        $r = $sc->requirements()->create(['term' => AcademicTerm::current(), 'name' => 'COR', 'status' => 'missing']);
        $this->actingAs($u)->postJson(route('scholar.requirements.upload', $r->id), ['file' => UploadedFile::fake()->create('cor.pdf', 20, 'application/pdf')]);
        $r->refresh()->update(['mime' => 'application/pdf']);   // fake files have no real PDF bytes to sniff

        $this->get('/scholar/requirements')->assertSee('data-preview-url="'.route('scholar.requirements.file', $r->id).'?inline=1"', false);

        $inline = $this->get(route('scholar.requirements.file', $r->id).'?inline=1')->assertOk();
        $this->assertSame('application/pdf', $inline->headers->get('Content-Type'));
        $this->assertSame('nosniff', $inline->headers->get('X-Content-Type-Options'));
        $this->assertStringStartsWith('inline', (string) $inline->headers->get('Content-Disposition'));

        $this->get(route('scholar.requirements.file', $r->id))->assertDownload('cor.pdf');

        // Someone else's file stays 404, inline or not.
        $this->actingAs($this->user('other@example.test'))->get(route('scholar.requirements.file', $r->id).'?inline=1')->assertNotFound();
    }

    public function test_a_dnsc_scholar_sees_the_list_but_submits_directly(): void
    {
        $u = $this->user();
        $this->scholarship('dnsc-academic', ['Grades with GWA']);
        $this->scholar($u, 'dnsc-academic');
        $this->actingAs($u);

        $this->get('/scholar/requirements')->assertOk()->assertSee('Grades with GWA')->assertSee('in person at the official DNSC scholarship office')->assertDontSee('name="file"', false);
    }

    public function test_profile_shows_the_scholar_details_grouped_like_the_prototype(): void
    {
        $u = $this->user();
        $this->scholar($u, 'ched-tdp', ['grant_type' => 'regular', 'student_number' => '2023-0042', 'program' => 'Bachelor of Science in Information Technology', 'year_level' => '3',
            'scholar_code' => 'SCID-2026-0042', 'birthdate' => '2004-07-01', 'gender' => 'female', 'mobile' => '09181234567', 'address' => 'Tagum City']);
        $this->actingAs($u);

        $this->get('/scholar/profile')->assertOk()
            ->assertSee('Ana Reyes')->assertSee('Student no.: 2023-0042')->assertSee('3rd year')
            ->assertSee('Personal information')->assertSee('Contact information')->assertSee('School information')->assertSee('Scholarship information')
            ->assertSee('ana@example.test')->assertSee('Bachelor of Science in Information Technology')->assertSee('CHED Tulong Dunong Program')
            ->assertSee('Request a correction')
            // Recorded by the provider (2026-11-20): birthday, gender, mobile, address, Scholar ID. Read-only here.
            ->assertSee('Jul 1, 2004')->assertSee('Female')->assertSee('0918 123 4567')->assertSee('Tagum City')->assertSee('SCID-2026-0042')
            ->assertDontSee('name="address"', false)
            ->assertDontSee('name="current_password"', false);   // the password lives in Settings
    }

    public function test_settings_changes_the_password(): void
    {
        $u = $this->user();
        $this->scholar($u, 'ched-tdp');
        $this->actingAs($u)->get('/scholar/settings')->assertOk()
            ->assertSee('Edit password')->assertSee('Change password')->assertSee('Forgot your password?')
            ->assertSee('action="'.route('scholar.password.change.update').'"', false)
            ->assertSee('class="dash-btn is-danger" href="#confirm-signout"', false)->assertSee('Sign out of BAKR?');

        $this->from('/scholar/settings')->put('/scholar/password', ['current_password' => 'wrong', 'password' => 'NewPass123', 'password_confirmation' => 'NewPass123'])
            ->assertRedirect('/scholar/settings');
        // After an error the password form is open again, with the message.
        $html = $this->get('/scholar/settings')->assertSee('This is not your current password.')->getContent();
        $this->assertMatchesRegularExpression('/<details class="sc-collapse"\s+open\b/', $html);
        $this->assertTrue(Hash::check('AnaPass123', $u->fresh()->password));

        $this->from('/scholar/settings')->put('/scholar/password', ['current_password' => 'AnaPass123', 'password' => 'NewPass123', 'password_confirmation' => 'NewPass123'])
            ->assertRedirect('/scholar/settings')->assertSessionHas('status');

        $this->assertTrue(Hash::check('NewPass123', $u->fresh()->password));
    }

    public function test_settings_emails_a_working_reset_link(): void
    {
        Notification::fake();
        $u = $this->user();
        $this->scholar($u, 'ched-tdp');
        $this->actingAs($u);

        $this->from('/scholar/settings')->post('/scholar/settings/reset-link')->assertRedirect('/scholar/settings')
            ->assertSessionHas('sent', fn ($s) => $s['title'] === 'Reset link sent' && str_contains($s['text'], 'ana@example.test'));
        $this->get('/scholar/settings')->assertSee('id="sent-dialog"', false)->assertSee('Reset link sent');

        $token = null;
        Notification::assertSentTo($u, ResetPassword::class, function ($n) use (&$token) {
            $token = $n->token;

            return true;
        });

        // The e-mailed link works even while signed in, then signs the person out, back to the Scholar login.
        $this->get('/student/reset-password/'.$token.'?email=ana@example.test')->assertOk();
        $this->post('/student/reset-password', ['token' => $token, 'email' => 'ana@example.test', 'password' => 'Reset1234', 'password_confirmation' => 'Reset1234'])
            ->assertRedirect(route('scholar.login'));
        $this->assertGuest();
        $this->assertTrue(Hash::check('Reset1234', $u->fresh()->password));
        $this->post('/scholarLogin', ['email' => 'ana@example.test', 'password' => 'Reset1234'])->assertRedirect(route('scholar.overview'));
    }

    public function test_forgot_password_from_the_scholar_login(): void
    {
        Notification::fake();
        $u = $this->user();

        $this->get('/scholarLogin')->assertSee(route('student.password.request', ['from' => 'scholar']), false);
        $this->get('/student/forgot-password?from=scholar')->assertOk()->assertSee(route('scholar.login'), false);
        $this->post('/student/forgot-password', ['email' => 'ana@example.test'])->assertSessionHas('status');
        Notification::assertSentTo($u, ResetPassword::class);
    }

    public function test_a_reset_link_also_clears_a_temporary_password(): void
    {
        $u = $this->user();
        $u->forceFill(['must_change_password' => true])->save();
        $token = Password::broker()->createToken($u);

        $this->post('/student/reset-password', ['token' => $token, 'email' => 'ana@example.test', 'password' => 'Reset1234', 'password_confirmation' => 'Reset1234'])
            ->assertRedirect(route('scholar.login'));

        $this->assertFalse($u->fresh()->must_change_password);
    }

    public function test_sign_out_from_the_confirmation_dialog(): void
    {
        $u = $this->user();
        $this->scholar($u, 'ched-tdp');
        $html = $this->actingAs($u)->get('/scholar/settings')->getContent();

        $this->assertMatchesRegularExpression('/<dialog[^>]*id="confirm-signout".*?action="'.preg_quote(route('scholar.logout'), '/').'"/s', $html);
        $this->assertStringContainsString('data-open-dialog="confirm-signout"', $html);

        $this->post('/scholar/logout')->assertRedirect('/');
        $this->assertGuest();
    }
}
