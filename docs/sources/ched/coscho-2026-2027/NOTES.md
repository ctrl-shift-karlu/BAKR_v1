# CHED CoScho (Coconut Farmers Scholarship), AY 2026–2027

- **Source:** https://mimaropa.ched.gov.ph/call-for-applications-scholarship-program-for-coconut-farmers-and-their-families-coscho-for-academic-year-2026-2027/ (provided by the owner on 2026-11-18).
- **Screenshots:** `01`–`03-call-page.png` (qualifications, documents, benefits, priority programs).
- **BAKR slug:** `coscho` (provider key `coscho`, logo CHED).

## What BAKR uses

| Item | From the source | In BAKR |
|---|---|---|
| Coconut farmer family | NCFRS-registered coconut farmer or dependent | `farm_fisher_family` = `coconut` |
| Income | Parents' combined ₱300,000 or less | `income_lte` 300000 |
| Who | SHS / ALS graduates, college students | `enrollment_status` in incoming / enrolled |
| GWA | College students: at least 80% | `college_gwa_percent >= 80`, skipped for incoming |
| No national gov't scholarship | stated | `contains_none` tes_tdp_ched / other_government |
| Priority programs | 80% of slots; 20% for other fields | `program_in`, **not required** (IT, Marine Bio, Social Work, Agroforestry = pass; BSFAS, Tourism Mgmt = maybe) |
| Benefits | ₱35,000 stipend + ₱5,000 books per semester | `grants` regular 40000 / semester |

## Caveats

- The call was posted by **CHED MIMAROPA** (deadline August 31, 2026). DNSC students apply through **CHEDRO XI**; no Davao dates are set in BAKR (`window_note` says so).
- Tuition assistance is only for private colleges in towns with no SUC/LUC, so it does not apply at DNSC.
