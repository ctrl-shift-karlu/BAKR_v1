# 2026-11-19 — Application form + Gemini AI matcher (major revision)

**Owner's request:** remove the questionnaire-style matching; students fill in a full application form
(personal, academic, family, other scholarships, achievements, short essays), and Gemini acts as an
**AI matcher** that compares the answers with each provider's eligibility and requirements.

**Owner's decisions (2026-11-19):**
- **Hybrid matching.** The rule engine still checks the published hard limits; Gemini reviews the whole
  application; the shown status is the **stricter** of the two (`ScholarshipMatch::stricter`). The AI can
  make a result stricter, never pass a failed published limit.
- **De-identified only.** Gemini never receives the name, e-mail, mobile number, street address / barangay
  or exact birth date (age only). Free text is scrubbed of e-mails, phone-like numbers, links and the
  student's own name parts (`App\Ai\MatchProfile`).
- **All four extra sections**: family & finances, other scholarships held, achievements & activities, short essays.
- **Public Scholarship Finder removed.** Guests only browse scholarship information; matching needs a
  student account. `/scholarship-finder` redirects to `/find-my-matches` (guest → register).

This replaces non-negotiable rules 1–3 of CLAUDE.md ("AI explains, never decides", "no PII / free text to
the AI", "only fields a rule needs"); CLAUDE.md is updated.

## How it works
1. Submit → `ScholarshipApplicationService` sanitizes, saves a new encrypted version, runs the rule check
   and stores every match with `rule_status`. `ai_match_status` = `pending` (or `off` with no Gemini key).
2. Results / dashboard show the rule results with "The Gemini AI matcher is reviewing your application";
   `student-ai.js` POSTs to `student.results.ai-match` (throttle 6/min) and reloads when done.
3. `AiMatchService::run()` (cache lock per application) → `GeminiMatcher` sends ONE request:
   system prompt `MatchPrompt::SYSTEM`, the de-identified profile, and per scholarship its published
   requirements (`Scholarship::qualifications()`), source note, documents and this student's rule check.
   Structured output (`responseMimeType: application/json` + `responseSchema`), temperature 0.2.
4. Reply validated by `MatchPrompt::parse()` (known ids, allowed statuses/fits, phrases ≤ 3 × 160 chars,
   markup stripped). Per match: `ai_status`, `ai_fit`, `ai_notes` (strengths / concerns / missing),
   `status = stricter(rule, ai)`, score = 60% rule + 40% fit (internal), explanation cached (`gemini`);
   overview → `ai_summary`. Failure or unusable reply → `failed`, rule results stay, retry button.
5. A provider edit (`rematch`) sets the application back to `pending`; it re-runs on the student's next visit
   (no burst of Gemini calls). Applications saved before this change are queued on their next visit.

## Added
- `app/Ai/MatchProfile.php` — the only student data sent to Gemini (de-identified, scrubbed, labelled).
- `app/Ai/MatchPrompt.php` — matcher instructions (incl. prompt-injection guard), data, JSON schema, reply validation.
- `app/Ai/GeminiMatcher.php` — the generateContent call (key in header; logs status codes only).
- `app/Services/AiMatchService.php` — run + merge (stricter status, fit, notes, explanation, overview).
- `database/migrations/2026_11_19_000002_add_ai_matching.php` — `scholarship_matches.rule_status / ai_status / ai_fit / ai_notes`, `scholarship_applications.ai_match_status / ai_matched_at / ai_model`.
- `resources/views/student/partials/ai-match-status.blade.php` — pending / failed / done / off banner.
- `tests/Feature/AiMatcherTest.php` — 8 tests (queue, stricter merge, de-identified request, failure, tampered reply, no key, rematch, scrubbing).
- Backups of the removed Finder: `docs/changes/backups/finder-2026-11-19/`.

## Changed
- `app/Matching/ApplicationSchema.php` — full application form: Part I personal (birth date, sex, civil status,
  citizenship, mobile, street address, municipality, province), II education (+ SHS strand, school type),
  III academic, IV family & finances (+ parents, occupations, household size, siblings in school),
  V other scholarships (+ details), VI achievements (+ honors, organizations, community work),
  VII short essays (80–1500 chars), VIII provider-specific; certification = Part IX (`certificationPart()`).
  New field types `date`, `tel`, `textarea`; `'ai' => false` marks fields never sent; `age()`.
- `app/Matching/AnswerSanitizer.php` — plain-text cleaning (tags, control chars, whitespace; phone digits only).
- `app/Http/Requests/ScholarshipApplicationRequest.php` — mobile normalized before validation; new messages.
- `app/Models/StudentConsent.php` — consent type is now `data_privacy_ai` (covers Gemini); old `data_privacy` consents are asked again.
- `app/Models/ScholarshipMatch.php`, `ScholarshipApplication.php` — new columns, `stricter()`, `aiLowered()`, `aiList()`, AI states.
- `app/Services/ScholarshipApplicationService.php` — stores `rule_status`, sets the AI state.
- `app/Http/Controllers/Student/StudentDashboardController.php` — `aiMatch()`; queues old applications.
- `routes/web.php` — `student.results.ai-match`; Finder routes removed (redirect kept).
- `config/bakr_ai.php` — `match_max_tokens` (8192), `match_timeout` (60 s); header comment.
- Views: `student/application` (title, privacy note, consent text, part number), `student/partials/field`
  (date / tel / textarea + counter), `student/results` (AI banner, fit tag, AI review lists, "rule check alone said…"),
  `student/dashboard`, `student/layout` ("My application form"), `student/application-print`, `student/help` (new FAQ),
  `partials/portal/about-bakr`, `home` (step 2), `admin/overview`, `errors/403`, `provider/help` (wording).
- JS: `public/js/student-form.js` (textarea, tel, date checks, counters, review), `public/js/student-ai.js` (runs the matcher).
- CSS: `student-portal.css` (textarea, counter, AI banner, fit tag, review grid, pre-line answers), `bakr-dark.css`, `bakr-form-print.css`.
- Tests: `MatchingFormTest` (new fields, Part IX, cleaning), `GeminiExplainerTest` (explainer = fallback when the matcher failed/off), `NoMatchTest` (Finder gone), `DnscCatalogTest`.

## Removed
- `app/Http/Controllers/ScholarshipFinderController.php`, `resources/views/finder/*`, `tests/Feature/ScholarshipFinderTest.php`.

## Verified
- `php artisan test` → 231/231 passing.
- `php artisan migrate` on the local MySQL `bakr` (additive columns only).
- No live Gemini call: the local `.env` has no `BAKR_GEMINI_KEY`, so the site currently runs rule-check only ("AI matcher is turned off").

## Follow-ups
- Add `BAKR_GEMINI_KEY` to `.env` (Google AI Studio), `php artisan config:clear`, submit a test form and check the reply.
  If the chosen model rejects `responseSchema`, set `BAKR_GEMINI_MODEL` to a model that supports structured output.
- Free tier: Google may use requests and human reviewers may read them. Use the paid tier for real students.
- `PublicAnalyticsService` / admin analytics still count by `status` (now the hybrid result); "Why students don't
  qualify" still reads the rule checks only.

## Update, same day: form cut down (owner: "remove the unnecessary questions, remove the essay part")
Removed **16 questions that no scholarship rule reads**: sex, civil status, mobile number, street address, city / municipality,
senior-high school type, parents' status, father / mother / guardian occupation, household size, siblings in school, other-aid
details, honors, organizations, community service, and **both essays (Part VII)**. The 27 questions that feed a rule stay.
The form is now Parts I-VII + Part VIII certification (about 5 minutes). Removed with them: `tel` / `textarea` field types,
the mobile-number cleanup, the character counters, the essay messages and styles. The AI prompt no longer mentions essays;
`MatchProfile::scrub` stays as a guard for any free-text field added later. Old saved forms may still hold the removed
answers until the student saves again (the sanitizer never stores them). Tests: 236/236 (form tests now assert the removed
questions are absent and never stored).
