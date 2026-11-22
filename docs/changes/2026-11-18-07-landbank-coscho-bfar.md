# 2026-11-18 (07): LANDBANK ILP, CHED CoScho, BFAR FSP + their provider accounts

## Request
Add three scholarships (owner's sources: DNSC post for LANDBANK ILP, CHED CoScho call, BFAR FSP page + FCEG form) with their own info on the Scholarships page and their own provider accounts. Fix bugs/inconsistencies found on the way.

## Owner's decisions
- Placeholder provider logins `landbank@bakr.test`, `coscho@bakr.test`, `bfar@bakr.test`; random 16-char passwords in `.env` only.
- One new matching question (Part IV): `farm_fisher_family` (RSBSA/ARB, NCFRS coconut, FishR, none). Justified by the LANDBANK, CoScho and BFAR rules.

## Changes
- `config/scholarships.php`: `landbank-ilp`, `coscho`, `bfar-fsp` (rules, benefits, documents, grants only where an amount is published). Sources + reasoning in `docs/sources/{landbank/ilp-2026, ched/coscho-2026-2027, bfar/fsp}/NOTES.md`.
- `config/providers.php`: providers `landbank`, `coscho`, `bfar` (both optional pages on).
- `Scholarship::SHORT_NAMES` ILP / CoScho / FSP; CoScho uses the CHED logo.
- `bakr:sync-scholarship` can now ADD a scholarship that is new in the config (no reseed of the real DB needed).
- Home page counts (11, 4/5/2 sector split) now come from the table.
- Bug fixed: a required tick-box group missing from an older saved form was read as an empty answer and gave a false "not eligible". `AnswerSanitizer` now leaves it out → null fact → `RuleEvaluator` says "unknown".

## Database (local)
- Backup: `storage/backups/bakr-2026-11-18-before-new-scholarships.sql` (contains user data, do not share).
- `bakr:sync-scholarship landbank-ilp|coscho|bfar-fsp --apply`, `ProviderAccountSeeder`.

## Tests
210/210 pass (new: program lists cover all 18 DNSC programs, farm question old-form = unknown, new scholarship statuses).

## Open
- LANDBANK benefit amounts are not published: no stipend shown.
- CoScho call is CHED MIMAROPA's; check CHEDRO XI dates.
- BFAR: DNSC BSFAS vs "BS Fisheries" is a maybe; FILG / ICC-IP amounts not provided.
- Replace the placeholder provider e-mails with the real offices' addresses when known.
