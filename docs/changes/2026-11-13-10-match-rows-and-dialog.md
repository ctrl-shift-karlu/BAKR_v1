# 2026-11-13 — My matches: slim rows with a details dialog; "All scholarships" off the student sidebar

## Changed
- **My matches** (`student/results`): each match is one slim row with:
  - the provider logo;
  - sector, name and status;
  - "X of Y requirements met";
  - a "Shared / Accepted" tag when the student shared it;
  - closeness;
  - **View details**.
- **View details** opens a `<dialog>` (reuses `.dash-dialog` and the `data-open-dialog` opener in `bakr-dashboard.js`). It holds what the big card used to show:
  - overview;
  - "Why this result" (built-in or AI explanation, plus the Explain with AI button when AI is on);
  - requirement checks;
  - benefits, documents and how to apply;
  - the share/apply consent form;
  - official link, "Answer missing questions" and Close.
- **Deep links:** `/student/results#match-{id}` opens that match's dialog. `bakr-dashboard.js` reads the `data-hash-dialog` attribute on the row. This keeps the dashboard "Why?" links and the redirect after "Explain with AI" working.
- **Student sidebar:** the "All scholarships" item was removed.
  - The `student.scholarships` page still exists. It is reachable from the catalogue icon in the page header, the dashboard "Browse scholarships" button and the near-miss "Full details" links.
- **Styles:** new `.sp-mrow*` / `.sp-mdialog*` rules in `student-portal.css`, using the type scale.
- **Checked** at 1440px and 375px: rows stack the button under the text on phones, and the dialog scrolls. No horizontal scroll.

## Tests
- `NoMatchTest` now also checks the row, the opener and the dialog markup. 109/109 pass.

## No percentages: results use words only
- `ScholarshipMatch::STATUS_LABELS`:
  - `eligible` → **Eligible**
  - `possible` → **Nearly eligible** (every checkable rule passes, something must be confirmed with the provider)
  - `needs_info` → **Needs more information**
  - `not_eligible` → **Not eligible**
- **Closeness % removed from:**
  - My matches rows and dialog;
  - dashboard match cards (the meter bar is gone; the status pill sits in the card foot);
  - the student catalogue ("X% closeness for you");
  - the near-miss cards (now "1 requirement away");
  - the Finder results;
  - the provider applicant list.
- **Sidebar summary:** reads "5 of 11 scholarships fit you so far" instead of a percentage. Tiles: Eligible / Nearly / Need info.
- **AI prompt:** `ExplanationPrompt::matchContext()` no longer takes or sends a score, so the AI can't quote a percentage either. A unit test asserts "closeness" is absent from the prompt.
- **Score still used internally:** it remains stored and is used only to order matches within a status, and to order near misses.

## Smooth page changes (no flinch on click)
- **Cross-document view transitions:** `@view-transition { navigation: auto; }` in `bakr-core.css`, `bakr-dashboard.css` and `student-auth.css`.
  - Clicking a link cross-fades to the next page (160ms) instead of blanking and repainting.
  - The sidebar (`.dash-side`, `bakr-side`) and the top navbar (`.bk-navbar`, `bakr-nav`) swap instantly in place, so they never blink.
  - Turned off for reduced motion.
  - Browsers without support load pages as before. No dependency.
- **No sideways shift:** the dashboard content column's scrollbar appeared only on long pages, so content shifted 10px sideways between short and long pages (measured: 1080px vs 1070px). Fixed with `scrollbar-gutter: stable` on `.dash.is-app .dash-main`, and on `html` for public pages; the app shell itself gets no gutter.
- **Checked:**
  - the transition rule loads on dashboard, public, login and staff pages;
  - the navbar Sign in dropdown still opens fully.

Tests: 109/109 pass.
