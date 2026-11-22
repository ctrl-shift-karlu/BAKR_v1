<?php

namespace App\Services;

use App\Ai\ClaudeExplainer;
use App\Ai\Explainer;
use App\Ai\ExplanationPrompt;
use App\Ai\GeminiExplainer;
use App\Ai\TemplateExplainer;
use App\Models\ScholarshipApplication;
use App\Models\ScholarshipMatch;

/** Fetches (and caches) the explanation for a match or a whole result set. */
class ExplanationService
{
    /**
     * @param  ClaudeExplainer  $claude  used when BAKR_AI_PROVIDER=claude
     * @param  GeminiExplainer  $gemini  the default AI explainer
     * @param  TemplateExplainer  $template  the built-in fallback
     */
    public function __construct(
        private ClaudeExplainer $claude,
        private GeminiExplainer $gemini,
        private TemplateExplainer $template,
    ) {}

    /** "gemini" or "claude" (config bakr_ai.provider). */
    public static function provider(): string
    {
        return config('bakr_ai.provider') === 'claude' ? 'claude' : 'gemini';
    }

    /** True when the chosen AI has a key, so explanations are written by the AI. */
    public static function aiAvailable(): bool
    {
        return self::provider() === 'claude' ? ClaudeExplainer::configured() : GeminiExplainer::configured();
    }

    /** The explainer chosen in config/bakr_ai.php (Gemini by default, or Claude). */
    private function ai(): Explainer
    {
        return self::provider() === 'claude' ? $this->claude : $this->gemini;
    }

    /** Explanation text for a match, or null when only the built-in one exists. */
    public static function aiText(ScholarshipMatch $m): ?string
    {
        return $m->explanation && in_array($m->explanation_source, ['claude', 'gemini'], true) ? $m->explanation : null;
    }

    /** The data an explainer needs for one match: scholarship facts, status and rule outcomes (no personal details). */
    public static function context(ScholarshipMatch $m): array
    {
        $s = $m->scholarship;

        return ExplanationPrompt::matchContext(
            ['name' => $s->name, 'overview' => $s->overview, 'documents' => $s->documents, 'window_note' => $s->window_note],
            $m->status, $m->checks,
        );
    }

    /** The always-available built-in explanation (not stored). */
    public function quick(ScholarshipMatch $m): string
    {
        return (string) $this->template->explainMatch(self::context($m));
    }

    /**
     * Ask the AI to explain one match. Only a real AI reply is stored (it stays cached until the
     * answers change). Returns false when the AI is off or unreachable, so the page can say so
     * and keep showing the built-in explanation.
     */
    public function forMatch(ScholarshipMatch $m): bool
    {
        if (self::aiText($m)) {
            return true;
        }

        $text = $this->ai()->explainMatch(self::context($m));
        if ($text === null) {
            return false;
        }
        $m->update(['explanation' => $text, 'explanation_source' => self::provider(), 'explained_at' => now()]);

        return true;
    }

    /** Built-in overview, shown until an AI overview exists. */
    public function quickSummary(ScholarshipApplication $app): string
    {
        $app->loadMissing('matches.scholarship');

        return (string) $this->template->summarize($app->matches->map(fn ($m) => self::context($m))->all());
    }

    /**
     * Asks the AI for the overview of all matches and caches it on the application.
     *
     * @return bool false when the AI is off or unreachable (the built-in overview stays)
     */
    public function forApplication(ScholarshipApplication $app): bool
    {
        if ($app->ai_summary) {
            return true;
        }

        $app->loadMissing('matches.scholarship');
        $text = $this->ai()->summarize($app->matches->map(fn ($m) => self::context($m))->all());
        if ($text === null) {
            return false;
        }
        $app->update(['ai_summary' => $text, 'ai_summary_at' => now()]);

        return true;
    }
}
