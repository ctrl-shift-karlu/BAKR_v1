<?php

namespace Tests\Feature;

use App\Models\Scholar;
use App\Models\Scholarship;
use App\Models\ScholarshipDeadline;
use App\Models\User;
use App\Notifications\ScholarUpdate;
use App\Support\AcademicTerm;
use Database\Seeders\ProviderAccountSeeder;
use Database\Seeders\ScholarshipSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Owner, 2026-11-21:
 *  - every scholar has an accurate "Scholar since" semester, picked when they are added: no maintaining
 *    documents and no stipend for a semester before it;
 *  - every scholarship has its OWN maintaining-documents deadline for EACH semester, editable on the
 *    Requirements page; saving it opens the checklist and e-mails that scholarship's scholars (and again
 *    when it changes). The old "Open / Update checklist" button is gone.
 */
class SemesterDeadlineTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'Provider-Test-Pass-2026';

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Mail::fake();
        foreach (array_keys(config('providers.accounts')) as $key) {
            config(["providers.accounts.{$key}.email" => "{$key}@provider.test", "providers.accounts.{$key}.password" => self::PASSWORD]);
        }
        $this->seed(ScholarshipSeeder::class);
        $this->seed(ProviderAccountSeeder::class);
        Scholarship::whereIn('slug', ['ched-tdp', 'ched-tes'])->update(['renewal_requirements' => json_encode(['COR', 'Grades'])]);
    }

    private function provider(string $key = 'ched'): User
    {
        return User::where('email', "{$key}@provider.test")->firstOrFail();
    }

    private function sid(string $slug): int
    {
        return (int) Scholarship::where('slug', $slug)->value('id');
    }

    private function scholar(string $slug, array $attrs = []): Scholar
    {
        $u = new User;
        $u->name = 'Ana Reyes';
        $u->email = uniqid('s').'@example.test';
        $u->password = Hash::make('ScholarPass123');
        $u->role = 'scholar';
        $u->status = 'active';
        $u->save();

        return Scholar::create($attrs + ['user_id' => $u->id, 'scholarship_id' => $this->sid($slug), 'status' => 'active',
            'started_term' => AcademicTerm::current(), 'stipend_amount' => 7500]);
    }

    private function setDeadline(string $slug, string $term, string $due)
    {
        return $this->post('/provider/requirements/deadline', ['scholarship_id' => $this->sid($slug), 'term' => $term, 'due_on' => $due]);
    }

    public function test_academic_term_helpers_compare_semesters(): void
    {
        $now = AcademicTerm::current();
        $this->assertSame(AcademicTerm::indexOf($now) - 1, AcademicTerm::indexOf(AcademicTerm::previous()));
        $this->assertNull(AcademicTerm::indexOf('2019'));
        $this->assertNull(AcademicTerm::indexOf('AY 2026–2028 · 1st Semester'));
        $this->assertSame([$now, AcademicTerm::previous()], array_slice(AcademicTerm::upTo($now), 0, 2));
        $this->assertSame([], AcademicTerm::upTo('Midyear'));

        $start = AcademicTerm::startOptions();
        $this->assertCount(10, $start);
        $this->assertSame($now, $start[1]);   // the next semester, then the current one, then eight before it
        $this->assertSame(AcademicTerm::indexOf($now) + 1, AcademicTerm::indexOf($start[0]));
    }

    public function test_each_scholarship_has_its_own_deadline_per_semester_and_scholars_are_emailed(): void
    {
        Notification::fake();
        $term = AcademicTerm::current();
        $tdp = $this->scholar('ched-tdp');
        $tes = $this->scholar('ched-tes');
        $later = $this->scholar('ched-tdp', ['started_term' => AcademicTerm::startOptions()[0]]);   // starts next semester
        $this->actingAs($this->provider());

        $this->setDeadline('ched-tdp', $term, '2026-12-10')->assertSessionHas('status', fn ($m) => str_contains($m, '1 scholar notified'));

        // Only TDP's scholars who had started get the checklist; TES has no deadline yet.
        $this->assertSame(2, $tdp->requirements()->where('term', $term)->count());
        $this->assertSame(0, $tes->requirements()->count());
        $this->assertSame(0, $later->requirements()->count());
        $this->assertSame('2026-12-10', ScholarshipDeadline::where('scholarship_id', $this->sid('ched-tdp'))->value('due_on')->toDateString());

        // The scholar is told in BAKR and by e-mail.
        Notification::assertSentTo($tdp->user, ScholarUpdate::class, fn ($n) => $n->kind === 'deadline' && str_contains($n->title, 'Dec 10, 2026'));
        Notification::assertSentOnDemand(ScholarUpdate::class, fn ($n, $channels, $to) => $to->routes['mail'] === $tdp->contactEmail() && $channels === ['mail']);
        Notification::assertNotSentTo($tes->user, ScholarUpdate::class);
        Notification::assertNotSentTo($later->user, ScholarUpdate::class);

        // TES gets a different date; TDP's stays.
        $this->setDeadline('ched-tes', $term, '2027-01-20')->assertSessionHasNoErrors();
        $this->assertSame('2027-01-20', $tes->requirements()->first()->due_on->toDateString());
        $this->assertSame('2026-12-10', $tdp->requirements()->first()->due_on->toDateString());

        // Changing a deadline tells the scholars again; saving the same date does not.
        Notification::fake();
        $this->setDeadline('ched-tdp', $term, '2026-12-20');
        Notification::assertSentTo($tdp->user, ScholarUpdate::class, fn ($n) => $n->title === 'New deadline: Dec 20, 2026');
        $this->assertSame('2026-12-20', $tdp->requirements()->first()->due_on->toDateString());
        Notification::fake();
        $this->setDeadline('ched-tdp', $term, '2026-12-20');
        Notification::assertNothingSent();

        // The same scholarship's next semester is a separate deadline.
        $this->setDeadline('ched-tdp', AcademicTerm::previous(), '2026-06-30')->assertSessionHasNoErrors();
        $this->assertSame(2, ScholarshipDeadline::where('scholarship_id', $this->sid('ched-tdp'))->count());
    }

    public function test_a_deadline_is_only_for_the_providers_own_scholarship_with_documents(): void
    {
        $this->actingAs($this->provider());
        $term = AcademicTerm::current();

        $this->setDeadline('dost-sei', $term, '2026-12-10')->assertNotFound();   // another provider's scholarship
        Scholarship::where('slug', 'ched-tdp')->update(['renewal_requirements' => json_encode([])]);
        $this->setDeadline('ched-tdp', $term, '2026-12-10')->assertSessionHasErrors('due_on');
        $this->assertSame(0, ScholarshipDeadline::count());
        $this->get('/provider/requirements')->assertOk()->assertSee('No maintaining documents yet');
    }

    public function test_scholar_since_decides_the_first_checklist_and_the_first_stipend(): void
    {
        $term = AcademicTerm::current();
        $next = AcademicTerm::startOptions()[0];
        $now = $this->scholar('ched-tdp');
        $later = $this->scholar('ched-tdp', ['started_term' => $next]);
        $this->actingAs($this->provider());

        // No stipend record for a semester before they started.
        $this->post('/provider/disbursements/generate', ['term' => $term, 'release_type' => 'semester'])->assertRedirect();
        $this->assertSame(1, $now->disbursements()->count());
        $this->assertSame(0, $later->disbursements()->count());

        // A record made before "Scholar since" was moved stays locked.
        $pay = $later->disbursements()->create(['term' => $term, 'amount' => 7500, 'status' => 'pending']);
        $this->post("/provider/disbursements/{$pay->id}/release")
            ->assertSessionHasErrors(['release' => "Ana Reyes's stipend can't be released: the scholar only started in {$next}. Check \"Scholar since\" on their profile."]);
        $this->assertSame('pending', $pay->fresh()->status);

        // Their own page says when it starts.
        $this->actingAs($later->user)->get('/scholar/requirements')->assertOk()->assertSee('Your scholarship starts in '.$next, false);
    }

    public function test_a_scholar_added_after_the_deadline_gets_the_checklists_since_they_started(): void
    {
        $term = AcademicTerm::current();
        $this->actingAs($this->provider());
        $this->setDeadline('ched-tdp', AcademicTerm::previous(), '2026-06-30');
        $this->setDeadline('ched-tdp', $term, '2026-12-10');

        $person = ['scholarship_id' => $this->sid('ched-tdp'), 'scholar_code' => 'TDP-9', 'first_name' => 'Ben', 'last_name' => 'Cruz', 'student_number' => '2024-0009',
            'email' => 'ben@example.test', 'campus' => 'Main (Panabo Campus)', 'program' => 'BS Information Technology', 'year_level' => '2',
            'birthdate' => '2005-03-14', 'gender' => 'male', 'mobile' => '0917 123 4567', 'address_street' => 'Purok 3, Gredu', 'address_city' => 'Panabo City', 'address_province' => 'Davao del Norte'];

        // Started this semester: only this semester's checklist, due on its deadline.
        $this->post('/provider/scholars', $person + ['started_term' => $term])->assertSessionHasNoErrors();
        $ben = Scholar::where('scholar_code', 'TDP-9')->firstOrFail();
        $this->assertSame($term, $ben->started_term);
        $this->assertSame([$term], $ben->requirements()->distinct()->pluck('term')->all());
        $this->assertSame('2026-12-10', $ben->requirements()->first()->due_on->toDateString());

        // Started last semester: both semesters.
        $this->post('/provider/scholars', ['scholar_code' => 'TDP-10', 'student_number' => '2024-0010', 'email' => 'cy@example.test', 'started_term' => AcademicTerm::previous()] + $person)->assertSessionHasNoErrors();
        $cy = Scholar::where('scholar_code', 'TDP-10')->firstOrFail();
        $this->assertSame(4, $cy->requirements()->count());

        // A semester that is not offered is refused.
        $this->post('/provider/scholars', ['scholar_code' => 'TDP-11', 'student_number' => '2024-0011', 'email' => 'di@example.test', 'started_term' => 'AY 1999–2000 · 1st Semester'] + $person)
            ->assertSessionHasErrors('started_term');
    }

    public function test_moving_scholar_since_later_removes_only_empty_earlier_items(): void
    {
        $prev = AcademicTerm::previous();
        $term = AcademicTerm::current();
        $s = $this->scholar('ched-tdp', ['started_term' => $prev]);
        $this->actingAs($this->provider());
        $this->setDeadline('ched-tdp', $prev, '2026-06-30');
        $this->setDeadline('ched-tdp', $term, '2026-12-10');
        $this->assertSame(4, $s->requirements()->count());
        $s->requirements()->where('term', $prev)->where('name', 'COR')->update(['status' => 'submitted', 'file_path' => 'scholar-requirements/1/cor.pdf']);

        $this->patch("/provider/scholars/{$s->id}", ['section' => 'scholarship', 'started_term' => $term, 'stipend_amount' => '7500'])
            ->assertSessionHasNoErrors()->assertSessionHas('status', fn ($m) => str_contains($m, '1 empty checklist item'));

        $this->assertSame($term, $s->fresh()->started_term);
        $this->assertSame(['COR'], $s->requirements()->where('term', $prev)->pluck('name')->all());   // the uploaded file stays
        $this->assertSame(2, $s->requirements()->where('term', $term)->count());

        // Moving it back gives the earlier semester's checklist again.
        $this->patch("/provider/scholars/{$s->id}", ['section' => 'scholarship', 'started_term' => $prev, 'stipend_amount' => '7500'])->assertSessionHasNoErrors();
        $this->assertSame(2, $s->requirements()->where('term', $prev)->count());
    }

    public function test_the_checklist_follows_new_documents_and_reinstated_scholars(): void
    {
        $term = AcademicTerm::current();
        $s = $this->scholar('ched-tdp');
        $gone = $this->scholar('ched-tdp', ['status' => 'terminated']);
        $this->actingAs($this->provider());
        $this->setDeadline('ched-tdp', $term, '2026-12-10');
        $this->assertSame(0, $gone->requirements()->count());

        // A document added under My scholarships joins the current semester's checklist.
        $this->put('/provider/scholarships/'.$this->sid('ched-tdp').'/documents', ['docs' => [
            ['name' => 'COR', 'types' => ['pdf'], 'max_mb' => '5', 'required' => '1'],
            ['name' => 'Grades', 'types' => ['pdf'], 'max_mb' => '5', 'required' => '1'],
            ['name' => 'Enrolment form', 'types' => ['pdf'], 'max_mb' => '5', 'required' => '1'],
        ]])->assertSessionHas('status', fn ($m) => str_contains($m, '1 new checklist item'));
        $this->assertSame('2026-12-10', $s->requirements()->where('name', 'Enrolment form')->firstOrFail()->due_on->toDateString());

        // Reinstating gives the checklist opened in the meantime.
        $this->patch("/provider/scholars/{$gone->id}/status", ['status' => 'active'])->assertRedirect();
        $this->assertSame(3, $gone->requirements()->where('term', $term)->count());
    }

    public function test_the_migration_backfills_deadlines_and_scholar_since(): void
    {
        Schema::drop('scholarship_deadlines');
        $prev = AcademicTerm::previous();
        $old = $this->scholar('ched-tdp', ['started_term' => null]);
        $old->requirements()->create(['term' => $prev, 'name' => 'COR', 'status' => 'approved', 'due_on' => '2026-05-31']);
        $odd = $this->scholar('ched-tes', ['started_term' => null]);
        DB::table('scholars')->where('id', $odd->id)->update(['started_term' => '2019']);

        (require database_path('migrations/2026_11_21_000003_create_scholarship_deadlines.php'))->up();

        $this->assertSame('2026-05-31', ScholarshipDeadline::where('scholarship_id', $this->sid('ched-tdp'))->where('term', $prev)->value('due_on')->toDateString());
        $this->assertSame($prev, $old->fresh()->started_term);                     // the earliest of their records
        $this->assertSame(AcademicTerm::current(), $odd->fresh()->started_term);   // not a semester: the one they were added in
    }
}
