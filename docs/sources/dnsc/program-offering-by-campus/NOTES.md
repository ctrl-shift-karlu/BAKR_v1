# DNSC — Program Offering by Campus

- **Issuer:** Davao del Norte State College (official website)
- **Page:** https://dnsc.edu.ph/program-offering-by-campus/ (linked from https://dnsc.edu.ph/program-offerings/, "View Programs by Campus")
- **Read on:** 2026-11-19 through a web fetch (dnsc.edu.ph refuses most automated requests with 403 / a Cloudflare check; this page was readable). The page shows **no date** and no "current as of" wording.
- **Used for:** `config/dnsc_campuses.php` → `DnscCatalogSeeder` → the campus → program lists of the application form.

## What the page lists (program names as BAKR spells them)
| Campus (name on the page) | Programs |
|---|---|
| **Main Campus – Panabo** (15) | BS Information Systems · BS Information Technology · BS Fisheries and Aquatic Sciences · BS Food Technology · BS Marine Biology · Bachelor of Public Administration · BA Communication · BSEd English · BSEd Mathematics · BSEd Science · BS Disaster Resilience and Management · BS Entrepreneurship · BS Social Work · BS Tourism Management · Bachelor of Technology and Livelihood Education |
| **Carmen Campus** (2) | Bachelor of Physical Education · BS Criminology |
| **Samal Campus** (2) (also "Igacos Campus") | BS Information Technology · BS Marine Biology |
| **Talaingod Campus** (1) (also "DJAL Talaingod Campus") | BS Agro-Forestry |
| **Asuncion Campus** (1) | BS Information Technology |
| **Santo Tomas Campus** (1) (also "Sto. Tomas") | BSEd Mathematics |

Not offered at the Main campus: **Agro-Forestry** (Talaingod), **Criminology** and **Physical Education** (Carmen).

## Cross-checks (other official articles)
- **Carmen:** DNSC news (March 2026) says CHED inspected "its Bachelor of Physical Education (BPEd) and Bachelor of Science in Criminology (BS Crim) programs" (inspection 17 March 2026); CHED RO XI (26 May 2025) inspected the BS Criminology program. BS Criminology starts at first-year level in AY 2026–2027 while the Certificate of Program Compliance is pending.
- **Asuncion:** DNSC news (3 August 2026): "The campus will initially offer the Bachelor of Science in Information Technology (BSIT), with additional academic programs to be introduced in the future"; extension classes start in AY 2026–2027.
- **Talaingod:** a DNSC news item calls the BSAF the lone academic program there.
- **Samal, Santo Tomas:** only this page; nothing else confirms them.
- The campus laws (RA 11855) create the Carmen, Santo Tomas and Samal campuses.

## Name differences on the site (BAKR keeps its own spelling)
"Agro-Forestry" vs "Agroforestry"; "Bachelor of Physical Education (BPE)" vs "(BPEd)"; the BSEd majors are listed by major on this page; BTLEd is "Technology and Livelihood Education" here and "Science in Technology and Livelihood Education" on another page.

## How BAKR uses it
Each campus lists exactly these programs (22 campus-program rows in total). The form shows only the chosen campus' programs and the server refuses any other pairing. Campus names already saved in students' forms are kept (Main stays "Main (Panabo Campus)"); "Santo Tomas Campus" is new.

## Follow-ups
- The page is undated: confirm with the DNSC registrar that it is current, especially Samal and Santo Tomas.
- Asuncion and Carmen are growing (new programs planned): add them to the config when DNSC announces them.
