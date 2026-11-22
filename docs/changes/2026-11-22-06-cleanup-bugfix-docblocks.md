# 2026-11-22 — Cleanup, bug fixes, simplification and DocBlocks

**Roadmap item(s):** owner's whole-project request (fix bugs, clean, simplify, document, test).

## Critical fix: `config/` was empty

The whole `config/` folder was empty on disk (emptied at 17:36), and the "Initial BAKR project upload"
commit (17:55) was made after that, so **git has no `config/` at all**. Without it every page that reads
`config('scholarships')`, `config('providers')`, `config('bakr')`… failed: 258 of 323 tests errored.

- Restored all 16 files from `docs/changes/backups/cleanup-2026-11-22/config/` (the snapshot taken before
  this morning's cleanup; the cleanup log says nothing in `config/` changed after it). Checked first: every
  secret is read with `env()`, none is written in the files.
- **Still untracked in git.** Commit `config/` (it is safe: no secrets) so the repository is complete.

## Changed

- `app/Http/Controllers/Scholar/ScholarRequirementController.php` — **bug:** replacing an upload deleted the
  old file *before* the row was updated; a failed save left the row pointing at a deleted file. Now the row is
  updated first and the old file is deleted after.
- `app/Models/ScholarRequirement.php` — new `fileResponse(bool $inline)`: the preview / download code that was
  copied in the scholar and the provider controller now lives once here (same headers: stored type, nosniff,
  no-store).
- `app/Http/Controllers/Provider/ProviderRequirementController.php` — `download()` uses `fileResponse()`.
- `app/Support/Spreadsheet.php` — `xlsx()` (one sheet) is now `workbook([$name => $rows])`; the duplicated
  zip/XML writer (~40 lines) and the copied column-letter closure are gone (`columnLetters()`).
- `app/Services/Provider/ProviderScope.php` — `providerHas()` is public, so the admin uses the same rule as the
  portal for optional pages (before, the admin treated a provider without a `features` list as having none,
  the portal as having all).
- `app/Http/Controllers/Admin/AdminController.php` — uses `ProviderScope::providerHas()`; the Excel export
  reuses the scholarships `analyticsData()` already loaded (one query less); typed `Collection` parameters.
- `app/Models/ScholarshipApplication.php` — new scope `currentFor($userId)`; replaces the same
  "current answers of this student" query copied in 7 places (FindMatches, StudentApplication ×2,
  StudentDashboard, StudentPortal, ScholarshipApplicationService, `student/layout`).
- `app/Support/AcademicTerm.php` — removed the Midyear-era aliases `currentSemester()`, `semesterOptions()`,
  `isValidSemester()` and `of()` (each only called `current()` / `options()` / `isValid()`); all callers,
  including migration `2026_11_21_000003` and the tests, use the real names.
- `app/Http/Controllers/Provider/ProviderDisbursementController.php` — removed `semester()`, an exact copy of
  `ProviderController::term()` once the aliases were gone.
- `app/Http/Controllers/Provider/ProviderScholarshipController.php` — removed a feature check in
  `updateDocuments()` that the route's `provider.feature:requirements` middleware already does.
- `resources/views/auth/change-password.blade.php`, `resources/views/scholar/settings.blade.php` — hidden
  username field (not submitted) so password managers save the new password for the right account (Chrome
  warned about it on both pages).
- `app/Http/Controllers/ScholarshipCatalogController.php` — comment named a class that does not exist.

## Added

- DocBlocks for every class and method that had none: **20 classes and 208 methods** across controllers,
  models, services, requests, mailables, middleware, matching and AI classes (a scanner now reports 0).
- `pint.json` — Laravel preset, excludes `docs/` (Pint had reformatted a file inside the backups).

## Verified

- `php artisan test` → 323/323 passing (2987 assertions); `vendor/bin/pint --test` passes.
- Real data (MySQL `bakr`), every GET page of every portal as a real user of each role, inside a rolled-back
  transaction: 45 portal pages × 7 provider accounts + 120 detail / filter / `?edit=` pages → all 200/302
  (only DNSC's Disbursements / Requirements answer 404, by design).
- Headless Chrome: 45 pages (public + every portal) → no JavaScript console errors; no sideways overflow at
  500 px and 820 px.
- Apache: `.env`, `storage/`, `vendor/`, `config/`, `routes/` all answer 403; security headers present.
- Security sweep: no raw SQL built from input, every `{!! !!}` escapes its data with `e()`, every POST form has
  CSRF, no `innerHTML` with server data, no debug statements.

## Follow-ups

- Commit `config/` and `pint.json`.
- `ProviderScholarshipController::update()` still accepts a request without `section` (the whole form, used
  only by tests). Every real form sends a section; making it required means rewriting those tests.
- `config/app.php` sets `'timezone' => 'UTC'`: "past due" and call dates flip at 8 AM Manila time. Changing it
  to `Asia/Manila` is the owner's call (existing timestamps were saved in UTC); not changed here.
