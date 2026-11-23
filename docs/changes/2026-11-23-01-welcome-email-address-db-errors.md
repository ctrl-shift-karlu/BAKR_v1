# 2026-11-23 — Glass welcome card, scholar e-mail change, address in parts, database-down page

**Roadmap item(s):** owner's request (home welcome pop-up, contact number as a fallback, editable name and
e-mail on the provider side, locked birthday / gender, address in parts, friendly page when the database is off).

## Home welcome pop-up
- `resources/views/partials/modals/welcome.blade.php` — frosted dark-green glass card: logo, title, text and two
  actions (Find my matches → `route('find')`, Browse scholarships → `route('scholarships')`).
- `public/css/bakr-layout.css` — new `.nx-welcome-card` (blur 18px, soft gold glow); solid gradient when blur is
  unavailable (`@supports not (backdrop-filter…)`) or switched off (`html.is-app`). Old `.nx-modal-band`,
  `.nx-avatar`, `.nx-welcome-kicker` styles removed.

## Provider › scholar profile
- **Name editable** (Personal card, `full_name`; also the scholar account's `users.name`). **Birthday and gender are
  locked** once recorded ("Cannot be changed"); an older row that never had them can get them once.
- **Address in parts:** Street (house no., street, purok / barangay), City / Municipality, Province — Add form, profile
  Contact card, Excel/CSV columns ("Street", "City / Municipality", "Province"; a single "Address" column is no longer
  accepted) and the template. `scholars.address` keeps the three joined, so older readers still work;
  `Scholar::addressLabel()` / `hasFullAddress()`; `ScholarRoster::addressFields()`.
- **Change e-mail** (`provider.scholars.email`, `…email.cancel`, `App\Services\Provider\ScholarEmailChange`): the new
  address is kept as `pending_email` and a one-time link (sha256 token, 72 h) is e-mailed to it
  (`ScholarEmailChangeRequested`). Only when the scholar opens it (`scholar.email.confirm`,
  `ScholarEmailConfirmController`, page `auth/email-confirmed`) the e-mail changes on the row, the account's other rows
  and the account; the old address gets `ScholarEmailChanged`. A temporary password never used → new login details to
  the new address. Refused: same address, another BAKR account's e-mail, another scholar of the scholarship.
  Withdraw while waiting. Mail failure / test mode → nothing kept.
- **Contact number as the fallback:** Call (`tel:`) and Text (`sms:`) buttons next to the mobile number; the scholar
  login and the scholar "Forgot password" page tell scholars who lost their e-mail to call / text their provider.

## Database off (XAMPP's MySQL stopped)
- `App\Support\DatabaseStatus` — opens the PDO connection inside try / catch, recognises PDO connection failures
  (MySQL codes 1044/1045/1049/2002/2003/2005/2006/2013, Laravel's lost-connection messages, SQLite "unable to open"),
  answers 503 + Retry-After with `resources/views/errors/database.blade.php` (standalone, no session / login / DB,
  `public/css/bakr-error.css`) or JSON. Logs the code only. Query bugs are not hidden.
- `App\Http\Middleware\EnsureDatabaseIsReachable` — prepended to the web group (before the database session starts).
- `bootstrap/app.php` — same page for a connection lost later in the request; not reported twice.
- `SecurityHeaders::HEADERS` is public (the 503 answer carries the same headers).

## Database
- Migration `2026_11_23_000001_add_address_parts_and_email_change_to_scholars`: `address_street`, `address_city`,
  `address_province`, `pending_email`, `pending_email_token`, `pending_email_sent_at` (all nullable). Applied locally.

## Tests
- New: `ScholarEmailChangeTest` (5), `DatabaseUnavailableTest` (3, incl. a real PDO connection to a closed MySQL
  port), `HomeWelcomeTest` (1). Updated for the address parts / locked fields: `ProviderRosterTest`,
  `ScholarIdAndDetailsTest`, `ScholarAccountTest`, `ScholarNotificationTest`,
  `SemesterDeadlineTest`. **332/332 pass.** Pint clean. `public/sw.js` VERSION → bakr-v13.
