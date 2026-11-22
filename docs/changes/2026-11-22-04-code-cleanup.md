# 2026-11-22 · Whole-project code cleanup

Owner's request: remove unused and duplicate code, dead CSS/JS/HTML, outdated comments and debug
statements; fix formatting and confusing names; delete unnecessary files only after checking they are
unused. Nothing a person sees or does changed.

Backup of everything before the cleanup: `docs/changes/backups/cleanup-2026-11-22/` (app, resources,
routes, config, database, tests, public/css, public/js, sw.js, manifest, offline page, composer.json).

## How "unused" was checked

- Small PHP scanners (no Node or Python here): every method defined in `app/` vs. every reference in
  `app`, `resources`, `routes`, `config`, `database`, `tests`, `bootstrap`, `public/js`; every Blade view
  vs. `view()` / `@include` names; every CSS/JS file vs. the layouts; every CSS class vs. the words used in
  views, PHP, JS and config. Classes built from data (`is-{{ $status }}`, `bk-c{{ $i }}`, `pv-sector-…`,
  tones like `cat1`) and Laravel's pagination markup (`page-item`, `page-link`) were checked by hand and kept.
- Pint (`no_unused_imports`) for PHP imports. No `dd()` / `dump()` / `console.log` / commented-out code was found.

## Removed

- **Files:** `app/Http/Requests/FinderCheckRequest.php` (public Finder, removed 2026-11-19),
  `app/Models/ProviderApplication.php` (share feature, removed 2026-11-18; the `provider_applications` table
  stays for old rows), `tests/Unit/ExampleTest.php` (Laravel placeholder).
- **PHP:** `Scholarship::providerApplications()`, `Scholarship::maintainingDocument()`, `Scholar::isRosterOnly()`,
  `PublicAnalyticsService::prepareTrend()`, `ProviderScholarController::labels()`,
  `ProviderRequirementController::labels()` (never called); `provider_application_id` in `Scholar::$fillable`.
- **CSS** (only rules for classes no page renders):
  - `bakr-dashboard.css`: the tiles, board/columns/rows, "at a glance" ledger and top-bar families, the old
    prototype sidebar classes (`.brand-name`, `.scholar-card` in the sidebar, `.sidebar-close`, `.admin-mini`…),
    `.kpi-card` / `.panel-card` / `.page-header` / `.hero` harmonising rules, `.dash-dot`, `.dash-label`,
    `.dash-row-actions`. Header comment updated (no Officer).
  - `student-portal.css`: Finder styles, old stat tiles / score ring / match rows / tabs / share form /
    consent box, `.sd-meter`, `.sd-nav`, `.sp-me`, unused scholar-portal bits (`.sc-overview`, `.sc-link`,
    `.sc-id-meta`, `.sc-office`…), and a run of empty comment stubs.
  - `bakr-home.css`: the unused hero "glass result card" (`.bk-hero-visual`, `.bk-result*`, `.bk-chip*`,
    `.bk-hero-sources`, `bk-hero-float`), `.nx-mark`, empty comment stubs; a comment glued to `@media` fixed.
  - `bakr-provider.css`: old detail-card layout (`.pv-card`, `.pv-grid`, `.pv-block`…), `.pv-save`,
    `.ms-savebar`, `.pv-roster-tag`, `.sp-id-meta/-pills`, `.md-head`.
  - `student-auth.css`: prototype login pieces (`.sa-topbar`, `.sa-hero`, `.sa-card`, `.sa-signing*`,
    `.sa-spinner`, `.sa-btn-line`, `.sa-link-btn`…), `.form-select`.
  - `bakr-layout.css`: `.nx-success*`, `.nav-register`, `.bk-main-offset`, `.navbar-toggler-icon`.
  - Smaller: `.feature-pill*` + `bk-shimmer` (scholarships), `.bk-insight-grid` / `.bk-legend-row` (about),
    `.nx-btn-ghost` (core), `.nx-feedback-form` (pages), `.bk-glass` / `.sa-spinner` (app mode), and the
    matching dark-mode rules in `bakr-dark.css`.
- **JS:** the `.dash-tile-bar[data-fill]` handler in `bakr-provider.js` (no tile bars exist).

## Duplicates merged, names clarified

- The Help-form validation was copied in three controllers → `Controller::validateHelpMessage()`
  (student, scholar, provider Help).
- `ScholarRequirementController::download()` re-listed the previewable file types → uses
  `ScholarRequirement::canPreview()` like the provider side.
- `RuleEvaluator`: `r()` → `result()`, `say()` → `describeAnswer()` (dropped its unused `$rule` argument),
  `num()` → `formatNumber()` (it formats, `AnswerNormalizer::num()` parses).

## Formatting

- Pint: `bootstrap/providers.php`, `tests/Feature/InstallableAppTest.php`, unused imports in four controllers.
- Trailing spaces and runs of blank lines removed in CSS, JS and one Blade view.
- Public-page section markers changed from `<!-- -->` (sent to every visitor) to Blade `{{-- --}}`; the
  outdated "VIDEO PLACEHOLDER, put your file here" note on About now just describes the video.
- `bakr-dashboard.js` header: it serves all four portals.

## Tests

323/323 (324 before, minus the placeholder `ExampleTest`). Every stylesheet still has balanced braces and
comments; Home, About, Scholarships and Student login checked in headless Chrome.
