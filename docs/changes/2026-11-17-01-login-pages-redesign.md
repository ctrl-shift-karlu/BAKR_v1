# 2026-11-17 — Staff login pages use the provider/student login design

The scholar, officer and admin logins and the staff `forgotPassword` page used an older split-card design (Inter font, inline `style`/`onclick`, no dark mode, no theme toggle). They now use the same design as `providerLogin` and `studentLogin`: a photo background, an intro column, and the cream `sa-auth-card`.

## Changes
- New `resources/views/partials/staff-login.blade.php`. `scholarLogin`, `officerLogin` and `adminLogin` now only `@include` it with their own text. Bootstrap input groups, a show/hide password toggle (`student-auth.js`), "Keep me signed in", the theme toggle and "Back to home".
- `forgotPassword` was rebuilt on `sa-auth` with two steps (request → "Check your email"). `?from=scholar|officer|admin` sets the login page that the back links return to. The default is officer, as before.
- New `public/js/bakr-staff-login.js`: shows the "Signing in…" overlay (`.sa-signing`) and then opens `form[data-dashboard]`. `bakr-scholar-login.js` now only defines `window.bakrLoginCheck` (the same demo accounts and `scholarSession` storage as before).
- `public/css/student-auth.css`: per-portal background photos (`body.is-scholar/officer/admin/forgot`), `.sa-btn-line`, `.sa-sent-icon`, the signing-in overlay and a disabled gold button. Dark rules are in `bakr-dark.css`.

## Still prototypes
These pages still have no real authentication. The POST routes only redirect, and JS opens the static dashboard.

## Now unused (safe to delete)
`public/css/bakr-scholar-login.css`, `bakr-officer-login.css`, `bakr-admin-login.css`, `bakr-forgot-password.css`; `public/js/bakr-officer-login.js`, `bakr-admin-login.js`.

## Officer role dropped (same session)
Owner's decision: there is no Officer. **Admin** sees the whole platform's analytics and manages accounts. **Providers** manage their own scholarships and scholars (the DNSC Scholarship and Grants Unit is the `dnsc` provider).
- Removed: `officerLogin` and `officerDashboardF` views, `bakr-officer-dashboard.css/js`, `bakr-officer-login.css/js`. Backups are in `docs/changes/backups/*.2026-11-17.bak`.
- `routes/web.php`: the static-portal loop is now `['scholar', 'admin']`, so `/officerLogin` and `/officerDashboardF` return 404.
- Navbar "Sign in as": the "Scholarship Officer" entry is removed. `forgotPassword` defaults back to `adminLogin`.
- Docs: ROADMAP (new "Roles" section, an Admin account-management item, admin-only auth, and a note to drop the officer workflow from `adminDashboardF`), README, CONTRIBUTING and CLAUDE.md.
- Not changed yet: the `adminDashboardF` prototype still has an "Officers" page and a "send back to Officer" workflow. It will be rebuilt for real (Phase 2).

## Checks
All 7 auth pages return 200, and the new assets and photos load. `php artisan test` passes 135/135. The pages have not been checked visually in a browser.
