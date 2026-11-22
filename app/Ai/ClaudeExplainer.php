<?php

namespace App\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/** Calls the Claude Messages API. Returns null on any failure so the caller can fall back. */
class ClaudeExplainer implements Explainer
{
    public static function configured(): bool
    {
        return filled(config('bakr_ai.key'));
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

        try {
            $response = Http::withHeaders([
                'x-api-key' => config('bakr_ai.key'),
                'anthropic-version' => config('bakr_ai.version'),
            ])
                ->acceptJson()
                ->timeout(config('bakr_ai.timeout'))
                ->retry(1, 400, throw: false)
                ->post(config('bakr_ai.endpoint'), [
                    'model' => config('bakr_ai.model'),
                    'max_tokens' => config('bakr_ai.max_tokens'),
                    'system' => ExplanationPrompt::SYSTEM,
                    'messages' => [['role' => 'user', 'content' => $message]],
                ]);

            if (! $response->successful()) {
                // Log the status only: never the prompt or the student's data.
                Log::warning('BAKR AI explainer request failed', ['status' => $response->status()]);

                return null;
            }

            return ExplanationPrompt::cleanReply($response->json('content.0.text'));
        } catch (Throwable $e) {
            Log::warning('BAKR AI explainer error', ['type' => $e::class]);

            return null;
        }
    }
}
