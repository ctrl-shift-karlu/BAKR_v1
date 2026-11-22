# 2026-11-13 — Routing logic, in-portal Help, About BAKR dialog

**Asked:** improve the system's logic and routes so dashboard users aren't sent out to the public About/Contact pages, add an "About BAKR" modal, and suggest proper destinations.

## Destination map (after this change)

| Someone clicks… | Who | Goes to |
|---|---|---|
| **Find a Scholarship** (navbar, footer) / **Find My Matches** (home) → `/find-my-matches` | Guest | Register |
| | Student, no answers yet | Questionnaire |
| | Student with answers | My matches |
| | Provider | Provider dashboard |
| `/studentLogin`, `/studentRegister`, `/providerLogin` while signed in | Student / provider | Their own dashboard (was: home page) |
| **All scholarships** (student sidebar, hero icon, dashboard button) | Student | `/student/scholarships`: the catalogue *inside* the portal, with "96% closeness for you" and status on each card and "See my result" in each modal |
| **Help & contact** (sidebar, hero "?") | Student / provider | `/student/help` or `/provider/help`: About, FAQ and a message form (name and e-mail come from the account) |
| **About BAKR** (sidebar, hero "i") | Student / provider | `<dialog>` pop-up on the same page. It also shows **once** on a person's first dashboard visit |
| **Public site** (student) / **Public page** (provider, new tab) | Both | The public site, labelled as such |
| Brand logo / first breadcrumb in the portal | Student | Student dashboard (was: public home) |

## Added
- `FindMatchesController` (`find`).
- `bootstrap/app.php`: `redirectUsersTo` by role.
- `Student/StudentPortalController`: `scholarships`, `help`, `message`.
- `Provider/ProviderHelpController`: `show`, `message`.
- Routes: `student.scholarships`, `student.help(.message)`, `provider.help(.message)`. The help forms are throttled to 5 per 10 minutes.
- `App\Services\ContactMessageService`: one store-then-email path for the public Contact page and both Help forms. `ContactController` now uses it.
- Views:
  - `student/help`, `student/scholarships`, `provider/help`
  - `partials/scholarship-catalogue`: extracted from `scholarships.blade.php`, with optional `$matches` / `$shares`
  - `partials/about-bakr`, `partials/about-dialog`
- `public/js/bakr-dashboard.js` (dialog open/close and first visit, wrapped in try/catch for storage).
- CSS for the dialog, about content and FAQ in `bakr-dashboard.css`; the in-portal catalogue in `student-portal.css`.
- Public routes are now named: `home`, `about`, `contact`, `scholarships`, `find`.
- `tests/Feature/PortalNavigationTest.php` (11 tests).

## Security notes
- On the portal help forms, **name and e-mail come from the signed-in account**, never from the request (a test posts a fake e-mail and checks it is ignored).
- Students get 403 on the provider help form.

## Verified
- `php artisan test` → 93/93; `pint --test` clean on changed PHP.
- `playwright-cli` at 1920 × 950 on a throwaway SQLite DB:
  - the first-visit dialog appears and doesn't reappear
  - the Help page
  - the in-portal catalogue with match status
  - fixed: the shelf heading was dark-on-dark inside the portal

## Suggested next steps (not done)
- **Questionnaire guard:** if a student opens My matches before answering, they already go to the questionnaire. Consider also linking "Finish your answers" from each *needs info* card to the exact step.
- **Remember where they were going:** when a guest clicks "Find a Scholarship" and then logs in instead of registering, `redirect()->intended()` already returns them. Keep using `route('find')` for new buttons so this keeps working.
- **Notifications page:** stipend releases and requirement reviews could appear as in-portal notices instead of only status pills.
- **Staff prototypes** (scholar/officer/admin) still link to static pages; give them the same Help and dialog when they get real auth (Phase 1).
