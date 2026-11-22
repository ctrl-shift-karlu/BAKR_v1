<?php

namespace Tests\Feature;

use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use App\Models\ScholarshipMatch;
use App\Models\ScholarshipRevision;
use App\Models\User;
use Database\Seeders\ProviderAccountSeeder;
use Database\Seeders\ScholarshipSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/** Providers edit their scholarship; the public Scholarships page and matching follow. */
class ScholarshipEditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        foreach (array_keys(config('providers.accounts')) as $key) {
            config([
                "providers.accounts.{$key}.email" => "{$key}@provider.test",
                "providers.accounts.{$key}.password" => 'Provider-Test-Pass-2026',
            ]);
        }
        $this->seed(ScholarshipSeeder::class);
        $this->seed(ProviderAccountSeeder::class);
    }

    private function grab(): Scholarship
    {
        return Scholarship::where('slug', 'grabscholar')->firstOrFail();
    }

    /** Form payload that keeps everything as it is (what the edit form posts unchanged). */
    private function payload(Scholarship $s, array $changes = []): array
    {
        $rules = [];
        foreach ($s->rules as $i => $r) {
            $rules[$i] = ['label' => $r['label'], 'required' => ($r['required'] ?? false) ? '1' : '0'];
            if (array_key_exists('value', $r) && in_array($r['type'], ['gte', 'lte', 'income_lte'], true)) {
                $rules[$i]['value'] = (string) $r['value'];
            }
            if ($r['type'] === 'confirm') {
                $rules[$i]['confirm_note'] = $r['confirm_note'] ?? '';
            }
        }

        return array_replace_recursive([
            'overview' => $s->overview,
            'benefits' => implode("\n", $s->benefits),
            'documents' => implode("\n", $s->documents),
            'application_route' => $s->application_route,
            'window_note' => $s->window_note,
            'application_opens_on' => $s->application_opens_on?->format('Y-m-d'),
            'application_closes_on' => $s->application_closes_on?->format('Y-m-d'),
            'results_on' => $s->results_on?->format('Y-m-d'),
            'official_url' => $s->official_url,
            'rules' => $rules,
        ], $changes);
    }

    public function test_my_scholarships_is_read_only_until_a_section_edit_is_pressed(): void
    {
        $s = $this->grab();
        $this->actingAs(User::where('email', 'grab@provider.test')->first());

        $read = $this->get('/provider/scholarships')->assertOk();
        $read->assertSee($s->overview)->assertSee('edit=overview', false)->assertSee('edit=apply', false)
            ->assertDontSee('name="overview"', false)->assertDontSee('Save section');

        // Editing one section opens ONLY that section's form.
        $this->get('/provider/scholarships?s='.$s->id.'&edit=period')->assertOk()
            ->assertSee('name="application_closes_on"', false)->assertSee('Save section')->assertSee('Cancel')
            ->assertDontSee('name="overview"', false)->assertDontSee('name="official_url"', false)
            ->assertDontSee('name="benefits"', false)->assertDontSee('name="rules[', false);

        // A failed save comes back with that section open and what was typed.
        $url = '/provider/scholarships?s='.$s->id.'&edit=apply';
        $bad = ['section' => 'apply', 'application_route' => 'Typed but invalid', 'official_url' => 'not a url'];
        $this->from($url)->put("/provider/scholarships/{$s->id}", $bad)->assertRedirect($url);
        $this->followingRedirects()->from($url)->put("/provider/scholarships/{$s->id}", $bad)
            ->assertSee('Typed but invalid')->assertSee('Some fields need your attention');
    }

    public function test_the_header_link_opens_the_scholarships_official_website(): void
    {
        $s = $this->grab();
        $this->actingAs(User::where('email', 'grab@provider.test')->first());

        $this->get('/provider/scholarships')->assertOk()
            ->assertSee('href="'.$s->official_url.'"', false)->assertSee('Official website')
            ->assertDontSee('See it on the public page');

        // No official page of its own → the provider's official site; an unsafe value is never linked.
        $s->forceFill(['official_url' => null])->save();
        $this->assertSame(config('providers.accounts.grab.website'), $s->fresh()->officialWebsite());
        $s->forceFill(['official_url' => 'javascript:alert(1)'])->save();
        $this->assertSame(config('providers.accounts.grab.website'), $s->fresh()->officialWebsite());
    }

    public function test_a_section_saves_only_its_own_fields(): void
    {
        $s = $this->grab();
        $this->actingAs(User::where('email', 'grab@provider.test')->first());
        $overview = $s->overview;
        $benefits = $s->benefits;
        $rules = $s->rules;

        // The request even carries other fields, but the Overview section may only change the overview.
        $this->put("/provider/scholarships/{$s->id}", [
            'section' => 'overview', 'overview' => 'Only the overview changed.',
            'benefits' => 'Hacked benefit', 'official_url' => 'https://example.test', 'window_note' => 'Hacked note',
            'rules' => [0 => ['label' => 'Hacked rule', 'required' => '0']],
        ])->assertSessionHasNoErrors()->assertRedirect(route('provider.scholarships', ['s' => $s->id]).'#overview');

        $s->refresh();
        $this->assertSame('Only the overview changed.', $s->overview);
        $this->assertSame($benefits, $s->benefits);
        $this->assertSame($rules, $s->rules);
        $this->assertNotSame('Hacked note', $s->window_note);
        $this->assertNotSame('https://example.test', $s->official_url);

        // The Application period section changes the dates and leaves the overview alone.
        $this->put("/provider/scholarships/{$s->id}", ['section' => 'period', 'application_opens_on' => '2027-06-21', 'application_closes_on' => '2027-07-16', 'results_on' => '2027-08-19', 'window_note' => 'Interviews in July'])
            ->assertSessionHasNoErrors();
        $s->refresh();
        $this->assertSame('2027-07-16', $s->application_closes_on->format('Y-m-d'));
        $this->assertSame('Interviews in July', $s->window_note);
        $this->assertSame('Only the overview changed.', $s->overview);
        $this->assertNotSame($overview, $s->overview);

        $this->put("/provider/scholarships/{$s->id}", ['section' => 'nope'])->assertStatus(422);
    }

    public function test_a_provider_edit_shows_on_the_public_page_and_the_home_page(): void
    {
        $s = Scholarship::where('slug', 'dnsc-academic')->firstOrFail();
        $this->actingAs(User::where('email', 'dnsc@provider.test')->first());
        $this->put("/provider/scholarships/{$s->id}", $this->payload($s, ['overview' => 'Edited in My scholarships for the academic grant.']))
            ->assertSessionHasNoErrors();

        $this->get('/scholarships')->assertOk()->assertSee('Edited in My scholarships for the academic grant.');
        $this->get('/')->assertOk()->assertSee('Edited in My scholarships for the academic grant.')
            ->assertSee('id="dnscAcademicModal"', false)
            ->assertSee(collect($s->documents)->first());   // home pop-up documents come from the table too
    }

    public function test_provider_sets_application_dates_and_students_see_the_call_status(): void
    {
        $s = $this->grab();
        $this->assertSame('2026-07-17', $s->application_closes_on->format('Y-m-d'));   // from the official SY 2026 page
        $this->actingAs(User::where('email', 'grab@provider.test')->first());

        $this->travelTo(Carbon::parse('2027-06-25'));
        $this->put("/provider/scholarships/{$s->id}", $this->payload($s, ['application_opens_on' => '2027-06-21', 'application_closes_on' => '2027-07-16', 'results_on' => '2027-08-19']))
            ->assertSessionHasNoErrors()->assertSessionHas('status', fn ($m) => str_starts_with($m, 'Saved.'));

        $s->refresh();
        $this->assertSame('open', $s->callStatus());
        $this->assertSame('Open, closes Jul 16 (in 21 days)', $s->callLabel());
        $rev = ScholarshipRevision::where('scholarship_id', $s->id)->latest('id')->first();
        $this->assertSame('2027-07-16', $rev->after['application_closes_on']);   // dates are in the edit history
        $this->assertFalse((bool) $rev->rules_changed);

        $this->get('/scholarships')->assertOk()->assertSee('Open, closes Jul 16 (in 21 days)')->assertSee('Results on Aug 19, 2027');
        $this->travelBack();
    }

    public function test_call_status_is_open_upcoming_or_closed_and_never_guessed(): void
    {
        $s = new Scholarship(['application_opens_on' => '2027-06-21', 'application_closes_on' => '2027-07-16']);
        $day = fn (string $d) => Carbon::parse($d);

        $this->assertSame('upcoming', $s->callStatus($day('2027-06-20')));
        $this->assertSame('Opens Jun 21, 2027', $s->callLabel($day('2027-06-20')));
        $this->assertSame('open', $s->callStatus($day('2027-06-21')));
        $this->assertSame('Open, closes Jul 16 (today)', $s->callLabel($day('2027-07-16 23:00')));   // the deadline day still counts
        $this->assertSame('closed', $s->callStatus($day('2027-07-17')));
        $this->assertSame('Closed Jul 16, 2027', $s->callLabel($day('2027-07-17')));
        $this->assertNull((new Scholarship)->callStatus());   // no dates published: no status
    }

    public function test_dates_are_validated_and_scoped_to_the_provider(): void
    {
        $s = $this->grab();
        $this->actingAs(User::where('email', 'grab@provider.test')->first());
        $this->put("/provider/scholarships/{$s->id}", $this->payload($s, ['application_opens_on' => '2027-07-20', 'application_closes_on' => '2027-07-01']))
            ->assertSessionHasErrors('application_closes_on');
        $this->put("/provider/scholarships/{$s->id}", $this->payload($s, ['application_opens_on' => 'next week']))
            ->assertSessionHasErrors('application_opens_on');

        // DOST cannot change Grab's dates: the scholarship is looked up inside its own scope.
        $this->actingAs(User::where('email', 'dost@provider.test')->first());
        $this->put("/provider/scholarships/{$s->id}", $this->payload($s, ['application_closes_on' => '2030-01-01']))->assertNotFound();
        $this->assertSame('2026-07-17', $s->fresh()->application_closes_on->format('Y-m-d'));
    }

    public function test_public_page_is_rendered_from_the_database(): void
    {
        $response = $this->get('/scholarships')->assertOk();
        foreach (Scholarship::all() as $s) {
            $response->assertSee($s->name)->assertSee('id="modal-'.$s->slug.'"', false);
        }
        $response->assertSee('Showing all '.count(config('scholarships.scholarships')).' scholarships');
    }

    public function test_text_edits_show_on_the_public_page_and_are_recorded(): void
    {
        $s = $this->grab();
        $this->actingAs(User::where('email', 'grab@provider.test')->first());

        $this->put("/provider/scholarships/{$s->id}", $this->payload($s, [
            'overview' => 'A brand new overview written by Grab.',
            'benefits' => "Benefit one\nBenefit two",
            'window_note' => 'June 1 – July 15, 2027',
            // Maintaining documents have their own form now: posting the old field here changes nothing.
            'renewal_requirements' => "COR\nGrades",
        ]))->assertRedirect()->assertSessionHas('status', fn ($m) => str_contains($m, 'public Scholarships page'));

        $s->refresh();
        $this->assertSame(['Benefit one', 'Benefit two'], $s->benefits);
        $this->assertSame([], $s->maintainingDocuments());

        $rev = ScholarshipRevision::firstOrFail();
        $this->assertFalse($rev->rules_changed);
        $this->assertSame('A brand new overview written by Grab.', $rev->after['overview']);

        $this->get('/scholarships')->assertOk()
            ->assertSee('A brand new overview written by Grab.')
            ->assertSee('Benefit two')
            ->assertSee('June 1 – July 15, 2027');
    }

    public function test_maintaining_documents_have_their_own_form_with_per_document_settings(): void
    {
        $s = $this->grab();
        $this->actingAs(User::where('email', 'grab@provider.test')->first());

        $html = $this->get("/provider/scholarships?s={$s->id}&documents=1")->assertOk()->getContent();
        $this->assertStringContainsString('action="'.route('provider.scholarships.documents', $s->id).'"', $html);
        $this->assertStringContainsString('data-doc-template', $html);

        $this->put("/provider/scholarships/{$s->id}/documents", ['docs' => [
            ['name' => 'Certificate of Registration', 'note' => 'With the registrar\'s seal.', 'types' => ['pdf'], 'max_mb' => '2', 'required' => '1'],
            ['name' => 'Leave of absence form', 'types' => ['pdf', 'docx'], 'max_mb' => '10', 'required' => '0'],
            ['name' => '', 'types' => ['pdf'], 'max_mb' => '5', 'required' => '1'],                       // blank new row: skipped
            ['name' => 'Old item', 'types' => ['pdf'], 'max_mb' => '5', 'required' => '1', 'remove' => '1'], // removed
        ]])->assertRedirect()->assertSessionHas('status', fn ($m) => str_contains($m, 'Maintaining documents saved'));

        $docs = $s->fresh()->maintainingDocuments();
        $this->assertSame(['Certificate of Registration', 'Leave of absence form'], array_column($docs, 'name'));
        $this->assertSame(['pdf'], $docs[0]['types']);
        $this->assertSame(2, $docs[0]['max_mb']);
        $this->assertSame('With the registrar\'s seal.', $docs[0]['note']);
        $this->assertTrue($docs[0]['required']);
        $this->assertSame(['pdf', 'docx'], $docs[1]['types']);
        $this->assertFalse($docs[1]['required']);
        $this->assertSame(1, ScholarshipRevision::count());

        $this->get("/provider/scholarships?s={$s->id}")->assertOk()
            ->assertSee('Certificate of Registration')->assertSee('Up to 2 MB')->assertSee('Only if it applies')->assertSee('PDF or Word (DOCX)');

        // Every document needs a file type, names can't repeat, unknown types and sizes are refused.
        $this->put("/provider/scholarships/{$s->id}/documents", ['docs' => [['name' => 'COR', 'max_mb' => '5', 'required' => '1']]])->assertSessionHasErrors('docs.0.types');
        $this->put("/provider/scholarships/{$s->id}/documents", ['docs' => [
            ['name' => 'COR', 'types' => ['pdf'], 'max_mb' => '5'], ['name' => 'cor', 'types' => ['pdf'], 'max_mb' => '5'],
        ]])->assertSessionHasErrors('docs.1.name');
        $this->put("/provider/scholarships/{$s->id}/documents", ['docs' => [['name' => 'COR', 'types' => ['exe'], 'max_mb' => '5']]])->assertSessionHasErrors('docs.0.types.0');
        $this->put("/provider/scholarships/{$s->id}/documents", ['docs' => [['name' => 'COR', 'types' => ['pdf'], 'max_mb' => '500']]])->assertSessionHasErrors('docs.0.max_mb');
        $this->assertCount(2, $s->fresh()->maintainingDocuments());
    }

    public function test_providers_without_requirements_have_no_documents_form(): void
    {
        $dnsc = Scholarship::where('slug', 'dnsc-academic')->firstOrFail();
        $this->actingAs(User::where('email', 'dnsc@provider.test')->first());

        $this->put("/provider/scholarships/{$dnsc->id}/documents", ['docs' => [['name' => 'X', 'types' => ['pdf'], 'max_mb' => '5']]])->assertNotFound();
        $this->get("/provider/scholarships?s={$dnsc->id}&documents=1")->assertOk()->assertDontSee('data-doc-form', false)->assertSee('in person at the official DNSC scholarship office');
        // Another provider's scholarship is not found either.
        $this->actingAs(User::where('email', 'grab@provider.test')->first())
            ->put('/provider/scholarships/'.Scholarship::where('slug', 'dost-sei')->value('id').'/documents', ['docs' => []])->assertNotFound();
    }

    public function test_saving_without_changes_does_nothing(): void
    {
        $s = $this->grab();
        $this->actingAs(User::where('email', 'grab@provider.test')->first());

        $this->put("/provider/scholarships/{$s->id}", $this->payload($s))->assertSessionHas('status', 'No changes to save.');
        $this->assertSame(0, ScholarshipRevision::count());
    }

    public function test_threshold_edit_rematches_students(): void
    {
        $s = $this->grab();
        $gwaIndex = collect($s->rules)->search(fn ($r) => $r['key'] === 'gwa');

        // A current application whose stored match for Grab says "eligible".
        $student = new User(['name' => 'S', 'email' => 's@example.test', 'password' => 'x']);
        $student->role = 'student';
        $student->save();
        $app = ScholarshipApplication::create(['user_id' => $student->id, 'version' => 1, 'is_current' => true, 'answers' => [], 'submitted_at' => now()]);
        ScholarshipMatch::create(['scholarship_application_id' => $app->id, 'scholarship_id' => $s->id, 'status' => 'eligible', 'score' => 100, 'checks' => []]);

        $this->actingAs(User::where('email', 'grab@provider.test')->first());
        $this->put("/provider/scholarships/{$s->id}", $this->payload($s, [
            'rules' => [$gwaIndex => ['value' => '92', 'label' => 'Grade 12 grade of at least 92%']],
        ]))->assertSessionHas('status', fn ($m) => str_contains($m, 're-matched 1 student'));

        $rule = Scholarship::find($s->id)->rules[$gwaIndex];
        $this->assertSame(92, $rule['value']);
        $this->assertSame('gte', $rule['type']);                 // type untouched
        $this->assertSame('shs_gwa_percent', $rule['fact']);     // fact untouched
        $this->assertTrue(ScholarshipRevision::firstOrFail()->rules_changed);

        // The stale "eligible" row was replaced by a fresh evaluation (empty answers → not eligible / needs info).
        $this->assertNotSame('eligible', ScholarshipMatch::where('scholarship_application_id', $app->id)->where('scholarship_id', $s->id)->value('status'));

        $this->get('/scholarships')->assertSee('Grade 12 grade of at least 92%');
    }

    public function test_rule_type_fact_and_structure_cannot_be_changed(): void
    {
        $s = $this->grab();
        $before = $s->rules;
        $this->actingAs(User::where('email', 'grab@provider.test')->first());

        $payload = $this->payload($s);
        $payload['rules'][0]['type'] = 'confirm';
        $payload['rules'][0]['fact'] = 'income';
        $payload['rules'][0]['value'] = 'anything';   // citizen rule is "eq": value is not editable
        $payload['rules'][99] = ['label' => 'Injected rule', 'required' => '1'];

        $this->put("/provider/scholarships/{$s->id}", $payload)->assertSessionHas('status', 'No changes to save.');
        $this->assertSame($before, $s->fresh()->rules);
    }

    public function test_invalid_rule_values_are_rejected(): void
    {
        $s = $this->grab();
        $gwaIndex = collect($s->rules)->search(fn ($r) => $r['key'] === 'gwa');
        $this->actingAs(User::where('email', 'grab@provider.test')->first());

        $this->put("/provider/scholarships/{$s->id}", $this->payload($s, ['rules' => [$gwaIndex => ['value' => '150']]]))
            ->assertSessionHasErrors("rules.{$gwaIndex}.value");
        $this->put("/provider/scholarships/{$s->id}", $this->payload($s, ['rules' => [0 => ['label' => '']]]))
            ->assertSessionHasErrors('rules.0.label');
        $this->put("/provider/scholarships/{$s->id}", $this->payload($s, ['official_url' => 'javascript:alert(1)']))
            ->assertSessionHasErrors('official_url');

        $this->assertSame(0, ScholarshipRevision::count());
    }

    public function test_income_cap_must_be_whole_pesos(): void
    {
        $s = Scholarship::where('slug', 'cebuana-clfi')->firstOrFail();
        $i = collect($s->rules)->search(fn ($r) => $r['type'] === 'income_lte');
        $this->actingAs(User::where('email', 'cebuana@provider.test')->first());

        $this->put("/provider/scholarships/{$s->id}", $this->payload($s, ['rules' => [$i => ['value' => '350000.50']]]))
            ->assertSessionHasErrors("rules.{$i}.value");
        $this->put("/provider/scholarships/{$s->id}", $this->payload($s, ['rules' => [$i => ['value' => '300000']]]))
            ->assertSessionHasNoErrors();
        $this->assertSame(300000, $s->fresh()->rules[$i]['value']);
    }
}
