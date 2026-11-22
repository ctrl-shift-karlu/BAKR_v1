# Official scholarship sources

BAKR's matching rules in `config/scholarships.php` come only from official documents. Each file in this folder is one of those documents. Each row in the table says which scholarship the document covers and which sections BAKR uses.

Each document has its own folder: the **screenshots** (`page-NN.png`, in reading order) and a **`NOTES.md`** with every section that matters, in text. Read `NOTES.md` first; open the screenshots to check wording.

**Adding a document:**
1. Save the PDF in `docs/sources/<provider>/`. Name it after the issuance, for example `ched/CMO-13-s2025-CMSP.pdf`.
2. Add a row to the table below.
3. Update the scholarship in `config/scholarships.php`, and set its `verified_on` and `source_note`.
4. Preview it with `php artisan bakr:sync-scholarship <slug>`, then run it again with `--apply`.

Only official, public issuances go here: CHED, UniFAST, DOST and the provider's own guidelines. Nothing with student data.

| Scholarship (slug) | Document | Issued / effective | Sections BAKR uses | File | Read on |
|---|---|---|---|---|---|
| CHED TDP (`ched-tdp`) | Revised TDP Guidelines of 2026 — UniFAST Board Resolution No. 2026-012 | Approved 16 Apr 2026, signed 3 Jun 2026; effective 1st Sem AY 2026–2027 | 2 (eligible HEIs), 3 (grant), 4 (qualifications: MRR, no other national aid), 5 (application process), 7-A (documents, Certificate of Indigency) | [ched/tdp-guidelines-2026/](ched/tdp-guidelines-2026/) (screenshots + NOTES.md) | 2026-11-15 |
| CHED Merit Scholarship (`ched-cmsp`) | CHED Memorandum Order No. 13, s. 2025 — Revised Policies and Guidelines for the CMSP; en banc Resolution No. 498-2025 | 30 Jun 2025 | Art. I; 4 (qualifications: 93% GWA, ₱500,000); 5 (who cannot apply); 6 (documents); 13 (benefits); 16 (payment documents); 17 (retention); 28.3 (selection) | [ched/cmo-13-s2025-cmsp/](ched/cmo-13-s2025-cmsp/) (screenshots + NOTES.md) | 2026-11-15 |
| CHED TES (`ched-tes`) | Revised TES Guidelines of 2026 (UniFAST Board), plus unifast.gov.ph/tes.html | Effective 1st Sem AY 2026–2027 | 1–3 (eligible HEIs; new slots ONLY for Listahanan/4Ps households, then solo-parent dependents and IPs, each by income); 4–6 (grant amounts, TES-3A PWD, TES-3B licensure, SARDO); 7 (qualifications, who is disqualified); 8–11 (process, documents, continuing grantees) | [ched/tes-guidelines-2026/](ched/tes-guidelines-2026/) (screenshots + NOTES.md) | 2026-11-15 |
| Bagong Pilipinas Merit (`bpmsp`) | Joint Memorandum Circular (CHED / TESDA / DepEd): Policies and Guidelines for the BPMSP, 39 pages. The JMC number and date were not in the screenshots | not shown | Part I §1–4 (scope, eligibility, who cannot apply, documents); Part II §5–10 (HE track: priority programs, documents, benefits, ranking 70% GWA / 30% income / +10 equity, priority domains, payment documents); Part V §18 (termination, return service) | [ched/bpmsp-jmc/](ched/bpmsp-jmc/) (screenshots + NOTES.md) | 2026-11-15 |
| DOST-SEI Undergraduate (`dost-sei`) | DOST-SEI Undergraduate Scholarship Program brochure (Merit and RA 7687 tracks) + DOST-SEI list of priority S&T programs | For Grade 12 graduating SY 2025–2026 (and earlier graduates not yet in college) | Criteria for eligibility (1–7.2, Merit vs RA 7687); scholarship privileges (regular year and mid-year); documentary requirements (Forms C–J, items 1–13); study placement; priority programs list (each DNSC program compared by name) | [dost/dost-sei-undergraduate/](dost/dost-sei-undergraduate/) (screenshots + NOTES.md) | 2026-11-15 |
| GrabScholar (`grabscholar`) | grab.com/ph GrabScholar College Scholarship page: "6 Merit-Based Scholarships for SY 2026-2030" | SY 2026 call: June 22 – July 17, 2026; results Aug 20, 2026 | Scope (6 full-ride, up to 4 years, any PH college); covered courses (STEM / Business / Sustainability, "included but not limited"); who may apply; qualifications (Filipino, no other scholarship, Grade 12 ≥ 90%, no SHS sanction); benefits; notification; documents by applicant type; timelines | [grab/grabscholar-sy2026-2030/](grab/grabscholar-sy2026-2030/) (screenshots + NOTES.md) | 2026-11-15 |
| Cebuana Lhuillier Foundation (`cebuana-clfi`) | CLFI scholarship FAQs Q1–Q12 (cebuanafoundation.org), plus its qualifications page read 2026-11-07 | SY 2026–2027 (results on or before the last week of Oct 2026) | Q1 (college, 1st–4th year); Q2 (qualifications: 24K Loyalty Card client, proof of income, GWA 85% with no failing mark, good moral, enrollment form, grading system); Q3 (public SUCs only); Q4 (screening, interview, results); Q5–Q7 (stipend up to ₱50,000, benefits, E2E); Q8 (no appeal); Q9 (courses non-negotiable); Q10 (one child per household); Q11 (all year levels); Q12 (registrar GWA certification) | [cebuana/clfi-faq/](cebuana/clfi-faq/) (screenshots + NOTES.md) | 2026-11-15 |

## Still needed
- **CHED priority programs list** (referred to by CMO 13, s. 2025). With it, the CMSP "priority program" check becomes a real rule.
- The original PDFs (optional; the screenshots and NOTES.md are the working copy).
- The BPMSP JMC number and date (title or signature page).
- Official documents for the DNSC programs (Academic, Grant-in-Aid A/B, Student Assistant).
- Cebuana: the full qualifications page, to confirm the ₱350,000 income cap and whether a Microsavings account or a relative's 24K card counts (the FAQ does not say).
