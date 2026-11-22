# 2026-11-19 — Eligibility audit, CMSP fix, income brackets

**Owner's report:** a CMSP scholar filled in the form as a Grade 12 student and CMSP did not show as eligible.
**Request:** check and fix all providers' eligibility rules from the official sources, in all pages, and make the
household income brackets follow each provider's limits.

## Why CMSP never showed "Eligible"
Two **required `confirm`** rules: "Graduated from a senior high school in the Philippines" (the form never asked) and
"Enrolled in a CHED priority program" (no list). Any required confirm caps a result at "Nearly eligible".

## Fixed
- **CMSP** (`ched-cmsp`): new question `shs_country` (Part II) → rule `eq ph`; priority program is now `program_in`
  against CHED's national priority list (CMO No. 7, s. 2023; `$chedPriority` in `config/scholarships.php`, sources in
  `docs/sources/ched/cmsp-priority-programs/NOTES.md`). DNSC programs named in it: BSIT, Agro-Forestry, Marine Biology,
  Social Work, Tourism Management, BSEd Mathematics / Science → pass. **Every other program stays a confirm (never a
  fail)** with an accurate note, because CHED may add newer or regional priorities (`open_list`).
- **BPMSP:** "incoming first-year" now reads `enrollment_status` (JMC 2.1; a current student has units, 3.4 / 3.5); same priority list.
- **DOST-SEI:** "STEM strand, or top 5% of a non-STEM class" is answered from the new strand question (STEM passes;
  others confirm, because only the principal can certify a *percentage*).
- **LANDBANK ILP:** "Resident of Davao del Norte" read from the new province question (select).
- **BFAR FSP:** "Not more than 25 years old" read from the birth date (age only; the date stays in BAKR).
- **Evaluator semantics:** an *optional* confirm no longer holds a result at "Nearly eligible"; a **`bonus`** rule
  (BPMSP special equity group, +10 ranking points) never changes the status; "Not sure" for Top 5 is "needs info", not "no".
- **Income brackets** (`ApplicationSchema::INCOME`): 11 brackets whose edges are the published caps — ₱300,000 (LANDBANK,
  CoScho), ₱350,000 (Cebuana), ₱500,000 (CMSP; BPMSP full points), ₱750k / 1M / 1.25M / 1.5M / 1.75M (BPMSP points),
  ₱2,000,000 (BPMSP cap). The old ₱250,001–350,000 bucket straddled the ₱300,000 cap, so LANDBANK and CoScho could never
  give a clear answer. Old keys stay readable (`LEGACY_INCOME`) but are not offered.
- **TES / DOST** are no longer stuck at "Nearly eligible" by their optional notes.

## Audited, unchanged (match their sources)
DNSC Academic / GIA A / GIA B / SA-C, CHED TDP (no income cap, indigency certificate), CHED TES (priority groups),
GrabScholar (incoming freshmen or first-year, 90%), Cebuana (₱350,000, 85% GWA, listed courses), CoScho (₱300,000, coconut family).

## Database
`php artisan bakr:sync-scholarship <slug> --apply` for `ched-cmsp`, `bpmsp`, `dost-sei`, `landbank-ilp`, `bfar-fsp`
(3 student forms re-matched each). Backup before: `docs/changes/backups/rule-fix-2026-11-19/scholarships-before.json`.

## Tests
`php artisan test` → **236/236**. New `tests/Unit/IncomeBracketTest.php` (every income cap is a bracket edge, contiguous
brackets, old keys readable, age / province / "not sure"); CMSP, DOST, BPMSP, TES expectations updated in
`RuleEvaluatorTest` / `MatchingPipelineTest` (a Grade 12 BSIT student is now **Eligible** for CMSP).

## Follow-ups
- **Verify the priority list with CHEDRO XI / the DNSC Scholarship Unit.** BAKR used a university's copy of CMO No. 7;
  CHED's CMO No. 15, s. 2026 (CMSP priorities) was an unreadable image PDF and the CHED PDF returned 403.
- Students who saved the form earlier lack `shs_country` and `home_province`: CMSP / LANDBANK show "needs more
  information" until they save the form again; their income keeps the old, wider bracket until they re-pick.

## Also: sticky modal buttons
- `public/css/bakr-dashboard.css` — every `.dash-dialog-foot` is sticky at the bottom of its modal (all portals, all screen sizes, light + dark; `100dvh` and safe-area padding on phones). Checked with headless Chrome at 1280 px, 500 px and dark mode.

## Also (later the same day)
- **Reminders, not eligibility:** the AI's "Still to provide or confirm" list is now `reminders` (documents, deadlines, slots), shown as a separate "Reminders - not part of your eligibility" note, and the prompt says reminders never lower a status (`MatchPrompt`, `results.blade.php`, `.sp-reminders`). Old reviews that stored it as `missing` still display.
- **Simpler income brackets:** 5 instead of 11 (up to 300k, 300-350k, 350-500k, 500k-2M, over 2M); BPMSP's finer steps only change ranking points. Older keys stay readable (`LEGACY_INCOME`).
- **Logic for the questions:** a 2nd-year student is no longer asked anything about senior high school. `'when'` on each field (`first_year` / `incoming` / `upper`) drives the form, the validation and the data cleaning; current 1st-years still give their Grade 12 grade (CMSP and GrabScholar read it). `tests/Unit/QuestionLogicTest.php`.
- **Campus -> program:** the form already filtered programs by campus; now the server also refuses a program the campus does not offer, and `DnscCatalogSeeder` removes programs a campus no longer lists. The data in `config/dnsc_campuses.php` is still a placeholder (every program at every campus) until the real per-campus offerings are provided.

## Campus -> program: official DNSC data
DNSC's own page "Program Offering by Campus" (https://dnsc.edu.ph/program-offering-by-campus/) now feeds `config/dnsc_campuses.php`
(notes and cross-checks: `docs/sources/dnsc/program-offering-by-campus/NOTES.md`): Main 15, Carmen 2, Samal 2, Talaingod 1, Asuncion 1 and a
NEW Santo Tomas campus 1 (BSEd Mathematics). Applied to the local DB with `db:seed --class=DnscCatalogSeeder` (1 campus and 1 program added,
69 removed; programs that stayed kept their ids). Students whose saved form pointed at a removed program must pick it again (the matcher treats
it as "confirm"). The page shows no date: confirm with the DNSC registrar. Tests: 244/244.

## Voice pass + provider logos
- **Second-person voice:** the home page (hero, steps, all FAQ answers, now accurate about the AI matcher and the Contact page), About, Contact, footer, welcome pop-up, sign-in pages, the 403 page, shared partials (About BAKR, Help, no-match, sign-out), several e-mail / flash / validation messages and the explainer prompt were rewritten to speak to "you". Student, scholar and provider portals were already in "you" and got only small fixes. One test phrase updated (`NoMatchTest`).
- **Logos:** official LANDBANK and BFAR marks (`public/images/logo/LANDBANK-Logo.svg`, `BFAR-Logo.svg`; Wikimedia Commons, public domain, checked for scripts) now identify those providers in `config/providers.php`, scholarship cards and the home partner strip. CHED, DOST, Cebuana, Grab and DNSC already had theirs.
