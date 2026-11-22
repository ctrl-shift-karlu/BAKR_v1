# BFAR Fisheries Scholarship Program (FSP)

- **Source:** https://www.bfar.da.gov.ph/opportunities/scholarship/ and the FCEG application form (page 2: criteria, checklist, support), provided by the owner on 2026-11-18.
- **Screenshots:** `01`–`04-fsp-page.png`. The FCEG PDF was attached in chat and was not saved here; download it again from the BFAR page if needed.
- **BAKR slug:** `bfar-fsp` (provider key `bfar`).

## What BAKR uses

| Item | From the source | In BAKR |
|---|---|---|
| Components | FILG (with honors), FCEG (fisherfolk children), ICCs/IPs | `any_of`: `farm_fisher_family` = `fishr`, `special_groups` = indigenous_people, SHS GWA ≥ 90 (DepEd "with honors", DO 36 s. 2016) |
| Level | SHS graduate / graduating, no college units | `incoming_first_year` + `previous_units = no` |
| GWA | Last 3 SHS semesters at least 80% | `shs_gwa_percent >= 80` |
| Course | Bachelor of Science in Fisheries | `program_in`: DNSC BSFAS = **maybe** (BFAR decides), all others = not |
| Good moral | certified | `disciplinary = no` |
| Age ≤ 25, 6-month residence | stated | `confirm` (not collected) |
| Support | FCEG: ₱5,000/month stipend, ₱2,000 books/sem, OJT ₱3,000, thesis ₱7,000, graduation ₱1,500 | `grants` regular 5000 / month |

## Not published

- No application dates on the page: `window_note` tells students to ask BFAR XI.
- FILG and ICC/IP amounts were not provided; only FCEG amounts are shown.
