<?php

namespace App\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Calls the Google Gemini API (generateContent). Returns null on any failure so the caller can
 * fall back to the built-in explanation.
 *
 * Privacy: the request carries only ExplanationPrompt data (rule outcomes, the student's answer
 * per requirement, scholarship facts). Never a name, e-mail or free text. On Gemini's FREE tier
 * Google may use requests to improve its products and human reviewers may read them; the paid
 * tier does not (see docs/changes/2026-11-13-11-gemini-explainer.md).
 */
class GeminiExplainer implements Explainer
{
    public static function configured(): bool
    {
        return filled(config('bakr_ai.gemini.key'));
    }

    public function explainMatch(array $ctx): ?string
    {
        return $this->ask(ExplanationPrompt::matchMessage($ctx));
    }

    public function summarize(array $contexts): ?string
    {
        return $this->ask(ExplanationPrompt::summaryMessage($contexts));
    }

    private function ask(string $message): ?string
    {
        if (! self::configured()) {
            return null;
        }

        $url = rtrim((string) config('bakr_ai.gemini.endpoint'), '/').'/'.rawurlencode((string) config('bakr_ai.gemini.model')).':generateContent';

        try {
            // The key goes in a header, not the URL, so it never lands in logs or error messages.
            $response = Http::withHeaders(['x-goog-api-key' => config('bakr_ai.gemini.key')])
                ->acceptJson()
                ->timeout(config('bakr_ai.timeout'))
                ->retry(1, 400, throw: false)
                ->post($url, [
                    'systemInstruction' => ['parts' => [['text' => ExplanationPrompt::SYSTEM]]],
                    'contents' => [['role' => 'user', 'parts' => [['text' => $message]]]],
                    'generationConfig' => [
                        'temperature' => 0.4,
                        'maxOutputTokens' => (int) config('bakr_ai.gemini.max_tokens'),
                    ],
                ]);

            if (! $response->successful()) {
                // Log the status only: never the prompt or the student's data.
                Log::warning('BAKR Gemini explainer request failed', ['status' => $response->status()]);

                return null;
            }

            $text = collect($response->json('candidates.0.content.parts', []))
                ->pluck('text')->filter(fn ($t) => is_string($t))->implode('');

            return $text === '' ? null : ExplanationPrompt::cleanReply($text);
        } catch (Throwable $e) {
            Log::warning('BAKR Gemini explainer error', ['type' => $e::class]);

            return null;
        }
    }
}
