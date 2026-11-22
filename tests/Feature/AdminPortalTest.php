<?php

namespace Tests\Feature;

use App\Mail\ContactMessageReply;
use App\Models\ContactMessage;
use App\Models\Scholar;
use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use App\Models\ScholarshipMatch;
use App\Models\User;
use App\Support\AcademicTerm;
use Database\Seeders\AdminAccountSeeder;
use Database\Seeders\ScholarshipSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/** Admin portal: real sign-in, overview, analytics (totals only), accounts, scholarships, messages. */
class AdminPortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['bakr.admin.email' => 'admin@bakr.test', 'bakr.admin.password' => 'Admin-Test-Pass-2026']);
        $this->seed(ScholarshipSeeder::class);
        $this->seed(AdminAccountSeeder::class);
    }

    private function admin(): User
    {
        return User::where('email', 'admin@bakr.test')->firstOrFail();
    }

    private function account(string $email, string $role): User
    {
        $u = new User;
        $u->forceFill(['name' => ucfirst($role).' Person', 'email' => $email, 'password' => Hash::make('Pass12345'), 'role' => $role, 'status' => 'active'])->save();

        return $u;
    }

    public function test_the_admin_signs_in_for_real_and_other_roles_cannot(): void
    {
        $this->assertSame('admin', $this->admin()->role);

        $this->get('/adminLogin')->assertOk()->assertSee('action="'.route('admin.login').'"', false);
        $this->post('/adminLogin', ['email' => 'admin@bakr.test', 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->post('/adminLogin', ['email' => 'admin@bakr.test', 'password' => 'Admin-Test-Pass-2026'])->assertRedirect(route('admin.overview'));
        $this->post('/admin/logout')->assertRedirect(route('admin.login'));
        $this->assertGuest();

        // Guests go to the admin login; other roles get 403; old prototype address opens the portal.
        $this->get('/admin')->assertRedirect('/adminLogin');
        $this->get('/adminDashboardF')->assertRedirect('/admin');
        $this->account('stu@example.test', 'student');
        $this->post('/adminLogin', ['email' => 'stu@example.test', 'password' => 'Pass12345'])->assertSessionHasErrors('email');
        $this->actingAs(User::where('email', 'stu@example.test')->first());
        foreach (['/admin', '/admin/analytics', '/admin/accounts', '/admin/scholarships', '/admin/messages'] as $page) {
            $this->get($page)->assertForbidden();
        }
    }

    public function test_every_admin_page_loads(): void
    {
        $this->actingAs($this->admin());

        foreach (['/admin' => 'Needs attention', '/admin/analytics' => 'Why students don', '/admin/accounts' => 'Students', '/admin/scholarships' => 'Last checked', '/admin/messages' => 'Open'] as $page => $text) {
            $this->get($page)->assertOk()->assertSee($text)->assertSee('data-dash-burger', false)->assertSee('id="confirm-signout"', false);
        }
    }

    public function test_analytics_show_totals_but_never_a_students_answers(): void
    {
        $student = $this->account('stu@example.test', 'student');
        $app = ScholarshipApplication::create(['user_id' => $student->id, 'version' => 1, 'is_current' => true, 'answers' => ['income' => 'SECRET-INCOME'], 'submitted_at' => now()]);
        $tdp = Scholarship::where('slug', 'ched-tdp')->firstOrFail();
        ScholarshipMatch::create(['scholarship_application_id' => $app->id, 'scholarship_id' => $tdp->id, 'status' => 'not_eligible', 'score' => 10,
            'checks' => [['key' => 'income', 'label' => 'Family income limit', 'result' => 'fail', 'required' => true]]]);

        $scholar = $this->account('sch@example.test', 'scholar');
        $s = Scholar::create(['user_id' => $scholar->id, 'scholarship_id' => $tdp->id, 'status' => 'active']);
        $s->requirements()->create(['term' => AcademicTerm::current(), 'name' => 'COR', 'status' => 'approved']);

        $html = $this->actingAs($this->admin())->get('/admin/analytics')->assertOk()
            ->assertSee('Answered the form')
            ->assertSee('Family income limit')          // the usual barrier, as a count
            ->assertSee('1 of 1')                        // CHED documents approved this semester
            ->assertDontSee('SECRET-INCOME')
            ->assertDontSee('stu@example.test')
            ->getContent();

        // Three table + chart pairs, all server-rendered (no chart library).
        $this->assertStringContainsString('class="dash-donut"', $html);
        $this->assertStringContainsString('class="viz-stack is-absolute"', $html);
        $this->assertStringNotContainsString('Scholars per scholarship</caption>', $html);   // the scholars table was removed: chart only
        $this->assertStringContainsString('class="viz-line"', $html);
        $this->assertStringContainsString('class="dash-table an-table"', $html);
        $this->assertStringContainsString('css/bakr-admin.css', $html);
    }

    public function test_matching_pair_shows_donut_and_most_and_least_matched(): void
    {
        $tdp = Scholarship::where('slug', 'ched-tdp')->firstOrFail();
        $tes = Scholarship::where('slug', 'ched-tes')->firstOrFail();
        foreach ([1, 2] as $i) {
            $app = ScholarshipApplication::create(['user_id' => $this->account("s{$i}@example.test", 'student')->id, 'version' => 1, 'is_current' => true, 'answers' => [], 'submitted_at' => now()]);
            ScholarshipMatch::create(['scholarship_application_id' => $app->id, 'scholarship_id' => $tdp->id, 'status' => 'eligible', 'score' => 90, 'checks' => []]);
            ScholarshipMatch::create(['scholarship_application_id' => $app->id, 'scholarship_id' => $tes->id, 'status' => $i === 1 ? 'eligible' : 'not_eligible', 'score' => 50, 'checks' => []]);
        }

        $this->actingAs($this->admin())->get('/admin/analytics')->assertOk()
            ->assertSee('Most matched')->assertSee('Least matched')
            ->assertSee('2 students eligible')   // TDP
            ->assertSee('1 student eligible');   // TES (fewest among scholarships that have answers)
    }

    public function test_scholar_filter_switches_between_scholarship_and_campus(): void
    {
        $tdp = Scholarship::where('slug', 'ched-tdp')->firstOrFail();
        $a = $this->account('a@example.test', 'scholar');
        $b = $this->account('b@example.test', 'scholar');
        Scholar::create(['user_id' => $a->id, 'scholarship_id' => $tdp->id, 'status' => 'active', 'program' => 'Bachelor of Science in Agro-Forestry']);
        Scholar::create(['user_id' => $b->id, 'scholarship_id' => $tdp->id, 'status' => 'terminated', 'program' => 'Bachelor of Science in Information Technology']);
        $c = $this->account('c@example.test', 'scholar');
        Scholar::create(['user_id' => $c->id, 'scholarship_id' => $tdp->id, 'status' => 'active', 'program' => 'Bachelor of Science in Information Technology', 'campus' => 'Samal Campus']);

        $this->actingAs($this->admin());
        $this->get('/admin/analytics')->assertOk()->assertSee('Scholars per scholarship')->assertDontSee('Talaingod Campus');
        $this->get('/admin/analytics?scholars=campus')->assertOk()
            ->assertSee('Scholars per campus')
            ->assertSee('Talaingod Campus · Active: 1')     // older row, no campus saved: Agro-Forestry is offered at one campus only
            ->assertSee('Samal Campus · Active: 1')         // the campus the provider chose
            ->assertSee('Campus not set · Terminated: 1');  // older row whose program (IT) several campuses offer
        $this->get('/admin/analytics?scholars=nonsense')->assertOk()->assertSee('Scholars per scholarship');
    }

    public function test_signup_chart_counts_new_student_accounts_per_period(): void
    {
        $this->account('new@example.test', 'student');
        $old = $this->account('old@example.test', 'student');
        $old->forceFill(['created_at' => now()->subMonths(3)])->save();
        $this->account('sch@example.test', 'scholar');   // scholars are not student sign-ups

        $this->actingAs($this->admin());
        foreach (['day', 'week', 'month', 'year'] as $range) {
            $this->get("/admin/analytics?signups={$range}")->assertOk()->assertSee("New accounts per {$range}");
        }
        $this->get('/admin/analytics?signups=month')->assertSee('2 new student accounts in the last 12 months');
        $this->get('/admin/analytics?signups=day')->assertSee('1 new student account in the last 30 days');
        $this->get('/admin/analytics?signups=banana')->assertOk()->assertSee('New accounts per month');
    }

    public function test_accounts_can_be_deactivated_reactivated_and_sent_a_reset_link(): void
    {
        Notification::fake();
        $scholar = $this->account('sch@example.test', 'scholar');
        $this->actingAs($this->admin());

        $this->get('/admin/accounts?role=scholar')->assertOk()->assertSee('sch@example.test')->assertSee('data-confirm=', false);
        $this->get('/admin/accounts?role=scholar&q=nobody')->assertOk()->assertDontSee('sch@example.test');

        $this->post("/admin/accounts/{$scholar->id}/reset-link")->assertSessionHas('sent', fn ($s) => $s['title'] === 'Reset link sent');
        Notification::assertSentTo($scholar, ResetPassword::class);

        $this->patch("/admin/accounts/{$scholar->id}/status", ['status' => 'inactive'])->assertSessionHas('status');
        $this->assertSame('inactive', $scholar->fresh()->status);
        $this->patch("/admin/accounts/{$scholar->id}/status", ['status' => 'banana'])->assertSessionHasErrors('status');

        // A deactivated account cannot sign in, and an open session is ended.
        $this->post('/admin/logout');
        $this->post('/scholarLogin', ['email' => 'sch@example.test', 'password' => 'Pass12345'])->assertSessionHasErrors('email');
        $this->actingAs($scholar->fresh())->get('/scholar')->assertRedirect('/');
        $this->assertGuest();

        $this->actingAs($this->admin())->patch("/admin/accounts/{$scholar->id}/status", ['status' => 'active']);
        $this->assertSame('active', $scholar->fresh()->status);
    }

    public function test_the_admin_cannot_manage_admin_accounts(): void
    {
        $this->actingAs($this->admin());

        $this->patch('/admin/accounts/'.$this->admin()->id.'/status', ['status' => 'inactive'])->assertNotFound();
        $this->get('/admin/accounts?role=admin')->assertOk()->assertDontSee('admin@bakr.test');   // falls back to students
        $this->assertSame('active', $this->admin()->fresh()->status);
    }

    public function test_messages_can_be_handled_and_shared_feedback_approved(): void
    {
        $question = ContactMessage::create(['topic' => 'question', 'name' => 'Ana', 'email' => 'ana@example.test', 'message' => 'How do I apply for TDP?']);
        $feedback = ContactMessage::create(['topic' => 'feedback', 'name' => 'Ben', 'email' => 'ben@example.test', 'message' => 'Great tool!', 'share_publicly' => true]);
        $this->actingAs($this->admin());

        $this->get('/admin/messages')->assertOk()->assertSee('How do I apply for TDP?')->assertSee('Show on public site');

        $this->patch("/admin/messages/{$question->id}", ['action' => 'handled'])->assertSessionHas('status');
        $this->assertNotNull($question->fresh()->handled_at);
        $this->get('/admin/messages')->assertDontSee('How do I apply for TDP?');
        $this->get('/admin/messages?show=all')->assertSee('How do I apply for TDP?');

        $this->patch("/admin/messages/{$feedback->id}", ['action' => 'approve']);
        $this->assertNotNull($feedback->fresh()->approved_at);
        $this->assertTrue(ContactMessage::publicFeedback()->whereKey($feedback->id)->exists());

        // A message the sender did not agree to share can never be made public.
        $this->patch("/admin/messages/{$question->id}", ['action' => 'approve'])->assertSessionHas('notice');
        $this->assertNull($question->fresh()->approved_at);
    }

    public function test_the_admin_replies_to_a_message_by_email_from_inside_bakr(): void
    {
        Mail::fake();
        $m = ContactMessage::create(['topic' => 'question', 'name' => 'Ana', 'email' => 'ana@example.test', 'message' => 'How do I apply for TDP?']);
        $this->actingAs($this->admin());

        $this->get('/admin/messages')->assertOk()->assertSee(route('admin.messages.reply', $m->id), false)->assertSee('Reply by e-mail')->assertDontSee('?subject=', false);   // no more mailto: reply link that needs a mail program on this computer

        $this->post("/admin/messages/{$m->id}/reply", ['reply' => 'x'])->assertSessionHasErrors('reply');
        Mail::assertNothingSent();

        // Success = a "Reply sent" modal on the next page (session 'sent'), not a green alert.
        $this->post("/admin/messages/{$m->id}/reply", ['reply' => 'Apply with CHED before the deadline.'])
            ->assertSessionHas('sent', fn ($s) => $s['title'] === 'Reply sent' && str_contains($s['text'], 'ana@example.test'))
            ->assertSessionMissing('status');
        Mail::assertSent(ContactMessageReply::class, fn ($mail) => $mail->hasTo('ana@example.test') && $mail->body === 'Apply with CHED before the deadline.');
        $m->refresh();
        $this->assertSame('Apply with CHED before the deadline.', $m->reply);
        $this->assertNotNull($m->replied_at);
        $this->assertNotNull($m->handled_at);   // replying also marks it handled

        $this->get('/admin/messages?show=all')->assertSee('You replied')->assertSee('Apply with CHED before the deadline.')->assertSee('Reply again');

        // Only an admin can reply.
        $this->actingAs($this->account('stu@example.test', 'student'))->post("/admin/messages/{$m->id}/reply", ['reply' => 'Hello there, friend.'])->assertForbidden();
    }

    public function test_a_failed_reply_email_saves_nothing_and_keeps_the_text(): void
    {
        $m = ContactMessage::create(['topic' => 'question', 'name' => 'Ana', 'email' => 'ana@example.test', 'message' => 'How do I apply for TDP?']);
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('smtp down'));
        $this->actingAs($this->admin());

        $this->post("/admin/messages/{$m->id}/reply", ['reply' => 'Apply with CHED before the deadline.'])
            ->assertSessionHas('notice')->assertSessionHasInput('reply', 'Apply with CHED before the deadline.');
        $this->assertNull($m->fresh()->reply);
        $this->assertNull($m->fresh()->handled_at);
    }

    public function test_the_admin_can_reset_a_forgotten_password(): void
    {
        Notification::fake();
        $this->get('/student/forgot-password?from=admin')->assertOk()->assertSee(route('admin.login'), false);
        $this->post('/student/forgot-password', ['email' => 'admin@bakr.test']);
        Notification::assertSentTo($this->admin(), ResetPassword::class);
    }

    public function test_scholar_accounts_filter_by_scholarship_and_campus_and_sort_by_name(): void
    {
        $tdp = Scholarship::where('slug', 'ched-tdp')->firstOrFail();
        $tes = Scholarship::where('slug', 'ched-tes')->firstOrFail();
        $make = function (string $name, Scholarship $s, string $program, ?string $campus = null) {
            $u = $this->account(strtolower(str_replace(' ', '.', $name)).'@example.test', 'scholar');
            $u->forceFill(['name' => $name])->save();
            Scholar::create(['user_id' => $u->id, 'scholarship_id' => $s->id, 'status' => 'active', 'program' => $program, 'campus' => $campus]);
        };
        $make('Ana Reyes', $tdp, 'Bachelor of Science in Information Technology', 'Samal Campus');
        $make('Ben Cruz', $tes, 'Bachelor of Science in Agro-Forestry');   // no campus saved: only Talaingod offers it
        $make('Carla Diaz', $tdp, 'Bachelor of Science in Information Technology');   // offered at several campuses: no campus

        $this->actingAs($this->admin());
        $names = fn (string $url) => collect(['Ana Reyes', 'Ben Cruz', 'Carla Diaz'])
            ->filter(fn ($n) => str_contains($this->get($url)->assertOk()->getContent(), '<strong>'.$n.'</strong>'))->values()->all();

        $this->assertSame(['Ana Reyes', 'Carla Diaz'], $names('/admin/accounts?role=scholar&scholarship='.$tdp->id));
        $this->assertSame(['Ana Reyes'], $names('/admin/accounts?role=scholar&campus=Samal+Campus'));
        $this->assertSame(['Ben Cruz'], $names('/admin/accounts?role=scholar&campus=Talaingod+Campus'));
        $this->assertSame([], $names('/admin/accounts?role=scholar&campus=Samal+Campus&scholarship='.$tes->id));
        $this->assertCount(3, $names('/admin/accounts?role=scholar&campus=Nowhere&scholarship=999'));   // unknown values are ignored

        $this->get('/admin/accounts?role=scholar')->assertSeeInOrder(['Ana Reyes', 'Ben Cruz', 'Carla Diaz'])
            ->assertSee('id="f-scholarship"', false)->assertSee('id="f-campus"', false)->assertSee('Name Z–A');
        $this->get('/admin/accounts?role=scholar&sort=name_desc')->assertSeeInOrder(['Carla Diaz', 'Ben Cruz', 'Ana Reyes']);
        // The scholarship / campus filters belong to the Scholars tab only.
        $this->get('/admin/accounts?role=student')->assertOk()->assertDontSee('id="f-campus"', false)->assertSee('id="f-sort"', false);
    }

    public function test_analytics_can_be_printed_and_downloaded_as_excel_without_student_details(): void
    {
        $student = $this->account('stu@example.test', 'student');
        ScholarshipApplication::create(['user_id' => $student->id, 'version' => 1, 'is_current' => true, 'answers' => ['income' => 'SECRET-INCOME'], 'submitted_at' => now()]);

        $this->actingAs($this->admin());
        $this->get('/admin/analytics')->assertOk()
            ->assertSee('data-print', false)->assertSee('Save as PDF')->assertSee(route('admin.analytics.export', ['signups' => 'month']), false);

        $res = $this->get('/admin/analytics/export.xlsx?signups=week')->assertOk();
        $this->assertStringContainsString('spreadsheetml.sheet', $res->headers->get('Content-Type'));
        $this->assertStringContainsString('bakr-analytics-', $res->headers->get('Content-Disposition'));

        $tmp = tempnam(sys_get_temp_dir(), 'xl');
        file_put_contents($tmp, $res->getContent());
        $zip = new \ZipArchive;
        $this->assertTrue($zip->open($tmp));
        $book = $zip->getFromName('xl/workbook.xml');
        foreach (['Summary', 'Matching', 'Scholars', 'Sign-ups', 'Providers'] as $sheet) {
            $this->assertStringContainsString('name="'.$sheet.'"', $book);
        }
        $all = '';
        for ($i = 1; $i <= 5; $i++) {
            $all .= $zip->getFromName("xl/worksheets/sheet{$i}.xml");
        }
        $zip->close();
        @unlink($tmp);

        $this->assertStringContainsString('Student accounts', $all);
        $this->assertStringContainsString('<v>1</v>', $all);                       // counts are real numbers
        $this->assertStringContainsString('New student accounts per week', $all);
        $this->assertStringNotContainsString('SECRET-INCOME', $all);
        $this->assertStringNotContainsString('stu@example.test', $all);

        // Admins only.
        $this->actingAs($student)->get('/admin/analytics/export.xlsx')->assertForbidden();
    }
}
