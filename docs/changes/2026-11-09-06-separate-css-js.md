# 2026-11-09 — Separate CSS/JS from views; shared navbar/footer everywhere it belongs

**Roadmap item(s):** code organisation (follow-up to the UI/UX audit)

Goal (user request): every page built like Home — CSS in `public/css/bakr-<page>.css`, JS in `public/js/bakr-<page>.js`, public pages on the shared navbar + footer, embedded copies removed.

## Changed
- `resources/views/scholarshipFinder.blade.php` (3,053 → 540 lines) — now `@extends('layouts.app')`: embedded navbar, footer, navbar-scroll script, and the "shared navbar" `!important` style block removed. PHP engine at the top unchanged. Its 2,225-line merged stylesheet (old home + scholarships + navbar + footer copies) filtered down to the finder's own `.sf-*` rules → `public/css/bakr-finder.css` (~1k lines); step loader + results count-up → `public/js/bakr-finder.js`. Fixed-navbar clearance now uses the shared `--nav-offset`.
- `adminDashboardF` (7,727 → 689), `officerDashboardF` (10,481 → 1,584), `scholarDashboardF` (9,430 → 802), `scholarLogin` (710 → 119), `officerLogin` (494 → 85), `adminLogin` (659 → 116), `forgotPassword` (479 → 79): every inline `<style>` → `bakr-<page>.css`, every inline `<script>` → `bakr-<page>.js` (same order, loaded where the last block was). No visual or behaviour change intended.
  - Blade inside the moved code: CSS `url("{{ asset(...) }}")` → `url("../images/...")`; login logo preload reads `document.body.dataset.logo` (`<body data-logo="{{ asset(...) }}">`); scholar sign-in guard → `bakr-scholar-dashboard-guard.js`, still in `<head>`, login URL via `data-login-url`.
- Login pages and staff dashboards intentionally do **not** get the public navbar/footer: login pages match `studentLogin` (standalone, "Back to home"); dashboards have their own sidebar navigation.

## Added
- `public/css/bakr-{finder,admin-dashboard,officer-dashboard,scholar-dashboard,scholar-login,officer-login,admin-login,forgot-password}.css`
- `public/js/bakr-{finder,admin-dashboard,officer-dashboard,scholar-dashboard,scholar-dashboard-guard,scholar-login,officer-login,admin-login,forgot-password}.js`
- Backups of every original view: `docs/changes/backups/*.blade.php.2026-11-09.bak`

## Verified
- `php artisan test` → 36/36. `node --check` on every new JS file.
- No `<style>` or inline `<script>` left in any view.
- Finder, full run in Chrome (desktop + 390 px): intro → 22 questions (21 swapped in place via fetch) → review → finish → results → details modal; shared navbar + footer present; no JS errors, no 404s, no horizontal scroll.
- Old vs new layout fingerprint (position/size/colour of the first 400 elements): admin, officer and scholar dashboards 0/400 differences; admin login differs only by the logo mid-animation. Scholar guard still redirects signed-out visitors to /scholarLogin.

## Follow-ups
- `php artisan serve` on Windows intermittently truncates large static files (seen on the 250 KB dashboard CSS) → dashboards can look half-styled under the dev server. Apache/XAMPP serves them fully.
- The three staff login CSS files are ~80% the same; could be merged into one `bakr-staff-auth.css` later.
- Scholar dashboard references `images/BAKR_logo.png`, which does not exist (pre-existing).
