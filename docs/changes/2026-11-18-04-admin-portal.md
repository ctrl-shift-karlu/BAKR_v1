# 2026-11-18 — Admin portal (real, kept simple)

## Owner's request
Build the admin dashboard: analytics across matching and scholar monitoring, plus management of student, scholar and provider accounts. Keep it simple, because this is a 2nd-year BSIT final project.

Decisions taken to keep it simple:
- The admin sees named accounts (needed to manage them) but never passwords or matching answers.
- There is one admin account, from `.env`.
- Scholarships are read-only for the admin.
- No audit log, no account creation, no e-mail editing.

## What was built
- **Sign-in:** `/adminLogin` is real (`AdminAuthController`, role `admin`, active only, throttled).
  - The account comes from `AdminAccountSeeder` using `ADMIN_NAME` / `ADMIN_EMAIL` / `ADMIN_PASSWORD` (min. 12 characters), added to `.env.example` and `config/bakr.php`.
  - Forgot password works for the admin too.
  - `/adminDashboardF` redirects to `/admin`.
  - `partials/staff-login` was removed (backup `backups/staff-login.blade.php.2026-11-18c.bak`).
- **Pages** (`Admin\AdminController`, `admin.layout` on the shared dashboard shell: burger, red sign-out dialog):
  - **Overview:** totals, a "needs attention" list (logins not e-mailed, uploads waiting more than 7 days, providers that haven't opened this semester's checklist, open messages, e-mail in test mode) and system status (semester, e-mail, AI).
  - **Analytics:**
    - matching funnel: accounts, answered the form, at least one match
    - result counts per scholarship
    - the required rule that rules students out most often
    - per provider: scholars by status, this semester's requirements (approved/all), and stipends (released, pending, locked, on hold)
    - all counts only, never anyone's answers
  - **Accounts:**
    - Students / Scholars / Providers tabs, with search
    - deactivate (with a confirmation) or reactivate
    - send a password-reset link
    - admin accounts and your own account can't be changed here
  - **Scholarships:** read-only. Shows application status, "Check again" when the official source is more than 6 months old, the maintaining documents, the number of scholars and provider edits.
  - **Messages:** open/all filter, reply by e-mail (mailto), mark handled or reopen, show or hide shared feedback on the public site (only if the sender agreed).
- `RoleMiddleware` signs out an account that was deactivated during an open session.
- Navbar and 403 page know the admin role.
- `bakr-dashboard.js` has a small `form[data-confirm]` handler.

## Not used any more (kept on disk)
`resources/views/adminDashboardF.blade.php`, `public/css/bakr-admin-dashboard.css`, `public/js/bakr-admin-dashboard.js`, `public/js/bakr-staff-login.js`, `public/js/bakr-scholar-login.js`. They can be deleted.

## Checks
- `php artisan test`: **200/200** (new `AdminPortalTest`).
- Live smoke test (local MySQL, GET only): all 8 admin page variants return 200.
- No admin account exists yet in the local database. Set `ADMIN_EMAIL` and `ADMIN_PASSWORD` in `.env`, then run `php artisan config:clear` and `php artisan db:seed --class=AdminAccountSeeder`.
- Not checked in a browser.
