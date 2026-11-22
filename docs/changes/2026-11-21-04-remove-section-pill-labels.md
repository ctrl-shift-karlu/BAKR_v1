# 2026-11-21 · Pill labels above section titles removed

Owner's request: completely remove the pills above every section title.

## Changes

- Removed the 10 gold pill labels (`.nx-story-label`) above section headings: home ("Featured DNSC scholarships", "How BAKR Matching Works"), About ("About Us", "Who we are", "Why BAKR") and the About partials ("Community Snapshot", "Graduate stories", "Visit us", "The team"). Each heading now starts its section (its `mt-2` top margin, kept for the pill, is gone; `#about .nx-h2` margin 16px → 0).
- CSS: every `.nx-story-label` rule (and its unused `nxGlowPulse` animation) removed from `bakr-core.css`; the dark rule in `bakr-dark.css` now covers only `.bk-sample-badge`.
- Kept: plain-text kickers that are not pills (e.g. "YOUR NEXT CHAPTER STARTS HERE", the home ledger / DNSC card kickers, Contact kickers, portal `dash-kicker`), and the "Updated …" badge below the About snapshot title.
- `sw.js` `VERSION` → `bakr-v5`.

## Also: "Applicant name" → "Student name" (owner, same session)

- Matching form header: "Applicant name" → **"Student name"**; its block class `bf-applicant` → `bf-student` (`student/application.blade.php`, `bakr-form.css`, dark rules in `bakr-dark.css`).
- Printable copy: section "Applicant" → **"Student"**, field "Name" → "Student name".
- No logic used the label (the name still comes from the signed-in account). The provider-side "applicants" wording (the removed Applicants page and its tests) is a different thing and stays.
