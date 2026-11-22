# CHED priority programs (national list) — used by CMSP and BPMSP

- **Issuer:** Commission on Higher Education
- **Document:** CMO No. 7, s. 2023, "List of Priority Programs" (national priority programs for AY 2023-2024 to 2027-2028, as stated on the page below)
- **Read on:** 2026-11-19, by web fetch of a **secondary copy**: https://sites.google.com/lspu.edu.ph/planning/knowledge-hub/ched-priority-programs-cmo-no-7-series-of-2023 (Laguna State Polytechnic University planning office)
- **Not read:** the CHED PDF itself (`ched.gov.ph/wp-content/uploads/CMO-NO.-7-S.-2023.pdf` returned 403), and **CMO No. 15, s. 2026** (PDF linked from https://caraga.ched.gov.ph/ched-scholarship-program-csp/, an image-only file that could not be read as text).
- **Used for:** `ched-cmsp` (rule `priority`) and `bpmsp` (rule `program`), through `$chedPriority` in `config/scholarships.php`

## What the sources say
- **CMSP (CMO 13 s.2025, Sec. 5.4 / 17.2):** a student enrolled in a **non-priority program cannot apply**; the program must be a CHED-identified priority program with government recognition (COPC for SUCs and LUCs).
- **CHED Caraga's CMSP page** says first priority goes to Psychology (educational psychology), Physical Therapy, Occupational Therapy and Speech-Language Pathology, and **second priority to the programs in CMO No. 7, s. 2023 "or the latest applicable CHED issuance"** (national, regional and GAD priorities).
- **CMO No. 7 national list** (A. National Priority Programs, as copied on the LSPU page):
  1. Science and Mathematics Education: Applied Physics/Physics; Applied Mathematics/Mathematics; Applied Statistics/Statistics; Biochemistry; Biology; Botany; Chemistry; Environmental Science; Geology; Human Biology; **Marine Biology**; Marine Science; Meteorology; Molecular Biology and Biotechnology; Vulcanology and Seismology
  2. Information Technology: Computer Science; Cyber Security; Entertainment and Multimedia Computing / Game Development and Animation; **Information Technology**; Library and Information Science
  3. Engineering and Technology: Agricultural and Biosystems, Ceramic, Chemical, Civil, Computer, Electrical, Electronics / ECE, Food, Geodetic, Industrial, Manufacturing / Production, Materials, Mechanical, Mechatronics, Metallurgical, Mining, Petroleum, Robotics, Sanitary and Structural Engineering; Aircraft Maintenance Technology; Aviation related programs; Engineering Technology; Industrial Technology; Mechatronics Engineering Technology
  4. Architecture and Related Programs: Architecture; Fine Arts; Interior Design; Landscape Architecture; Environmental Planning
  5. Business and Management: Accountancy; Business Analytics; Hospitality / Hotel and Restaurant Management; **Tourism Management**
  6. Health Profession Education: Dental Medicine; Optometry; Medical Technology; Midwifery; Pharmacy; Physical Therapy; Public Health; Occupational Therapy; Nursing; Nutrition and Dietetics; Respiratory Therapy; Radiologic Therapy; Speech Language Pathology
  7. Maritime Education: Naval Architecture and Marine Engineering
  8. Social Sciences: Community Development; Human Services (Guidance and Counselling); Indigenous Peoples Studies; Peace Studies; Psychology; **Social Work**
  9. Teacher Education: Secondary Education majors in **Science and Mathematics**; Culture and Arts; Early Childhood Education; Special Needs Education; Sports and Exercise Science
  10. Multi and Interdisciplinary: Agribusiness; **Agro-forestry**; Data Science and Analytics; Disaster Risk Management; Climate Change; Renewable / Sustainable Energy
- Allocation on that page: National 95% (STEM: Science and Mathematics 15%, Engineering and Technology 15%, IT Education 20%; Non-STEM: Business and Management 10%, Multi and Interdisciplinary 10%, Other disciplines 25%) and Regional 5%. The regional list comes from a separate CHED memorandum.

## How BAKR uses it
- **Pass (exact name in the national list):** BS Information Technology, BS Agro-Forestry, BS Marine Biology, BS Social Work, BS Tourism Management, BSEd Mathematics, BSEd Science.
- **Everything else** (for example BS Information Systems, Fisheries and Aquatic Sciences, Food Technology, Disaster Resilience and Management, Criminology, Entrepreneurship, BSEd English, BPE, BTLE, BA Communication, BPA): **not named in the list by that name, so the rule says "confirm" with CHEDRO XI, never "not eligible"**. Close names such as "Disaster Risk Management" vs "Disaster Resilience and Management" are deliberately not guessed.
- **Follow-up:** get a text copy of CMO No. 15, s. 2026 and the CHED PDF of CMO No. 7, then update `$chedPriority` and this file. If CHEDRO XI publishes a Region XI list, add it too.
