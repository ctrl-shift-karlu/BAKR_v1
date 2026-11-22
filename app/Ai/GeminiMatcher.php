<?php

namespace App\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * The Gemini AI matcher: ONE generateContent request per saved application, returning structured
 * JSON (MatchPrompt::responseSchema) with a status, fit, notes and explanation for every scholarship
 * plus an overview. Returns null on any failure; the caller then keeps the rule-check results.
 *
 * Privacy: the request carries MatchProfile data only (no name, e-mail, contact number, street
 * address or birth date). On Gemini's FREE tier Google may use requests and human reviewers may
 * read them; use the paid tier for real student data in production.
 */
class GeminiMatcher
{
    /** True when a Gemini API key (BAKR_GEMINI_KEY) is set; without it BAKR uses the rule check only. */
    public static function configured(): bool
    {
        return filled(config('bakr_ai.gemini.key'));
    }

    /** The Gemini model name from config/bakr_ai.php (stored on the application as ai_model). */
    public static function model(): string
    {
        return (string) config('bakr_ai.gemini.model');
    }

    /**
     * @param  array<string,mixed>  $profile  MatchProfile::build()
     * @param  list<array<string,mixed>>  $scholarships  MatchPrompt::scholarship() rows
     * @return array{results: array<string,array>, summary: ?string}|null
     */
    public function match(array $profile, array $scholarships): ?array
    {
        if (! self::configured() || $scholarships === []) {
            return null;
        }

        $url = rtrim((string) config('bakr_ai.gemini.endpoint'), '/').'/'.rawurlencode(self::model()).':generateContent';

        try {
            // The key goes in a header, not the URL, so it never lands in logs or error messages.
            $response = Http::withHeaders(['x-goog-api-key' => config('bakr_ai.gemini.key')])
                ->acceptJson()
                ->timeout((int) config('bakr_ai.gemini.match_timeout', 60))
                ->retry(1, 800, throw: false)
                ->post($url, [
                    'systemInstruction' => ['parts' => [['text' => MatchPrompt::SYSTEM]]],
                    'contents' => [['role' => 'user', 'parts' => [['text' => MatchPrompt::message($profile, $scholarships)]]]],
                    'generationConfig' => [
                        'temperature' => 0.2,   // the same application should give the same result
                        'maxOutputTokens' => (int) config('bakr_ai.gemini.match_max_tokens', 8192),
                        'responseMimeType' => 'application/json',
                        'responseSchema' => MatchPrompt::responseSchema(),
                    ],
                ]);

            if (! $response->successful()) {
                // Status only: never the prompt or the student's data.
                Log::warning('BAKR Gemini matcher request failed', ['status' => $response->status()]);

                return null;
            }

            $text = collect($response->json('candidates.0.content.parts', []))
                ->pluck('text')->filter(fn ($t) => is_string($t))->implode('');

            $parsed = MatchPrompt::parse($text, array_column($scholarships, 'id'));
            if ($parsed === null) {
                Log::warning('BAKR Gemini matcher reply unusable', ['finish' => $response->json('candidates.0.finishReason')]);
            }

            return $parsed;
        } catch (Throwable $e) {
            Log::warning('BAKR Gemini matcher error', ['type' => $e::class]);

            return null;
        }
    }
}
