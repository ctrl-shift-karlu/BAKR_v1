# 2026-11-18 — Official admin account, prototype clean-up, graphical analytics

## Owner's requests
1. Put the official admin account in the database.
2. Connect all files accordingly.
3. Make the admin analytics graphical: pie charts and more, where appropriate.

## 1. Admin account
- `ADMIN_EMAIL` / `ADMIN_PASSWORD` were already set in the local `.env` (the values were not read or printed).
- Ran `php artisan config:clear` and `php artisan db:seed --class=AdminAccountSeeder`. Result: 1 active admin account.
- Sign in at `/adminLogin`. "Forgot password?" there works too.

## 2. Files connected
- Every login (student, scholar, provider, admin) now uses real authentication.
- Removed the leftover static prototype files that nothing used any more:
  - `resources/views/adminDashboardF.blade.php`
  - `public/css/bakr-admin-dashboard.css`
  - `public/js/bakr-admin-dashboard.js`
  - `public/js/bakr-admin-login.js`
  - `public/js/bakr-staff-login.js`
  - `public/js/bakr-scholar-login.js`
- Copies are in `docs/changes/backups/prototypes-2026-11-18/`.
- `/adminDashboardF` still redirects to `/admin`.
- The scholar prototype (`scholarDashboardF` + `bakr-scholar-dashboard*`) is kept as the design guide. It is unrouted.

## 3. Graphical analytics (`/admin/analytics`)
Server-rendered SVG and CSS. No chart library and no chart JavaScript. Three reusable partials in `resources/views/admin/partials/`:

| Chart | Form | Why that form |
|---|---|---|
| All current results | donut | part of a whole, 4 groups |
| Scholars by status | donut | part of a whole, 5 groups |
| Stipends this semester (released / not yet / locked / on hold) | donut | part of a whole, 4 groups |
| From sign-up to a match | bars | funnel steps (amounts) |
| What rules students out most | bars | ranked amounts |
| Scholars per provider | bars | ranked amounts |
| Released this semester per provider (₱) | bars | ranked amounts |
| Results per scholarship | 100% stacked bars | how each scholarship splits up |
| Maintaining requirements per provider | 100% stacked bars | how each provider's documents split up |

- **Colours:** the dataviz skill's validated palette, as `--viz-*` tokens in the new `public/css/bakr-admin.css`.
  - Status colours: good / warning / serious / critical. Categorical colours: blue and violet. Plus a neutral gray.
  - Each meaning keeps one fixed colour everywhere (e.g. green is always approved, released, eligible or active).
  - Dark steps are in `bakr-dark.css`.
  - The palette validator could not be run here because Node isn't installed, so the documented, already-validated values were used unchanged.
- **Accessibility:** a legend with values and % on every chart, hover tooltips (SVG `<title>` / `title`), and a "Show as table" `<details>` on every chart, so colour never carries meaning alone. A 2px surface gap between segments.
- **Privacy:** still counts only. `AdminPortalTest` asserts that a student's answers and e-mail never appear.

## Checks
- `php artisan test`: **200/200**.
  - One run hit a Windows file lock on compiled views ("Access is denied" renaming `storage/framework/views/*.tmp`; Apache probably had the files open). It passed after `php artisan view:clear`.
- Live, as the real admin account (GET only): every admin page returns 200. Analytics renders 3 donuts, the bar charts and 2 stacked charts; a chart with no data shows "No data yet".
- Not checked in a browser (light and dark, phone width).
