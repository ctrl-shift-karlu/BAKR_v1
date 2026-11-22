# CHED Memorandum Order No. 13, s. 2025: Revised Policies and Guidelines for the CHED Merit Scholarship Program (CMSP)

- **Issuer:** Commission on Higher Education, Office of the President
- **Authority:** RA 7722; Art. XIV of the 1987 Constitution; CHED en banc **Resolution No. 498-2025, 30 June 2025**
- **Pages:** 11 (screenshots `page-01`…`page-08` cover Art. I, V, VIII, IX, X and XII)
- **Used for:** `ched-cmsp`
- **Read in full:** 2026-11-15

## Art. I, program overview
CMSP is a **competitive** CHED scholarship for academically talented and deserving Filipino **incoming or current first-year** college students with a **GWA of at least 93%** (or its equivalent) on their Grade 12 report card.

## Art. V, application process
**Section 4, eligibility.**
- **4.1** Filipino citizen.
- **4.2** Graduate of a **senior high school in the Philippines** with a GWA of at least 93%.
- **4.3** Combined annual gross income of parents or guardians **not over ₱500,000**.

**Section 5, ineligible applicants.**
| | Who cannot apply |
|---|---|
| 5.1 | Foreign students |
| 5.2 | Students who are not incoming or current first-year undergraduates |
| 5.3 | Students in priority programs **without CHED government recognition or certification** |
| 5.4 | Students who will enroll in a **non-priority program** |
| 5.5 | **Transferees and shiftees with credited units** |
| 5.6 | Existing recipients of any national government-funded scholarship or grant, **including TES and TDP**. One-time grants are exempt |
| 5.7 | Students who completed a degree, or **second-course takers** |
| 5.8 | Applicants who submitted tampered or falsified documents |

**Section 6, documents.**
- **6.1** Application form (Annex A).
- **6.2** NSO or PSA birth certificate.
- **6.3** Certified true copy of Form 138 (SF9), signed by the SHS registrar.
- **6.4** **Any one** of:
  - latest ITR of the parents or guardian;
  - BIR Certificate of Tax Exemption or Non-Filer;
  - certified true copy of the latest contract or proof of income (OFW or seafarer parents);
  - Social Case Study Report from the C/MSWDO.
- **6.5** If applicable:
  - PWD ID or PDAO certification;
  - Solo Parent ID (applicant or parent);
  - Senior Citizen ID;
  - DHSUD or C/MSWDO certification (underprivileged and homeless);
  - C/MSWDO Social Case Study (Magna Carta for the Poor, or first-generation students);
  - NCIP certification (Indigenous Peoples).
- **6.6** Notarized Certificate of Guardianship, if applicable.

## Art. VIII, financial assistance
**Section 13.1, SUCs and LUCs (DNSC).** Tuition and other school fees are covered by **Free Higher Education**.

| Program | Stipend | Book / connectivity | Total |
|---|---|---|---|
| **Full-SSP** | ₱35,000 per semester (₱7,000 × 5 months) | ₱5,000 per semester | **₱40,000 per semester; ₱80,000 per AY** |
| **Half-SSP** | ₱17,500 per semester (₱3,500 × 5 months) | ₱2,500 per semester | **₱20,000 per semester; ₱40,000 per AY** |

**Section 13.2, private HEIs.**
- Full-PESFA: ₱60,000 per semester, including ₱20,000 TOSF.
- Half-PESFA: ₱30,000 per semester, including ₱10,000 TOSF.

**Section 14:** trimester and quarter schools receive the same totals, distributed proportionally.

## Art. IX, payment
**Section 16.1, direct payment.** Used when the HEI has no MOA, has fewer than 10 scholars, has unliquidated balances, or has verified complaints. Payment is by bank credit or check.

| Payment | Documents |
|---|---|
| **Initial** | Certified COR/COE; college ID or a government ID with the scholar's signature, or a school certification; photocopy of an **active Landbank ATM card** |
| **Succeeding** | Certified COR/COE; certified grades with the GWA of the previous term; HEI certification that the scholar has no other national scholarship (**every start of the AY**) |

**Section 16.2, through HEIs.** Used for schools with a MOA and 10 or more scholars, with a billing statement (Annex G).

**Section 16.3:** liquidation within 30 working days.

## Art. X, scholar responsibilities (Section 17)
| | Responsibility |
|---|---|
| 17.1 | Follow the scholarship contract (Annex B-2) |
| 17.2 | Enroll in a CHED-identified **priority program** (SUCs or LUCs with COPC) |
| 17.3 | Keep a **GWA of at least 85% (full) or 80% (half)** |
| 17.4 | Carry the required **regular load**, unless finishing the last remaining subjects |
| 17.5 | Finish within the prescribed period |
| 17.6 | Get CHEDRO's **written approval (Annex H) before transferring, shifting, or taking an LOA**. An LOA is allowed **once**, up to 1 AY, with **no benefits** while on leave |
| 17.7 | Refund the benefits if terminated |
| 17.8 | Show good moral character |
| 17.9 | Report changes to CHEDRO |
| 17.10 | Submit reports on time |
| 17.11 | Graduate exit form (Annex I) |

## Art. XII, administration (Section 28)
**28.1 OSDS:** slot distribution per region, WFP, sub-allotments, database (Data Privacy Act).

**28.2 AFMS:** budget and fund transfers.

**28.3 CHEDROs:**
- **28.3.2** Accept and evaluate applications.
- **28.3.3** **Determine qualified applicants and rank them by the selection criteria.** Full vs half is decided here.
- **28.3.4** Notify applicants by **SMS or e-mail**.
- **28.3.5** Issue the **NOA (Annex B-1)** directly, or through the HEIs.
- **28.3.6** Orient scholars.
- **28.3.7** Handle LOA, replacement, transfer, shift, termination and appeals (Annex H).
- **28.3.10** Official RankList and Masterlist within 45 days.
- **28.3.16** Collect refunds from disqualified scholars.

## How BAKR uses it
- **Rules:** citizen; first year; Grade 12 GWA of at least 93%; income ₱500,000 or less; no other national grant; no degree; no credited units; **Philippine SHS** (answered in the form, `shs_country`, since 2026-11-19); **priority program** (`program_in` against CHED's national list, see `../cmsp-priority-programs/NOTES.md`; a program not named in it stays a *confirm* with CHEDRO XI).
- **Why CMSP never showed "Eligible" before 2026-11-19:** the Philippine-SHS and priority-program rules were both required *confirm* rules, and any required confirm caps a result at "Nearly eligible". Both are answered from the form now.
- **Not modelled:** full vs half (decided by CHEDRO ranking).
- **Still open:** CMO No. 15, s. 2026 (CMSP priority list) and the CHED PDF of CMO No. 7 could not be read; see the priority-programs notes.
