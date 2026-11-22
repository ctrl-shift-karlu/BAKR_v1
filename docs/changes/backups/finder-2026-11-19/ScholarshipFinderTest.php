<?php

namespace Tests\Feature;

use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use App\Models\ScholarshipMatch;
use App\Models\User;
use Database\Seeders\ScholarshipSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** The public Scholarship Finder uses the real questions and rule engine, and saves nothing. */
class ScholarshipFinderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ScholarshipSeeder::class);
    }

    /** A currently enrolled 2nd-year whose family earns ₱350,001–₱400,000. */
    private function answers(array $override = []): array
    {
        return $override + [
            'citizenship' => 'filipino',
            'enrollment_status' => 'currently_enrolled',
            'year_level' => '2',
            'campus_id' => '1',
            'program_id' => '1',
            'previous_degree' => 'no',
            'previous_units' => 'no',
            'within_years' => 'yes',
            'top_five' => 'no',
            'college_gwa' => '88',
            'college_gwa_scale' => 'percent',
            'failing_grade' => 'no',
            'disciplinary' => 'no',
            'dost_exam' => 'not_taken',
            'household_income' => '350k_400k',
            'existing_assistance' => ['none'],
            'farm_fisher_family' => ['none'],
            'willing_student_assistant' => 'no',
            'cebuana_client' => 'no',
            'cebuana_microsavings' => 'no',
        ];
    }

    /** The HTML of one scholarship's result card. */
    private function card(string $html, string $slug): string
    {
        $start = strpos($html, 'id="title-'.$slug.'"');
        $this->assertNotFalse($start, "No result card for {$slug}");

        return substr($html, $start, strpos($html, '</article>', $start) - $start);
    }

    public function test_finder_shows_the_real_questionnaire(): void
    {
        $this->get('/scholarship-finder')->assertOk()
            ->assertSee('Combined yearly gross income of your parents or guardian')
            ->assertSee('₱350,001 – ₱400,000')
            ->assertSee('Nothing you enter here is saved')
            ->assertDontSee('privacy_consent', false);
    }

    public function test_income_is_checked_against_the_real_cap_not_an_invented_one(): void
    {
        $html = $this->post('/scholarship-finder', $this->answers())->assertOk()->getContent();

        // The 2026 TDP guidelines set no income cap, so TDP never turns a family away for income;
        // need is confirmed with a Certificate of Indigency instead.
        $tdp = $this->card($html, 'ched-tdp');
        $this->assertStringNotContainsString('sp-pill not_eligible', $tdp);
        $this->assertStringNotContainsString('₱400,000 or less', $tdp);
        $this->assertMatchesRegularExpression('/sp-check confirm">.*?Certificate of Indigency/s', $tdp);

        // Matches are listed, grouped by status; "not a match right now" is left out.
        $this->assertStringNotContainsString('sp-pill not_eligible', $html);
        $this->assertStringNotContainsString('Not eligible', $html);
    }

    public function test_nothing_is_saved(): void
    {
        $this->post('/scholarship-finder', $this->answers())->assertOk();

        $this->assertSame(0, ScholarshipApplication::count());
        $this->assertSame(0, ScholarshipMatch::count());
        $this->assertSame(0, User::count());
    }

    public function test_answers_are_validated_like_the_portal(): void
    {
        $this->from('/scholarship-finder')
            ->post('/scholarship-finder', $this->answers(['household_income' => 'a-lot', 'citizenship' => '']))
            ->assertRedirect('/scholarship-finder')
            ->assertSessionHasErrors(['household_income', 'citizenship']);
    }

    public function test_finder_uses_the_rules_in_the_database_so_provider_edits_apply(): void
    {
        $tdp = Scholarship::where('slug', 'ched-tdp')->firstOrFail();
        $rules = $tdp->rules;
        foreach ($rules as $i => $r) {
            if ($r['key'] === 'citizen') {
                $rules[$i]['value'] = 'other';   // a provider-side rule change the matcher must pick up
            }
        }
        DB::table('scholarships')->where('id', $tdp->id)->update(['rules' => json_encode($rules)]);

        // With the changed rule TDP is no longer a match, so it is left out of the results.
        $html = $this->post('/scholarship-finder', $this->answers())->assertOk()->getContent();
        $this->assertStringNotContainsString('id="title-ched-tdp"', $html);
        $this->assertStringContainsString('id="title-dnsc-academic"', $html);   // other matches are still listed
    }

    public function test_signed_in_students_are_pointed_to_their_saved_matches(): void
    {
        $u = new User;
        $u->name = 'S';
        $u->email = 's@example.test';
        $u->password = 'x';
        $u->role = 'student';
        $u->save();

        $this->actingAs($u)->get('/scholarship-finder')->assertOk()->assertSee('open my matches');
    }
}
