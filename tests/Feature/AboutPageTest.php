<?php

namespace Tests\Feature;

use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use App\Models\ScholarshipMatch;
use App\Models\User;
use App\Services\PublicAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * About page (2026-11-21): the matching chart counts every outcome, the team names, no graduate stories,
 * and the star-rating feedback form (stored as contact_messages topic feedback).
 */
class AboutPageTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $over = []): array
    {
        return array_merge([
            'rating' => 4, 'aspects' => ['matching', 'mobile'],
            'name' => 'Ana Reyes', 'email' => 'ana@example.com', 'message' => 'The matching was clear.',
        ], $over);
    }

    public function test_the_page_shows_the_team_and_no_placeholders(): void
    {
        $this->get('/about')->assertOk()
            ->assertSee('The people behind BAKR')
            ->assertSee('Karl Angelo T. Gabaes')->assertSee('Rochelle D. Abella')->assertSee('Marielle Joie Javier')
            ->assertSee('images/img/team/member-3.jpg', false)
            ->assertDontSee('Scholars who made it')
            ->assertDontSee('We will add the details here.')
            ->assertDontSee('Full name')
            ->assertSee('id="feedbackForm"', false)
            ->assertSee(route('about.feedback'), false);
    }

    public function test_feedback_with_stars_is_saved_and_emailed(): void
    {
        Mail::fake();
        $this->post('/about/feedback', $this->payload(['share_publicly' => '1']))
            ->assertRedirect(route('about').'#feedback')->assertSessionHas('feedback_sent');

        $m = ContactMessage::sole();
        $this->assertSame('feedback', $m->topic);
        $this->assertSame(4, (int) $m->rating);
        $this->assertSame(['matching', 'mobile'], $m->aspects);
        $this->assertTrue($m->share_publicly);
        $this->assertNull($m->approved_at);   // never public until the BAKR team approves it
        Mail::assertSent(ContactMessageReceived::class);
        $this->assertNotNull($m->emailed_at);
    }

    public function test_the_comment_is_optional_but_the_rating_is_not(): void
    {
        Mail::fake();
        $this->post('/about/feedback', $this->payload(['rating' => null]))->assertSessionHasErrors('rating');
        $this->post('/about/feedback', $this->payload(['rating' => 6]))->assertSessionHasErrors('rating');
        $this->post('/about/feedback', $this->payload(['aspects' => ['hacking']]))->assertSessionHasErrors('aspects.0');
        $this->post('/about/feedback', $this->payload(['email' => 'nope']))->assertSessionHasErrors('email')
            ->assertRedirect(route('about').'#feedback');
        $this->assertDatabaseCount('contact_messages', 0);

        $this->post('/about/feedback', $this->payload(['message' => '', 'aspects' => []]))->assertSessionHas('feedback_sent');
        $m = ContactMessage::sole();
        $this->assertSame('', $m->message);
        $this->assertNull($m->aspects);
    }

    public function test_honeypot_stores_nothing(): void
    {
        Mail::fake();
        $this->post('/about/feedback', $this->payload(['website' => 'http://spam']))->assertSessionHas('feedback_sent');
        $this->assertDatabaseCount('contact_messages', 0);
        Mail::assertNothingSent();
    }

    public function test_only_approved_shared_ratings_are_summarised(): void
    {
        ContactMessage::create(['topic' => 'feedback', 'name' => 'Ben Cruz', 'email' => 'b@x.com', 'message' => 'Not approved yet', 'rating' => 1, 'share_publicly' => true]);
        ContactMessage::create(['topic' => 'feedback', 'name' => 'Lea Santos', 'email' => 'l@x.com', 'message' => 'Very helpful for my TES', 'rating' => 5, 'share_publicly' => true, 'approved_at' => now()]);

        $this->get('/about')->assertOk()
            ->assertSee('Very helpful for my TES')->assertSee('From 1 shared rating')
            ->assertDontSee('Not approved yet')->assertDontSee('l@x.com');
    }

    public function test_the_matching_chart_counts_every_outcome_of_active_scholarships(): void
    {
        $base = ['sector' => 'private', 'provider' => 'p', 'overview' => 'x', 'benefits' => [], 'documents' => [], 'rules' => []];
        $active = Scholarship::forceCreate($base + ['slug' => 'a', 'name' => 'Active A', 'is_active' => true]);
        $inactive = Scholarship::forceCreate($base + ['slug' => 'b', 'name' => 'Old B', 'is_active' => false]);

        foreach (range(1, 12) as $i) {
            $user = User::forceCreate(['name' => "S$i", 'email' => "s$i@example.test", 'password' => 'x', 'role' => 'student', 'status' => 'active']);
            $app = ScholarshipApplication::forceCreate(['user_id' => $user->id, 'answers' => [], 'is_current' => true, 'version' => 1]);
            ScholarshipMatch::forceCreate(['scholarship_application_id' => $app->id, 'scholarship_id' => $active->id, 'status' => $i <= 10 ? 'not_eligible' : 'eligible', 'score' => 0, 'checks' => []]);
            ScholarshipMatch::forceCreate(['scholarship_application_id' => $app->id, 'scholarship_id' => $inactive->id, 'status' => 'eligible', 'score' => 0, 'checks' => []]);
        }

        $out = app(PublicAnalyticsService::class)->summary()['charts']['outcomes'];
        $rows = collect($out['rows'])->keyBy('label');

        $this->assertSame(12, $out['total']);                       // the inactive scholarship is left out
        $this->assertSame(10, $rows['Not eligible']['count']);       // "Not eligible" is part of the picture now
        $this->assertSame(83, $rows['Not eligible']['percent']);
        $this->assertTrue($rows['Eligible']['hidden']);              // 2 results: under the privacy minimum
        $this->assertSame('gray', $rows['Not eligible']['tone']);
        $this->get('/about')->assertOk()->assertSee('Based on 12 results from 12 profiles');
    }
}
