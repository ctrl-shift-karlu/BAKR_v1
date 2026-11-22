<?php

namespace App\Services;

use App\Ai\GeminiMatcher;
use App\Ai\MatchProfile;
use App\Ai\MatchPrompt;
use App\Models\ScholarshipApplication;
use App\Models\ScholarshipMatch;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Runs the Gemini AI matcher on a saved application and merges its verdicts into the stored
 * rule-check matches (hybrid matching, owner 2026-11-19):
 *
 *   shown status = the STRICTER of the rule check and the AI (ScholarshipMatch::stricter)
 *   score        = 60% rule score + 40% AI fit (internal, for sorting only; never shown)
 *
 * The AI's explanation and overview are cached on the match / application, so the explainer makes
 * no extra calls. Saving the form, or a provider changing a scholarship, sets the application back
 * to "pending"; the results page then asks for this run in the background (student-ai.js).
 */
class AiMatchService
{
    private const FIT_POINTS = ['high' => 100, 'medium' => 60, 'low' => 25];

    public function __construct(private GeminiMatcher $matcher) {}

    public static function available(): bool
    {
        return GeminiMatcher::configured();
    }

    /** The ai_match_status a freshly matched application starts with. */
    public static function initialState(): string
    {
        return self::available() ? ScholarshipApplication::AI_PENDING : ScholarshipApplication::AI_OFF;
    }

    /**
     * Ask Gemini once and apply the result. Returns true when the AI review is (now) in place.
     * Only pending or failed applications are run, so a page refresh never costs another request.
     */
    public function run(ScholarshipApplication $application): bool
    {
        if ($application->aiDone()) {
            return true;
        }
        if (! self::available()) {
            $application->update(['ai_match_status' => ScholarshipApplication::AI_OFF]);

            return false;
        }

        // One run at a time per application (two open tabs, a double click).
        $lock = Cache::lock('bakr-ai-match:'.$application->id, 120);
        if (! $lock->get()) {
            return false;
        }

        try {
            $application->refresh();
            if ($application->aiDone()) {
                return true;
            }

            $matches = $application->matches()->with('scholarship')->get()->filter(fn ($m) => $m->scholarship !== null);
            $rows = $matches->map(fn (ScholarshipMatch $m) => MatchPrompt::scholarship(
                $m->scholarship->slug,
                $m->scholarship->only(['name', 'provider', 'sector', 'overview', 'source_note', 'documents']),
                $m->scholarship->qualifications(),
                $m->rule_status ?? $m->status,
                (array) $m->checks,
            ))->values()->all();

            $profile = MatchProfile::build($application->answers ?? [], $application->user?->name);
            $reply = $this->matcher->match($profile, $rows);

            if ($reply === null) {
                $application->update(['ai_match_status' => ScholarshipApplication::AI_FAILED]);

                return false;
            }

            DB::transaction(function () use ($application, $matches, $reply) {
                foreach ($matches as $m) {
                    $ai = $reply['results'][$m->scholarship->slug] ?? null;
                    if ($ai === null) {
                        continue;   // the AI skipped this one: the rule check stands
                    }
                    $rule = $m->rule_status ?? $m->status;
                    $m->update([
                        'rule_status' => $rule,
                        'ai_status' => $ai['status'],
                        'ai_fit' => $ai['fit'],
                        'ai_notes' => $ai['notes'],
                        'status' => ScholarshipMatch::stricter($rule, $ai['status']),
                        'score' => (int) round(0.6 * $m->score + 0.4 * self::FIT_POINTS[$ai['fit']]),
                    ] + ($ai['explanation'] ? [
                        'explanation' => $ai['explanation'], 'explanation_source' => 'gemini', 'explained_at' => now(),
                    ] : []));
                }

                $application->update([
                    'ai_match_status' => ScholarshipApplication::AI_DONE,
                    'ai_matched_at' => now(),
                    'ai_model' => GeminiMatcher::model(),
                ] + ($reply['summary'] ? ['ai_summary' => $reply['summary'], 'ai_summary_at' => now()] : []));
            });

            return true;
        } finally {
            $lock->release();
        }
    }
}
