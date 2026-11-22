# 2026-11-16 — CONTRIBUTING.md for a new collaborator

The owner is adding a collaborator to the GitHub repository. New `CONTRIBUTING.md`:

**Setup**
- **Requirements:** PHP 8.4+ (XAMPP's 8.2 is too old), Composer, MySQL; npm is not needed.
- **Clone and `composer install`.**
- **`.env`:** keys are shared privately; use the owner's `APP_KEY` when using the owner's dump, because the answers are encrypted.
- **Database:** option A, the owner's dump (includes campuses and programs, which have no seeder); option B, an empty database plus seeders.
- **Run and test:** `php artisan serve`; `php artisan test` and Pint.

**Workflow**
- Branch, then a pull request into `main`; no direct pushes and no force-pushes.
- A session log in `docs/changes/` for each work session.
- The non-negotiable rules.
- Scholarship data: read `docs/sources/*/NOTES.md`; use `bakr:sync-scholarship`, never `ScholarshipSeeder` on real data.
- Front-end conventions.
- A troubleshooting table.

**Also fixed**
- README and CLAUDE.md said PHP 8.3+. The locked packages need **8.4.1+** (Apache on XAMPP PHP 8.2 returns a 500 "Composer detected issues in your platform"). Both now say so, and README links CONTRIBUTING.md and `docs/sources/`.

Documentation only; no code changed.
