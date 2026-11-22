# 2026-11-13 — Dashboards on the home-page palette, with photos and cards

**Roadmap item(s):** Phase 4 follow-up to "Shared dashboard shell".

The vibrant emerald/sky/coral palette from 2026-11-12 was replaced. Every dashboard now uses the home page's palette (`bakr-core.css`):
- deep green `#143a2b` / `#1f4d3a`
- gold `#b8912f` / `#f6dfa0`
- sage `#8fa898`, teal `#2c6470`
- a vanilla panel
- serif numbers (Source Serif 4)

Life comes from photos and cards rather than bright colours.

## Changed
- `public/css/bakr-dashboard.css`:
  - **Tokens:** the home palette; the backdrop is the campus photo under a green overlay; the tiles are green, gold, teal and sage.
  - **Sidebar:** deep green with a gold active pill, like the navbar's "Home". Counters are gold. Staff-prototype sidebar text is recoloured for the dark background.
  - **New components:**
    - `.dash-hero`: photo banner with a gold kicker, a heading with a serif italic gold `<em>`, actions, and glass icon buttons.
    - `.dash-ledger`: the home page's "At a glance" panel, with a green intro and stat cells with serif numbers.
    - `.dash-photo-card`: a cover photo, provider logo, sector chip and progress bars.
    - `.dash-split3`, `.dash-kicker`.
  - Serif section titles and KPI numbers. Pills, buttons, flash messages and tables now use palette tints.
- `bakr-provider.css`, `student-portal.css`: the remaining off-palette colours were moved to palette tints.
- `provider/layout.blade.php`:
  - The plain top bar is replaced by `.dash-hero`. Each page sets `@section('photo')` and `@section('kicker')`.
  - Source Serif 4 is now loaded.
- `provider/dashboard.blade.php`:
  - a greeting hero
  - the ledger (active scholars, stipends released with a three-part bar, new applicants, requirements approved)
  - one photo card per scholarship
  - the applicants and uploads cards
  - The "This term" board was dropped; its numbers now sit on the photo cards.
- Other provider pages: each has its own campus photo and an `<em>` heading.
- `student/layout.blade.php`: the photo hero is back (a different campus photo per page). `student/scholarship.blade.php` has the scholarship photo behind its banner.
- `bakr-provider.js`: `.dash-split3 [data-w]` widths.

## Verified
- `php artisan test` → 79/79.
- `playwright-cli` on a throwaway SQLite DB with demo data. The DB and 94 demo PDFs were deleted afterwards.
  - Provider overview and disbursements, plus student "My scholarship", at 1440px.
  - The provider pages at 375px, with no horizontal scroll.
- Photos used: `dnsc_campus.jpg`, `graduates.jpg`, and `opt/` versions (students2, student, lib, dnsc_gate, gate2). No new image files.
