# 2026-11-22 — Whole-project audit (bugs, consistency, layout)

**Roadmap item(s):** none (owner asked for a full audit: UI, links/forms/modals, JS, logic, CRUD, queries, validation, auth, uploads, frontend/backend consistency)

## How it was checked
- Read every controller, the auth/role/password middleware, ProviderScope, upload/download code, the stipend release rule, the AI matcher flow and every portal script (`public/js`).
- Temporary crawl test (deleted afterwards): signed in as a guest, a student with and without answers, a scholar, every one of the 8 provider accounts (each with active / terminated / roster-only scholars, requirements in every state, pending + released stipends, correction messages) and the admin, and opened ~200 pages incl. every `?edit=` card. No 5xx anywhere.
- Every rendered page loaded in headless Chrome (CLAUDE.md "probe" method, `public/_p/`, deleted afterwards) with a probe for JS errors, failed files, sideways overflow, text under 12px and low contrast: 500px light + dark, 820px and 1400px light.
- Every `asset('…')` in the views checked against `public/`.

## Fixed
- `app/Http/Controllers/Provider/ProviderRequirementController.php` — **a never-uploaded document could be approved.** The review form only shows Approve when a file exists, but the server accepted `approved` (and `missing`) for any row, so a stale page or hand-made request could approve an empty item, which unlocks the stipend (`Scholar::releaseBlocker`). Now only `approved` / `needs_revision` are accepted and only for a row with a file.
- `app/Http/Controllers/Student/StudentDashboardController.php` + `public/js/student-ai.js` — **"AI matcher could not finish" shown while it was still running.** A second tab / reload found the run locked (`AiMatchService` cache lock) and got a 503 + failure message. Now that case answers 202 `{state: pending}` and the page waits and asks again (every 5 s, up to ~2 min); the no-JS button redirects with "still reviewing".
- `app/Support/MailTime.php` (new), `app/Services/ScholarNotifier.php`, `app/Services/Provider/ScholarAccounts.php` — **big batches of e-mail could hit PHP's time limit.** Saving a deadline e-mails every scholar of the scholarship and an Excel import (up to 2,000 rows) e-mails every new login, synchronously, ~1 s each over Gmail SMTP. Past `max_execution_time` the request died halfway (some told, the rest not, the provider shown an error for a save that went through). Each e-mail now restarts PHP's timer (only when a limit is set; CLI/tests untouched).
- `app/Http/Controllers/Scholar/ScholarPortalController.php` (Overview) + `resources/views/scholar/scholarship.blade.php` (Stipends) — **the scholar was told to upload documents when the hold was on the provider's side,** and the Stipends list showed the provider-facing reason in brackets ("Stipend waiting for your requirements (the scholar is marked inactive)"). Now: documents not approved → "waiting for your requirements"; no deadline / not on the checklist → "waiting for {provider} to open your … maintaining documents"; inactive / terminated / before "Scholar since" each have their own plain sentence.
- `public/css/bakr-dark.css` — **dark-mode contrast** (found by the probe; headless Chrome followed the Windows dark setting):
  - teal text (`--dash-teal` / `--dash-info` had no dark value: "Waiting for review" tiles, `.pv-attn` "Grant type not set" / "Not recorded yet", info notes) 2.5:1 → dark values `#7cc4cf`;
  - red attention text (`.dash-meta .is-attn` "Due …" / "1 to review" / "E-mail notice failed", `.pv-roster-tag.is-attn`) 2.5:1 → `#ffb4ab` (text only; `--dash-danger` stays for solid buttons);
  - gold `.dash-avatar.is-3` codes on the scholar Overview ("TDP", "CLFI") 1.3:1 → dark text, like `.is-1`;
  - `.sch-mine` (student › All scholarships, "your result" line) had a hard-coded `#5b564f` with no dark override.
- `public/css/bakr-dashboard.css` — **light-mode contrast**: `--dash-ink-faint` `#8a847a` (3.7:1 on white; every field hint, "Not announced", card subtitle, ID label, help line) → `#6f6a62` (≈5.3:1; the dark value is separate and unchanged). Avatars on the gold fills (default / `.is-1` / `.is-3`: "TDP", "AS", "CoScho") had white text at 2.4:1 / 1.3:1 → dark green text; the dark-green `.is-2` / `.is-4` keep white.
- `public/css/student-portal.css` — `.sp-sector` (My matches cards: "Government", "DNSC Scholarship") used `--gold` text on white, 2.95:1 → `--dash-gold-text`; `.sp-changed-tag` ("Different now" on a saved version) 4.3:1 → lighter gold fill.
- Contrast re-check with a fresh Chrome profile at 500px: light went from ~280 findings to none real (left: white captions over homepage photos, which the probe can't measure, and one admin link at 4.21:1); dark only shows the homepage's decorative 10%-opacity step numbers.
- `public/css/bakr-form.css`, `public/css/bakr-form-print.css` — "Not answered" (`dd.is-empty`) was `#8a847a` on white, 3.6:1, also in light mode and print → `#6b665e`.
- `public/css/bakr-admin.css` — analytics "None yet" bars: `--dash-ink-faint` was 4:1 on the dark track → `--dash-ink-soft`.
- `public/js/bakr-core.js` — **a URL hash that is not a valid CSS selector blanked public pages.** `document.querySelector(location.hash)` threw on e.g. `/about#2026` or an encoded character, stopping the script before scroll-reveal started, so every `.nx-reveal` section stayed hidden. Now looked up with `getElementById` (same guard `bakr-dashboard.js` already had). Checked in Chrome with `/#2026`.
- `app/Http/Controllers/FindMatchesController.php` — "Find a Scholarship" sent a signed-in admin back to the homepage (the button seemed to do nothing); now the admin overview, like the login redirect in `bootstrap/app.php`. Test: `PortalNavigationTest::test_find_sends_the_admin_to_the_admin_overview`.
- `resources/views/home.blade.php` — "See all scholarships" linked a route through `asset('scholarships')`; now `route('scholarships')`.

## Tests
- `AiMatcherTest::test_a_review_already_running_in_another_tab_answers_still_working_not_failed`
- `ProviderOperationsTest::test_a_document_the_scholar_never_uploaded_cannot_be_approved`
- `ScholarPortalTest::test_a_stipend_held_by_the_provider_side_is_not_blamed_on_the_scholar`
- `ScholarPortalTest::test_my_scholarship_shows_info_benefits_documents_and_stipends`: its scenario has no deadline, so it now expects the provider-side wording (it used to assert the misleading label).

## Verified
- `php artisan test` → 319/319 passing (315 before + 4 new).
- Headless Chrome on every page at 500 / 820 / 1400px: no JS errors, no sideways overflow, no text under 12px.

## Checked and fine (no change)
- Role gates, deactivated accounts signed out, every provider URL id looked up inside `ProviderScope`, student data scoped to the account, scholar files only through their own records, `nosniff` previews, login redirects kept inside the signed-in portal, reset links, forced password change.
- Stipend release rule (single + bulk), "Scholar since", campus → program checks, Scholar ID uniqueness, maintaining-document settings, section-only saves on My scholarships and the scholar profile.
- The About video "failed" only in headless Chrome (Apache serves it with range requests: 206).

## Follow-ups (owner's decision)
- E-mails are still sent inside the request. For hundreds of scholars at once the real fix is a queue (`QUEUE_CONNECTION=database` is already set) with `php artisan queue:work` running, and the mailables marked `ShouldQueue`. That changes how BAKR is run (a worker must always be on), so it was not done here.
