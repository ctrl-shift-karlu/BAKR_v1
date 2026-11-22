# 2026-11-19 (01): CoScho under CHED, scholar status, document preview, one sign-out, sidebar fixes

## Request
Put CoScho (and any other CHED scholarship) in the CHED provider account, with the same rule for every provider, and no extra dashboards. Fix the sidebar bugs and design flaws. Make every sign-out look and work the same. Scholar status on the provider side is only Active, Inactive (maintaining documents not submitted past due) or Terminated, and only the scholar's own provider can terminate. Providers can preview uploaded documents without downloading them (Download stays). Fix the collapsible UI of the provider Documents (Requirements) view.

## Decisions taken (owner can change)
- **One account per administering provider.** CoScho → `ched`. LANDBANK and BFAR (DA) are their own providers, so they keep their accounts. The `coscho` login is **deactivated** (not deleted) by the migration; `PROVIDER_COSCHO_*` removed from `.env.example` (still in the local `.env`, now unused).
- **Status:** `scholars.status` stores only `active` | `terminated`. **Inactive is derived**: an active scholar with a requirement in `missing` / `needs_revision` whose `due_on` is before today. It clears by itself when the scholar submits. Old values were mapped: probation, on leave → active; **graduated → terminated** (the local DB had none of them).
- **Due dates:** new `scholar_requirements.due_on`. "Open / Update checklist" takes a due date (default: last day of the semester, Dec 31 / May 31) and applies it to the term's not-yet-approved items. Existing rows were backfilled with the semester end.
- **Terminate / Reinstate** is its own action (`PATCH provider/scholars/{id}/status`), behind a red confirmation; the details form no longer has a status field. `ProviderScope` makes another provider's scholar a 404.

## Changes
- `config/providers.php`: `coscho` slug in `ched`; `coscho` account removed.
- `Scholar`: `STATUSES` (active/inactive/terminated), `STORED_STATUSES`, `STATUS_TONES`, `standing()`, `overdueRequirements()`, scopes `withStanding()` / `whereStanding()`, `statusTone()`, `isTerminated()`. `GOOD_STANDING` = active.
- `ScholarRequirement`: `due_on`, `isOverdue()`, `canPreview()`, `PREVIEW_MIMES`. `AcademicTerm::endDate()`.
- Provider: Scholars list/KPIs/filter by the three statuses; sidebar "Scholars" badge now counts **Inactive** (it showed the total as "to do"); scholar profile has a **Scholarship status** card (why inactive, overdue items, Terminate / Reinstate); dashboard shows the status split.
- Provider documents: shared `provider/partials/requirement-item` (status, Past due, due date, file row with **Preview** + **Download**, note, review); `provider/partials/file-preview` dialog (PDF in a frame, JPG/PNG as image, open in new tab, download); `?inline=1` on `provider.requirements.file` (PDF/JPG/PNG only, stored type, nosniff, no-store). Requirements "By scholar" is now a real accordion (bordered rows, chevron, hover/focus, to-review / past-due / complete flags, documents inside the row). "Ask for revision" opens its note on its own line.
- Scholar portal: Inactive / Terminated actions on Overview; due date and Past due on each document.
- Admin: analytics donut uses the three statuses; "Current scholars".
- **Sign-out, everywhere the same:** `partials/dash-side-foot` (who is signed in + red **Sign out**, pinned at the bottom of every portal sidebar, never inside a collapsible group) and `partials/dash-hero-signout` (red icon, last hero button) in student, scholar, provider and admin; all open `partials/signout-dialog`. The public navbar, the 403 page and the change-password page now open the same dialog instead of signing out instantly.
- **Sidebar fixes:** collapsible group headers now line up with the plain section labels (same gold caps and left edge, chevron only); student Explore is collapsible like the scholar's and gained "All scholarships"; admin's one-link collapsible became a plain "Explore" section; provider "Account" group (which held Help/About/Public page) renamed "Help & info"; badges use one `.dash-count` with screen-reader text; long labels/names ellipsize; logo avatars no longer stretch to the image width.
- `DemoScholarSeeder`: only active/terminated; one demo scholar per scholarship missed an earlier due date (shows Inactive).

## Database (local)
- Migration `2026_11_18_000002_scholar_status_and_requirement_due_dates` ran (3 scholars, all already active; 3 requirement rows got due 2026-12-31; `coscho` login → inactive).

## Tests
215/215 pass. New: terminate/reinstate only by the own provider; inactive from an overdue document and back to active on submit; provider inline preview (PDF inline, non-previewable always downloads); checklist due date; every portal + public navbar uses the shared sign-out.

## Open
- Graduated scholars now show as Terminated. If the owner wants "Graduated" kept apart, it needs a 4th stored status.
- Scholarship `provider` text for CoScho is still "CHED (CoScho)" (public label); change with `bakr:sync-scholarship coscho` if it should read "CHED".
