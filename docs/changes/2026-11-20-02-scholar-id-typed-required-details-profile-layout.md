# 2026-11-20 · Scholar ID typed freely, required personal details, scholar profile layout

**Asked (owner):** the personal details added for scholars must not be optional; gender is Male or Female only; the Scholar ID is just typed (remove the whole format idea); the provider's scholar profile page was too stretched out: lay it out like the other pages.

## What changed
- **Scholar ID format removed.** `App\Support\ScholarIdFormat` deleted; migration `2026_11_20_000002_drop_scholar_id_format` drops `scholarships.scholar_id_format` (the local values were `SCID-0000-0000`, `HEI-000-000-000`, `SCID-000-000`; saved Scholar IDs are untouched). My scholarships no longer has a "Scholar ID format" section (an old `?edit=scholar_id` link shows the read view; its save is refused with 422). `ScholarshipEditor` no longer tracks the field.
- **Scholar ID** is free text (max 40), stored as typed (trimmed, inner spaces collapsed, case kept: `Scholar::normalizeCode()`), unique per scholarship ignoring capitals. The input no longer forces capitals.
- **Birthday, gender, mobile, address are required** on Add scholars, in Excel/CSV files (new required columns: Mobile number, Address, Birthday, Gender) and on the profile. **Gender = Male / Female** (`Scholar::GENDERS`); files accept M / F. The migration clears any other stored gender (none existed locally).
- **Provider's scholar profile redesigned** (`provider/scholars/show`, `sp-*` styles in `bakr-provider.css`): identity summary, status card, then cards two per row: School information · Scholarship details, Personal information · Contact information, Maintaining documents · Stipend history. Each card is read-only with its own **Edit** (`?edit=<section>`) and **Save section** / Cancel, like My scholarships. `ProviderScholarController::update` now requires a `section` (school / scholarship / personal / contact) and validates and saves only that card's fields; every field of a card is required except the stipend. "Requirements by term" is now "Maintaining documents by semester" (wording rule).
- Add scholars page and the "Columns BAKR reads" help no longer mention a format or "(optional)".

## Checks
- 264/264 tests (`ScholarIdAndDetailsTest` rewritten, unit `ScholarIdFormatTest` replaced by `ScholarRosterParsingTest`; fixtures in `ProviderRosterTest`, `ProviderOperationsTest`, `ScholarAccountTest` now send the required details).
- Migration applied to the local MySQL `bakr` DB.
- Rendered pages from the local data (DOST, CHED, all 7 providers' My scholarships and Scholars list): headless Chrome probe at 500 / 820 / 1400 px found no overflow, sideways scroll or text under 12px; screenshots checked in light and dark.
