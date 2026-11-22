# 2026-11-21 · About page: matching chart fixed, team finalised, star-rating feedback

Owner's request: fix the matching analytics, remove "We will add the details here.", remove "Scholars who made it", finalise "The people behind BAKR" (names only, photo placeholders), and add a working feedback section with a 1–5 star rating above the footer.

## Matching analytics (fix)

- **Bug:** "How profiles match scholarships" counted only Eligible / Nearly eligible / Needs more information. Not eligible (most results) was left out, so the shares did not describe the results, and a hidden small group left an unexplained gap in the bar. Matches for inactive scholarships were also counted.
- `PublicAnalyticsService`: all four outcomes (`STATUS_ORDER`), active scholarships only, a `tone` per row (`OUTCOME_TONES`: good / warn / blue / gray, same meaning as `--viz-*`), `total`, `profiles`, `with_match` (profiles with at least one Eligible / Nearly eligible result, shown only when ≥ 10).
- New chart **"Scholarships students match most"**: profiles eligible or nearly eligible per scholarship (top 5, `Scholarship::shortName()`), share of all profiles; groups < 10 hidden.
- View: segments grow by count (`flex-grow`), so the bar always adds up. Small groups are pooled into one striped segment, and narrow segments hide their label (the legend still shows the number).
- Layout bugs fixed on the same page: the year-level chart wrapped its 5th column (`--cols`), the line chart's axis text was ~6px on phones (larger SVG text, "0" hidden there), the program bars and donut track were light grey in dark mode, the Scholarship Office rows were very tall on phones (`dt` flex-basis), and "Fewer than 10" wrapped.

## Content

- Removed "We will add the details here." (office) and the whole "Scholars who made it" section (`partials/about/graduate-stories` deleted, its CSS / JS tag removed; backup in `docs/changes/backups/about-2026-11-21/`).
- Team: Karl Angelo T. Gabaes, Rochelle D. Abella, Marielle Joie Javier. No roles. Photos: add `public/images/img/team/member-1.jpg` / `-2` / `-3` (square) and they replace the placeholder by themselves. On phones the cards become one row per person.
- The "Ready to find your match?" strip moved up (after "What you can gain"), so the team and the feedback form sit right above the footer.

## Feedback section

- `partials/about/feedback`: required 1–5 stars (radio group, CSS fill, live word "4 out of 5 · Very good"), optional "Which parts of BAKR did you use?" chips (`ContactMessage::ASPECTS`), optional comment, name + e-mail (pre-filled when signed in), optional "share" consent, honeypot. The side panel shows the average, the star distribution and two recent quotes from **approved shared** feedback only.
- `POST /about/feedback` (`about.feedback`, throttle 5/10) → `AboutController::feedback` → `SiteFeedbackRequest` → `ContactMessageService::record()` (topic `feedback`, stored, then e-mailed). Errors and success go back to `/about#feedback`.
- DB: migration `2026_11_21_000002_add_aspects_to_contact_messages` (`aspects` JSON, nullable). `ContactMessage::RATINGS`, `ASPECTS`, `aspectLabels()`. The comment may be empty (stored as `''`).
- Admin › Messages and the notification e-mail show "Used: …" and "Rating only, no comment". The Contact page hides an empty comment and uses `RATINGS`.
- Privacy Policy "Anyone who writes to us" now mentions the star rating and the parts used.
- Styles reuse `bakr-contact.css` (now also loaded on About); new rules in `bakr-about.css`, dark rules in `bakr-dark.css`. `sw.js` → `bakr-v7`.

## Checks

- New `tests/Feature/AboutPageTest.php` (6 tests). Full suite: 295/295.
- Probe (rendered in a rolled-back transaction, headless Chrome at 500 / 820 / 1400px, light + dark, with and without shared ratings + a validation error): no sideways scroll, no card overflow, no text under 12px.
