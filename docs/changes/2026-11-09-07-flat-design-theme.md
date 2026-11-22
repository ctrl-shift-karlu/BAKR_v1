# 2026-11-09 · Flat design theme for student portal

## What changed

Replaced the skeuomorphic (raised surfaces, bevelled shadows, brass rims) visual treatment in the student portal with a flat editorial design. No code changes to controllers, routes, views, or business logic — CSS only.

### File changed

`public/css/student-portal.css`

1. **Page background**: changed from the parchment radial-gradient to a plain warm off-white `#f5f3ef`.
2. **Shell grid**: narrowed the sidebar column from `300px` to `220px`; removed the `24px` gap between sidebar and main (they now share a border instead).
3. **Skeuomorphic finish section** (was lines 388–457): replaced entirely with a **Flat Design Theme** section. Key changes:
   - **Sidebar** (`.sd-side`): solid dark green (`#143a2b`) with a faint diagonal-line texture (`repeating-linear-gradient`), full viewport height, 3px gold right border.
   - **Profile card and nav** (`.sd-me`, `.sd-nav`): transparent — text floats on the dark sidebar. White text, gold icons.
   - **Active nav item**: gold tint highlight + a small rotated gold square (diamond marker) on the left edge via `::before`.
   - **Section headings** (`.sd-head h2`): a 5×18px skewed (`skewX(-14deg)`) gold bar prepended via `::before`.
   - **Match cards** (`.sd-card`): flat white `#fff`, 1.5px border, 8px radius, no box-shadow. A `clip-path: polygon(0 0, 100% 0, 0 100%)` triangle in the top-left corner coded by `data-status` (green/gold/grey/blue).
   - **Buttons, inputs, meters**: all flat — single border, no gradients, no emboss.
4. **Mobile responsive block** (≤992px): sidebar stacks vertically at the top with a 3px gold bottom border instead of a right border; nav scrolls horizontally as before but without the pill border-radius.

## Tests

36/36 pass (CSS-only change; no PHP touched).

## Mockup reference

`docs/mockups/flat-layout/` — before/after screenshots and the concept CSS used during design iteration.
