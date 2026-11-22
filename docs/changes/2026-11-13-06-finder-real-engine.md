# 2026-11-13 — Scholarship Finder uses the real rules; work committed

**Problem:** `/scholarship-finder` was a standalone PHP script inside a Blade view. It had its own `session_start()`, CSRF token, questions and rule engine, with thresholds marked "VERIFY" that contradicted `config/scholarships.php`:

| Scholarship | Old Finder | Real rule |
|---|---|---|
| CHED TDP | income < ₱300,000 | ≤ ₱400,000 |
| CHED CMSP | income < ₱300,000 | ≤ ₱500,000 |
| BPMSP | income < ₱300,000 | ≤ ₱2,000,000 |
| CHED TES | income < ₱300,000 | no income cap (priority groups) |
| DNSC SA-C | income < ₱300,000 | no published limit (confirm) |
| DNSC Academic | GWA ≥ 90 | not published (confirm) |
| DOST-SEI | STEM strand + Grade 12 ≥ 85 | not published (confirm) |

Students were turned away from scholarships they may qualify for, which breaks non-negotiable rule 6. Provider edits never reached it.

## Changed
- `/scholarship-finder` → `ScholarshipFinderController`:
  - **GET** shows the same questionnaire as the student portal (`ApplicationSchema`, `student.partials.field`, `student-form.js`), with no consent box.
  - **POST** (`throttle:20,1`) validates with `FinderCheckRequest`, which is the portal's rules minus consent. It runs `AnswerSanitizer` → `ProgramCatalog::enrich` → `MatchingService` against the **rules in the `scholarships` table**. It renders results grouped by status, with per-rule checks and the built-in (non-AI) explanation.
  - **Nothing is saved:** no application, no matches, no user, no session copy of the answers.
- Results page: "Create a free account" / "I have an account" / "Start over". Signed-in students are pointed to their saved matches.
- `routes/web.php`: named routes `finder`, `finder.check`.
- `student-portal.css`: Finder page styles.

## Added
- `app/Http/Requests/FinderCheckRequest.php`, `app/Http/Controllers/ScholarshipFinderController.php`
- `resources/views/finder/show.blade.php`, `resources/views/finder/results.blade.php`
- `tests/Feature/ScholarshipFinderTest.php` (6 tests):
  - a ₱350,001–₱400,000 family is **not** rejected from TDP
  - every scholarship is listed
  - nothing is saved
  - answers are validated
  - a DB rule change (as a provider edit would make) changes the Finder result
  - signed-in students are pointed to their saved matches
- Backup of the old page: `docs/changes/backups/scholarshipFinder.blade.php.2026-11-13.bak`

## Not removed (needs approval)
- `resources/views/scholarshipFinder.blade.php`, `public/css/bakr-finder.css` and `public/js/bakr-finder.js` are now unused. Delete them once confirmed.

## Also
- All earlier work was committed as `7c06277` on `ui-ux-audit-fixes` (local only, not pushed). `docs/mockups/liquid-glass/` was left out; it predates this work.

## Verified
- `php artisan test` → **99/99**; `pint --test` clean.
- `playwright-cli`: filled the form in a browser as a ₱350,001–₱400,000, 2nd-year student → "3 of 11 scholarships may fit you", with **CHED TDP: Possible match, 96%**.
