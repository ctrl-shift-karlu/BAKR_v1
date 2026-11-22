# 2026-11-13 — Results hide non-matches; "missing just one requirement" when nothing matches

## Changed
- **My matches** (`/student/results`), the **Scholarship Finder results** and the **student dashboard** list only *Likely eligible*, *Possible match* and *Needs more information* (`ScholarshipMatch::LISTED`).
  - "Not a match right now" scholarships are no longer listed, and their filter tab is gone. `?status=not_eligible` falls back to all matches.
  - The Finder heading still counts against every active scholarship ("0 of 11 scholarships may fit you").
- **When nothing matches**, `partials/no-match` shows:
  1. "No scholarship matches you right now", with a Change my answers / Start over button.
  2. "Closest: missing just one requirement": the not-a-match scholarships whose **only** failed *required* rule is one rule, closest score first. Failed optional rules don't count. Each card shows:
     - the rule label (from the scholarships table);
     - the rule's own detail ("Your answer: No.");
     - closeness;
     - Full details and Official site links.
  3. If none is one requirement away: "No scholarship is just one requirement away either…".
- **Near misses** appear only when nothing is listed, as requested.
- On the dashboard, near misses replace "Your best matches" when there is nothing likely, possible or needing info, capped at 4.
- **Catalogue deep links** (`/scholarships#card-…`, `/student/scholarships#card-…`) now open the sector tab that holds the card. Before, the Finder's "Full details" link landed on a hidden card. (`bakr-scholarships.js`)
- **Eligibility is unchanged:** statuses still come only from `RuleEvaluator`. This is display only.

## Added
- `ScholarshipMatch::LISTED`, `failedRequired()`, `isNearMiss()`, `missingRequirement()`, `nearMisses()`.
- `resources/views/partials/no-match.blade.php`; `.sp-nomatch` / `.sp-near*` styles in `student-portal.css`.
- `tests/Feature/NoMatchTest.php` (5 tests):
  - non-matches hidden;
  - near misses shown only when nothing matches;
  - a two-fail scholarship isn't a near miss;
  - the "none either" message;
  - the Finder with the real engine.

## Updated tests
- `ScholarshipFinderTest`: results no longer list every scholarship. A provider's stricter TDP cap now removes TDP from the list instead of showing it as not eligible.

Tests: 109/109 pass.
