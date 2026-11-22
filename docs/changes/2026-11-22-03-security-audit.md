# 2026-11-22 — Security and data-handling audit

Checked: SQL injection, input validation, authentication, role restrictions, record ownership (IDOR),
file uploads, password handling, sessions, restricted pages, secrets / error exposure, XSS, CSRF.

## Fixed

1. **Critical: the whole project was served by Apache.** BAKR sits in `htdocs/BAKR`, so
   `http://localhost/BAKR/.env` (DB, Gmail App Password, Gemini key, `APP_KEY`, admin password) and
   `storage/logs/laravel.log` (in `MAIL_MAILER=log` mode it holds temporary scholar passwords) answered 200.
   Apache listens on every network interface (`Listen 80`). New root `.htaccess`: everything outside
   `public/` → 403, `/BAKR/` → `/BAKR/public/`, no directory listings, dotfiles / logs / databases denied
   even without mod_rewrite. Checked: `.env`, logs, `vendor/`, `storage/app/private/` → 403; the site,
   CSS and `manifest.json` → 200. Apache's access log showed only requests from this machine (`::1`).
2. **No security headers.** New `App\Http\Middleware\SecurityHeaders` (web group): `X-Frame-Options:
   SAMEORIGIN` (clickjacking on Terminate / Release / Deactivate), `X-Content-Type-Options: nosniff`,
   `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy`. A header a response already
   set is kept. No CSP (the CDN assets would need a careful allow-list).
3. **Other sessions survived a password change.** `User::endSessions()` deletes the account's rows in the
   `sessions` table: after a password change (all but the current session), after a reset (all), and when
   the admin deactivates an account (all, instead of waiting for the next portal request).
4. `.env.example`: what to set when going live (`APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`,
   `SESSION_ENCRYPT=true`, …).

## Checked, no change needed

- SQL: every raw fragment is static or uses bound `?`; searches use bound `LIKE` inside grouped closures.
- Roles / ownership: every portal route has `auth` + `role:`; provider IDs go through `ProviderScope`,
  scholar / student IDs through `user_id` scopes; `role`, `status`, `provider_key` are not mass-assignable.
- Uploads: content-based `mimes:` + size from the item's own settings, random file names on the private
  disk, inline preview only for PDF/JPG/PNG with `nosniff`. Excel import: size limit, XML without external
  entities, part-size and row limits.
- Passwords: `hashed` cast, min 8 with letters + numbers, temporary passwords from `Str::password`, never
  logged; reset replies never reveal whether an e-mail exists. Login: server-set role/status, throttled,
  session regenerated; logout invalidates the session and token.
- XSS: every `{!! !!}` prints values passed through `e()`; links from providers are http(s) only.
- CSRF: no exclusions; the fetch / XHR code sends the token.
- Secrets: none in the source; the Gemini key goes in a header; AI failures log only status codes.

## Owner to-do

- **Change the secrets that were readable before the fix** if this PC was on a shared network: Gmail App
  Password, `BAKR_GEMINI_KEY`, `ADMIN_PASSWORD` / provider passwords, MySQL password. **Do NOT change
  `APP_KEY`** (saved matching answers would become unreadable).
- Before going live or opening a tunnel: `APP_DEBUG=false` and the other settings listed in `.env.example`.

## Files

- New: `.htaccess`, `app/Http/Middleware/SecurityHeaders.php`, `tests/Feature/SecurityHardeningTest.php`
- Changed: `bootstrap/app.php`, `app/Models/User.php`, `app/Http/Controllers/Auth/StudentPasswordChangeController.php`,
  `app/Http/Controllers/Auth/StudentPasswordResetController.php`, `app/Http/Controllers/Admin/AdminController.php`,
  `.env.example`, `CLAUDE.md`
- Database: none

Tests: 324/324 pass.
