# 2026-11-17 — Adding a scholar creates their login (temporary password, change at first sign-in)

## Owner's decisions
- A scholar's account is created at the moment the provider adds them. The e-mail entered becomes the scholar's sign-in, and BAKR generates a temporary password that the scholar must change as soon as they sign in.
- The account is a normal **student** account. The scholar signs in at `/studentLogin` and lands on "My scholarship". `/scholarLogin` stays a prototype.
- The temporary password is **e-mailed** to the scholar. The provider never sees it.

## Flow
1. The provider adds the scholar, either with the form or an Excel import. **E-mail is now required.**
2. `ScholarAccounts::forEmail()` runs in the same database transaction as the scholar row:
   - **New e-mail:** a `role=student`, `status=active` user is created with a random 12-character temporary password (letters and numbers) and `must_change_password = true`.
   - **E-mail that already belongs to a student:** that account is linked. The scholar gets a "you were added" e-mail with no password, and their password is untouched.
   - **Provider or staff e-mail:** refused. Nothing is created and that import row is skipped.
3. After the commit, `ScholarAccountCreated` is sent immediately (not queued, so the password never sits in a job). Success sets `scholars.credentials_sent_at`. On failure the scholar is kept, the provider sees "Login not sent", and can press **Resend login details**.
4. The scholar signs in with the temporary password and is sent to `/student/password` ("Change your password"). The `password.changed` middleware (`EnsurePasswordChanged`) sends every student page back there until the password is changed. The new password must be at least 8 characters with letters and numbers, and different from the temporary one. Afterwards the scholar goes to My scholarship.
5. On the provider side, the scholar profile shows the account state: login not sent, e-mailed but not signed in yet, or active. "Resend login details" sets a new temporary password; the old one stops working. It is only available until the scholar sets their own password, and after that they use "Forgot password?".

## Security and privacy
- Role, status and `must_change_password` are set server-side only.
- The password is stored only as a hash and never appears on any provider page (tested).
- A provider can resend only for its own scholars (ProviderScope; another provider gets 404). Resending is throttled to 6 per minute.
- **Local `.env` has `MAIL_MAILER=log`**, so Laravel writes each e-mail, password included, to `storage/logs/laravel.log`. That is fine for testing, but use smtp for real scholars and keep logs private.
- Gmail SMTP sends at most about 500 e-mails a day. A very large import can hit that limit, and the failed ones show up in the import report for resending.

## Files
- New:
  - migration `2026_11_17_000003_add_scholar_accounts` (`users.must_change_password`, `scholars.credentials_sent_at`), already run on local `bakr`
  - `app/Services/Provider/ScholarAccounts.php`
  - `app/Mail/ScholarAccountCreated.php` and `resources/views/emails/scholar-account.blade.php`
  - `app/Http/Middleware/EnsurePasswordChanged.php`
  - `app/Http/Controllers/Auth/StudentPasswordChangeController.php` and `resources/views/studentChangePassword.blade.php`
  - `tests/Feature/ScholarAccountTest.php` (10 tests)
- Changed:
  - `ScholarRoster` (e-mail required, account per row, e-mails after the commit, refuses non-student e-mails)
  - `ProviderScholarRosterController` (messages, `resendLogin`)
  - `StudentAuthController` (sign-in → change page)
  - `Scholar` (`accountState()`, `canResendLogin()`) and `User` (cast)
  - `routes/web.php` (`student.password.change*`, `provider.scholars.login`, `password.changed` on the student group)
  - `bootstrap/app.php` (alias)
  - provider views: `scholars/create`, `scholars/show`, `scholars/index`, `help`
  - CSS: `bakr-provider.css`, `student-auth.css`
  - `ProviderRosterTest` (e-mails in the fixtures)
  - `CLAUDE.md`

## Follow-up: "the e-mail didn't go through"
- **Cause:** the local `.env` had Laravel's placeholder mail settings (`MAIL_MAILER=log`, `127.0.0.1:2525`, `hello@example.com`). The login e-mail for scholar #1 was only written to `storage/logs/laravel.log`, and because the log driver "succeeds", BAKR marked it as sent.
- **`.env`:** the `MAIL_*` lines were copied from the owner's full copy (`Downloads/d41d8cd9.env`): Gmail SMTP, `bakr.dnsc@gmail.com`, a 16-character App Password. The other lines were not touched, and `APP_KEY` in particular was kept (rule 8). The previous `.env` was backed up to the session scratchpad. `config:clear` was run, and the SMTP login test (connect, TLS, AUTH, no message) passed.
- **Code:** `ScholarAccounts::mailIsTestMode()`. With `MAIL_MAILER=log`, a send is not recorded (`credentials_sent_at` stays null, "Login not sent"), the provider gets a "test mode" message, and the Add page and profile show a warning. New test: `test_log_mailer_is_not_counted_as_sent`.
- **Scholar #1** (`gabaes.karlangelo@dnsc.edu.ph`): the login details were resent through Gmail at 15:49:03. The temporary password from the log no longer works.
- **Log:** the one logged scholar-login e-mail entry was removed from `storage/logs/laravel.log`. The other 20 entries were kept.
- Tests: 172/172 pass.

## Checks
- `php artisan test`: 171/171 pass.
- Live (MySQL), signed in as CHED: `/provider/scholars/new` returns 200, the e-mail field is required and the account note is shown. No real scholar was added, so no real e-mail was sent.
- Not checked: a real Gmail delivery, and the change-password page in a browser (light and dark).
