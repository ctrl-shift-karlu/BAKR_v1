<?php

namespace Tests\Feature;

use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $over = []): array
    {
        return array_merge([
            'topic' => 'question', 'name' => 'Ana Reyes', 'email' => 'ana@example.com',
            'message' => 'How do I know which scholarship fits me?',
        ], $over);
    }

    public function test_message_is_saved_and_emailed(): void
    {
        Mail::fake();
        $this->post('/contact', $this->payload())->assertRedirect(url('contact').'#message')->assertSessionHas('contact_sent');

        $this->assertDatabaseCount('contact_messages', 1);
        Mail::assertSent(ContactMessageReceived::class, fn ($m) => $m->hasTo(config('bakr.contact_to')) && $m->hasReplyTo('ana@example.com'));
        $this->assertNotNull(ContactMessage::first()->emailed_at);
    }

    public function test_message_is_kept_when_mail_fails(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('smtp down'));
        $this->post('/contact', $this->payload())->assertSessionHas('contact_sent');

        $this->assertNull(ContactMessage::first()->emailed_at);
    }

    public function test_validation_and_honeypot(): void
    {
        Mail::fake();
        $this->post('/contact', $this->payload(['email' => 'nope', 'message' => 'short']))->assertSessionHasErrors(['email', 'message']);
        $this->post('/contact', $this->payload(['website' => 'http://spam']))->assertSessionHas('contact_sent');   // bot sees success, nothing is stored
        $this->assertDatabaseCount('contact_messages', 0);
        Mail::assertNothingSent();
    }

    public function test_only_approved_shared_feedback_is_public(): void
    {
        ContactMessage::create(['topic' => 'feedback', 'name' => 'A', 'email' => 'a@x.com', 'message' => 'Clear and helpful site', 'rating' => 5, 'share_publicly' => true]);
        ContactMessage::create(['topic' => 'feedback', 'name' => 'B', 'email' => 'b@x.com', 'message' => 'Approved and shared', 'rating' => 4, 'share_publicly' => true, 'approved_at' => now()]);

        $this->assertSame(1, ContactMessage::publicFeedback()->count());
        $this->get('/contact')->assertOk()->assertSee('Approved and shared')->assertDontSee('Clear and helpful site');
    }
}
