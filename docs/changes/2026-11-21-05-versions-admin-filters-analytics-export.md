# 2026-11-21 · Matching-form versions, admin scholar filters, analytics export

## 1. Matching-form version control (fixed + made usable)
- **Bug:** answers are stored as JSON, which writes a whole-number grade `88.0` as `88` (read back as int).
  `$current->answers === $answers` was then false, so re-submitting UNCHANGED answers with a whole-number
  grade always created a new version. Fixed with `ScholarshipApplicationService::sameAnswers()` (both sides
  through the same JSON round trip, keys sorted).
- My profile › Saved versions: each row opens the version, shows date + time and "N answers changed".
- New page `student.profile.version` (`/student/profile/versions/{version}`, own versions only, the current
  one redirects to My profile): answers per part, the ones different now marked "Different now" + "Now: …".
- "Use these answers again" → `student.application.show?version=N` fills the form with that version
  (notice at the top); nothing is saved until the student submits (Privacy agreement as always) → new version.
- `ApplicationSchema::changedFields()`; shared partials `student/partials/answers`, `student/partials/versions`.

## 2. Admin › Accounts › Scholars
- Filters: scholarship (`?scholarship=<id>`) and campus (`?campus=<official name>`; stored campus, or for
  older rows the only campus offering their program, same as `Scholar::campusLabel()`). Scholars tab only.
- Sort by name on every tab: `?sort=name_asc|name_desc` (`AdminController::NAME_SORTS`).
- Search button sits beside the box from 576px; under it, full width, on phones (`.ac-*` in `bakr-admin.css`).
  Selects apply on change (`public/js/bakr-admin-accounts.js`, `<noscript>` Apply). Campus shown per scholar.

## 3. Admin › Analytics: Print / Save as PDF / Download Excel
- Print and Save as PDF open the browser print window (`public/js/bakr-admin-print.js`; light colours while
  printing). Print styles (`@media print` in `bakr-admin.css`): no sidebar/hero/buttons, one column, every
  collapsed row shown, BAKR print header with semester + date.
- Excel: `admin.analytics.export` (`/admin/analytics/export.xlsx`) → 5 sheets (Summary, Matching, Scholars per
  scholarship + per campus, Sign-ups, Providers), real numbers, bold headers. Counts only.
  New `App\Support\Spreadsheet::workbook()` (multi-sheet; `xlsx()` unchanged).

## Tests
`StudentVersionHistoryTest` (4 new), `AdminPortalTest` (+2). Full suite: 289/289 pass.
