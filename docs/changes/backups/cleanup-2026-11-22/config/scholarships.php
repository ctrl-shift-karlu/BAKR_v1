<?php

/*
 * Scholarship definitions used by the matcher. Seeded into the `scholarships` table
 * (php artisan db:seed --class=ScholarshipSeeder); after that the table is the source of truth,
 * so an admin screen can edit it later.
 *
 * Every rule says where it came from:
 *   'src' => 'page'  the BAKR scholarships page (scholarshipsPage.blade.php)
 *   'src' => 'web'   the provider's own web page, read on 2026-11-07
 *
 * Rule types (see App\Matching\RuleEvaluator):
 *   eq, in, not_in        compare one fact
 *   gte, lte              numeric compare
 *   income_lte            household income bracket against a cap (peso)
 *   contains_any          a checkbox group includes at least one value
 *   contains_none         a checkbox group includes none of the values
 *   maybe_in              'pass' list passes, 'maybe' list needs confirming, anything else fails
 *   any_of                passes when any sub-rule passes
 *   confirm               cannot be decided from BAKR answers; shown as "confirm with the provider"
 * 'required' => true  a fail makes the scholarship "not eligible".
 * 'required' => false a fail only lowers the closeness score and moves it to "possible".
 *
 * Nothing here invents a threshold. Where a source gives none, the rule is a `confirm` rule.
 */

$FB = 'https://www.facebook.com/DNSCScholarshipAndFinancialGrants';
$unitNote = 'Set by the DNSC Scholarship and Grants Unit. It is not published in the sources BAKR uses.';

$dnsc = ['key' => 'dnsc', 'label' => 'DNSC student (current or incoming)', 'type' => 'eq', 'fact' => 'dnsc_student', 'value' => 'yes', 'required' => true, 'weight' => 2, 'src' => 'page'];

/*
 * DNSC programs that are on CHED's NATIONAL priority list (CMO No. 7, s. 2023, "List of Priority Programs"),
 * read on 2026-11-19 from the copy on the LSPU planning page (docs/sources/ched/cmsp-priority-programs/NOTES.md).
 * Used by the CMSP and the BPMSP, which both require a CHED priority program. Only exact matches by name are
 * listed; a DNSC program that is not named in the list is left out and the rule says "confirm", never "not
 * eligible". CHED's newer CMO No. 15, s. 2026 (CMSP first priorities: Psychology, Physical, Occupational and
 * Speech-Language Therapy) could not be read, and regional lists exist, so CHEDRO has the last word.
 */
$chedPriority = [
    'Bachelor of Science in Information Technology',          // Information Technology
    'Bachelor of Science in Agro-Forestry',                   // Multi and Interdisciplinary: Agro-forestry
    'Bachelor of Science in Marine Biology',                  // Science and Mathematics: Marine Biology
    'Bachelor of Science in Social Work',                     // Social Sciences: Social Work
    'Bachelor of Science in Tourism Management',              // Business and Management: Tourism Management
    'Bachelor of Secondary Education - Mathematics',          // Teacher Education: Science and Mathematics majors
    'Bachelor of Secondary Education - Science',
];

return [
    'verified_on' => '2026-11-07',

    'scholarships' => [

        // ------------------------------------------------------------------ DNSC
        [
            'slug' => 'dnsc-academic', 'name' => 'DNSC Academic Scholarship', 'sector' => 'internal',
            'provider' => 'DNSC Scholarship and Grants Unit', 'official_url' => $FB,
            'overview' => 'Scholarship assistance for DNSC students who meet the institution\'s academic excellence requirements.',
            'benefits' => ['Set by the DNSC Scholarship and Grants Unit'],
            'documents' => ['Latest Certificate of Registration (COR)', 'Latest grades / academic record', 'Student identification / school ID', 'Other documents required by the DNSC Scholarship and Grants Unit'],
            'application_route' => 'Through the DNSC Scholarship and Grants Unit.',
            'window_note' => null,
            'source_note' => 'Requirements from the BAKR scholarships page. The official Facebook page could not be read by BAKR, so the GWA cut-off is not known.',
            'rules' => [
                $dnsc,
                ['key' => 'enrolled', 'label' => 'Currently enrolled or enrolling', 'type' => 'in', 'fact' => 'enrollment_status', 'value' => ['currently_enrolled', 'incoming_first_year'], 'required' => true, 'weight' => 1, 'src' => 'page'],
                ['key' => 'gwa', 'label' => 'Meets the academic (GWA) requirement', 'type' => 'confirm', 'fact' => 'last_gwa_percent', 'required' => true, 'weight' => 3, 'src' => 'page', 'confirm_note' => $unitNote, 'min_percent' => null],
            ],
        ],
        [
            'slug' => 'dnsc-gia-a', 'name' => 'DNSC Grant-in-Aid (GIA) – Category A', 'sector' => 'internal',
            'provider' => 'DNSC Scholarship and Grants Unit', 'official_url' => $FB,
            'overview' => 'Financial assistance for students with exceptional skills in music, dance, arts, and sports.',
            'benefits' => ['Set by the DNSC Scholarship and Grants Unit'],
            'documents' => ['Latest Certificate of Registration (COR)', 'Latest grades / academic record', 'Student identification / school ID', 'Proof of talent, awards, or achievements', 'Other documents required by the DNSC Scholarship and Grants Unit'],
            'application_route' => 'Through the DNSC Scholarship and Grants Unit.',
            'window_note' => null,
            'source_note' => 'Requirements from the BAKR scholarships page.',
            'rules' => [
                $dnsc,
                ['key' => 'enrolled', 'label' => 'Currently enrolled or enrolling', 'type' => 'in', 'fact' => 'enrollment_status', 'value' => ['currently_enrolled', 'incoming_first_year'], 'required' => true, 'weight' => 1, 'src' => 'page'],
                ['key' => 'talent', 'label' => 'Has a skill in music, dance, arts, or sports', 'type' => 'contains_any', 'fact' => 'talent_categories', 'value' => ['music', 'dance', 'arts', 'sports'], 'required' => true, 'weight' => 4, 'src' => 'page'],
                ['key' => 'awards', 'label' => 'Has awards or achievements to show', 'type' => 'eq', 'fact' => 'has_awards', 'value' => 'yes', 'required' => false, 'weight' => 2, 'src' => 'page'],
            ],
        ],
        [
            'slug' => 'dnsc-gia-b', 'name' => 'DNSC Grant-in-Aid (GIA) – Category B', 'sector' => 'internal',
            'provider' => 'DNSC Scholarship and Grants Unit', 'official_url' => $FB,
            'overview' => 'Financial assistance intended for qualified Student Supreme Council officers and Student Publication Editorial Board members.',
            'benefits' => ['Set by the DNSC Scholarship and Grants Unit'],
            'documents' => ['Latest Certificate of Registration (COR)', 'Latest grades / academic record', 'Student identification / school ID', 'Proof of student leadership position or appointment', 'Proof of Student Publication membership, if applicable', 'Other documents required by the DNSC Scholarship and Grants Unit'],
            'application_route' => 'Through the DNSC Scholarship and Grants Unit.',
            'window_note' => null,
            'source_note' => 'Requirements from the BAKR scholarships page.',
            'rules' => [
                $dnsc,
                ['key' => 'enrolled', 'label' => 'Currently enrolled', 'type' => 'eq', 'fact' => 'enrollment_status', 'value' => 'currently_enrolled', 'required' => true, 'weight' => 1, 'src' => 'page'],
                ['key' => 'leader', 'label' => 'Student Supreme Council officer or Student Publication Editorial Board member', 'type' => 'contains_any', 'fact' => 'leadership_roles', 'value' => ['ssc_officer', 'publication_board'], 'required' => true, 'weight' => 5, 'src' => 'page'],
            ],
        ],
        [
            'slug' => 'dnsc-sa-c', 'name' => 'DNSC Student Assistant (SA) – Category C', 'sector' => 'internal',
            'provider' => 'DNSC Scholarship and Grants Unit', 'official_url' => $FB,
            'overview' => 'Assistance for students from financially disadvantaged families who are willing to perform approved institutional support services.',
            'benefits' => ['Set by the DNSC Scholarship and Grants Unit'],
            'documents' => ['Latest Certificate of Registration (COR)', 'Latest grades / academic record', 'Student identification / school ID', 'Proof of family income / financial need', 'Other documents required by the DNSC Scholarship and Grants Unit'],
            'application_route' => 'Through the DNSC Scholarship and Grants Unit.',
            'window_note' => null,
            'source_note' => 'Requirements from the BAKR scholarships page. No income limit is published, so the unit decides financial need.',
            'rules' => [
                $dnsc,
                ['key' => 'enrolled', 'label' => 'Currently enrolled or enrolling', 'type' => 'in', 'fact' => 'enrollment_status', 'value' => ['currently_enrolled', 'incoming_first_year'], 'required' => true, 'weight' => 1, 'src' => 'page'],
                ['key' => 'willing', 'label' => 'Willing to work as a student assistant', 'type' => 'eq', 'fact' => 'willing_student_assistant', 'value' => 'yes', 'required' => true, 'weight' => 4, 'src' => 'page'],
                ['key' => 'need', 'label' => 'Shows financial need (family income)', 'type' => 'confirm', 'fact' => 'income_max', 'required' => true, 'weight' => 3, 'src' => 'page', 'confirm_note' => 'The unit decides what counts as financial need. Bring proof of family income.'],
            ],
        ],

        // ------------------------------------------------------------ Government
        [
            'slug' => 'ched-tdp', 'name' => 'CHED Tulong Dunong Program (TDP)', 'sector' => 'government',
            // Source: Revised TDP Guidelines of 2026 (UniFAST Board Resolution No. 2026-012, approved 2026-04-16,
            // signed 2026-06-03, effective 1st Semester AY 2026-2027), Sections 2-7. Provided by the owner on 2026-11-15.
            // The guidelines publish no income cap: need is shown with a Certificate of Indigency (Section 7-A).
            'provider' => 'CHED / UniFAST', 'official_url' => 'https://unifast.gov.ph/',
            'overview' => 'A CHED-UniFAST grant for Filipino students taking their first undergraduate degree in a state, local or private college that is in the CHED Registry and has an agreement (MOA) with CHED-UniFAST. Qualifying does not guarantee a grant: CHED cross-checks all applicants and releases the final list of qualified grantees.',
            'benefits' => ['₱7,500 per semester (₱15,000 per academic year), for new and continuing grantees', 'The amount may be increased if the UniFAST Board approves', 'Continuing grantees do not re-apply; they keep the grant up to the Maximum Residency Rule', 'Paid by direct credit to your account, by check, or through DNSC, which must release it within 15 working days of receiving the funds'],
            // Stipend per release, from the benefits above (owner: amounts change only with the official source). 'per' = semester | month.
            'grants' => [['key' => 'regular', 'label' => 'TDP grant', 'amount' => 7500, 'per' => 'semester']],
            'documents' => ['Scanned TDP Application Form', 'Certificate of Registration / Enrollment (COR / COE), PDF copy', 'Certificate of Indigency issued by the Punong Barangay where you live (if it cannot be secured: Certificate of Tax Exemption, or a Social Case Study from a registered social worker)'],
            // Sections 6.2-6.4 and 7-B: continuing grantees are validated every semester.
            'renewal_requirements' => ['Certificate of Registration / Enrollment (COR / COE), PDF copy, for the current semester (or your name on DNSC\'s certified Enrollment List)', 'If returning after a one-term Leave of Absence: the LOA you submitted to DNSC (PDF copy)', 'If you transferred from another school: COR / COE of the previous and the current semester (PDF copies)', 'If you submit directly to CHEDRO: a school ID or government-issued ID with your specimen signature (PDF copy)'],
            'application_route' => 'After DNSC\'s enrollment period, the UniFAST Secretariat issues a call. Submit the application and documents through the partner implementer (DNSC) or directly to the CHED Regional Office (CHEDRO). CHEDRO uploads applications to the RC Portal, cross-checks for duplicate grants, and releases the list of qualified applicants to DNSC.',
            'window_note' => 'The call opens after DNSC\'s enrollment period each semester. These guidelines apply from the 1st Semester of AY 2026-2027.',
            'source_note' => 'From the Revised TDP Guidelines of 2026 (UniFAST Board Resolution No. 2026-012, effective 1st Semester AY 2026-2027). These guidelines set no income limit; financial need is shown with a Certificate of Indigency.',
            'verified_on' => '2026-11-15',
            'rules' => [
                ['key' => 'citizen', 'label' => 'Filipino citizen', 'type' => 'eq', 'fact' => 'citizenship', 'value' => 'filipino', 'required' => true, 'weight' => 2, 'src' => 'web'],
                ['key' => 'first_degree', 'label' => 'Taking a first undergraduate degree (no previous bachelor\'s degree)', 'type' => 'eq', 'fact' => 'previous_degree', 'value' => 'no', 'required' => true, 'weight' => 3, 'src' => 'web'],
                ['key' => 'enrolled', 'label' => 'Enrolled at DNSC (the call opens after the enrollment period)', 'type' => 'in', 'fact' => 'enrollment_status', 'value' => ['currently_enrolled', 'incoming_first_year'], 'required' => true, 'weight' => 1, 'src' => 'web'],
                ['key' => 'mrr', 'label' => 'Within the Maximum Residency Rule (the normal length of the program plus one year)', 'type' => 'eq', 'fact' => 'within_years', 'value' => 'yes', 'required' => true, 'weight' => 2, 'src' => 'web', 'skip_if' => ['enrollment_status' => 'incoming_first_year']],
                ['key' => 'no_other', 'label' => 'Not receiving another national government-funded financial assistance (Free Higher Education and one-time grants such as AICS or SMART do not count, but TDP and SMART cannot be received in the same semester)', 'type' => 'contains_none', 'fact' => 'existing_assistance', 'value' => ['tes_tdp_ched', 'other_government'], 'required' => true, 'weight' => 3, 'src' => 'web'],
                ['key' => 'indigency', 'label' => 'Can get a Certificate of Indigency from your barangay', 'type' => 'confirm', 'fact' => 'income_max', 'required' => true, 'weight' => 4, 'src' => 'web', 'confirm_note' => 'Ask the Punong Barangay where you live. If it cannot be secured, a Certificate of Tax Exemption or a Social Case Study from a registered social worker is accepted.'],
                ['key' => 'retention', 'label' => 'Meets DNSC admission and retention policies', 'type' => 'confirm', 'fact' => 'dnsc_student', 'required' => false, 'weight' => 1, 'src' => 'web', 'confirm_note' => 'DNSC and the CHED Regional Office check this when they validate your enrollment.'],
                ['key' => 'slots', 'label' => 'A TDP call is open at DNSC this semester', 'type' => 'confirm', 'fact' => 'dnsc_student', 'required' => false, 'weight' => 1, 'src' => 'web', 'confirm_note' => 'The UniFAST Secretariat issues the call after the enrollment period. Ask the DNSC Scholarship and Grants Unit.'],
            ],
        ],
        [
            'slug' => 'ched-tes', 'name' => 'CHED Tertiary Education Subsidy (TES)', 'sector' => 'government',
            // Source: Revised TES Guidelines of 2026 (UniFAST Board, effective 1st Semester AY 2026-2027), Sections 1-11,
            // and unifast.gov.ph/tes.html (Eligibility, Benefits, Documentary Requirements). Provided by the owner on 2026-11-15.
            // Section 3 (read in full): new slots go to (1) Listahanan households (4Ps if Listahanan is discontinued), then
            // (2) "all other enrolled students ... as follows": 2.1 dependents of solo parents (active in the DSWD registry) and
            // 2.2 Indigenous Peoples (NCIP list). Students in none of these groups are not covered, so the group rule is required.
            'provider' => 'CHED / UniFAST', 'official_url' => 'https://unifast.gov.ph/tes.html',
            'overview' => 'A CHED-UniFAST subsidy for Filipino students in state, local and private colleges in the CHED Registry with a UniFAST agreement. New slots go first to students from Listahanan (or 4Ps) households, then to dependents of solo parents and Indigenous Peoples, each ranked by household per capita income and subject to available funds.',
            'benefits' => ['Listahanan / 4Ps households at state colleges like DNSC: ₱10,000 per semester (₱20,000 per academic year)', 'Dependents of solo parents and Indigenous Peoples (second priority): ₱5,000 per semester (₱10,000 per academic year)', 'Persons with disability (TES-3A): an additional ₱5,000 per semester (₱10,000 per academic year)', 'Graduates of programs with a licensure exam (TES-3B): one-time reimbursement of up to ₱8,000', 'Students at risk of dropping out (SARDO): a one-time ₱10,000, subject to DNSC\'s evaluation', 'Given for the standard length of your program plus one year, if you stay eligible; the amount is not reduced while you remain eligible, subject to available funds', 'Paid by direct credit to your account, by check, or through DNSC, which must release it within 15 working days of receiving the funds'],
            // Two TES categories with different amounts (benefits above). The PWD add-on and one-time grants are not per-release stipends.
            'grants' => [['key' => 'listahanan', 'label' => 'Listahanan / 4Ps household', 'amount' => 10000, 'per' => 'semester'], ['key' => 'second_priority', 'label' => 'Solo-parent or IP dependent', 'amount' => 5000, 'per' => 'semester']],
            'documents' => ['Certificate of Registration / Enrollment (COR / COE)', 'School ID or any government-issued ID with your specimen signature (if you submit directly to CHEDRO)', 'PWD ID issued by the PDAO of your city or municipality, if applicable'],
            'renewal_requirements' => ['Certificate of Registration / Enrollment (COR / COE), PDF copy, for the current semester (or your name on DNSC\'s certified Enrollment List)', 'If returning after a one-term Leave of Absence: the LOA you submitted to DNSC (PDF copy)', 'If you transferred from another school: COR / COE of the previous and the current semester (PDF copies)', 'If you submit directly to CHEDRO: a school ID or government-issued ID with your specimen signature (PDF copy)'],
            'application_route' => 'You do not apply on your own: after the UniFAST call, DNSC submits its list of enrolled student applicants to the UniFAST Portal. CHEDRO and the UniFAST Regional Coordinators validate the documents and issue the Master List of grantees. If DNSC cannot submit your documents, you may submit them directly to CHEDRO XI.',
            'window_note' => 'Applications follow the UniFAST call each academic year. These guidelines apply from the 1st Semester of AY 2026-2027.',
            'source_note' => 'From the Revised TES Guidelines of 2026 and unifast.gov.ph. New slots are only for Listahanan / 4Ps households, dependents of solo parents and Indigenous Peoples, in that order, while funds last.',
            'verified_on' => '2026-11-15',
            'rules' => [
                ['key' => 'citizen', 'label' => 'Filipino citizen', 'type' => 'eq', 'fact' => 'citizenship', 'value' => 'filipino', 'required' => true, 'weight' => 2, 'src' => 'web'],
                ['key' => 'enrolled', 'label' => 'Enrolled at DNSC (non-students cannot apply)', 'type' => 'in', 'fact' => 'enrollment_status', 'value' => ['currently_enrolled', 'incoming_first_year'], 'required' => true, 'weight' => 1, 'src' => 'web'],
                ['key' => 'no_degree', 'label' => 'No previous undergraduate degree (not in a second undergraduate program)', 'type' => 'eq', 'fact' => 'previous_degree', 'value' => 'no', 'required' => true, 'weight' => 3, 'src' => 'web'],
                ['key' => 'duration', 'label' => 'Not overstaying in the program (within the normal years plus a 1-year grace)', 'type' => 'eq', 'fact' => 'within_years', 'value' => 'yes', 'required' => true, 'weight' => 2, 'src' => 'web', 'skip_if' => ['enrollment_status' => 'incoming_first_year']],
                ['key' => 'no_other', 'label' => 'Not already a TDP, CHED scholarship or other national government aid beneficiary (one-time grants such as AICS or SMART do not count)', 'type' => 'contains_none', 'fact' => 'existing_assistance', 'value' => ['tes_tdp_ched', 'other_government'], 'required' => true, 'weight' => 3, 'src' => 'web'],
                ['key' => 'priority', 'label' => 'In a TES group: Listahanan or 4Ps household (first priority), dependent of a solo parent in the DSWD registry, or Indigenous Peoples household listed by NCIP', 'type' => 'contains_any', 'fact' => 'special_groups', 'value' => ['listahanan', 'four_ps', 'solo_parent', 'indigenous_people'], 'required' => true, 'weight' => 5, 'src' => 'web'],
                ['key' => 'admission', 'label' => 'Meets DNSC admission and retention policies', 'type' => 'confirm', 'fact' => 'dnsc_student', 'required' => false, 'weight' => 1, 'src' => 'web', 'confirm_note' => 'DNSC checks this when it validates your enrollment.'],
            ],
        ],
        [
            'slug' => 'dost-sei', 'name' => 'DOST-SEI Undergraduate Scholarship', 'sector' => 'government',
            // Source: DOST-SEI Undergraduate Scholarship Program brochure (Merit and RA 7687 tracks: criteria, privileges,
            // documentary requirements, study placement) and the DOST-SEI list of priority S&T programs. Provided by the
            // owner on 2026-11-15. Each DNSC program was compared by name with the official list (rule 'course').
            // No GWA cut-off is published (selection is by the DOST-SEI Scholarship Examination). RA 7687's income
            // "set values" and 4-year residency apply to that track only and have no published number, so they are confirms.
            'provider' => 'DOST – Science Education Institute', 'official_url' => 'https://www.science-scholarships.ph/',
            'overview' => 'A DOST-SEI scholarship for talented Filipino students entering priority Science and Technology programs. Two tracks: the Merit Scholarship (formerly NSDB / NSTA) for students with high aptitude in science and mathematics, and the RA 7687 Scholarship for students whose families\' socio-economic status does not exceed DOST\'s set values. Scholars may study at state colleges like DNSC.',
            'benefits' => ['Tuition and other school fees: actual fees up to ₱40,000 a year (for scholars in private schools)', 'Learning materials and / or connectivity allowance: ₱10,000 a year', 'Monthly living allowance: ₱8,000 a month', 'Clothing allowance: ₱1,000 (first semester of first year only)', 'Transportation: one economy-class round trip a year, if you study outside your home province', 'Group accident and health insurance', 'Thesis allowance: ₱10,000', 'Research dissemination grant: up to ₱50,000 (applied for by the scholar, subject to approval)', 'Graduation allowance: ₱1,000', 'Mid-year term, if your curriculum requires it: fees up to ₱10,000, ₱2,000 learning materials, and ₱8,000 a month'],
            // Monthly living allowance (benefits above). Other allowances are paid separately, not per release.
            'grants' => [['key' => 'regular', 'label' => 'DOST-SEI scholar', 'amount' => 8000, 'per' => 'month']],
            'documents' => ['Form C – Certificate of Good Moral Character', 'Form D – Certificate of Good Health', 'Form E – Principal\'s Certification: E1 Grade 12 STEM strand; E2 Grade 12 non-STEM strand in the upper 5% of the class; E3 homeschool; E4 Alternative Learning System', 'Form F – Certificate of Residency', 'Form G – Parent\'s Certification that you have no pending immigration application to another country', 'Form H – DOST-SEI Scholarship Examination / Award', 'Form I – Applicant\'s Certification of No Post-Secondary Units', 'Form J – Signed Declaration of Applicant and Parent / Legal Guardian', 'Recent passport-size picture (4.5 cm × 3.5 cm)', 'Birth certificate', 'One proof of income of your parents, guardian or spouse: 2024 Income Tax Return; BIR Form 1701; OFW employment contract; BIR Certificate of Exemption from Filing of ITR; Certificate of Indigency; Certificate of Employment with Compensation; or proof of pension', 'Notarized Affidavit of Guardianship, if applicable', 'Marriage certificate, if married'],
            'application_route' => 'Apply online through the DOST-SEI scholarship site (science-scholarships.ph), where the forms are posted, then take the DOST-SEI Scholarship Examination. Qualifiers enroll in a priority S&T program at the University of the Philippines, a state college such as DNSC, or a private school that is a CHED Center of Excellence / Development or has FAAP Level III accreditation.',
            'window_note' => 'For Grade 12 students graduating in SY 2025–2026, and earlier Grade 12 graduates not yet enrolled in college. Watch science-scholarships.ph for the call and exam schedule.',
            'source_note' => 'From the DOST-SEI Undergraduate Scholarship brochure and the DOST-SEI priority S&T programs list. Students who applied before and did not qualify, or qualified but did not avail, may apply again.',
            'verified_on' => '2026-11-15',
            'rules' => [
                ['key' => 'citizen', 'label' => 'Natural-born Filipino citizen', 'type' => 'eq', 'fact' => 'citizenship', 'value' => 'filipino', 'required' => true, 'weight' => 2, 'src' => 'web'],
                ['key' => 'incoming', 'label' => 'Graduating from Grade 12, or a Grade 12 graduate not yet enrolled in college', 'type' => 'eq', 'fact' => 'enrollment_status', 'value' => 'incoming_first_year', 'required' => true, 'weight' => 3, 'src' => 'web'],
                // Brochure 3.1 / 3.2: STEM = all students; non-STEM = top 5% of the class (a percentage, not a rank), which only the principal can certify (Form E2; E3 homeschool; E4 ALS). So a STEM graduate passes and everyone else is a "confirm".
                ['key' => 'strand', 'label' => 'STEM strand graduate, or in the top 5% of a non-STEM graduating class', 'type' => 'maybe_in', 'fact' => 'shs_strand', 'pass' => ['stem'], 'maybe' => ['abm', 'humss', 'gas', 'tvl', 'arts_design', 'sports', 'als', 'other'], 'required' => true, 'weight' => 3, 'src' => 'web'],
                ['key' => 'course', 'label' => 'Enrolling in a DOST-SEI priority S&T program', 'type' => 'program_in', 'fact' => 'program_name', 'required' => true, 'weight' => 5, 'src' => 'web',
                    // DNSC programs as named in the database. Listed as: BS Agroforestry, BS Fisheries and Aquatic Sciences,
                    // BS Food Technology, BS Information System, BS Information Technology, BS Marine Biology, BSE Mathematics, BSE Science.
                    'pass' => ['Bachelor of Science in Agro-Forestry', 'Bachelor of Science in Fisheries and Aquatic Sciences', 'Bachelor of Science in Food Technology', 'Bachelor of Science in Information Systems', 'Bachelor of Science in Information Technology', 'Bachelor of Science in Marine Biology', 'Bachelor of Secondary Education - Mathematics', 'Bachelor of Secondary Education - Science'],
                    // Listed only "with specialization in Information and Communications Technology".
                    'maybe' => ['Bachelor of Technology and Livelihood Education'],
                    'not' => ['Bachelor of Arts in Communication', 'Bachelor of Physical Education', 'Bachelor of Public Administration', 'Bachelor of Science in Criminology', 'Bachelor of Science in Disaster Resilience and Management', 'Bachelor of Science in Entrepreneurship', 'Bachelor of Science in Social Work', 'Bachelor of Science in Tourism Management', 'Bachelor of Secondary Education - English'],
                ],
                ['key' => 'no_units', 'label' => 'Has not earned any post-secondary (college or vocational) units', 'type' => 'eq', 'fact' => 'previous_units', 'value' => 'no', 'required' => true, 'weight' => 2, 'src' => 'web'],
                ['key' => 'exam', 'label' => 'DOST-SEI Scholarship Examination taken or passed (selection is by this exam)', 'type' => 'in', 'fact' => 'dost_exam', 'value' => ['taken_waiting', 'passed'], 'required' => false, 'weight' => 3, 'src' => 'web'],
                ['key' => 'ra7687', 'label' => 'RA 7687 track only: family income within DOST\'s set values, and living in your municipality for at least 4 years', 'type' => 'confirm', 'fact' => 'income_max', 'required' => false, 'weight' => 1, 'src' => 'web', 'confirm_note' => 'The Merit track has no income or residency requirement. For RA 7687, DOST-SEI checks your proof of income and your barangay\'s certificate of residency.'],
            ],
        ],
        [
            'slug' => 'ched-cmsp', 'name' => 'CHED Merit Scholarship Program (CMSP)', 'sector' => 'government',
            // Source: CHED Memorandum Order No. 13, s. 2025 "Revised Policies and Guidelines for the CMSP"
            // (CHED en banc Resolution No. 498-2025, 30 June 2025): Art. I, Sections 4-6, 13, 16-17 and 28.3.
            // Provided by the owner on 2026-11-15. Full vs half scholarship is decided by CHEDRO ranking (28.3.3):
            // the CMO pages seen give no GWA split for it, so BAKR does not guess one.
            'provider' => 'CHED', 'official_url' => 'https://ched.gov.ph/',
            'overview' => 'A competitive CHED scholarship for academically talented and deserving incoming or current first-year college students who graduated from a Philippine senior high school with a Grade 12 GWA of at least 93%. CHED Regional Offices evaluate and rank applicants; meeting the requirements does not guarantee a slot.',
            'benefits' => ['State colleges like DNSC: tuition and other school fees under Free Higher Education', 'Full scholarship (Full-SSP): ₱40,000 per semester (₱80,000 a year) = stipend ₱35,000 (₱7,000 a month × 5 months) + book / connectivity allowance ₱5,000', 'Half scholarship (Half-SSP): ₱20,000 per semester (₱40,000 a year) = stipend ₱17,500 (₱3,500 a month × 5 months) + book / connectivity allowance ₱2,500', 'Paid every semester until you finish the degree program, if you keep the scholarship', 'Paid by CHEDRO to your bank account or by check, or through DNSC if it has an agreement with CHEDRO. For the first payment: certified COR / COE, college ID or government ID with your signature, and a copy of an active Landbank ATM card'],
            // Full-SSP vs Half-SSP per semester (benefits above). CHEDRO decides which one a scholar gets.
            'grants' => [['key' => 'full', 'label' => 'Full scholarship (Full-SSP)', 'amount' => 40000, 'per' => 'semester'], ['key' => 'half', 'label' => 'Half scholarship (Half-SSP)', 'amount' => 20000, 'per' => 'semester']],
            'documents' => ['Accomplished application form (Annex A)', 'Birth certificate issued by the NSO or PSA', 'Certified true copy of Form 138 (Learner\'s Progress Report Card / SF9), signed by the registrar of your senior high school', 'Any one: latest ITR of parents or guardian; BIR Certificate of Tax Exemption / Non-Filer; certified latest contract or proof of income (children of OFWs and seafarers); or Social Case Study Report from the C/MSWDO', 'If applicable: PWD ID or PDAO certification; Solo Parent ID; Senior Citizen ID; DHSUD or C/MSWDO certification (underprivileged and homeless family); C/MSWDO Social Case Study (Magna Carta for the Poor / first-generation students); NCIP certification (Indigenous Peoples)', 'Notarized Certificate of Guardianship, if applicable'],
            'renewal_requirements' => ['Certified true copy of the Certificate of Registration / Enrollment (COR / COE)', 'Certified true copy of grades showing the GWA of the previous semester', 'HEI certification that you do not receive another national government-funded scholarship (every start of the academic year)'],
            'application_route' => 'Apply to the CHED Regional Office (CHEDRO XI for Davao) with the Annex A form and documents. CHEDRO evaluates and ranks applicants, tells you the result by SMS or e-mail, and issues a Notice of Award (NOA) to qualified applicants or through DNSC.',
            'window_note' => 'CHED announces one call per academic year for incoming and current first-year students. Watch CHED and CHEDRO XI announcements.',
            'source_note' => 'From CHED Memorandum Order No. 13, s. 2025 (Revised Policies and Guidelines for the CMSP). Priority programs: BAKR uses CHED\'s national priority list (CMO No. 7, s. 2023) and marks only the DNSC programs named in it; for any other program CHEDRO XI decides, and CHED may also set newer or regional priorities. To keep the scholarship: GWA of at least 85% (full) or 80% (half), a regular load, a CHED priority program, finishing on time, and good moral character. You need CHEDRO\x27s written approval before transferring, shifting, or taking a leave of absence (allowed once, up to one academic year, with no benefits while on leave).',
            'verified_on' => '2026-11-15',
            'rules' => [
                ['key' => 'citizen', 'label' => 'Filipino citizen', 'type' => 'eq', 'fact' => 'citizenship', 'value' => 'filipino', 'required' => true, 'weight' => 2, 'src' => 'web'],
                ['key' => 'first_year', 'label' => 'Incoming or current first-year college student', 'type' => 'eq', 'fact' => 'year_level', 'value' => 1, 'required' => true, 'weight' => 3, 'src' => 'web'],
                ['key' => 'gwa', 'label' => 'Grade 12 GWA of at least 93%', 'type' => 'gte', 'fact' => 'shs_gwa_percent', 'value' => 93, 'required' => true, 'weight' => 5, 'src' => 'web'],
                ['key' => 'income', 'label' => 'Combined parent / guardian gross income of ₱500,000 or less', 'type' => 'income_lte', 'fact' => 'income', 'value' => 500000, 'required' => true, 'weight' => 4, 'src' => 'web'],
                ['key' => 'no_other', 'label' => 'Not already getting another national government-funded scholarship or grant, including TES or TDP (one-time grants do not count)', 'type' => 'contains_none', 'fact' => 'existing_assistance', 'value' => ['tes_tdp_ched', 'other_government'], 'required' => true, 'weight' => 3, 'src' => 'web'],
                ['key' => 'no_degree', 'label' => 'Has not finished an undergraduate degree (no second-course takers)', 'type' => 'eq', 'fact' => 'previous_degree', 'value' => 'no', 'required' => true, 'weight' => 3, 'src' => 'web'],
                ['key' => 'no_credited_units', 'label' => 'Not a transferee or shiftee with credited college units', 'type' => 'eq', 'fact' => 'previous_units', 'value' => 'no', 'required' => true, 'weight' => 3, 'src' => 'web'],
                ['key' => 'ph_shs', 'label' => 'Graduated from a senior high school in the Philippines', 'type' => 'eq', 'fact' => 'shs_country', 'value' => 'ph', 'required' => true, 'weight' => 1, 'src' => 'web'],
                ['key' => 'priority', 'label' => 'Enrolled in a CHED priority program with CHED recognition (non-priority programs cannot apply)', 'type' => 'program_in', 'fact' => 'program_name', 'required' => true, 'weight' => 3, 'src' => 'web', 'open_list' => true, 'confirm_note' => 'Your program is not named in CHED\'s national priority list that BAKR uses (CMO No. 7, s. 2023). CHED may have newer or regional priorities and CHEDRO XI decides, so ask CHEDRO XI or the DNSC Scholarship and Grants Unit.',
                    // CMO 13 s.2025 Sec. 5.4 / 17.2: priority program. National list = CMO No. 7 s.2023 (see $chedPriority). CHEDRO XI decides regional and GAD priorities and checks the Certificate of Program Compliance.
                    'pass' => $chedPriority],
            ],
        ],
        [
            'slug' => 'bpmsp', 'name' => 'Bagong Pilipinas Merit Scholarship Program (BPMSP)', 'sector' => 'government',
            // Source: Joint Memorandum Circular "Policies and Guidelines for the Bagong Pilipinas Merit Scholarship
            // Program (BPMSP)" (CHED / TESDA / DepEd, 39 pages), Parts I, II and V: Sections 1-10 and 18.
            // Provided by the owner on 2026-11-15; the JMC number and date were not in the screenshots.
            // Only the Higher Education Track is modelled (DNSC is a college); the TVET Diploma Track is TESDA's.
            // The priority-domain check stays a confirm: the domain alone is not enough, the DNSC program itself must
            // be on the CEB-approved list and be COE/COD, Level III-IV accredited or CEB-approved (Section 5.1).
            'provider' => 'CHED', 'official_url' => 'https://bpms.ched.gov.ph/',
            'overview' => 'A merit-based CHED scholarship for academically talented Filipino senior high school graduates whose parents or guardians earn ₱2,000,000 a year or less. Higher Education Track: incoming first-year students in the Top 5 of their class or with a GWA of at least 95%, enrolling in a CHED priority program. CHEDRO ranks applicants by GWA (70%) and family income (30%), plus 10 points for special equity groups.',
            'benefits' => ['State colleges like DNSC: tuition and other school fees under Free Higher Education', 'Stipend and allowance: ₱45,000 per academic year (₱22,500 per semester)', 'Academic Excellence Incentive: ₱18,000 per term for the top 15 scholars per region', 'Until you finish the degree program, as long as you pass all subjects every semester', 'Amounts may be adjusted depending on available funds and Commission En Banc approval'],
            // Stipend and allowance per semester (benefits above). The Academic Excellence Incentive is only for the top 15 per region.
            'grants' => [['key' => 'regular', 'label' => 'BPMSP scholar', 'amount' => 22500, 'per' => 'semester']],
            'documents' => ['Application form, filled in on the official BPMSP online portal (Annex A for applicants from GIDCAs)', 'PSA birth certificate', 'Certified true copy of Form 138 / SF9, signed by your senior high school registrar', 'Proof of income, any one: BIR Certificate of Tax Exemption / Non-Filer; DSWD or C/MSWDO certificate for 4Ps beneficiaries; latest ITR (BIR Form 1701) or BIR Form 2316 of both parents or guardians, or of the applicant; or certified latest contract or proof of income (children of OFWs and seafarers)', 'College admission slip or proof of admission', 'Parent / Legal Guardian Consent Form (Annex L), if applicable', 'Certified certificate from your senior high school that you were in the Top 5 of your graduating class, if applicable', 'Proof of a special equity group, if applicable: PWD ID or PDAO certification; Solo Parent ID (yours or your parent\'s); Senior Citizen ID; Social Case Study for first-generation students; or NCIP certification for Indigenous Peoples'],
            'renewal_requirements' => ['Certified true copy of the Certificate of Registration / Enrollment (COR / COE)', 'Certified true copy of grades showing the GWA of the previous semester', 'HEI certification that you do not receive another nationally funded scholarship (every start of the academic year)'],
            'application_route' => 'CHED and DepEd announce the call for applications. Apply on the official BPMSP online portal; it is coursed through the CHED Regional Office where your college is (CHEDRO XI for DNSC). CHEDRO shortlists and ranks applicants, checks that you are enrolled in a priority program, and issues a Notice of Award. Accept it within 15 days. If you qualify for two national government scholarships, you must choose one.',
            'window_note' => 'CHED and DepEd announce one call each academic year on the BPMSP portal. If you apply before you are admitted, you get one month from the start of the semester to submit proof of enrollment.',
            'source_note' => 'From the CHED-TESDA-DepEd Joint Memorandum Circular on the BPMSP (Higher Education Track). Scholars render one year of return service in the Philippines for every year of support, and lose the grant if they fail a subject, drop below a regular load, or shift outside their priority domain. A leave of absence is allowed once, for up to one academic year. Holders of TESDA NC I to IV and working people with no college units may also apply, at any age.',
            'verified_on' => '2026-11-15',
            'rules' => [
                ['key' => 'citizen', 'label' => 'Filipino citizen (foreign or dual citizens cannot apply)', 'type' => 'eq', 'fact' => 'citizenship', 'value' => 'filipino', 'required' => true, 'weight' => 2, 'src' => 'web'],
                // JMC Sec. 2.1 (HE track): "an incoming college student who graduated from Grade 12". A student already enrolled has college units (3.4) or is repeating first year (3.5).
                ['key' => 'first_year', 'label' => 'Incoming first-year college student (not repeating or re-entering first year)', 'type' => 'eq', 'fact' => 'enrollment_status', 'value' => 'incoming_first_year', 'required' => true, 'weight' => 3, 'src' => 'web'],
                ['key' => 'merit', 'label' => 'Top 5 of the graduating class, or a Grade 12 GWA of at least 95%', 'type' => 'any_of', 'required' => true, 'weight' => 5, 'src' => 'web', 'rules' => [
                    ['key' => 'top5', 'label' => 'Top 5 of the graduating class', 'type' => 'eq', 'fact' => 'top_five', 'value' => 'yes'],
                    ['key' => 'gwa95', 'label' => 'Grade 12 GWA of at least 95%', 'type' => 'gte', 'fact' => 'shs_gwa_percent', 'value' => 95],
                ]],
                ['key' => 'no_units', 'label' => 'No college units earned before (including transferees or shiftees with credited units)', 'type' => 'eq', 'fact' => 'previous_units', 'value' => 'no', 'required' => true, 'weight' => 2, 'src' => 'web'],
                ['key' => 'no_degree', 'label' => 'Has not completed an undergraduate degree', 'type' => 'eq', 'fact' => 'previous_degree', 'value' => 'no', 'required' => true, 'weight' => 2, 'src' => 'web'],
                ['key' => 'income', 'label' => 'Combined parent / guardian gross income of ₱2,000,000 or less', 'type' => 'income_lte', 'fact' => 'income', 'value' => 2000000, 'required' => true, 'weight' => 3, 'src' => 'web'],
                ['key' => 'no_other', 'label' => 'Not already getting a national government-funded scholarship or grant (private, LGU and one-time local grants do not count)', 'type' => 'contains_none', 'fact' => 'existing_assistance', 'value' => ['tes_tdp_ched', 'other_government'], 'required' => true, 'weight' => 3, 'src' => 'web'],
                ['key' => 'no_discipline', 'label' => 'No pending case or record of disciplinary action in school', 'type' => 'eq', 'fact' => 'disciplinary', 'value' => 'no', 'required' => true, 'weight' => 2, 'src' => 'web'],
                // Priority domains in the JMC: Agriculture and Fisheries; Creative Industries; Healthcare and Allied Programs; Engineering and Technology; Financial Services; Teacher Education; Digital Technology; Science and Mathematics. BAKR sorts the DNSC programs that are named in CHED's national priority list ($chedPriority); any other program stays a "confirm" for CHEDRO XI.
                ['key' => 'program', 'label' => 'Enrolling in a CHED priority program', 'type' => 'program_in', 'fact' => 'program_name', 'required' => true, 'weight' => 3, 'src' => 'web', 'open_list' => true, 'confirm_note' => 'Your program is not named in CHED\'s national priority list that BAKR uses (CMO No. 7, s. 2023). CHED may have newer or regional priorities and CHEDRO XI decides, so ask CHEDRO XI or the DNSC Scholarship and Grants Unit.', 'pass' => $chedPriority],
                ['key' => 'equity', 'label' => 'Special equity group (10 extra ranking points): Indigenous Peoples, PWD, solo parent or dependent, living in poverty, or first-generation student', 'type' => 'contains_any', 'fact' => 'special_groups', 'value' => ['indigenous_people', 'pwd', 'solo_parent', 'listahanan', 'four_ps', 'first_generation'], 'required' => false, 'bonus' => true, 'weight' => 2, 'src' => 'web'],
            ],
        ],

        // --------------------------------------------------------------- Private
        [
            'slug' => 'grabscholar', 'name' => 'GrabScholar College Scholarship', 'sector' => 'private',
            // Source: GrabScholar College Scholarship page "6 Merit-Based Scholarships for SY 2026-2030" (grab.com/ph):
            // scope, covered courses ("kasama pero hindi limitado" + "at marami pang iba"), who may apply, qualifications,
            // benefits, notification, documentary requirements by applicant type, and program timelines.
            // Provided by the owner on 2026-11-15. The course list is NOT exhaustive, so only clearly outside programs fail.
            'provider' => 'Grab Philippines', 'official_url' => 'https://www.grab.com/ph/community/grabscholar-college-scholarship/',
            'overview' => 'Six full-ride, merit-based scholarships for up to four years, for Filipino incoming first-year college students in academic years 2026–2030 who enroll in a 3- to 4-year Business, STEM (except medical courses) or Sustainability program at any university or college in the Philippines. Scholars are also linked to training, mentorship, internships and possible employment at Grab.',
            'benefits' => ['100% tuition and miscellaneous fees', 'School supplies allowance (uniform, textbooks, student ID): ₱11,000 a year', 'Monthly student allowance: ₱8,000 a month', 'Learning and development programs', 'Internship opportunities, mentorship and possible employment at Grab', 'For the whole 3- to 4-year program, as long as you meet the retention requirements'],
            // Monthly student allowance (benefits above). The yearly supplies allowance is paid separately.
            'grants' => [['key' => 'regular', 'label' => 'GrabScholar', 'amount' => 8000, 'per' => 'month']],
            'documents' => ['Birth certificate of the student (if you are the sibling of a Grab / Move It driver or merchant partner, also the partner\'s birth certificate; a spouse of a partner submits a marriage certificate instead)', 'Marriage certificate, if you are the spouse of a Grab / Move It driver or merchant partner (owner or staff)', 'Certificate of legal guardianship, if a legal guardian is applying for you', 'Grade 12 report card', 'Acceptance letter from your college or university, or any similar document', 'Certificate of Good Moral Standing from your senior high school'],
            'application_route' => 'Apply through the official GrabScholar application, yourself (adult applicant) or through a parent or legal guardian. Shortlisted applicants take assessment tests and interviews. Only selected applicants are notified by SMS and e-mail, and the list of scholars is posted on the GrabScholar website.',
            'window_note' => 'SY 2026 call: shortlist notified July 22; assessment tests and interviews July 25 – August 18, 2026.',
            'application_opens_on' => '2026-06-22', 'application_closes_on' => '2026-07-17', 'results_on' => '2026-08-20',
            'source_note' => 'From the GrabScholar College Scholarship page for SY 2026–2030. The course list there is "included but not limited to", so BAKR only rules out programs that are clearly not Business, STEM or Sustainability.',
            'verified_on' => '2026-11-15',
            'rules' => [
                ['key' => 'citizen', 'label' => 'Filipino citizen', 'type' => 'eq', 'fact' => 'citizenship', 'value' => 'filipino', 'required' => true, 'weight' => 2, 'src' => 'web'],
                ['key' => 'first_year', 'label' => 'Incoming first-year college student', 'type' => 'eq', 'fact' => 'year_level', 'value' => 1, 'required' => true, 'weight' => 3, 'src' => 'web'],
                ['key' => 'gwa', 'label' => 'Grade 12 grade of at least 90%', 'type' => 'gte', 'fact' => 'shs_gwa_percent', 'value' => 90, 'required' => true, 'weight' => 5, 'src' => 'web'],
                ['key' => 'none_other', 'label' => 'No other scholarship or educational assistance', 'type' => 'contains_none', 'fact' => 'existing_assistance', 'value' => ['tes_tdp_ched', 'other_government', 'private_foundation', 'dnsc_internal'], 'required' => true, 'weight' => 3, 'src' => 'web'],
                ['key' => 'course', 'label' => 'A 3- to 4-year Business, STEM (not medical) or Sustainability program', 'type' => 'program_in', 'fact' => 'program_name', 'required' => true, 'weight' => 4, 'src' => 'web',
                    // Named on Grab's list: Information Technology, Information Systems, Entrepreneurship, Agroforestry.
                    'pass' => ['Bachelor of Science in Information Technology', 'Bachelor of Science in Information Systems', 'Bachelor of Science in Entrepreneurship', 'Bachelor of Science in Agro-Forestry'],
                    // Not named, but may fall under STEM, Business or Sustainability ("at marami pang iba"): Grab decides.
                    'maybe' => ['Bachelor of Science in Fisheries and Aquatic Sciences', 'Bachelor of Science in Food Technology', 'Bachelor of Science in Marine Biology', 'Bachelor of Science in Disaster Resilience and Management', 'Bachelor of Science in Tourism Management', 'Bachelor of Public Administration', 'Bachelor of Science in Social Work', 'Bachelor of Secondary Education - Mathematics', 'Bachelor of Secondary Education - Science', 'Bachelor of Technology and Livelihood Education'],
                    // Clearly outside Business, STEM and Sustainability.
                    'not' => ['Bachelor of Science in Criminology', 'Bachelor of Physical Education', 'Bachelor of Secondary Education - English', 'Bachelor of Arts in Communication'],
                ],
                ['key' => 'conduct', 'label' => 'No disciplinary sanction in senior high school', 'type' => 'eq', 'fact' => 'disciplinary', 'value' => 'no', 'required' => true, 'weight' => 2, 'src' => 'web'],
            ],
        ],
        [
            'slug' => 'cebuana-clfi', 'name' => 'Cebuana Lhuillier Foundation National Scholarship Program', 'sector' => 'private',
            // Source: Cebuana Lhuillier Foundation (CLFI) scholarship FAQs Q1-Q12 (cebuanafoundation.org), provided by the
            // owner on 2026-11-15, plus the 2026-11-07 reading of the foundation's qualifications page.
            // The FAQ lists no income cap number ("economically challenged", "proof of total family income"); the
            // ₱350,000 cap is kept from the 2026-11-07 qualifications page, which the FAQ does not contradict.
            // The FAQ's qualifications (Q2) do not mention a Microsavings account, so that rule is no longer required.
            // Q9: the course list is non-negotiable, so programs not on it fail.
            'provider' => 'Cebuana Lhuillier Foundation', 'official_url' => 'https://cebuanafoundation.org/',
            'overview' => 'College scholarship grants from the Cebuana Lhuillier Foundation (CLFI) for economically challenged Filipino students, 1st to 4th year, in public state universities and colleges, in courses aligned with its Education-to-Employment (E2E) program. Open to all year levels; only one child per household at a time.',
            'benefits' => ['Fixed stipend of up to ₱50,000 for school-related and living expenses', 'Monthly learning sessions led by resource persons or subject matter experts, and various trainings', 'Annual general assembly, outreach programs, leadership boot camp and a Cebuana Lhuillier Museum tour', 'Recognition of exemplary academic and non-academic achievements', 'Membership in the Cebuana Lhuillier Scholar Alumni Association', 'Education-to-Employment (E2E): employment opportunities after graduation; scholars who graduate with Latin honors or a GWA of 90% are exempted from the PJLI pre-employment test'],
            'documents' => ['Cebuana Lhuillier 24K Loyalty Card', 'Proof of total family income', 'Grades showing a GWA of at least 85% with no failing mark: 2nd semester of SY 2025–2026 (freshmen: senior high school grades; if your report card is not yet released, a registrar\'s certification of your Grade 12 GWA)', 'Certificate of Good Moral Character signed by an official of your last school', 'Current enrollment or registration form (1st semester of SY 2026–2027)', 'Your university or college grading system in percentages'],
            'application_route' => 'Fill in the online application form on the Scholarship Form tab of the CLFI website. CLFI does a paper screening with departments of the PJ Group of Companies; successful applicants are interviewed together with their parents or guardians. Results are final and cannot be appealed, but you may apply again in later years.',
            'window_note' => 'Open once a year. For SY 2026–2027, official results are announced on or before the last week of October 2026.',
            'source_note' => 'From the Cebuana Lhuillier Foundation scholarship FAQs (Q1–Q12) and its qualifications page. Only the listed courses are accepted (no exceptions), and only one child per household at a time.',
            'verified_on' => '2026-11-15',
            'rules' => [
                ['key' => 'citizen', 'label' => 'Filipino citizen', 'type' => 'eq', 'fact' => 'citizenship', 'value' => 'filipino', 'required' => true, 'weight' => 2, 'src' => 'web'],
                ['key' => 'client', 'label' => 'Cebuana Lhuillier client with a 24K Loyalty Card', 'type' => 'maybe_in', 'fact' => 'cebuana_client', 'pass' => ['self'], 'maybe' => ['relative'], 'required' => true, 'weight' => 3, 'src' => 'web'],
                ['key' => 'savings', 'label' => 'Cebuana Lhuillier Microsavings account (on the foundation\'s earlier qualifications page; not in the current FAQ)', 'type' => 'maybe_in', 'fact' => 'cebuana_microsavings', 'pass' => ['yes'], 'maybe' => ['willing_to_open'], 'required' => false, 'weight' => 1, 'src' => 'web'],
                ['key' => 'enrolled', 'label' => 'Enrolled, or enrolling, in a public state university or college (DNSC is a state college); any year level', 'type' => 'in', 'fact' => 'enrollment_status', 'value' => ['currently_enrolled', 'incoming_first_year'], 'required' => true, 'weight' => 1, 'src' => 'web'],
                ['key' => 'course', 'label' => 'Supported course (Accountancy / Finance, Business, Hospitality, Electrical / Civil / Mechanical / Industrial Engineering, Architecture, Psychology, IT); no other courses are accepted', 'type' => 'program_in', 'fact' => 'program_name', 'required' => true, 'weight' => 4, 'src' => 'web',
                    'pass' => ['Bachelor of Science in Information Technology'],
                    // Related to a supported course but not named exactly: CLFI decides.
                    'maybe' => ['Bachelor of Science in Information Systems', 'Bachelor of Science in Entrepreneurship', 'Bachelor of Science in Tourism Management'],
                    'not' => ['Bachelor of Arts in Communication', 'Bachelor of Physical Education', 'Bachelor of Public Administration', 'Bachelor of Science in Agro-Forestry', 'Bachelor of Science in Criminology', 'Bachelor of Science in Disaster Resilience and Management', 'Bachelor of Science in Fisheries and Aquatic Sciences', 'Bachelor of Science in Food Technology', 'Bachelor of Science in Marine Biology', 'Bachelor of Science in Social Work', 'Bachelor of Secondary Education - English', 'Bachelor of Secondary Education - Mathematics', 'Bachelor of Secondary Education - Science', 'Bachelor of Technology and Livelihood Education'],
                ],
                ['key' => 'income', 'label' => 'Total annual family income of ₱350,000 or less', 'type' => 'income_lte', 'fact' => 'income', 'value' => 350000, 'required' => true, 'weight' => 4, 'src' => 'web'],
                ['key' => 'gwa', 'label' => 'GWA of at least 85% from the last semester or school (freshmen: senior high school grades)', 'type' => 'gte', 'fact' => 'last_gwa_percent', 'value' => 85, 'required' => true, 'weight' => 4, 'src' => 'web'],
                ['key' => 'no_fail', 'label' => 'No failing mark in any subject', 'type' => 'eq', 'fact' => 'failing_grade', 'value' => 'no', 'required' => true, 'weight' => 2, 'src' => 'web'],
            ],
        ],

        // ------------------------------------------- Added 2026-11-18 (owner's sources, docs/sources/)
        [
            'slug' => 'landbank-ilp', 'name' => 'LANDBANK Scholarship Program (ILP)', 'sector' => 'government',
            // Sources (docs/sources/landbank/ilp-2026/): the DNSC Scholarship Office announcement for the ILP (eligible
            // courses, eligibility criteria, special-application documents, October 30, 2026 deadline) and the ILP list of
            // documents (regular application). Provided by the owner on 2026-11-18. Neither gives the benefit amounts,
            // so no stipend is listed (owner: never invent an amount).
            'provider' => 'LANDBANK', 'official_url' => $FB,
            'overview' => 'A LANDBANK scholarship for children and grandchildren of agrarian reform beneficiaries and small farmers / fishers in Davao del Norte, in four agriculture- and technology-related DNSC programs. At DNSC it has a special application for qualified third- and fourth-year regular students; the regular application is for incoming first-year students.',
            'benefits' => ['The call does not state the benefit amounts. Ask the DNSC Scholarship Office or LANDBANK (iskolarnglandbank@landbankfoundation.com).'],
            'documents' => ['Duly filled-out and signed ILP Application Form', 'Special application (3rd / 4th year): certified true copy of grades from first year college to the latest available grades', 'Special application: letter of recommendation from the College Dean', 'Special application: Certificate of Good Moral Character from the College OSAS', 'Regular application (incoming first year): certified true copy of grades 11–12 with school seal, letter of recommendation from the school principal, and Certificate of Good Moral Character from the senior high school\'s Office of Student Affairs', 'Endorsement letter from a LANDBANK agriculture-related client cooperative, organization, partner SUC or other government agency', 'PSA / NSO original birth certificate', 'Proof of registration in the Registry System for Basic Sectors in Agriculture (RSBSA), or a certification from the Municipal Agriculture Office (MAO), the Municipal Agrarian Reform Office (MARO) or the DA / DAR office', 'Photocopy of the recent ITR of your parents, a BIR Certificate of Tax Exemption, or a Certificate of Indigency', 'Barangay Clearance from your home barangay', 'Medical Certificate', 'Certification of non-enjoyment of other government scholarships', 'Pre-recorded video (3 minutes at most) introducing yourself and answering: "Why am I pursuing my chosen course?" and "How do I see myself for nation building?" (e-mail it to iskolarnglandbank@landbankfoundation.com)'],
            'application_route' => 'Submit the signed ILP Application Form and the supporting documents to the DNSC Scholarship Office, and e-mail the video to iskolarnglandbank@landbankfoundation.com.',
            'window_note' => 'DNSC deadline for the current call: October 30, 2026.',
            'application_closes_on' => '2026-10-30',
            'source_note' => 'From the DNSC Scholarship Office announcement of the LANDBANK Scholarship Program and the ILP list of documents. The call does not state the benefit amounts, so none are shown.',
            'verified_on' => '2026-11-18',
            'rules' => [
                ['key' => 'course', 'label' => 'Enrolled in an eligible course: BS Fisheries and Aquatic Sciences, BS Food Technology, BS Agroforestry or BS Information Technology', 'type' => 'program_in', 'fact' => 'program_name', 'required' => true, 'weight' => 5, 'src' => 'web',
                    'pass' => ['Bachelor of Science in Fisheries and Aquatic Sciences', 'Bachelor of Science in Food Technology', 'Bachelor of Science in Agro-Forestry', 'Bachelor of Science in Information Technology'],
                    'maybe' => [],
                    'not' => ['Bachelor of Arts in Communication', 'Bachelor of Physical Education', 'Bachelor of Public Administration', 'Bachelor of Science in Criminology', 'Bachelor of Science in Disaster Resilience and Management', 'Bachelor of Science in Entrepreneurship', 'Bachelor of Science in Information Systems', 'Bachelor of Science in Marine Biology', 'Bachelor of Science in Social Work', 'Bachelor of Science in Tourism Management', 'Bachelor of Secondary Education - English', 'Bachelor of Secondary Education - Mathematics', 'Bachelor of Secondary Education - Science', 'Bachelor of Technology and Livelihood Education'],
                ],
                ['key' => 'year', 'label' => 'Incoming first-year student (regular application), or a third- or fourth-year regular student (special application)', 'type' => 'any_of', 'required' => true, 'weight' => 3, 'src' => 'web', 'rules' => [
                    ['key' => 'incoming', 'label' => 'Incoming first-year student', 'type' => 'eq', 'fact' => 'enrollment_status', 'value' => 'incoming_first_year'],
                    ['key' => 'upper', 'label' => 'Third- or fourth-year student', 'type' => 'in', 'fact' => 'year_level', 'value' => [3, 4]],
                ]],
                ['key' => 'family', 'label' => 'Child or grandchild of an Agrarian Reform Beneficiary (ARB) or a small farmer / fisher registered in the RSBSA', 'type' => 'contains_any', 'fact' => 'farm_fisher_family', 'value' => ['rsbsa'], 'required' => true, 'weight' => 5, 'src' => 'web'],
                ['key' => 'income', 'label' => 'Total annual family income of ₱300,000 or less', 'type' => 'income_lte', 'fact' => 'income', 'value' => 300000, 'required' => true, 'weight' => 4, 'src' => 'web'],
                ['key' => 'none_other', 'label' => 'Not receiving any other financial assistance, grant or scholarship', 'type' => 'contains_none', 'fact' => 'existing_assistance', 'value' => ['tes_tdp_ched', 'other_government', 'private_foundation', 'dnsc_internal'], 'required' => true, 'weight' => 3, 'src' => 'web'],
                ['key' => 'no_fail', 'label' => 'No conditional or failing grades', 'type' => 'eq', 'fact' => 'failing_grade', 'value' => 'no', 'required' => true, 'weight' => 2, 'src' => 'web'],
                ['key' => 'resident', 'label' => 'Resident of Davao del Norte', 'type' => 'eq', 'fact' => 'lives_in_davao_del_norte', 'value' => 'yes', 'required' => true, 'weight' => 2, 'src' => 'web'],   // proof: Barangay Clearance
                ['key' => 'standing', 'label' => 'Special application: a regular student in good academic standing', 'type' => 'confirm', 'fact' => 'dnsc_student', 'required' => false, 'weight' => 1, 'src' => 'web', 'confirm_note' => 'Your certified grades and the College Dean\'s recommendation show this.', 'skip_if' => ['enrollment_status' => 'incoming_first_year']],
            ],
        ],
        [
            'slug' => 'coscho', 'name' => 'Scholarship Program for Coconut Farmers and Their Families (CoScho)', 'sector' => 'government',
            // Sources (docs/sources/ched/coscho-2026-2027/): CHED's "Call for Applications: CoScho for AY 2026-2027"
            // (qualifications, documentary requirements, financial benefits, list of priority programs), provided by the
            // owner on 2026-11-18. That call was posted by CHED MIMAROPA; DNSC students apply in their own region.
            // Priority programs: 80% of slots; "20% of slots will be for other fields", so the program rule is not required.
            'provider' => 'CHED (CoScho)', 'official_url' => 'https://mimaropa.ched.gov.ph/call-for-applications-scholarship-program-for-coconut-farmers-and-their-families-coscho-for-academic-year-2026-2027/',
            'overview' => 'A CHED scholarship for registered coconut farmers and their dependents (children of coconut farmers in the National Coconut Farmers Registry System, NCFRS). Open to senior high school and ALS graduates and to college students with a GWA of at least 80%, whose parents earn ₱300,000 a year or less. Most slots go to priority programs; 20% are for other fields.',
            'benefits' => ['Stipend (food, educational tours, transportation, projects, medical insurance, internet, communication): ₱35,000 per semester (₱70,000 a year)', 'Book allowance and other learning materials: ₱5,000 per semester (₱10,000 a year)', 'Total regular allowances: ₱40,000 per semester (₱80,000 a year)', 'Tuition fee assistance only for scholars in private colleges in towns with no state or local college; DNSC is a state college, so it does not apply here', 'Thesis allowance ₱25,000, internship allowance ₱10,000 and laptop allowance ₱30,000', 'Summer allowances, only when the program requires a summer term: stipend ₱7,000 a month and book allowance ₱2,000', 'Monetary grant for graduates who top the licensure examination, subject to available funds', 'Review fee assistance for up to two licensure examination takes'],
            // Regular allowances per semester (benefits above). Thesis, internship, laptop and summer allowances are paid separately.
            'grants' => [['key' => 'regular', 'label' => 'CoScho scholar', 'amount' => 40000, 'per' => 'semester']],
            'documents' => ['Accomplished application form', '2x2 picture', 'PSA birth certificate', 'Academic records: senior high school graduates, certified true copy of Form 138 or SF9; ALS graduates, certified true copy of Grade 12 grades; college students, certified true copy of the grades of the latest term or semester attended', 'PCA certification (Philippine Coconut Authority)', 'Certificate of Good Moral Character (incoming first year or transferees)', 'Notice of Admission from the college (incoming first year or transferees)', 'Proof of income of both parents or the guardian, any one: latest ITR of the applicant, spouse, parents or guardians if employed; BIR Certificate of Tax Exemption; BIR Certificate of No Income; BIR Certificate of Low Income; or certification / case study from the C/MSWDO', 'For 2nd- to 4th-year college applicants: original or certified copy of a certification from the college that you are enrolled in a priority program and do not enjoy any national government-funded scholarship'],
            'application_route' => 'Apply through the online application link in your CHED Regional Office\'s CoScho call (CHEDRO XI for DNSC students) with the documents above.',
            'window_note' => 'The AY 2026–2027 call in the source was posted by CHED MIMAROPA with an August 31, 2026 deadline. Check CHEDRO XI for the Davao call and its dates.',
            'source_note' => 'From CHED\'s call for applications for CoScho, AY 2026–2027. 80% of slots go to the listed priority programs and 20% to other fields.',
            'verified_on' => '2026-11-18',
            'rules' => [
                ['key' => 'citizen', 'label' => 'Filipino citizen', 'type' => 'eq', 'fact' => 'citizenship', 'value' => 'filipino', 'required' => true, 'weight' => 2, 'src' => 'web'],
                ['key' => 'coconut', 'label' => 'A registered coconut farmer in the NCFRS, or his / her dependent', 'type' => 'contains_any', 'fact' => 'farm_fisher_family', 'value' => ['coconut'], 'required' => true, 'weight' => 5, 'src' => 'web'],
                ['key' => 'income', 'label' => 'Combined annual income of parents of ₱300,000 or less', 'type' => 'income_lte', 'fact' => 'income', 'value' => 300000, 'required' => true, 'weight' => 4, 'src' => 'web'],
                ['key' => 'status', 'label' => 'Senior high school graduate, ALS graduate, or college student', 'type' => 'in', 'fact' => 'enrollment_status', 'value' => ['incoming_first_year', 'currently_enrolled'], 'required' => true, 'weight' => 1, 'src' => 'web'],
                ['key' => 'gwa', 'label' => 'College students: GWA of at least 80%', 'type' => 'gte', 'fact' => 'college_gwa_percent', 'value' => 80, 'required' => true, 'weight' => 4, 'src' => 'web', 'skip_if' => ['enrollment_status' => 'incoming_first_year']],
                ['key' => 'no_other', 'label' => 'Not enjoying any national government-funded scholarship program', 'type' => 'contains_none', 'fact' => 'existing_assistance', 'value' => ['tes_tdp_ched', 'other_government'], 'required' => true, 'weight' => 3, 'src' => 'web'],
                ['key' => 'priority', 'label' => 'Enrolled in a CoScho priority program (80% of slots; 20% are for other fields)', 'type' => 'program_in', 'fact' => 'program_name', 'required' => false, 'weight' => 3, 'src' => 'web',
                    // Named on the list: BS Information Technology (27), BS Marine Biology (29), BS Social Work (33), BS Agroforestry (14).
                    'pass' => ['Bachelor of Science in Information Technology', 'Bachelor of Science in Marine Biology', 'Bachelor of Science in Social Work', 'Bachelor of Science in Agro-Forestry'],
                    // Close to a listed program but not the same name: BS Fisheries (23), BS Tourism (34). CHED decides.
                    'maybe' => ['Bachelor of Science in Fisheries and Aquatic Sciences', 'Bachelor of Science in Tourism Management'],
                    'not' => ['Bachelor of Arts in Communication', 'Bachelor of Physical Education', 'Bachelor of Public Administration', 'Bachelor of Science in Criminology', 'Bachelor of Science in Disaster Resilience and Management', 'Bachelor of Science in Entrepreneurship', 'Bachelor of Science in Food Technology', 'Bachelor of Science in Information Systems', 'Bachelor of Secondary Education - English', 'Bachelor of Secondary Education - Mathematics', 'Bachelor of Secondary Education - Science', 'Bachelor of Technology and Livelihood Education'],
                ],
            ],
        ],
        [
            'slug' => 'bfar-fsp', 'name' => 'BFAR Fisheries Scholarship Program (FSP)', 'sector' => 'government',
            // Sources (docs/sources/bfar/fsp/): bfar.da.gov.ph/opportunities/scholarship (program description, the three
            // components and their application forms) and the FCEG application form, page 2 (general criteria of
            // eligibility, checklist of documents, scholarship support / privileges). Provided by the owner on 2026-11-18.
            // Only the FCEG support amounts were provided, so the stipend is the FCEG one. The scholarship is for a
            // Bachelor of Science in Fisheries; DNSC's BS Fisheries and Aquatic Sciences is a "maybe" (BFAR decides).
            // "With Honors" uses DepEd's definition (GWA of 90 or higher, DepEd Order No. 36, s. 2016).
            'provider' => 'BFAR – Fisheries Scholarship Program', 'official_url' => 'https://www.bfar.da.gov.ph/opportunities/scholarship/',
            'overview' => 'A full college scholarship from the Bureau of Fisheries and Aquatic Resources (BFAR) for qualified high school graduates taking a Bachelor of Science in Fisheries. Three components: the Fisheries Industry Leader Grant (FILG) for senior high school graduates with honors; the Fisherfolk Children Educational Grant (FCEG) for children of registered municipal fisherfolk; and FSP-ICCs/IPs for poor but deserving members of Indigenous Cultural Communities / Indigenous Peoples recognized by the NCIP.',
            'benefits' => ['A full scholarship grant for college studies leading to the degree of Bachelor of Science in Fisheries', 'Free matriculation and other school fees, except the dormitory fee', 'Monthly stipend: ₱5,000', 'Semestral book allowance: ₱2,000', 'OJT support: ₱3,000', 'Research / thesis / special problem support: ₱7,000', 'Graduation support: ₱1,500', 'Amounts above are from the FCEG application form; check the FILG and ICC/IP forms for theirs'],
            // FCEG monthly stipend (benefits above). Book, OJT, thesis and graduation support are paid separately.
            'grants' => [['key' => 'regular', 'label' => 'FSP scholar (FCEG)', 'amount' => 5000, 'per' => 'month']],
            'documents' => ['Duly accomplished and signed FSP application form for your component (FILG, FCEG or ICCs/IPs, downloadable from the BFAR website)', 'Original or certified true copy of your birth certificate (PSA or Civil Registrar)', 'Certified true copy of Report Card Form 138 or 137 (GWA of the last 3 semesters of senior high school of at least 80%)', 'Certification of good moral character from the School Registrar', 'FCEG: photocopy of the Fish-R / Mangingisdang Juan ID or Certificate of Registration of your parent(s) or guardian(s), issued by the MAO or BFAR', 'Two recent 2x2 pictures taken within the last three months, colored, white background (phone photos may be allowed)', 'Barangay Residence Certificate of your parent(s) or guardian(s)', 'In case of guardianship: affidavit of the guardian that you have been his / her ward for at least five years'],
            'application_route' => 'Download the application form for your component from the BFAR scholarship page, submit it with the documents to the BFAR Regional Office (BFAR XI for Davao), then take the FSP examination (bring 2 pencils, a valid ID and your admission slip; the exam starts at 8:00 AM).',
            'window_note' => 'The BFAR page gives no application dates. Ask the BFAR Regional Office for the current examination schedule.',
            'source_note' => 'From the BFAR Fisheries Scholarship Program page and the FCEG application form. The scholarship is for a Bachelor of Science in Fisheries at a school where BFAR allots slots; ask BFAR XI whether DNSC\'s BS Fisheries and Aquatic Sciences qualifies.',
            'verified_on' => '2026-11-18',
            'rules' => [
                ['key' => 'citizen', 'label' => 'Natural-born Filipino citizen', 'type' => 'eq', 'fact' => 'citizenship', 'value' => 'filipino', 'required' => true, 'weight' => 2, 'src' => 'web'],
                ['key' => 'incoming', 'label' => 'Senior high school graduate or graduating student, not yet in college', 'type' => 'eq', 'fact' => 'enrollment_status', 'value' => 'incoming_first_year', 'required' => true, 'weight' => 3, 'src' => 'web'],
                ['key' => 'no_units', 'label' => 'Has not earned any units in college', 'type' => 'eq', 'fact' => 'previous_units', 'value' => 'no', 'required' => true, 'weight' => 2, 'src' => 'web'],
                ['key' => 'gwa', 'label' => 'Senior high school GWA (last 3 semesters) of at least 80%', 'type' => 'gte', 'fact' => 'shs_gwa_percent', 'value' => 80, 'required' => true, 'weight' => 4, 'src' => 'web'],
                ['key' => 'component', 'label' => 'Fits a component: child of a registered municipal fisherfolk (FCEG), member of an Indigenous Cultural Community / Indigenous Peoples group (ICCs/IPs), or graduating with honors (FILG)', 'type' => 'any_of', 'required' => true, 'weight' => 5, 'src' => 'web', 'rules' => [
                    ['key' => 'fishr', 'label' => 'Parent or guardian is a registered municipal fisherfolk (FishR)', 'type' => 'contains_any', 'fact' => 'farm_fisher_family', 'value' => ['fishr']],
                    ['key' => 'ip', 'label' => 'Member of an Indigenous Peoples group', 'type' => 'contains_any', 'fact' => 'special_groups', 'value' => ['indigenous_people']],
                    ['key' => 'honors', 'label' => 'Graduating with honors (DepEd: GWA of 90 or higher)', 'type' => 'gte', 'fact' => 'shs_gwa_percent', 'value' => 90],
                ]],
                ['key' => 'course', 'label' => 'Taking a Bachelor of Science in Fisheries', 'type' => 'program_in', 'fact' => 'program_name', 'required' => true, 'weight' => 5, 'src' => 'web',
                    // DNSC has no program named exactly "Bachelor of Science in Fisheries".
                    'pass' => [],
                    'maybe' => ['Bachelor of Science in Fisheries and Aquatic Sciences'],
                    'not' => ['Bachelor of Arts in Communication', 'Bachelor of Physical Education', 'Bachelor of Public Administration', 'Bachelor of Science in Agro-Forestry', 'Bachelor of Science in Criminology', 'Bachelor of Science in Disaster Resilience and Management', 'Bachelor of Science in Entrepreneurship', 'Bachelor of Science in Food Technology', 'Bachelor of Science in Information Systems', 'Bachelor of Science in Information Technology', 'Bachelor of Science in Marine Biology', 'Bachelor of Science in Social Work', 'Bachelor of Science in Tourism Management', 'Bachelor of Secondary Education - English', 'Bachelor of Secondary Education - Mathematics', 'Bachelor of Secondary Education - Science', 'Bachelor of Technology and Livelihood Education'],
                ],
                ['key' => 'conduct', 'label' => 'Of good moral character, certified by the school head or principal', 'type' => 'eq', 'fact' => 'disciplinary', 'value' => 'no', 'required' => true, 'weight' => 2, 'src' => 'web'],
                ['key' => 'age', 'label' => 'Not more than 25 years old at the time of the examination', 'type' => 'lte', 'fact' => 'age', 'value' => 25, 'required' => true, 'weight' => 1, 'src' => 'web'],   // age from the birth date; proof: birth certificate
                ['key' => 'residence', 'label' => 'You or your parent / guardian have lived for at least 6 months in a municipality of the region where the slot is allotted', 'type' => 'confirm', 'fact' => 'dnsc_student', 'required' => true, 'weight' => 1, 'src' => 'web', 'confirm_note' => 'Shown by the Barangay Residence Certificate. Ask BFAR XI which municipalities have slots.'],
            ],
        ],
    ],
];
