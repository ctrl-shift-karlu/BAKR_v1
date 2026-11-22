# LANDBANK Scholarship Program (ILP), DNSC call 2026

- **Source:** DNSC Scholarship Office announcement (Facebook post) + the ILP list of documents, provided by the owner on 2026-11-18.
- **Screenshot:** `01-dnsc-post-documents.png` (list of documents, special + regular application).
- **BAKR slug:** `landbank-ilp` (provider key `landbank`).

## What BAKR uses

| Item | From the source | In BAKR |
|---|---|---|
| Eligible courses | BSFAS, BS Food Technology, BS Agroforestry, BSIT | `program_in` pass list; every other DNSC program = not |
| Year | Special application: 3rd/4th-year regular students; regular application: incoming 1st year | `any_of` (incoming OR year 3–4) |
| Family | Child/grandchild of an ARB or RSBSA-registered small farmer/fisher | new question `farm_fisher_family` = `rsbsa` |
| Income | ₱300,000 or less | `income_lte` 300000 (the ₱250k–350k bracket = confirm) |
| No other scholarship | stated | `contains_none` on all assistance |
| No conditional/failing grades | stated | `failing_grade = no` |
| Davao del Norte resident | stated | `confirm` (BAKR does not collect the address) |
| Deadline | October 30, 2026 | `application_closes_on` |

## Not published

- **Benefit amounts**: not in the post. No `grants` entry. Add one only with an official source.
