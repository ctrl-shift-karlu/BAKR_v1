# 2026-11-13 — Steady sidebar, no footer under dashboards, fuller student dashboard

**Reported:** on the student dashboard the bottom looked unfinished. The public site footer showed under the panel (a gold line and a cut-off card), and the "BAKR overview" card sat alone with empty space beside it. The sidebar had its own scrollbar, and near the end of a page it slid up with the panel.

## Changed
- `layouts/app.blade.php`: the footer is skipped when a page sets `@section('no_footer')`.
  - `student/layout.blade.php` sets it.
  - The provider layout is standalone and never had the footer.
- `public/css/bakr-dashboard.css`: new **app shell**, opt-in with `.dash.is-app` (provider body, student wrapper), at **≥ 992px**:
  - The panel is exactly the window height minus the gutters, and the page itself no longer scrolls.
  - The sidebar is `position: relative`, full height, and never moves.
  - Only `.dash-main` scrolls (thin themed scrollbar, `overscroll-behavior: contain`).
  - The sidebar is more compact and its scrollbar is hidden.
  - Windows ≤ 860px tall hide the sidebar extras (the student's match summary); windows ≤ 700px tall tighten the links.
  - Phones keep normal page scrolling.
- `student/dashboard.blade.php`: when nothing needs more answers, a **"You're all set"** card fills the space beside the overview. It shows counts per match status (each links to the filtered results) and the next step (share a likely match with its provider).
- `student-portal.css`: styles for that card. "Your best matches" cards now stretch to fill the row (`auto-fit`).

## Verified
- `php artisan test` → 81/81.
- `playwright-cli` at 1920 × 950 on a throwaway SQLite DB (deleted afterwards, with its demo files):
  - Student dashboard and provider dashboard, scholars and requirements: the document height equals the window (no page scroll), the sidebar has no overflow, and it stays at 24px from the top after the content is scrolled to the end.
  - At 375px the page scrolls normally with no sideways overflow.
