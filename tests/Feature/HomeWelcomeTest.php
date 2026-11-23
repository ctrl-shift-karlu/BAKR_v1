<?php

namespace Tests\Feature;

use Database\Seeders\ScholarshipSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Owner, 2026-11-23: the home page welcome pop-up is a frosted dark-green glass card (logo, title, text and two
 * actions), with a solid card when blur is off (installed app, older browsers). The old photo band, avatar and
 * kicker are gone.
 */
class HomeWelcomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_welcome_card_has_its_two_actions_and_no_old_parts(): void
    {
        $this->seed(ScholarshipSeeder::class);

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('id="welcomeModal"', $html);
        $this->assertStringContainsString('nx-welcome-card', $html);
        $this->assertStringContainsString('Welcome to BAKR', $html);
        $this->assertStringContainsString('href="'.route('find').'"><i class="bi bi-search" aria-hidden="true"></i> Find my matches', $html);
        $this->assertStringContainsString('href="'.route('scholarships').'"><i class="bi bi-journal-text" aria-hidden="true"></i> Browse scholarships', $html);
        foreach (['nx-modal-band', 'nx-avatar', 'nx-welcome-kicker'] as $gone) {
            $this->assertStringNotContainsString($gone, $html);
        }

        $css = file_get_contents(public_path('css/bakr-layout.css'));
        $this->assertStringContainsString('backdrop-filter: blur(18px)', $css);
        $this->assertStringContainsString('@supports not ((backdrop-filter: blur(1px))', $css);   // solid fallback
        $this->assertStringContainsString('html.is-app .nx-welcome-card', $css);                  // installed app: solid
        foreach (['.nx-modal-band', '.nx-avatar', '.nx-welcome-kicker'] as $gone) {
            $this->assertStringNotContainsString($gone, $css);
        }
    }
}
