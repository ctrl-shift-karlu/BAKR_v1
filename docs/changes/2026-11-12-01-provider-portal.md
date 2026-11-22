# 2026-11-12 — Provider Portal

**Roadmap item(s):** Phase 2, "Provider Portal" (new item, ticked).

A 4th "Sign in as" entry, **Scholarship Provider**, opens `/providerLogin`. Five provider accounts (CHED/UniFAST, DOST-SEI, Cebuana Lhuillier Foundation, DNSC Scholarship and Grants Unit, Grab Philippines) share one dashboard view. The data shown depends only on the signed-in user's `provider_key`.

## Added
- `config/providers.php`: the only provider → scholarship-slug mapping (plus name, logo and icon). It reads `PROVIDER_<KEY>_EMAIL` / `_PASSWORD` from `.env`, and only the seeder uses them.
- `database/migrations/2026_11_12_000001_add_provider_key_to_users_table.php`: nullable, indexed `users.provider_key`. The `role` column is a string, so `provider` needs no schema change.
- `database/seeders/ProviderAccountSeeder.php`: creates or updates the 5 accounts by e-mail and hashes the password. It sets `role=provider`, `provider_key` and `status=active` server-side. An account is skipped (with a warning) if its credentials are empty, its password is under 12 characters, or its e-mail already belongs to a non-provider account.
- `app/Http/Controllers/Auth/ProviderAuthController.php`: `Auth::attempt` limited to `role=provider` + `status=active`. It also refuses an account whose `provider_key` isn't in the config. Includes logout.
- `app/Http/Controllers/Provider/ProviderDashboardController.php`: scholarships come from `whereIn('slug', config slugs)`. Qualifications are built verbatim from `rules` (`label`, `required`, `confirm_note`, `any_of` sub-labels). Stats are `COUNT(*)` of matches on **current** applications, grouped by scholarship × status. Groups under `PublicAnalyticsService::MIN_GROUP_SIZE`, including 0, are shown as "Fewer than 10". The page has no provider-wide totals, so a hidden group can't be worked out by subtraction.
- `resources/views/providerLogin.blade.php` (standalone, reuses `student-auth.css`) and `resources/views/provider/dashboard.blade.php`.
- `public/css/bakr-provider.css`, `public/js/bakr-provider.js` (password toggle, jump-link highlight). No inline `<style>`/`<script>`.
- `tests/Feature/ProviderPortalTest.php`: 18 tests in all (one of them runs once per provider through a data provider).

## Changed
- `resources/views/partials/navbar.blade.php`: added the Scholarship Provider entry.
- `routes/web.php`: `providerLogin` GET/POST (`guest`, POST `throttle:6,1`). Added the `provider.` group (`auth` + `role:provider`) with `dashboard` and `logout`. No provider key or ID appears in any URL.
- `bootstrap/app.php`: guests on `provider/*` are now redirected to `/providerLogin` instead of `/scholarLogin`. Pint also reformatted this small file.
- `.env.example`: empty `PROVIDER_*` keys.
- `docs/ROADMAP.md`, `CLAUDE.md`: portal noted, test count updated.

## Verified
- `php artisan test` → 54/54 passing.
- `vendor/bin/pint --test` on every new and changed PHP file → clean. A repo-wide `pint --test` still flags 25 **pre-existing** files (including `app/Matching`, which was out of scope), so the repo was not reformatted.
- Manual check with `playwright-cli` against a throwaway SQLite DB (since deleted). Login at 1440px; CHED dashboard at 1440px and 375px with no horizontal scroll; confirm rules, the any-of rule and the "Not published by provider" fallback all render. Fixed the privacy note wrapping into columns on mobile.
- **Not run against the live `bakr` MySQL DB** (needs approval).

## Follow-ups
- Live DB: back it up, then run `php artisan migrate` and `php artisan db:seed --class=ProviderAccountSeeder` (and `ScholarshipSeeder` if it hasn't been run).
- Provider password reset. The student broker deliberately refuses non-students.
- A "report a correction" flow for providers, feeding the future scholarship manager.
- Config data that may be outdated is listed in the session report; nothing in `config/scholarships.php` was changed.
