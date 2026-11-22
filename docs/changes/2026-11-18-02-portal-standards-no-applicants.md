# 2026-11-18 — Portal standards, red danger actions, working forgot password, burger menus, no Applicants

## Owner's requests
1. Standardize layout, design and text sizing everywhere, especially the scholar portal.
2. Sign-out and other dangerous actions use the red palette from the root (`--danger` / `--dash-danger` #b42318).
3. Make "Forgot password" work.
4. Make the provider sidebar's Account section collapsible.
5. Use a burger menu on mobile in every portal.
6. Remove the Applicants idea from the provider portal: providers only monitor scholars they already have.

## 1. Applicants removed (provider and student side)
- **Provider:**
  - removed the Applicants page and route, `ProviderApplicantController`, the sidebar item and its counter
  - Overview: the "Review applicants" button, the applicants chart and the "New applicants" card are gone; there is now a **Recently added scholars** card with "Add scholars"
  - `ProviderScope::applications()/application()/matchCounts()` removed (no page uses them any more)
  - Help FAQ: new "Does BAKR handle applications? No …"
- **Student:** the match dialog's "Apply: share my profile" form, the "Shared / Accepted" badges, the share routes and `StudentShareController` are removed. Eligible and nearly-eligible matches now say "To apply, follow {provider}'s official process" with the application route. The About, Help, Finder and dashboard texts were updated.
- The `provider_applications` table and model are kept, so no data is lost; they are simply unused.
- `DemoScholarSeeder` no longer creates demo applicants.
- Backups: `docs/changes/backups/ProviderApplicantController.php.2026-11-18.bak`, `StudentShareController.php.2026-11-18.bak`, `applicants.blade.php.2026-11-18.bak`.

## 2. Forgot password works for every real account
- `StudentPasswordResetController` serves active **student, scholar and provider** accounts (`RESETTABLE_ROLES`).
  - The same reply is given for unknown or inactive e-mails, and no mail is sent for them.
  - After the reset, each person is sent to their own login (provider → `/providerLogin`).
- Added "Forgot password?" to the provider login. The Scholar/Admin prototype logins link to the real flow.
- `/forgotPassword` (the old static mock-up that sent nothing) now redirects to the real flow. The static view and its CSS/JS were removed (backups `forgotPassword.blade.php`, `bakr-forgot-password.css/js` `.2026-11-18.bak`).
- The request page's back link follows `?from=`. The request and reset pages lost their inline `style`/`onerror` attributes (they now use the `is-forgot` class).

## 3. Shared portal chrome (`bakr-dashboard.css`, `bakr-dashboard.js`)
- **Burger drawer below 992px** for the provider, student and scholar portals: `partials/dash-mobilebar` (burger + brand + portal name), the sidebar `#dash-side` slides in with a backdrop, a close button, Esc, backdrop tap, or following a link closes it, focus moves into the drawer and back, and it closes when the window gets wider. This replaces the sideways-scrolling pill strip. The public navbar and the staff prototypes already had their own burgers.
- **Collapsible groups:** provider **Account** (Help, About, Public page, Sign out; opens by itself on Help) and scholar **Explore**. The CSS moved from `student-portal.css` into the shared shell.
- **Danger red:**
  - `.dash-btn.is-danger` is now solid `--dash-danger` (it was pale pink); `.is-danger-soft` was added
  - sidebar and hero sign-out links are red; the public navbar sign-out turns red on hover
  - the first-sign-in password page's "Sign out" is red-tinted
  - "Remove" in the upload dialog uses the soft danger style
  - **every sign-out** now goes through the shared `partials/signout-dialog` (red "Sign out" button); the scholar Profile page uses it too
- **Standards:**
  - new tokens `--dash-gap` (16px between cards, grids and sections, also `.pv-gap`) and `--dash-pad` (card padding 22px, 16px on phones); the scholar Requirements cards use them
  - one `.dash-card-title` rule for h2 and h3, so scholar card titles now match the provider's
  - text sizes moved onto the `--text-*` scale: brand, nav, buttons, inputs (16px, which avoids iOS zoom), tabs, dialog titles and buttons, KPI, stat and donut numbers, the student match ring and scores
  - removed sizes below 12px (brand subtitle, avatar initials, donut label, ring label); avatars now grow into pills for codes like "DOST-SEI"

## Incident (fixed)
A PowerShell batch replace flattened a one-pair list and replaced every "." in `public/css/bakr-dark.css` with "d". The file was restored from this session's Claude Code checkpoint (`~/.claude/file-history/…/b5ce4fb0f9f79976@v4`, the version just before today's edits) and today's two dark-mode edits were re-applied with the Edit tool. A second slip of the same kind cut the start of the `.dash-card-title` rule in `bakr-dashboard.css`; that was fixed and checked. Braces balance in every edited stylesheet, and the only lines missing from the old checkpoint are the intended changes. The damaged copy is in the session scratchpad. CLAUDE.md now warns against this technique.

## Checks
- `php artisan test`: 185/185 pass. New: `tests/Feature/PortalChromeTest.php`.
  - provider reset end to end
  - same reply for unknown or inactive e-mails
  - the old `/forgotPassword` address redirects
  - collapsible Account section, red sign-out dialog and burger, on the provider and student portals
  
  `ProviderOperationsTest` now asserts that Applicants returns 404. The applicant and share tests were removed together with the feature.
- Live (MySQL), signed in as CHED: every provider page returns 200 with the burger, the Account group and the red sign-out; `/provider/applicants` returns 404; the provider login has "Forgot password?"; the request page goes back to `/providerLogin`.
- **Not checked visually.** Playwright is not available here. Please look at a phone width and at dark mode.
