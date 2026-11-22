<?php

/*
 * AI settings.
 *
 * Since 2026-11-19 (owner) Gemini is also the AI MATCHER (App\Ai\GeminiMatcher, hybrid): the rule
 * engine checks the published hard limits, Gemini reviews the whole de-identified application, and
 * BAKR shows the stricter result. The matcher always uses Gemini (BAKR_GEMINI_KEY); BAKR_AI_PROVIDER
 * only picks who writes explanations the matcher did not. With no Gemini key, matching falls back
 * to the rule check alone and the built-in explanations, so the system still works offline.
 *
 * .env:
 *   BAKR_AI_PROVIDER=gemini                    gemini (default) or claude
 *   BAKR_GEMINI_KEY=...                        Google AI Studio key (never commit this)
 *   BAKR_GEMINI_MODEL=gemini-3.5-flash-lite    optional; a free-tier Flash model
 *   BAKR_AI_KEY=sk-ant-...                     Claude key, only when BAKR_AI_PROVIDER=claude
 *   BAKR_AI_MODEL=claude-haiku-5-5             optional
 *
 * Gemini free tier: Google may use requests to improve its products and human reviewers may
 * read them. BAKR sends no name or e-mail, but it does send the student's answer per
 * requirement (e.g. income bracket). Use the paid tier for real student data in production.
 */
return [
    'provider' => env('BAKR_AI_PROVIDER', 'gemini'),

    'gemini' => [
        'key' => env('BAKR_GEMINI_KEY'),
        'model' => env('BAKR_GEMINI_MODEL', 'gemini-3.5-flash-lite'),
        'endpoint' => env('BAKR_GEMINI_ENDPOINT', 'https://generativelanguage.googleapis.com/v1beta/models'),
        'max_tokens' => 1024,
        // AI matcher: one request covers every scholarship, so it needs more room and time.
        'match_max_tokens' => (int) env('BAKR_GEMINI_MATCH_MAX_TOKENS', 8192),
        'match_timeout' => (int) env('BAKR_GEMINI_MATCH_TIMEOUT', 60),
    ],

    // Claude (Anthropic Messages API)
    'key' => env('BAKR_AI_KEY'),
    'model' => env('BAKR_AI_MODEL', 'claude-haiku-5-5'),
    'endpoint' => env('BAKR_AI_ENDPOINT', 'https://api.anthropic.com/v1/messages'),
    'version' => '2023-06-01',
    'max_tokens' => 450,

    'timeout' => (int) env('BAKR_AI_TIMEOUT', 20),
    // Explanations are cached on the match, so each match costs one request until the answers change.
];
