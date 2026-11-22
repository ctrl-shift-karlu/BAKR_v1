# 2026-11-12 — Provider operations: applicants, scholars, stipends, requirements, scholarship editing

**Roadmap item(s):** Phase 2, "Provider operations" (new item, ticked). It also does the catalogue part of Phase 4 "Blade components" (the public Scholarships page is now rendered from the database).

## Decisions (asked and answered in the session)
- **Palette:** vibrant BAKR (emerald, sky, sunflower, coral), in `bakr-dashboard.css` tokens. It applies to all dashboards.
- **Applicants:** a student opts in per scholarship ("Apply: share my profile") and ticks a consent box (Data Privacy Act).
  - The provider sees the student's name, e-mail, program, campus and year level (an encrypted snapshot), plus the live match status.
  - No other questionnaire answers are shared. The student can withdraw until the provider decides; withdrawing clears the snapshot.
  - Everyone who has not opted in stays an aggregate (groups under 10 hidden).
- **Requirements:** real file uploads.
  - Only PDF, JPG or PNG, up to 5 MB, checked by the `mimes` rule on file content.
  - Files go to the private `local` disk as `scholar-requirements/{scholar}/{random}.{ext}`, and are streamed only to the owner or that scholarship's provider.
  - Uploads happen in the **student portal** ("My scholarship"), because the scholar portal is still a static prototype without login.
- **Editing:** providers edit text and qualifications.
  - Qualification edits are limited to each existing rule's label, confirm note and "Required" flag, plus the number for `gte` / `lte` / `income_lte` rules (including inside `any_of`).
  - Rule type, fact, and adding or removing rules are not possible; those are tied to `ApplicationSchema`.
  - Every save writes a `scholarship_revisions` row. A rules change re-matches every current application (`ScholarshipApplicationService::rematch()`, now actually called).

## Added
- Migration `2026_11_12_000002_create_provider_operations_tables`:
  - New tables: `provider_applications`, `scholars`, `scholar_requirements`, `stipend_disbursements`, `scholarship_revisions`.
  - New `scholarships` columns: `renewal_requirements` (JSON) and `updated_by`.
- Models: `ProviderApplication`, `Scholar`, `ScholarRequirement`, `StipendDisbursement`, `ScholarshipRevision`.
- `Scholarship::qualifications()` and `shortName()`.
- `App\Support\AcademicTerm`: PH term labels such as "AY 2026–2027 · 1st Semester", and the list of terms offered.
- `App\Services\Provider\ProviderScope`: the only way provider code reads data. IDs from URLs are resolved inside it, so another provider's records are 404.
- `App\Services\Provider\ScholarshipEditor`: validation (percent 0–100; income in whole pesos), revision, re-match.
- Provider controllers (`ProviderController` base, plus Applicant, Scholar, Disbursement, Requirement, Scholarship) and their views under `resources/views/provider/`. The views share `provider/layout.blade.php` and `provider/partials/review-form.blade.php`.
- Student: `StudentShareController` (share / withdraw), `StudentScholarController` (My scholarship, upload, download) and `student/scholarship.blade.php`. Results cards now offer sharing.
- `ScholarshipCatalogController` with a DB-driven `scholarships.blade.php`. The hand-written version is backed up in `docs/changes/backups/scholarships.blade.php.2026-11-12.bak`.
- `partials/scholarship-qualifications.blade.php`, used by both the public modal and the provider preview.
- `DemoScholarSeeder` (demo data only; refuses production; `@demo.bakr.test` e-mails; `DEMO_SCHOLAR_PASSWORD`).
- Tests: `ProviderOperationsTest`, `StudentScholarTest`, `ScholarshipEditTest`.

## Changed
- `config/providers.php`: `demo_password`. `.env.example`: `DEMO_SCHOLAR_PASSWORD`.
- `routes/web.php`:
  - provider routes: applicants, scholars, disbursements, requirements, scholarships
  - student routes: share, withdraw, scholarship, upload, file
  - `/scholarships` now uses a controller
- `bakr-dashboard.css`: vibrant tokens, plus shared components (KPIs, pills, buttons, forms, filters, tables, progress, flash, pagination, requirement lists).
- `bakr-provider.css`: page helpers and the rule editor. `bakr-provider.js`: progress and tile widths from `data-*`, auto-submit filters, bulk select.
- `student/layout.blade.php`: a "My scholarship" link for scholars. `student-portal.css`: share and scholarship styles.
- `bakr-scholarships.css`: qualification styles in the modal. It also fixes an existing overlap of the check icons in "Required Documents", where `bakr-layout.css` positions them absolutely.
- `ProviderPortalTest`: qualification and stats assertions now point at `/provider/scholarships` and `/provider/applicants`.

## Verified
- `php artisan test` → **79/79** passing; `pint --test` clean on every new or changed PHP file.
- `playwright-cli` against a throwaway SQLite DB with the demo seeder (deleted afterwards, along with the 94 demo PDFs it wrote):
  - all 6 provider pages at 1440px, plus student "My scholarship" and the public modal
  - all 6 provider pages at 375px: no horizontal scroll
- Fixed during the check:
  - KPI icons were shrunk by a text rule.
  - Filters stretched full width.
  - `.visually-hidden` labels inside scrolling tables widened the page (`.dash-table-wrap` is now `position: relative`).
  - Grid children overflowed on the editor.
  - A Blade directive was glued to a word (`status@endif`).
  - The demo seeder hashed 88 passwords (now hashed once).

## Follow-ups
- **Live DB:** back up `bakr`, then `php artisan migrate`. `DemoScholarSeeder` is optional and for trying things out only.
- **Retention:** decide how long requirement files are kept and delete them when a scholar record is removed (currently only replaced files are deleted).
- **Notifications:** e-mail students when a requirement is reviewed or a stipend is released.
- **Governance:** consider BAKR-staff approval of qualification edits before they go live, and a "revert to revision" action.
- The scholar portal prototype still has no login; once it does, move "My scholarship" there.
