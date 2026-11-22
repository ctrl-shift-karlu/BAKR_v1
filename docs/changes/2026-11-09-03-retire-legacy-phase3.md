# 2026-11-09 — Retire legacy Phase 3 (document upload) code

**Roadmap item(s):** Phase 0: "Retire legacy Phase 3 code"

## Removed
- `app/Services/StudentApplicationService.php`
- `app/Policies/ApplicationDocumentPolicy.php` (folder `app/Policies` now empty, removed)
- `app/Models/ApplicationDocument.php`, `app/Models/DocumentType.php`, `app/Models/StudentProfile.php`
- `app/Events/StudentApplicationSubmitted.php` (folder `app/Events` now empty, removed)
- `database/seeders/DocumentTypeSeeder.php`
- `tests/Feature/StudentApplicationTest.php` (the 7 failing legacy tests)

## Added
- `database/migrations/2026_11_09_000001_drop_legacy_phase3_tables.php`: drops `application_documents`, `student_applications`, `student_profiles`, `document_types`. `down()` recreates the empty schema (no data).
  The old create migrations are kept so a fresh install still runs in order.

## Changed
- `docs/ROADMAP.md`, `CLAUDE.md`, `README.md`: test count and legacy notes.

## Verified
- `php artisan test` → 30/30 passing.
- No remaining references to the removed classes in `app`, `routes`, `config`, `database`, `tests`, `bootstrap`, `resources`.
- Migration up → rollback → up on a scratch SQLite DB: OK; `student_consents`, `campuses`, `programs` and all active tables intact.

## Live database
- Backed up to `%USERPROFILE%\Documents\bakr-backups\bakr-2026-11-09-before-legacy-drop.sql` (17 tables; kept outside htdocs so Apache can't serve it).
- Ran the migration on `bakr`: dropped tables held 1 profile, 1 application, 3 document rows, 3 document types. 13 tables remain.

## Still open
- `storage/app/private/student-documents/` still holds 3 old uploaded files (git-ignored, not committed). Decide whether to delete them (they are student documents, so DPA 2012 data minimisation says yes, unless needed).
