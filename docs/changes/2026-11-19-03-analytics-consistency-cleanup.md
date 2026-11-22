# 2026-11-19 (03): Simpler admin analytics, whole-app consistency pass, file-structure cleanup

## Request
Simplify the admin Analytics view (less noise, easier to understand). Then make everything work and connect, clean up logic bugs, UI glitches and unwanted elements, and make everything consistent (palette, logos, layout rules, texts, fonts, sizes, down to the data). Then simplify the file structure without changing the concept, idea or logic.

## How it was checked
- Crawled every GET page as guest, student, scholar, each of the 7 providers and admin on the real local data (102 pages): all 200 / expected redirects, no PHP notices, no raw Blade, no missing CSS/JS/images.
- Phone-width probe on every page: no page scrolls sideways (wide tables and the home gallery scroll inside their own boxes, by design).
- Contact-sheet screenshots, light and dark, before and after.

## 1. Admin Analytics (simplified)
Was 9 charts (3 donuts, 3 bar lists, 2 stacked bars) that repeated the same numbers. Now: 4 headline numbers (accounts → answered → has a match, current scholars), "Results per scholarship", "Why students don't qualify" (top failing rule per scholarship, as a list), and ONE table of scholars per provider (active / inactive / terminated, documents approved, stipends released + ₱). `admin/partials/donut` and `bars` removed; `bakr-admin.css` trimmed to what is used.

## 2. Logic bugs fixed
- "Only if it applies" documents never sent were counted as owed: every scholar got "Upload: If on leave…" on Overview, a sidebar badge, "needs your action", and providers saw them as "Not submitted". New rule `ScholarRequirement::isOwed()` / `->owed()` used for every count (scholar overview, badge, requirements page, provider KPIs, dashboard donut, scholars list, profile, admin).
- A terminated scholar could still upload documents (the page also ignored the `canUpload` flag it was given) and still had to-dos. Now 403 on upload, no upload buttons, no to-dos.
- A stipend prepared before termination could still be released. `releaseBlocker()` now locks it ("the scholarship was terminated"), with its own message.
- `ProviderDashboardController` requirement count joined `scholars` (two `status` columns): the new scope qualifies its columns.

## 3. Consistency
- **Sign-in pages:** 8 near-copies (≈85 lines each) → one `layouts/auth` + `auth/partials/login-form`. Card subtitle = the portal's name (was "DNSC Scholarship Matching System" on two); eyebrows "BAKR <Portal> Portal"; provider login no longer describes the removed Applicants feature; removed an inline `style`, inline `onerror`, `asset('studentRegister')` page links; favicon and referrer policy on all; background photo per portal via CSS class (provider's moved out of `bakr-provider.css`). "Keep me signed in" stays on personal accounts only (students, scholars), not on staff accounts.
- **Colours:** one token set (`--viz-*`) for every chart in every portal, one colour per meaning; provider donuts used terracotta for "Not submitted"/"On hold" (now gray / danger red like the pills), admin used a different bright palette, the student summary bar used a different blue.
- **Logos:** one source (`config/providers.php`); `Scholarship::logoPath()` reads the owning account instead of its own slug map.
- **Portal header:** every hero has theme · About · Help · Sign out (student and scholar also repeated "All scholarships" and "Profile", already in the sidebar); "Help and contact" everywhere.
- **Wording:** "semester" instead of "term", "maintaining documents" instead of "renewal requirements" (26 view strings + messages); scholar status labels now come from the model ("Not submitted", "Needs revision" instead of "Not uploaded", "Revision needed"); scholar summary tiles are tinted only when not zero.
- **Sizes:** the last live text under 12px (navbar subtitle) uses `--text-xs`; login card type uses the `--text-*` scale.

## 4. File structure
- `resources/views`: top level = public pages only; `auth/` = every sign-in page; `partials/portal/` = chrome shared by the portals (mobilebar, side-foot, hero-signout, about-dialog, about-bakr, help, chart-donut). URLs unchanged.
- Removed (moved to `docs/changes/backups/cleanup-2026-11-19/`): unrouted prototypes `scholarDashboardF` and the old standalone `scholarshipFinder` with their CSS/JS (~260 KB), orphaned `bakr-admin-login.css` / `bakr-scholar-login.css`, `partials/modals/feedback-success`, 5 unused images (~2 MB), `package.json` / `vite.config.js` / `.npmrc` (nothing used them; `npm run build` failed), the stale `UPDATE-COURSES-AND-DESIGN.md`, and the old Boost text of `AGENTS.md` (now a 3-line pointer to CLAUDE.md).
- `composer setup` no longer runs npm; `composer dev` = `php artisan serve`. `CONTACT-SETUP.md` moved to `docs/` (links updated).

## Not changed (on purpose)
- URLs, routes, database, matching rules, roles and every portal's concept.
- `provider_applications` table/model (unused since 2026-11-18, kept as before).
- LANDBANK and BFAR still show the BAKR logo: no official logo file was provided.

## Tests
224/224 pass (new: optional-not-sent is not a to-do, terminated scholar cannot upload, terminated stipend cannot be released; analytics test rewritten for the simpler page).
