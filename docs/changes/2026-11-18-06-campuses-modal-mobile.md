# 2026-11-18 — DNSC campus list, "finish your form" modal, mobile check

## Owner's requests
1. Fix all bugs, including the missing DNSC campus list in the matching form.
2. When a student who hasn't finished the form clicks other dashboard options, show a proper modal instead of an alert.
3. Make every page mobile responsive.
4. Simplify the UI where possible without losing the core idea.

The owner supplied `officerDashboardF.blade.php` as the source for campuses and programs ("just use the info here").

## 1. Campus list (the bug)
- **Cause:** the `campuses` and `programs` tables were empty. BAKR had no seeder for them, so the form's campus list was blank.
- **Fix:** new `config/dnsc_campuses.php` and a new `DnscCatalogSeeder` (safe to re-run, never deletes). Ran it on the local DB: 5 campuses, 90 campus-program rows.
- **Campuses (from the prototype):** Main (Panabo Campus), Carmen, Samal, Talaingod, Asuncion.
- **Programs:**
  - The 18 programs come from the prototype's institutes.
  - They are spelled exactly as the DOST `program_in` rule lists, without the "(BSIT)" suffixes. DOST matching compares names exactly, so a different spelling would always become "confirm".
  - The prototype's "Disaster **and** Resilience Management" is written as the rules' "Disaster Resilience and Management".
- **Not known:** which campus offers which program. The prototype doesn't split programs by campus, so every program is offered at every campus. Matching uses only the program name. Change `programs` per campus in the config when the official offerings are known, then re-run the seeder.
- **Checks:** all 18 programs have a field (`bakr:check-programs`: 0 unrecognized) and all are in the DOST lists.

## 2. Modal instead of the banner
- **Before:** a student with no answers who opened Dashboard, My matches or My profile was sent back to the form with a status banner ("Fill in your information first…").
- **Now:**
  - Those sidebar links and the hero profile icon open a "Finish your matching form first" modal directly.
  - Opening the address anyway (or with JavaScript off) redirects to the form with the modal already open. It names the page they tried, e.g. "**My matches** opens after you submit the form once."
- **Details:**
  - The icon is gold (`.dash-confirm-icon.is-info`), because this isn't a dangerous action.
  - `bakr-dashboard.js` opens `dialog[data-auto-open]` and then skips the first-visit About dialog, so two modals never stack.

## 3. Mobile
- **How it was checked:** rendered all 37 pages (public, logins, student, scholar, provider, admin) as real users inside a DB transaction that was rolled back, so nothing was saved. Then measured horizontal overflow in headless Chrome at phone width.
- **Bugs found and fixed:**
  - Admin Overview's "Needs attention" rows were 819px wide on a 500px screen. The shared `.dash-list` now uses `grid-template-columns: minmax(0, 1fr)`; on phones, titles wrap instead of being cut off, and a row's button drops below its text.
- **Result:** after the fix, all 37 pages fit with no sideways scroll.
- **Limits:** headless Chrome's minimum width is 500px. That is still below the 576px phone breakpoint, so the phone styles were tested. Screenshots were checked for the matching form, admin analytics and scholar profile.

## 4. Other bugs found while checking
- **Scholar About text:** the scholar portal's "About BAKR" showed the student matching steps. It now has its own scholar version (`audience: 'scholar'`): check your scholarship, submit requirements, follow your stipends.
- **Dark mode:** the bold lead-ins in About BAKR were fixed dark green on a dark background. They now use `--dash-text-strong`.
- **Dark mode:** the "Show as table" links on the admin charts were nearly invisible. They are now gold.

## Simplify
No large redesign was made. The checks found no layout problems beyond the ones above. Removing features blindly would risk the core flows (matching → scholar monitoring → provider → admin). Specific pages to trim can be named in a follow-up.

## Checks
- `php artisan test`: **207/207**. New: `DnscCatalogTest`, plus two tests in `PortalChromeTest` (the modal, the scholar About text).
