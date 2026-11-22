# 2026-11-13 — Stipend release type (per semester / per month); no Midyear on Disbursements

## Changed
- `stipend_disbursements` gets two columns (migration `2026_11_13_000001_add_release_type_to_stipend_disbursements`):
  - `release_type`: `semester` | `monthly`, default `semester`. Existing rows become `semester`.
  - `period_month`: `YYYY-MM` for monthly rows, `''` for semester rows.
  - The unique key changes from (scholar_id, term) to (scholar_id, term, period_month). The new key is added before the old one is dropped, because MySQL needs an index for the `scholar_id` foreign key.
  - Rolling back deletes monthly rows.
- **Disbursements page (every provider):**
  - The Semester filter offers only 1st/2nd Semester terms; Midyear is gone. During June–July the page defaults to the 2nd Semester that just ended (`AcademicTerm::currentSemester()`).
  - "Prepare" has a **Release** choice: *Per semester* makes one record per scholar; *Per month* makes one per month of the semester (1st: Aug–Dec, 2nd: Jan–May). Each record's amount is the scholar's stipend amount, so the field is now labelled "Stipend per release".
  - A scholar already prepared for that semester is skipped, whichever type was used.
  - Table has a new **Release** column ("Per semester", or "Per month" with the month). The row form is now status + Save on one line and the reference underneath.
- Scholars list: a monthly scholar's stipend status shows "3/5 months released".
- Scholar page and student "My scholarship": stipend history shows the month for monthly rows.
- Sidebar Disbursements count, Overview stipend chart and scholars list use the current semester, so they don't go blank during Midyear.
- `DemoScholarSeeder`: stipends only for semesters; every fourth demo scholar is paid monthly (past months released).
- Requirements page still lists Midyear terms (not part of this change).

## Added
- `AcademicTerm::currentSemester()`, `semesterOptions()`, `isValidSemester()`, `isMidyear()`, `months()`.
- `StipendDisbursement::RELEASE_TYPES`, `releaseTypeLabel()`, `periodLabel()`.
- Tests: `tests/Unit/AcademicTermTest.php` (3); `ProviderOperationsTest` monthly prepare + "no Midyear" tests. **104/104 pass.**

## Database
- Backed up to `storage/app/private/backups/bakr-before-release-type-20261113-193426.sql` (18 tables).
- Migrated the real `bakr` MySQL DB: both columns and the new unique key are in place, and the old key was dropped. The table had no rows yet.
