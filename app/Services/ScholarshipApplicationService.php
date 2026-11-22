<?php

namespace App\Services;

use App\Matching\AnswerSanitizer;
use App\Matching\MatchingService;
use App\Matching\ProgramCatalog;
use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use App\Models\StudentConsent;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Saves a student's matching-form answers as versions and stores the rule-check results (step 1 of hybrid
 * matching; the Gemini review is step 2, AiMatchService). Also re-matches saved answers after a scholarship changes.
 */
class ScholarshipApplicationService
{
    /**
     * @param  MatchingService  $matcher  runs the rule check for every scholarship
     */
    public function __construct(private MatchingService $matcher) {}

    /**
     * Save the answers as a new version (only when something changed) and run the matching.
     * Returns the current application; ->wasRecentlyCreated tells the caller whether a new version was written.
     *
     * @param  array<string,mixed>  $input  raw validated answers
     */
    public function submit(User $user, array $input, bool $consentGiven = false): ScholarshipApplication
    {
        $answers = ProgramCatalog::enrich(AnswerSanitizer::clean($input));   // adds program name, campus name and field

        return DB::transaction(function () use ($user, $answers, $consentGiven) {
            if ($consentGiven) {
                // The student agrees to the current Privacy Policy on every submit (2026-11-21): keep the latest date.
                StudentConsent::updateOrCreate(
                    ['user_id' => $user->id, 'consent_type' => StudentConsent::TYPE_DATA_PRIVACY],
                    ['accepted_at' => now()],
                );
            }

            $current = ScholarshipApplication::currentFor($user->id)->lockForUpdate()->first();

            // Same answers, same results: do not create a new version.
            if ($current && self::sameAnswers($current->answers, $answers)) {
                return $current;
            }

            $current?->update(['is_current' => false]);

            $application = ScholarshipApplication::create([
                'user_id' => $user->id,
                'version' => ((int) ScholarshipApplication::where('user_id', $user->id)->max('version')) + 1,
                'is_current' => true,
                'status' => $current ? 'updated' : 'submitted',
                'answers' => $answers,
                'submitted_at' => now(),
            ]);

            $this->storeMatches($application);

            return $application;
        });
    }

    /**
     * Are two sets of answers the same once stored? The answers are kept as JSON, which writes a whole-number
     * grade 88.0 as 88 (read back as an int), so a plain === saw every re-submit with a whole-number grade as a
     * change and made a new version. Both sides go through the same JSON round trip, keys sorted.
     */
    public static function sameAnswers(?array $a, ?array $b): bool
    {
        $canon = function ($v) use (&$canon) {
            if (! is_array($v)) {
                return $v;
            }
            if (! array_is_list($v)) {
                ksort($v);
            }

            return array_map($canon, $v);
        };

        return json_encode($canon(json_decode((string) json_encode($a ?? []), true)))
            === json_encode($canon(json_decode((string) json_encode($b ?? []), true)));
    }

    /**
     * Re-run matching on the current answers (use after a provider edits a scholarship). The AI
     * review is set back to pending and runs again the next time the student opens their results,
     * so one provider edit never fires a Gemini request for every student at once.
     */
    public function rematch(ScholarshipApplication $application): void
    {
        DB::transaction(function () use ($application) {
            $application->matches()->delete();
            $application->update(['ai_summary' => null, 'ai_summary_at' => null]);
            $this->storeMatches($application);
        });
    }

    /**
     * Step 1 of the hybrid match: the rule check of the published hard limits, stored at once.
     * Step 2 (Gemini, AiMatchService::run) is asked for by the results page in the background.
     */
    private function storeMatches(ScholarshipApplication $application): void
    {
        $scholarships = Scholarship::where('is_active', true)->orderBy('sort_order')->get();
        $results = $this->matcher->evaluate(
            $application->answers,
            $scholarships->map(fn ($s) => ['id' => $s->id, 'rules' => $s->rules]),
        );

        foreach ($results as $row) {
            $application->matches()->create($row + ['rule_status' => $row['status']]);
        }

        $application->update([
            'ai_match_status' => AiMatchService::initialState(),
            'ai_matched_at' => null,
            'ai_model' => null,
        ]);
    }
}
