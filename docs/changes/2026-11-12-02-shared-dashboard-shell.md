# 2026-11-12 — Shared dashboard shell for every dashboard

**Roadmap item(s):** Phase 4, "Shared dashboard shell" (new item, ticked).

The goal was to make the provider dashboard look like a modern app dashboard (reference: a "Time Tracker" style layout with a floating panel, white sidebar, coloured summary cards, board columns and pill toggles), and to make **every** dashboard use the same design and the same CSS.

## Decisions
- **One stylesheet, one set of shell classes.** `public/css/bakr-dashboard.css` holds the tokens, the shell and the components. Each dashboard puts `.dash` / `.dash-panel` / `.dash-side` / `.dash-nav` / `.dash-main` on its own shell elements.
- **Layout from the reference, palette from BAKR.** The reference is indigo and pink, but every existing dashboard's content (buttons, KPI icons, badges) is green and gold. An indigo shell clashed with all of it, so the tokens use BAKR green, teal, gold and terracotta. To re-theme, change the `.dash` token block only.
- **Staff prototypes are opt-in only.** `adminDashboardF`, `officerDashboardF` and `scholarDashboardF` got class additions plus the stylesheet link. No content, IDs, inline handlers or JS changed. Their content CSS stays; the shared file only aligns surfaces (cards, KPI tiles, headers, hero banner).
- **Geometry from 992px up.** Below that, staff dashboards keep their own mobile drawers and offcanvas menus (recoloured). Provider and student turn the sidebar into a single sticky strip that scrolls sideways.
- **Gold tile uses dark text** so it meets contrast. The reference's white-on-amber does not.

## Added
- `public/css/bakr-dashboard.css`: the shared shell and components. These are `.dash-tiles`/`.dash-tile` (two-tone bar driven by `--fill`), `.dash-board`/`.dash-col`/`.dash-row`, `.dash-card`, `.dash-seg`, `.dash-icon-btn`, `.dash-avatar` and `.dash-section-head`.

## Changed
- `resources/views/provider/dashboard.blade.php`: rebuilt on the shell. It has a sidebar (sections, official pages, account, provider block) and a top bar with icon buttons. One coloured tile per scholarship, whose bar is the share of **required** rules out of all rules (real data from `rules`). An "Applicants by match status" board has 4 columns × the provider's scholarships, with the same aggregates and "Fewer than 10" hiding. Details sit in tabbed panels (all panels render server-side; JS only hides inactive ones).
- `public/css/bakr-provider.css`: now only the login background and the detail-card content. Old hero, stats and topbar rules removed.
- `public/js/bakr-provider.js`: tile bars, accessible tabs (arrow keys, `aria-selected`, hash support) and a sidebar scroll-spy.
- `resources/views/student/layout.blade.php`: shell rebuilt on the shared classes. The photo banner is replaced by the white top bar (breadcrumbs, title, sub-title, actions, icon buttons). The match summary is now a soft card in the sidebar, and the user block sits at the bottom. Child views are unchanged (`hero_title`, `hero_sub`, `hero_actions` still work).
- `public/css/student-portal.css`: removed 71 rules that only styled the retired shell (`sd-shell`, `sd-side`, `sd-nav`, `sd-help`, `sd-banner`, `sd-brand`, `sd-avatar`). They were removed by a selector-aware script; a backup was kept outside the repo during the session. A short block was appended that fits the summary card, breadcrumbs and banner actions into the new shell.
- `resources/views/{admin,officer,scholar}DashboardF.blade.php`: `class="dash"` on `<body>`, plus `dash-panel`, `dash-side`, `dash-nav`, `dash-main` and `dash-user` on existing elements. Each also links `bakr-dashboard.css`.
- `tests/Feature/ProviderPortalTest.php`: stats assertions follow the new board markup.
- `CLAUDE.md`, `docs/ROADMAP.md`: the shared-shell convention.

## Verified
- `php artisan test` → 54/54 passing; `pint --test` clean on the changed PHP.
- Manual check with `playwright-cli` against a throwaway SQLite DB with 40 demo students (deleted afterwards):
  - provider (CHED) at 1440px and 375px
  - student dashboard at 1440px and 375px
  - admin, officer and scholar at 1440px, plus officer at 375px and the scholar mobile drawer
  - no horizontal scroll at 375px on any of them
- Fixed during the check:
  - 4-letter badges overflowing
  - scroll-spy picking the wrong item at the top of the page
  - the mobile nav stacking into rows
  - blurry breadcrumbs (old banner text-shadow)
  - scholar hero colour not matching the other heroes
  - the scholar drawer's close button covering its tag

## Follow-ups
- `php artisan serve` still sometimes truncates the 6.4k-line scholar CSS (known; use Apache). This caused one half-styled render during the check and is unrelated to this change.
- Staff prototypes still load Bootstrap 5.3.3 and Inter; provider and student use 5.3.8 and Poppins. The shared shell sets Poppins on all of them.
- The prototypes' internal components (KPI cards, tables, forms) keep their own CSS. Moving them onto `.dash-*` components belongs with the Phase 2 conversion of the prototypes into real Blade.
- `DESIGN.md` (Phase 4) should document the `bakr-dashboard.css` tokens and components.
