# 2026-11-18 — Separate scholar accounts, stipend release rule, no Midyear

## Owner's requests
1. A stipend (disbursement) is available only when the scholar has submitted ALL maintaining requirements. Owner's choice: **approved** documents only count.
2. Remove Midyear: DNSC has no Midyear.
3. Scholars added by providers must be **scholar accounts**, separate from student accounts. A student login must never show the scholar dashboard. Matching belongs to the Student login and signup; the Scholar login is the monitoring system.
4. Use the scholar prototype (`scholarDashboardF`, where the old Scholar login went) as the guide. The real portal becomes THE scholar dashboard, with the prototype's Profile and Settings views.
5. Owner's choice for an e-mail that already has a student account: **convert it to a scholar account**.

## 1. Stipend release rule
- `Scholar::releaseBlocker($term)` returns why a stipend can't be released yet, or null when it can.
  - Every requirement of that semester must be **approved**. "Waiting for review" and "needs revision" block, and so does a checklist that hasn't been opened yet.
  - Not blocked: providers that don't collect requirements in BAKR, and scholarships with no maintaining documents listed.
- `ProviderDisbursementController`:
  - a single "Released" is refused with a clear error
  - "Mark selected as released" skips locked rows and says how many were skipped
  - preparing records, On hold and Not yet released are always allowed
- Disbursements page:
  - a callout, "N stipends can't be released yet", with "Review requirements"
  - "Locked: 1 of 2 requirements … not approved yet" under the status
  - no checkbox on locked rows, and the Released option is disabled
- Scholar side:
  - My scholarship › Stipends explains the rule and marks waiting stipends
  - Overview adds "your stipend is waiting for your requirements"
  - Help FAQ updated
- Provider Help has a new FAQ: "Why can't I release a stipend?"
- `DemoScholarSeeder` now follows the rule.

## 2. No Midyear
- `AcademicTerm` has only the 1st Semester (Aug–Dec) and the 2nd Semester (Jan–Jul; the June–July break counts as the 2nd Semester that just ended).
  - `options()` lists consecutive semesters.
  - New: `previous()`.
  - Removed: `isMidyear()`.
  - The `*Semester*` helpers are now the same as the plain ones.
- The live DB had no Midyear rows.
- Not changed: DOST's published benefit line "Mid-year term, if your curriculum requires it…" in `config/scholarships.php`. That is DOST's own official text.

## 3. Separate scholar accounts
- **Roles:** `student` (matching) and `scholar` (monitoring). The `scholar.gate` middleware and `Scholar::heldBy()` were removed.
- **Logins:**
  - `/scholarLogin` is real now (`ScholarAuthController`; the view is no longer the prototype partial)
  - `/studentLogin` refuses scholar accounts and `/scholarLogin` refuses student accounts, each with a message naming the right login
  - `redirectUsersTo` knows the scholar role
- **Routes:** everything for scholars is under `/scholar` with `role:scholar`:
  - Overview, My scholarship, Requirements
  - upload and file (`scholar.requirements.*`)
  - Profile, Settings, the reset link
  - forced password change (`/scholar/password`)
  - Explore: `/scholar/scholarships` and `/scholar/help`
  - `scholar.logout`

  Students lost the old `/student/scholarship` and `/student/requirements/*` routes.
- **Controllers:** `Student\ScholarPortalController` became `Scholar\ScholarPortalController`, and `Student\StudentScholarController` became `Scholar\ScholarRequirementController`. Copies of the old files are in `backups/*.2026-11-18b.bak`.
- **Layouts:** a new `scholar.layout` for the scholar portal; `student.layout` is now matching only.
- **Account creation (`ScholarAccounts`):**
  - a new e-mail gets a scholar account
  - an existing scholar account gets the extra scholarship
  - an existing **student** account is converted to a scholar account, keeping its password; the e-mail says "now a scholar account, use the Scholar login"
  - a provider or staff e-mail is refused
  - the login e-mail always links to the Scholar login
- **Password handling:**
  - `EnsurePasswordChanged` and `StudentPasswordChangeController` work for both roles
  - the reset flow serves scholars and sends them back to `/scholarLogin`
- **Navbar and 403 page:** the navbar shows "Scholar portal" and the scholar sign-out, and hides "Find a Scholarship" for scholars. The 403 page explains the student/scholar split.
- **Migration** `2026_11_18_000001_move_scholars_to_scholar_accounts`: every student account that held a scholarship becomes a scholar account. It has a working `down()`.
  - Applied to the local MySQL DB: 3 accounts converted.
  - The role backup taken before it (ids, e-mails, roles; no passwords) is at `docs/changes/backups/users-before-scholar-role.2026-11-18.json`.

## 4. Profile and Settings (after the prototype)
- **Profile:**
  - an identity summary: avatar, name, program · year, pills for year level and student no., and the scholarship(s)
  - then cards, two per row: Personal, Contact, School, Scholarship
  - read-only, because the provider keeps the records; "Request a correction" leads to Help
  - the prototype's Address card is left out because BAKR doesn't collect addresses (data minimisation)
- **Settings:**
  - Edit password: a `<details>` card with a "Change password"/"Cancel" toggle that works without JS and reopens after an error
  - Forgot your password? (e-mail a reset link)
  - a red Sign out card that opens the shared confirmation dialog
- New styles in `student-portal.css`: `sc-id*`, `sc-profile-grid`, `sc-collapse*`, `sc-signout`, `sc-card-sub`. They use only `--dash-*` / `--text-*` tokens, so dark mode follows.
- The Help body is now shared: `partials/portal-help` (student and scholar FAQs).

## Checks
- `php artisan test`: **193/193 pass**.
  - New tests: separate logins both ways, student→scholar conversion, extra scholarship, Profile/Settings, forgot password from the Scholar login, the release lock (single, bulk, needs revision, checklist not opened, no renewal list), no Midyear, empty states for a scholar account without a scholarship.
  - `StudentScholarTest` was replaced by `ScholarRequirementTest`.
- Live smoke test (local MySQL; GET only, signed in in-process):
  - every `/scholar/*` page returns 200, `/student/dashboard` returns 403, and `/find-my-matches` goes to `/scholar`
  - every CHED provider page returns 200
  - no "Midyear" anywhere
- Not checked in a browser: the Profile/Settings look on a phone and in dark mode.
