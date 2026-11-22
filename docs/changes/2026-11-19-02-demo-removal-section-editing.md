# 2026-11-19 ? Demo removal + per-section editing (My scholarships)

- Removed demo data/code: `DemoScholarSeeder` (backup in `docs/changes/backups/demo-removal-2026-11-19/`), sample numbers + badge on About, `demo_password`, `DEMO_SCHOLAR_PASSWORD`, demo-seeder test.
- My scholarships: every section has its own Edit / form / Save (`?edit=overview|qualifications|benefits|documents|period|apply`); saving one section changes only its fields (`SECTION_FIELDS`, hidden `section`). Hero "Edit information" button and the one big form were removed. New `provider/partials/section-foot`. Edit header no longer sticky on small screens; textareas taller.
- Files: `resources/views/provider/scholarships.blade.php`, `provider/partials/section-foot.blade.php` (new), `provider/partials/maintaining-docs.blade.php`, `ProviderScholarshipController.php`, `public/css/bakr-provider.css`, `tests/Feature/ScholarshipEditTest.php`, `CLAUDE.md`. No DB changes.
