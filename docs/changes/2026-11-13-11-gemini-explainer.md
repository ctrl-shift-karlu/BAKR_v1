# 2026-11-13 — Gemini writes the explanations (rules still decide)

## Decision (owner)
- **Matching stays with the rule engine** (`RuleEvaluator`); AI matching was declined.
  - AI verdicts can vary between runs and invent thresholds.
  - It would need the raw answers.
  - It would break non-negotiable rule 1.
- **Gemini writes every explanation.** The built-in TemplateExplainer text is only shown while Gemini is answering, or if it is off or unreachable.
- **Prompt content (owner's choice):** rule outcomes **plus the student's answer per requirement**, e.g. "Your income range (Up to ₱250,000) is within the limit". Never a name, e-mail or free text.

## Gemini API facts (checked 2026-11-13 on aistudio.google.com/docs)
- **Free tier exists.** Free Flash models include `gemini-3.5-flash-lite` (our default), `gemini-3.8-flash`, `gemini-3.6-flash` and `gemini-3.1-flash-lite`. `gemini-2.5-flash` is deprecated and routes to 3.5-flash-lite on 2026-12-01.
- **Data on the free tier is used to improve Google products**, and "human reviewers may read, annotate, and process your API input and output". The paid tier does not use prompts or responses to improve products. → Use the paid tier before real student data goes live.
- **Developer terms:** the Philippines is a supported region. The key holder must be 18+, and the API is "for developers … for professional or business purposes".
- **Rate limits** are per project. Free-tier numbers are only shown at aistudio.google.com/rate-limit and "are not guaranteed".
- **API call:** `POST …/v1beta/models/{model}:generateContent`. The key is sent in the `x-goog-api-key` header, never in the URL, so it can't leak into logs.

## Changed / added
- `app/Ai/GeminiExplainer.php`:
  - same `ExplanationPrompt::SYSTEM` safety prompt as Claude;
  - temperature 0.4;
  - replies are cleaned with `cleanReply()`;
  - failures return null and log the HTTP status only.
- `config/bakr_ai.php`: `provider` (default `gemini`), plus a `gemini.{key,model,endpoint,max_tokens}` block. The Claude settings are unchanged.
- `ExplanationService`:
  - picks the provider;
  - `aiAvailable()`, `aiText()`;
  - stores `explanation_source` = `gemini` | `claude`.
- `StudentDashboardController`: `explainMatch` and `explainAll` also answer JSON `{ok, text}` (503 when the AI fails). Same ownership scoping, same throttles (12/min, 6/min).
- `public/js/student-ai.js`:
  - asks for the explanation when a match dialog opens, and for the dashboard overview on load;
  - writes the reply with `textContent` (never HTML);
  - keeps the built-in text on failure.
- The "Explain with AI" buttons are now `<noscript>` fallbacks only.
- `layouts/app`: `<meta name="csrf-token">`.
- `.env.example` / local `.env`: `BAKR_AI_PROVIDER=gemini`, `BAKR_GEMINI_KEY=` (empty), `BAKR_GEMINI_MODEL=gemini-3.5-flash-lite`.
- The public Scholarship Finder still uses the built-in explanation. Nothing is saved there, and an open AI endpoint would invite abuse.

## Tests
- `tests/Feature/GeminiExplainerTest.php` (6, with `Http::fake`, no real calls):
  - the key is in the header and not the URL;
  - same safety prompt;
  - the answer is included, but no name, e-mail or percentage;
  - the reply is saved once and cached;
  - status unchanged;
  - 429 → built-in text and a retry next time;
  - another student's match → 404 with nothing sent;
  - the dashboard overview;
  - no key → nothing sent.
- Browser: with the explain endpoint mocked, opening a match swaps in the AI text and the label becomes "AI explanation"; the dashboard overview does the same on load.
- 115/115 pass.

## To turn it on
1. Get a key at https://aistudio.google.com (the account holder must be 18+).
2. Put it in `.env`: `BAKR_GEMINI_KEY=...`
3. Run `php artisan config:clear`.

## Turned on (same day)
- The owner's Google AI Studio key was put in the local `.env` (git-ignored) and the config cleared.
- **Live check:** a dummy TDP result (no student data) went through `GeminiExplainer` with the real prompt. The reply came back in about 5s and followed the rules: "looks like a match", no invented figures, next step = ask DNSC about slots.
- `phpunit.xml` now forces `BAKR_GEMINI_KEY` and `BAKR_AI_KEY` to empty, so the test suite can never call a real AI. The tests use `Http::fake`.
