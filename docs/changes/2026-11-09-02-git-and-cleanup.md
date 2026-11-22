# 2026-11-09 — Git/GitHub setup and duplicate class cleanup

**Roadmap item(s):** Phase 0: "Install Git and init the repo", "Remove the duplicate `app/Http/Services/PublicAnalyticsService.php`"

## Added
- Git for Windows 2.56 and GitHub CLI 2.103 (installed via winget).
- Git repo on `main`, pushed to https://github.com/MsAbella-27/BAKR. `.env` and `vendor/` stay excluded by `.gitignore`.
- Global git identity: Rochelle Abella <288599509+MsAbella-27@users.noreply.github.com>; credential helper = Git Credential Manager.

## Removed
- `app/Http/Services/PublicAnalyticsService.php`: outdated copy declaring `App\Services\PublicAnalyticsService` from the wrong folder. Nothing referenced it; the real class is `app/Services/PublicAnalyticsService.php`.

## Changed
- `docs/ROADMAP.md`: ticked both items; updated the Version control row.

## Verified
- `php artisan test` → 30/37 passing (unchanged; the 7 failures are the known legacy `StudentApplicationTest`).
- Autoload classmap never referenced the removed file.

## Follow-ups
- `gh` CLI is installed but not logged in (git push works through Credential Manager). Run `gh auth login` if you want PRs/issues from the CLI.
- Restart VS Code so its terminal picks up `git` on PATH.
