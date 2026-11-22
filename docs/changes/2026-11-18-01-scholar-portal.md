# 2026-11-18 — Scholar portal (no matching): Overview, My scholarship, Requirements, Explore, Profile

## Owner's request
Fix the scholar dashboard:
- Remove matching.
- Sidebar: **Overview** (summary, actions needed), **My scholarship** (info, benefits, maintaining documents, disbursement info), **Requirements** (each scholarship's own maintaining documents, submitted to that scholarship's provider), a collapsible **Explore** section, and **Profile** in place of "Sign out". Profile has a working forgot password and change password (by e-mail) and a sign-out with a confirmation dialog, all in the existing design.

## Design
- A scholar keeps their **student** account; there is no new role. `Scholar::heldBy($userId)` decides which portal the account gets. `student.layout` has two sidebar modes: the student (matching) menu is unchanged, and the scholar menu is new. Both use the shared `.dash` shell.
- `ScholarGate` middleware (`scholar.gate:student|scholar`):
  - Matching routes (dashboard, results, profile, application, explain, share) send a scholar to `/scholar`.
  - `/scholar/*` sends a student without a scholarship to the student dashboard.
  - `/find-my-matches`, sign-in and the first password change also go to `/scholar` for scholars.
- New `ScholarPortalController`. Every query is scoped to the signed-in user's own `scholars` rows, and `?s=` can only pick one of them.

## Pages
- **Overview:** summary tiles (scholarships held, requirements approved this term, waiting for review, last stipend), then "Actions needed" with the most urgent first:
  - documents that need revision, with the provider's note
  - documents not uploaded for this term
  - probation
  - grant type not set yet
  - "You're all set" when nothing is left
  
  It also lists the scholarships held, with status, grant type and stipend.
- **My scholarship:** tabs when the scholar holds several scholarships. Cards: scholarship information (provider, status, grant type, scholar since, student number, program, year level, overview, official link), Benefits, Documents to keep your scholarship (that scholarship's `renewal_requirements`), and Stipends (per release, how often, total received, history with references). Providers without Disbursements get a "handles stipends directly" note.
- **Requirements:** one card per scholarship with **its own** list. This term's rows have upload/replace, status, the reviewer's note and the file link; earlier terms are folded under "Earlier terms". If the provider hasn't opened this term yet, its list is shown with "not opened yet". Providers without Requirements (DNSC) show the list with "submit directly", and there are no upload buttons. Uploads still go through `student.requirements.upload`, so they land in that scholarship's provider review queue.
- **Explore:** a collapsible sidebar group with All scholarships, Help & contact (scholar questions for scholars), About BAKR and Public site. It opens automatically on those pages, and becomes a pill on phones.
- **Profile:**
  - Account details.
  - Change password: current, new and confirm; it stays on Profile.
  - Forgot your password?: "E-mail me a reset link", which uses the Laravel broker and is throttled to 3 per 10 minutes.
  - Sign out: a `<dialog>` confirmation ("Stay signed in" / "Sign out"), with a no-JavaScript fallback.
- **Password reset links** now also work for a signed-in user (the routes are no longer guest-only). A reset signs the session out and clears `must_change_password`.
- The old `resources/views/student/scholarship.blade.php` was removed (backup in `docs/changes/backups/student-scholarship.blade.php.2026-11-18.bak`). `/student/scholarship` redirects to `/scholar/scholarship`.

## Follow-up: Requirements modelled on the prototype's Documents view
The owner gave the old `scholarDashboardF` prototype as the guide and asked for submission to work.

**Each scholarship card**
- A summary band with "x/y submitted", the provider and term, an alert ("N need your action: … to revise, … to upload", "Nothing needs your action", or "checklist not opened yet"), and tiles for Approved, Waiting for review, Revision needed, Not uploaded and Total.

**Each document**
- An accordion row: icon, name, "Sent <date>" or a status note, a status pill, and a quick **Upload / Replace** button.
- Opening a row shows the details: status with an explanation, sent to, term, reviewed date, the provider's note, the file (name, type and size, Download, and Preview for PDF/JPG/PNG), and a large Upload/Replace button.

**Upload dialog** (`<dialog class="dash-dialog">`)
- Shows the current file if there is one, a drag-and-drop zone with "Browse files", and a file card with Change and Remove.
- Checks the type (PDF/JPG/PNG) and the size (5 MB, read from `ScholarRequirement::MIMES` / `MAX_KB`) before sending.
- The upload is a real XHR to `student.requirements.upload` with `Accept: application/json`, with a **real progress bar**. Server errors come back as 422 JSON and are shown in the dialog. 409, 413, 419, 404 and network failures each get a plain message.
- On success the page reloads on that document (`#req-ID`), showing the flash "… was sent to {provider}. It is now waiting for their review."

**Without JavaScript**
- Each open row has a `<noscript>` form that posts to the same route. The plain post redirects back to `#req-ID`.

**Preview dialog**
- `student.requirements.file?inline=1` streams the file inline only for PDF/JPG/PNG, with a fixed `Content-Type`, `X-Content-Type-Options: nosniff` and `Cache-Control: private, no-store`, and the same ownership check (another user gets 404). Images are shown as `<img>` and PDFs in an `<iframe>`, with a Download button.

**Other**
- Providers that collect directly (DNSC) keep the "submit directly" card. The dialogs and the script are only on the page when at least one of the scholar's scholarships collects documents in BAKR.
- New: `public/js/bakr-scholar-requirements.js`. Changed: `StudentScholarController` (JSON reply, `#req` redirect, inline preview), `scholar/requirements.blade.php`, `scholar/partials/requirement-rows.blade.php`, `student-portal.css` (the old `sc-req-*` rules were replaced), and `bakr-dark.css`.
- Tests: 188/188 pass. New: the dialog upload is received by the provider, readable 422/409 errors, the no-JS post returns to `#req-ID`, and the preview is inline and safe.
- **Not checked:** the script itself. Node is not installed, so there was no syntax check, and nothing has been run in a browser.

## Checks
- `php artisan test`: 184/184 pass. New: `tests/Feature/ScholarPortalTest.php` (12 tests). Updated: `ScholarAccountTest`, `ProviderRosterTest`, `StudentScholarTest` (new addresses).
- Not checked in a browser: Playwright is not on PATH here. The only live scholar account belongs to the owner, and its password was not changed for a check.
