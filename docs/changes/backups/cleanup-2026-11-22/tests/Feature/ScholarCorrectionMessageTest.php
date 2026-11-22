<?php

namespace Tests\Feature;

use App\Mail\ScholarMessageReply;
use App\Models\ContactMessage;
use App\Models\Scholar;
use App\Models\ScholarMessage;
use App\Models\Scholarship;
use App\Models\User;
use App\Support\AcademicTerm;
use Database\Seeders\ScholarshipSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * "Request a correction" (owner, 2026-11-20): a scholar's request to fix their details goes to the
 * provider of the chosen scholarship (provider › Messages), never to the admin's Messages.
 */
class ScholarCorrectionMessageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->seed(ScholarshipSeeder::class);
    }

    private function account(string $role, string $email, ?string $providerKey = null): User
    {
        $u = new User;
        $u->forceFill(['name' => $role === 'scholar' ? 'Ana Reyes' : strtoupper((string) $providerKey), 'email' => $email, 'password' => Hash::make('Pass-12345678'),
            'role' => $role, 'status' => 'active', 'provider_key' => $providerKey])->save();

        return $u;
    }

    private function scholar(User $u, string $slug): Scholar
    {
        return Scholar::create(['user_id' => $u->id, 'scholarship_id' => Scholarship::where('slug', $slug)->value('id'),
            'status' => 'active', 'started_term' => AcademicTerm::current(), 'scholar_code' => 'ID-'.$slug]);
    }

    public function test_the_request_goes_to_the_chosen_scholarships_provider_not_the_admin(): void
    {
        $ana = $this->account('scholar', 'ana@example.test');
        $tdp = $this->scholar($ana, 'ched-tdp');
        $dost = $this->scholar($ana, 'dost-sei');
        $ched = $this->account('provider', 'ched@provider.test', 'ched');
        $dostProvider = $this->account('provider', 'dost@provider.test', 'dost');

        $this->actingAs($ana)->get('/scholar/profile')->assertOk()
            ->assertSee('Request a correction')->assertSee('href="#corrections"', false)
            ->assertSee('action="'.route('scholar.profile.correction').'"', false)
            ->assertSee('Which scholarship?');   // two scholarships: the scholar picks the provider

        $this->post(route('scholar.profile.correction'), ['scholar_id' => $tdp->id, 'topic' => 'personal', 'message' => 'My birthday should be June 1, 2004.'])
            ->assertRedirect(route('scholar.profile').'#corrections')
            ->assertSessionHas('sent', fn ($s) => $s['title'] === 'Request sent' && str_contains($s['text'], 'CHED'));
        $this->get('/scholar/profile')->assertSee('id="sent-dialog"', false)->assertSee('Request sent');

        $m = ScholarMessage::sole();
        $this->assertSame([$tdp->id, $ana->id, 'personal'], [$m->scholar_id, $m->user_id, $m->topic]);
        $this->assertSame(0, ContactMessage::count());   // nothing in the admin's inbox

        // CHED receives it (badge + page); DOST does not.
        $this->actingAs($ched)->get('/provider/messages')->assertOk()
            ->assertSee('My birthday should be June 1, 2004.')->assertSee('Ana Reyes')->assertSee('Open (1)')
            ->assertSee(route('provider.scholars.show', [$tdp->id, 'edit' => 'personal']), false);
        $this->get('/provider/dashboard')->assertSee(route('provider.messages'), false)->assertSee('1 open');
        $this->actingAs($dostProvider)->get('/provider/messages')->assertOk()->assertDontSee('My birthday should be June 1, 2004.');
        $this->patch(route('provider.messages.update', $m->id), ['action' => 'handled'])->assertNotFound();
        $this->post(route('provider.messages.reply', $m->id), ['reply' => 'Not my scholar, sorry.'])->assertNotFound();

        // The admin's Messages page never lists it.
        $admin = $this->account('admin', 'admin@example.test');
        $this->actingAs($admin)->get('/admin/messages')->assertOk()->assertDontSee('My birthday should be June 1, 2004.');
        $this->assertNotNull($dost);
    }

    public function test_the_provider_replies_and_the_scholar_sees_it(): void
    {
        $ana = $this->account('scholar', 'ana@example.test');
        $tdp = $this->scholar($ana, 'ched-tdp');
        $ched = $this->account('provider', 'ched@provider.test', 'ched');
        $m = ScholarMessage::create(['scholar_id' => $tdp->id, 'user_id' => $ana->id, 'topic' => 'school', 'message' => 'I am in 3rd year now, not 2nd.']);

        $this->actingAs($ched)->post(route('provider.messages.reply', $m->id), ['reply' => 'short'])->assertSessionHasErrors('reply');
        $this->post(route('provider.messages.reply', $m->id), ['reply' => 'Fixed: your year level is now 3rd year.'])
            ->assertSessionHas('sent', fn ($s) => $s['title'] === 'Reply sent' && str_contains($s['text'], 'ana@example.test'));

        $m->refresh();
        $this->assertSame('Fixed: your year level is now 3rd year.', $m->reply);
        $this->assertNotNull($m->handled_at);
        $this->assertNotNull($m->reply_emailed_at);
        $this->assertSame($ched->id, $m->replied_by);
        Mail::assertSent(ScholarMessageReply::class, fn ($mail) => $mail->hasTo('ana@example.test') && $mail->correction->is($m)
            && $mail->hasReplyTo('ched@provider.test')
            && str_contains($mail->render(), 'Fixed: your year level is now 3rd year.') && str_contains($mail->render(), 'I am in 3rd year now, not 2nd.'));

        $this->get('/provider/messages')->assertSee('No open messages');
        $this->get('/provider/messages?show=all')->assertSee('Fixed: your year level is now 3rd year.')->assertSee('Handled');
        $this->patch(route('provider.messages.update', $m->id), ['action' => 'reopen'])->assertSessionHas('status');
        $this->assertNull($m->fresh()->handled_at);
        $this->patch(route('provider.messages.update', $m->id), ['action' => 'delete'])->assertSessionHasErrors('action');

        $this->actingAs($ana)->get('/scholar/profile')->assertOk()
            ->assertSee('Your requests')->assertSee('I am in 3rd year now, not 2nd.')
            ->assertSee('Fixed: your year level is now 3rd year.')->assertSee('Answered')
            ->assertDontSee('Which scholarship?');   // one scholarship: no picker
    }

    public function test_a_scholar_can_only_write_about_their_own_scholarship(): void
    {
        $ana = $this->account('scholar', 'ana@example.test');
        $this->scholar($ana, 'ched-tdp');
        $other = $this->scholar($this->account('scholar', 'ben@example.test'), 'dost-sei');

        $this->actingAs($ana)->from('/scholar/profile')
            ->post(route('scholar.profile.correction'), ['scholar_id' => $other->id, 'topic' => 'personal', 'message' => 'Please change this record.'])
            ->assertRedirect('/scholar/profile')->assertSessionHasErrors(['scholar_id'], null, 'correction');
        $this->post(route('scholar.profile.correction'), ['scholar_id' => $other->id, 'topic' => 'nope', 'message' => 'x'])
            ->assertSessionHasErrors(['topic', 'message'], null, 'correction');
        $this->assertSame(0, ScholarMessage::count());

        // Students and providers cannot post to the scholar route.
        $this->actingAs($this->account('student', 'stu@example.test'))
            ->post(route('scholar.profile.correction'), ['scholar_id' => $other->id, 'topic' => 'personal', 'message' => 'Please change this record.'])->assertForbidden();
    }

    public function test_without_a_scholarship_there_is_no_correction_form(): void
    {
        $ana = $this->account('scholar', 'ana@example.test');

        $this->actingAs($ana)->get('/scholar/profile')->assertOk()->assertDontSee('id="corrections"', false);
        $this->post(route('scholar.profile.correction'), ['topic' => 'personal', 'message' => 'Please change this record.'])
            ->assertRedirect(route('scholar.profile'))->assertSessionHas('notice');
        $this->assertSame(0, ScholarMessage::count());
    }

    public function test_scholar_help_sends_corrections_to_the_provider(): void
    {
        $ana = $this->account('scholar', 'ana@example.test');
        $this->scholar($ana, 'ched-tdp');

        $this->actingAs($ana)->get('/scholar/help')->assertOk()
            ->assertDontSee('Scholarship info needs updating')
            ->assertSee(route('scholar.profile').'#corrections', false);
        $this->post('/scholar/help', ['topic' => 'info', 'message' => 'Please update my birthday.'])->assertSessionHasErrors('topic');
        $this->assertSame(0, ContactMessage::count());
    }
}
