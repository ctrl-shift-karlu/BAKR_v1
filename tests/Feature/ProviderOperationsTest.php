<?php

namespace Tests\Feature;

use App\Models\Scholar;
use App\Models\Scholarship;
use App\Models\User;
use App\Support\AcademicTerm;
use App\Support\MaintainingDocument;
use Database\Seeders\ProviderAccountSeeder;
use Database\Seeders\ScholarshipSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Scholars, disbursements and requirements in the Provider Portal (no applicants: providers monitor existing scholars). */
class ProviderOperationsTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'Provider-Test-Pass-2026';

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

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

    private function student(string $email = 'student@example.test', string $role = 'student'): User
    {
        $u = new User;
        $u->name = 'Juan Dela Cruz';
        $u->email = $email;
        $u->password = Hash::make('StudentPass123');
        $u->role = $role;
        $u->status = 'active';
        $u->save();

        return $u;
    }

    private function scholar(string $slug, array $attrs = []): Scholar
    {
        return Scholar::create($attrs + [
            'user_id' => $this->student(uniqid('s').'@example.test', 'scholar')->id,   // a scholar account
            'scholarship_id' => Scholarship::where('slug', $slug)->value('id'),
            'status' => 'active',
            'stipend_amount' => 7500,
        ]);
    }

    public function test_every_provider_page_loads(): void
    {
        $this->scholar('ched-tdp');
        $this->actingAs($this->provider('ched'));

        foreach (['dashboard', 'scholars', 'disbursements', 'requirements', 'scholarships'] as $page) {
            $this->get("/provider/{$page}")->assertOk();
        }
        $this->get('/provider/scholars/'.Scholar::first()->id)->assertOk();
    }

    public function test_a_student_gets_403_on_every_provider_page(): void
    {
        $this->actingAs($this->student());

        foreach (['dashboard', 'scholars', 'disbursements', 'requirements', 'scholarships'] as $page) {
            $this->get("/provider/{$page}")->assertForbidden();
        }
    }

    public function test_another_providers_records_are_not_found(): void
    {
        $dostScholar = $this->scholar('dost-sei');
        $req = $dostScholar->requirements()->create(['term' => AcademicTerm::current(), 'name' => 'COR', 'status' => 'submitted', 'file_path' => 'x/y.pdf']);
        $pay = $dostScholar->disbursements()->create(['term' => AcademicTerm::current(), 'amount' => 5000, 'status' => 'pending']);
        $dostScholarship = Scholarship::where('slug', 'dost-sei')->firstOrFail();

        $this->actingAs($this->provider('ched'));

        $this->get("/provider/scholars/{$dostScholar->id}")->assertNotFound();
        $this->patch("/provider/scholars/{$dostScholar->id}", ['status' => 'terminated'])->assertNotFound();
        $this->get("/provider/requirements/{$req->id}/file")->assertNotFound();
        $this->patch("/provider/requirements/{$req->id}", ['status' => 'approved'])->assertNotFound();
        $this->post("/provider/disbursements/{$pay->id}/release")->assertNotFound();
        $this->put("/provider/scholarships/{$dostScholarship->id}", ['overview' => 'Hacked'])->assertNotFound();
        $this->get('/provider/scholars?scholarship='.$dostScholarship->id)->assertNotFound();

        // Bulk release silently ignores IDs outside the provider's scope.
        $this->post('/provider/disbursements/release', ['ids' => [$pay->id]])->assertRedirect();

        $this->assertSame('active', $dostScholar->fresh()->status);
        $this->assertSame('submitted', $req->fresh()->status);
        $this->assertSame('pending', $pay->fresh()->status);
        $this->assertNotSame('Hacked', $dostScholarship->fresh()->overview);

        // And lists never show them.
        $this->get('/provider/scholars')->assertDontSee($dostScholar->user->name);
    }

    public function test_there_is_no_applicants_page_any_more(): void
    {
        $this->actingAs($this->provider('ched'));

        $this->get('/provider/applicants')->assertNotFound();
        $html = $this->get('/provider/dashboard')->assertOk()->getContent();
        $this->assertStringNotContainsString('Applicants', $html);
        $this->assertStringContainsString('Recently added scholars', $html);
    }

    public function test_scholar_details_can_be_updated_and_validated(): void
    {
        $s = $this->scholar('ched-tdp');
        $this->actingAs($this->provider('ched'));

        // Each card of the profile saves on its own ("section").
        $school = ['section' => 'school', 'scholar_code' => 'TDP-1', 'student_number' => '2026-00001', 'year_level' => '1', 'campus' => 'Samal Campus', 'program' => 'BS Information Technology'];
        $this->patch("/provider/scholars/{$s->id}", $school)->assertSessionHasNoErrors()->assertRedirect();
        $this->patch("/provider/scholars/{$s->id}", ['section' => 'scholarship', 'stipend_amount' => '8000'])->assertSessionHasNoErrors()->assertRedirect();
        $s->refresh();
        $this->assertSame('active', $s->status);
        $this->assertSame('2026-00001', $s->student_number);
        $this->assertSame('8000.00', $s->stipend_amount);

        $this->patch("/provider/scholars/{$s->id}", ['section' => 'scholarship', 'stipend_amount' => '-1'])->assertSessionHasErrors('stipend_amount');
        // Status is not part of the details forms (terminate is its own confirmed action).
        $this->patch("/provider/scholars/{$s->id}", ['status' => 'terminated'] + $school);
        $this->assertSame('active', $s->fresh()->status);
    }

    public function test_only_the_own_provider_terminates_and_reinstates_a_scholar(): void
    {
        $s = $this->scholar('ched-tdp');
        $dost = $this->scholar('dost-sei');
        $this->actingAs($this->provider('ched'));

        // Inactive is derived, never set by hand; old statuses are gone.
        foreach (['inactive', 'probation', 'graduated'] as $bad) {
            $this->patch("/provider/scholars/{$s->id}/status", ['status' => $bad])->assertSessionHasErrors('status');
        }
        $this->patch("/provider/scholars/{$dost->id}/status", ['status' => 'terminated'])->assertNotFound();
        $this->assertSame('active', $dost->fresh()->status);

        $this->patch("/provider/scholars/{$s->id}/status", ['status' => 'terminated'])->assertRedirect()->assertSessionHas('status');
        $this->assertSame('terminated', $s->fresh()->status);
        $this->get("/provider/scholars/{$s->id}")->assertOk()->assertSee('Reinstate scholar')->assertDontSee('id="confirm-terminate"', false);

        $this->patch("/provider/scholars/{$s->id}/status", ['status' => 'active'])->assertRedirect();
        $this->assertSame('active', $s->fresh()->status);
        $this->get("/provider/scholars/{$s->id}")->assertOk()->assertSee('id="confirm-terminate"', false);

        // A scholar cannot do it: the route is provider-only.
        $this->actingAs($s->user)->patch("/provider/scholars/{$s->id}/status", ['status' => 'terminated'])->assertForbidden();
    }

    public function test_a_provider_without_bakr_documents_marks_scholars_inactive_by_hand(): void
    {
        $s = $this->scholar('dnsc-academic');
        $this->actingAs($this->provider('dnsc'));

        $this->get("/provider/scholars/{$s->id}")->assertOk()
            ->assertSee('in person at the official DNSC scholarship office')->assertSee('Mark inactive')->assertSee('Terminate scholarship');
        $this->get('/provider/scholarships')->assertOk()->assertSee('in person at the official DNSC scholarship office');

        $this->patch("/provider/scholars/{$s->id}/status", ['status' => 'inactive'])->assertRedirect()->assertSessionHas('status');
        $this->assertSame('inactive', $s->fresh()->standing());
        $this->assertSame(1, Scholar::whereStanding('inactive')->count());
        $this->get("/provider/scholars/{$s->id}")->assertSee('Marked Inactive by')->assertSee('Mark active');

        // The scholar sees why, and where to go.
        $this->actingAs($s->user)->get('/scholar')->assertOk()->assertSee('is marked inactive')->assertSee('DNSC scholarship office');
        $this->get('/scholar/requirements')->assertOk()->assertSee('in person at the official DNSC scholarship office');

        $this->actingAs($this->provider('dnsc'));
        $this->patch("/provider/scholars/{$s->id}/status", ['status' => 'active']);
        $this->assertSame('active', $s->fresh()->standing());
        $this->patch("/provider/scholars/{$s->id}/status", ['status' => 'terminated']);
        $this->assertSame('terminated', $s->fresh()->standing());
    }

    public function test_the_scholars_list_has_the_status_actions_on_every_row(): void
    {
        $dnsc = $this->scholar('dnsc-academic');
        $this->actingAs($this->provider('dnsc'));
        $html = $this->get('/provider/scholars')->assertOk()->getContent();
        $this->assertStringContainsString('Mark inactive', $html);
        $this->assertStringContainsString('data-terminate-url="'.route('provider.scholars.status', $dnsc->id).'"', $html);
        $this->assertStringContainsString('id="confirm-terminate"', $html);

        $ched = $this->scholar('ched-tdp', ['status' => 'terminated']);
        $html = $this->actingAs($this->provider('ched'))->get('/provider/scholars')->assertOk()->getContent();
        $this->assertStringNotContainsString('Mark inactive', $html);   // automatic for CHED
        $this->assertStringContainsString('Reinstate', $html);
    }

    public function test_providers_with_bakr_documents_cannot_set_inactive_by_hand(): void
    {
        $s = $this->scholar('ched-tdp');
        $this->actingAs($this->provider('ched'));

        $this->patch("/provider/scholars/{$s->id}/status", ['status' => 'inactive'])->assertSessionHasErrors('status');
        $this->assertSame('active', $s->fresh()->status);
        $this->get("/provider/scholars/{$s->id}")->assertOk()->assertDontSee('Mark inactive')->assertSee('Terminate scholarship');
    }

    public function test_every_provider_can_terminate_its_own_scholars(): void
    {
        foreach (['dnsc' => 'dnsc-academic', 'cebuana' => 'cebuana-clfi', 'landbank' => 'landbank-ilp', 'bfar' => 'bfar-fsp', 'grab' => 'grabscholar', 'dost' => 'dost-sei', 'ched' => 'coscho'] as $key => $slug) {
            $s = $this->scholar($slug);
            $this->actingAs($this->provider($key))->patch("/provider/scholars/{$s->id}/status", ['status' => 'terminated'])->assertRedirect();
            $this->assertSame('terminated', $s->fresh()->status, $key);
        }
    }

    public function test_a_scholar_with_an_overdue_document_is_inactive_until_they_submit(): void
    {
        $term = AcademicTerm::current();
        $late = $this->scholar('ched-tdp');
        $ok = $this->scholar('ched-tdp');
        $gone = $this->scholar('ched-tdp', ['status' => 'terminated']);
        $r = $late->requirements()->create(['term' => $term, 'name' => 'COR', 'status' => 'missing', 'due_on' => now()->subDay()->toDateString()]);
        $ok->requirements()->create(['term' => $term, 'name' => 'COR', 'status' => 'missing', 'due_on' => now()->addWeek()->toDateString()]);
        $gone->requirements()->create(['term' => $term, 'name' => 'COR', 'status' => 'missing', 'due_on' => now()->subDay()->toDateString()]);

        $this->assertSame('inactive', $late->fresh()->standing());
        $this->assertSame('active', $ok->fresh()->standing());
        $this->assertSame('terminated', $gone->fresh()->standing());

        $this->actingAs($this->provider('ched'));
        $ids = fn ($status) => collect($this->get('/provider/scholars?status='.$status)->assertOk()->viewData('scholars')->items())->pluck('id')->all();
        $this->assertSame([$late->id], $ids('inactive'));
        $this->assertSame([$ok->id], $ids('active'));
        $this->assertSame([$gone->id], $ids('terminated'));
        $this->get("/provider/scholars/{$late->id}")->assertOk()->assertSee('past due and not submitted');

        // Sent back for revision after the due date still counts; submitting clears it.
        $r->update(['status' => 'needs_revision']);
        $this->assertSame('inactive', $late->fresh()->standing());
        $r->update(['status' => 'submitted']);
        $this->assertSame('active', $late->fresh()->standing());
    }

    public function test_the_provider_previews_a_document_without_downloading_it(): void
    {
        $s = $this->scholar('ched-tdp');
        Storage::disk('local')->put('scholar-requirements/9/a.pdf', '%PDF-1.4 test');
        Storage::disk('local')->put('scholar-requirements/9/b.docx', 'not previewable');
        $pdf = $s->requirements()->create(['term' => AcademicTerm::current(), 'name' => 'COR', 'status' => 'submitted', 'file_path' => 'scholar-requirements/9/a.pdf', 'original_name' => 'cor.pdf', 'mime' => 'application/pdf']);
        $doc = $s->requirements()->create(['term' => AcademicTerm::current(), 'name' => 'Grades', 'status' => 'submitted', 'file_path' => 'scholar-requirements/9/b.docx', 'original_name' => 'g.docx', 'mime' => 'application/msword']);
        $this->actingAs($this->provider('ched'));

        $inline = $this->get("/provider/requirements/{$pdf->id}/file?inline=1")->assertOk();
        $this->assertSame('application/pdf', $inline->headers->get('Content-Type'));
        $this->assertSame('nosniff', $inline->headers->get('X-Content-Type-Options'));
        $this->assertStringStartsWith('inline', (string) $inline->headers->get('Content-Disposition'));
        // Download stays available; a type the browser can't show is always a download.
        $this->get("/provider/requirements/{$pdf->id}/file")->assertDownload('cor.pdf');
        $this->get("/provider/requirements/{$doc->id}/file?inline=1")->assertDownload('g.docx');

        $html = $this->get('/provider/requirements')->assertOk()->getContent();
        $this->assertStringContainsString('data-file-preview="'.route('provider.requirements.file', $pdf->id).'?inline=1"', $html);
        $this->assertStringContainsString('id="pv-file-preview"', $html);
        $this->assertStringContainsString('class="pv-acc"', $html);
        $this->assertStringContainsString('href="'.route('provider.requirements.file', $doc->id).'" download', $html);
    }

    public function test_checklist_items_take_each_documents_settings_and_follow_later_edits(): void
    {
        Scholarship::where('slug', 'ched-tdp')->update(['renewal_requirements' => json_encode([
            ['name' => 'COR', 'note' => 'Signed', 'types' => ['pdf'], 'max_mb' => 2, 'required' => true],
            ['name' => 'If on leave: LOA', 'types' => ['pdf', 'jpg'], 'max_mb' => 5, 'required' => false],
        ])]);
        $s = $this->scholar('ched-tdp');
        $term = AcademicTerm::current();
        $this->actingAs($this->provider('ched'));

        $this->setDeadline('ched-tdp', $term, '2026-12-15')->assertRedirect();
        $cor = $s->requirements()->where('name', 'COR')->firstOrFail();
        $loa = $s->requirements()->where('name', 'If on leave: LOA')->firstOrFail();
        $this->assertSame(['pdf'], $cor->accepted_types);
        $this->assertSame(2048, $cor->max_kb);
        $this->assertSame('Signed', $cor->instructions);
        $this->assertFalse($cor->is_optional);
        $this->assertTrue($loa->is_optional);

        // Editing the document later updates the open (not approved) items.
        $tdp = Scholarship::where('slug', 'ched-tdp')->firstOrFail();
        $this->put("/provider/scholarships/{$tdp->id}/documents", ['docs' => [
            ['name' => 'COR', 'types' => ['pdf', 'png'], 'max_mb' => '10', 'required' => '1'],
            ['name' => 'If on leave: LOA', 'types' => ['pdf'], 'max_mb' => '5', 'required' => '0'],
        ]])->assertSessionHas('status', fn ($m) => str_contains($m, 'open checklist'));
        $this->assertSame(['pdf', 'png'], $cor->fresh()->accepted_types);
        $this->assertSame(10240, $cor->fresh()->max_kb);
        $this->assertNull($cor->fresh()->instructions);
    }

    public function test_a_terminated_scholars_stipend_cannot_be_released(): void
    {
        $term = AcademicTerm::current();
        $s = $this->cleared('ched-tdp', $term);
        $pay = $s->disbursements()->create(['term' => $term, 'amount' => 7500, 'status' => 'pending']);
        $s->update(['status' => 'terminated']);
        $this->actingAs($this->provider('ched'));

        $this->post("/provider/disbursements/{$pay->id}/release")
            ->assertSessionHasErrors(['release' => "Juan Dela Cruz's stipend can't be released: the scholarship was terminated. Reinstate the scholar first."]);
        $this->post('/provider/disbursements/release', ['ids' => [$pay->id]]);
        $this->assertSame('pending', $pay->fresh()->status);
    }

    public function test_older_plain_text_documents_read_their_own_wording(): void
    {
        $doc = MaintainingDocument::fromText('If returning after a Leave of Absence: the LOA you submitted (PDF copy)');
        $this->assertFalse($doc['required']);
        $this->assertSame(['pdf'], $doc['types']);

        $doc = MaintainingDocument::fromText('Certified true copy of grades');
        $this->assertTrue($doc['required']);
        $this->assertSame(['pdf', 'jpg', 'png'], $doc['types']);
        $this->assertSame(5, $doc['max_mb']);
    }

    /** Sets one scholarship's deadline for a semester, as the Requirements page does. */
    private function setDeadline(string $slug, string $term, ?string $due)
    {
        return $this->post('/provider/requirements/deadline', ['scholarship_id' => Scholarship::where('slug', $slug)->value('id'), 'term' => $term, 'due_on' => $due]);
    }

    public function test_setting_a_deadline_opens_the_checklist_and_changing_it_moves_the_due_date(): void
    {
        Scholarship::where('slug', 'ched-tdp')->update(['renewal_requirements' => json_encode(['COR'])]);
        $s = $this->scholar('ched-tdp');
        $term = AcademicTerm::current();
        $this->actingAs($this->provider('ched'));

        $this->get('/provider/requirements')->assertOk()->assertSee('Deadline not set')->assertSee('Set deadline')
            ->assertDontSee('Open checklist')->assertDontSee('Update checklist');

        $this->setDeadline('ched-tdp', $term, '2026-09-15')->assertRedirect()->assertSessionHas('status', fn ($m) => str_contains($m, 'due Sep 15, 2026'));
        $this->assertSame('2026-09-15', $s->requirements()->first()->due_on->toDateString());
        $this->get('/provider/requirements')->assertSee('Due Sep 15, 2026')->assertSee('Save deadline');

        $this->setDeadline('ched-tdp', $term, '2026-10-01')->assertRedirect();
        $this->assertSame('2026-10-01', $s->requirements()->first()->due_on->toDateString());
        $this->assertSame(1, $s->requirements()->count());   // the item is kept, only the date moves

        $this->setDeadline('ched-tdp', $term, 'soon')->assertSessionHasErrors('due_on');
        $this->setDeadline('ched-tdp', $term, null)->assertSessionHasErrors('due_on');
        $this->assertSame('2026-10-01', $s->requirements()->first()->due_on->toDateString());
    }

    public function test_preparing_a_term_creates_pending_stipends_for_good_standing_scholars_only(): void
    {
        $active = $this->scholar('ched-tdp');
        $probation = $this->scholar('ched-tes', ['status' => 'active', 'stipend_amount' => 10000]);
        $graduated = $this->scholar('ched-tdp', ['status' => 'terminated']);
        $noAmount = $this->scholar('ched-tdp', ['stipend_amount' => null]);
        $term = AcademicTerm::current();

        $this->actingAs($this->provider('ched'));
        $this->post('/provider/disbursements/generate', ['term' => $term, 'release_type' => 'semester'])->assertRedirect();
        $this->post('/provider/disbursements/generate', ['term' => $term, 'release_type' => 'monthly'])->assertRedirect();   // idempotent: already prepared

        $this->assertSame(1, $active->disbursements()->count());
        $this->assertSame('pending', $active->disbursements()->first()->status);
        $this->assertSame('semester', $active->disbursements()->first()->release_type);
        $this->assertSame('10000.00', $probation->disbursements()->first()->amount);
        $this->assertSame(0, $graduated->disbursements()->count());
        $this->assertSame(0, $noAmount->disbursements()->count());

        $this->post('/provider/disbursements/generate', ['term' => 'AY 1999â€“2000 Â· 1st Semester', 'release_type' => 'semester'])->assertSessionHasErrors('term');
        $this->post('/provider/disbursements/generate', ['term' => $term, 'release_type' => 'weekly'])->assertSessionHasErrors('release_type');
    }

    public function test_preparing_monthly_creates_one_record_per_month_of_the_semester(): void
    {
        $scholar = $this->scholar('ched-tdp', ['stipend_amount' => 1500]);
        $term = AcademicTerm::current();

        $this->actingAs($this->provider('ched'));
        $this->post('/provider/disbursements/generate', ['term' => $term, 'release_type' => 'monthly'])->assertRedirect();
        $this->post('/provider/disbursements/generate', ['term' => $term, 'release_type' => 'monthly'])->assertRedirect();   // idempotent

        $rows = $scholar->disbursements()->orderBy('period_month')->get();
        $this->assertSame(AcademicTerm::months($term), $rows->pluck('period_month')->all());
        $this->assertCount(5, $rows);
        $this->assertSame(['monthly'], $rows->pluck('release_type')->unique()->values()->all());
        $this->assertSame('1500.00', $rows->first()->amount);

        $this->get('/provider/disbursements')->assertOk()->assertSee('Per month')->assertSee($rows->first()->periodLabel());
    }

    public function test_there_is_no_midyear_anywhere(): void
    {
        $this->actingAs($this->provider('ched'));
        $midyear = 'AY 2026â€“2027 Â· Midyear';

        $this->assertSame([], array_values(array_filter(AcademicTerm::options(), fn ($t) => str_contains($t, 'Midyear'))));
        $this->get('/provider/disbursements')->assertOk()->assertDontSee('Midyear');
        $this->get('/provider/requirements')->assertOk()->assertDontSee('Midyear');
        $this->get('/provider/disbursements?term='.urlencode($midyear))->assertOk()->assertDontSee('Midyear');
        $this->post('/provider/disbursements/generate', ['term' => $midyear, 'release_type' => 'semester'])->assertSessionHasErrors('term');
        $this->setDeadline('ched-tdp', $midyear, '2026-12-01')->assertSessionHasErrors('term');
    }

    /** A scholar whose requirements for $term are all approved (so their stipend may be released). */
    private function cleared(string $slug, string $term, array $attrs = []): Scholar
    {
        $s = $this->scholar($slug, $attrs);
        $s->requirements()->create(['term' => $term, 'name' => 'COR', 'status' => 'approved']);
        $s->requirements()->create(['term' => $term, 'name' => 'Grades', 'status' => 'approved']);

        return $s;
    }

    public function test_release_single_and_bulk(): void
    {
        $term = AcademicTerm::current();
        $a = $this->cleared('ched-tdp', $term)->disbursements()->create(['term' => $term, 'amount' => 7500, 'status' => 'pending']);
        $b = $this->cleared('ched-tdp', $term)->disbursements()->create(['term' => $term, 'amount' => 7500, 'status' => 'pending']);
        $c = $this->cleared('ched-tdp', $term)->disbursements()->create(['term' => $term, 'amount' => 7500, 'status' => 'pending']);

        $this->actingAs($this->provider('ched'));
        $this->post("/provider/disbursements/{$a->id}/release", ['reference' => 'OR-1'])
            ->assertRedirect()->assertSessionHas('status', 'Juan Dela Cruz: stipend released.');
        $this->assertSame('released', $a->fresh()->status);
        $this->assertNotNull($a->fresh()->released_at);
        $this->assertSame('OR-1', $a->fresh()->reference);

        // Pressing Release again changes nothing (same date, same reference).
        $releasedAt = $a->fresh()->released_at;
        $this->post("/provider/disbursements/{$a->id}/release", ['reference' => 'OR-2'])
            ->assertSessionHas('notice', "Juan Dela Cruz's stipend was already released.");
        $this->assertEquals($releasedAt, $a->fresh()->released_at);
        $this->assertSame('OR-1', $a->fresh()->reference);

        // Released rows offer no button; there is no status dropdown and no On hold anywhere.
        $this->get('/provider/disbursements')->assertOk()
            ->assertDontSee('name="status"', false)
            ->assertDontSee('On hold')
            ->assertDontSee(route('provider.disbursements.release-one', $a->id), false)
            ->assertSee(route('provider.disbursements.release-one', $b->id), false);

        $this->post('/provider/disbursements/release', ['ids' => [$b->id, $c->id], 'reference' => 'BATCH-7'])->assertRedirect();
        $this->assertSame(['released', 'released'], [$b->fresh()->status, $c->fresh()->status]);
        $this->assertSame('BATCH-7', $c->fresh()->reference);

        $this->get('/provider/disbursements?status=released')->assertOk()->assertSee('BATCH-7');
    }

    public function test_a_document_the_scholar_never_uploaded_cannot_be_approved(): void
    {
        $term = AcademicTerm::current();
        $s = $this->scholar('ched-tdp');
        $cor = $s->requirements()->create(['term' => $term, 'name' => 'COR', 'status' => 'missing']);
        $pay = $s->disbursements()->create(['term' => $term, 'amount' => 7500, 'status' => 'pending']);
        $this->actingAs($this->provider('ched'));

        $this->patch("/provider/requirements/{$cor->id}", ['status' => 'approved'])->assertSessionHasErrors('status');
        $this->patch("/provider/requirements/{$cor->id}", ['status' => 'missing'])->assertSessionHasErrors('status');
        $this->assertSame('missing', $cor->fresh()->status);
        // So the stipend stays locked.
        $this->post("/provider/disbursements/{$pay->id}/release")->assertSessionHasErrors('release');
        $this->assertSame('pending', $pay->fresh()->status);
    }

    public function test_a_stipend_is_released_only_when_every_requirement_of_the_semester_is_approved(): void
    {
        $term = AcademicTerm::current();
        $s = $this->scholar('ched-tdp');
        $cor = $s->requirements()->create(['term' => $term, 'name' => 'COR', 'status' => 'approved']);
        $grades = $s->requirements()->create(['term' => $term, 'name' => 'Grades', 'status' => 'submitted', 'file_path' => 'x/g.pdf']);
        $s->requirements()->create(['term' => AcademicTerm::previous(), 'name' => 'Old', 'status' => 'missing']);   // another semester: ignored
        $pay = $s->disbursements()->create(['term' => $term, 'amount' => 7500, 'status' => 'pending']);
        $this->actingAs($this->provider('ched'));

        // Waiting for review is not enough: approved only (owner's rule).
        $this->post("/provider/disbursements/{$pay->id}/release")->assertSessionHasErrors('release');
        $this->assertSame('pending', $pay->fresh()->status);
        $this->post('/provider/disbursements/release', ['ids' => [$pay->id]])
            ->assertSessionHas('notice', fn ($m) => str_contains($m, 'Marked 0') && str_contains($m, '1 record skipped'));
        $this->assertSame('pending', $pay->fresh()->status);

        // The page shows the lock and offers no Release button for that row.
        $this->get('/provider/disbursements')->assertOk()
            ->assertSee('Locked: 1 of 2 requirements for '.$term.' not approved yet')
            ->assertSee("can't be released yet", false)
            ->assertDontSee('value="'.$pay->id.'" form="bulk-release"', false)
            ->assertDontSee(route('provider.disbursements.release-one', $pay->id), false);

        // Needs revision also blocks; approving everything opens the lock.
        $grades->update(['status' => 'needs_revision', 'review_note' => 'Blurry']);
        $this->post("/provider/disbursements/{$pay->id}/release")->assertSessionHasErrors('release');
        $this->patch("/provider/requirements/{$grades->id}", ['status' => 'approved'])->assertRedirect();
        $this->get('/provider/disbursements')->assertSee(route('provider.disbursements.release-one', $pay->id), false);
        $this->post("/provider/disbursements/{$pay->id}/release", ['reference' => 'OR-9'])->assertSessionHasNoErrors();
        $this->assertSame('released', $pay->fresh()->status);
        $this->assertSame('OR-9', $pay->fresh()->reference);
        $this->assertSame('approved', $cor->fresh()->status);
    }

    public function test_release_waits_for_the_semester_deadline_to_be_set(): void
    {
        $term = AcademicTerm::current();
        Scholarship::where('slug', 'ched-tdp')->update(['renewal_requirements' => json_encode(['COR'])]);
        $pay = $this->scholar('ched-tdp')->disbursements()->create(['term' => $term, 'amount' => 7500, 'status' => 'pending']);
        $this->actingAs($this->provider('ched'));

        $this->post("/provider/disbursements/{$pay->id}/release")
            ->assertSessionHasErrors(['release' => "Juan Dela Cruz's stipend can't be released yet: the deadline for the {$term} maintaining documents has not been set yet. Set it on the Requirements page first."]);

        // A scholarship with no maintaining documents at all has nothing to wait for.
        Scholarship::where('slug', 'ched-tdp')->update(['renewal_requirements' => json_encode([])]);
        $this->post("/provider/disbursements/{$pay->id}/release")->assertSessionHasNoErrors();
        $this->assertSame('released', $pay->fresh()->status);
    }

    public function test_the_migration_turns_on_hold_stipends_back_into_not_yet_released(): void
    {
        $term = AcademicTerm::current();
        $pay = $this->cleared('ched-tdp', $term)->disbursements()->create(['term' => $term, 'amount' => 7500, 'status' => 'pending']);
        DB::table('stipend_disbursements')->where('id', $pay->id)->update(['status' => 'on_hold']);

        (require database_path('migrations/2026_11_22_000001_remove_on_hold_stipends.php'))->up();

        $this->assertSame('pending', $pay->fresh()->status);
        $this->assertNull($pay->fresh()->released_at);
    }

    public function test_a_deadline_builds_the_checklist_from_the_maintaining_documents(): void
    {
        $tdp = Scholarship::where('slug', 'ched-tdp')->firstOrFail();
        $tdp->update(['renewal_requirements' => ['Certificate of Registration', 'Grades']]);
        $active = $this->scholar('ched-tdp');
        $terminated = $this->scholar('ched-tdp', ['status' => 'terminated']);
        $term = AcademicTerm::current();

        $this->actingAs($this->provider('ched'));
        $this->setDeadline('ched-tdp', $term, '2026-12-15')->assertRedirect();
        $this->setDeadline('ched-tdp', $term, '2026-12-15')
            ->assertSessionHas('status', fn ($m) => str_contains($m, 'Nothing changed'));   // idempotent

        $this->assertSame(2, $active->requirements()->where('term', $term)->count());
        $this->assertSame(0, $terminated->requirements()->count());
        $this->assertSame('missing', $active->requirements()->first()->status);
    }

    public function test_reviewing_a_requirement(): void
    {
        $s = $this->scholar('ched-tdp');
        $r = $s->requirements()->create(['term' => AcademicTerm::current(), 'name' => 'COR', 'status' => 'submitted', 'file_path' => 'scholar-requirements/1/a.pdf', 'original_name' => 'cor.pdf']);
        Storage::disk('local')->put('scholar-requirements/1/a.pdf', '%PDF-1.4 test');

        $this->actingAs($this->provider('ched'));
        $this->get("/provider/requirements/{$r->id}/file")->assertOk()->assertDownload('cor.pdf');

        $this->patch("/provider/requirements/{$r->id}", ['status' => 'needs_revision'])->assertSessionHasErrors('review_note');
        $this->patch("/provider/requirements/{$r->id}", ['status' => 'needs_revision', 'review_note' => 'Blurry'])->assertRedirect();
        $this->assertSame('needs_revision', $r->fresh()->status);
        $this->assertSame('Blurry', $r->fresh()->review_note);

        $this->patch("/provider/requirements/{$r->id}", ['status' => 'approved'])->assertRedirect();
        $this->assertSame('approved', $r->fresh()->status);
        $this->assertNull($r->fresh()->review_note);
    }
}
