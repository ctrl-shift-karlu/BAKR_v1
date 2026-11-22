<?php

namespace App\Matching;

/**
 * The single source of truth for the student's scholarship matching form: sections, fields,
 * options and validation. The form view, the validator, the profile page, the printable copy,
 * the rule engine (hard limits) and the Gemini AI matcher (App\Ai\MatchProfile) all read from here.
 *
 * Since 2026-11-19 (owner) this is a full matching form (personal, academic, family, other
 * scholarships, achievements, provider questions), cut down the same day to ONLY the questions that
 * a scholarship rule reads: no sex, civil status, contact number, street address, parents' jobs,
 * household size or essays (data minimisation, Data Privacy Act of 2012). Fields marked 'ai' => false
 * (the exact birth date) never leave BAKR; MatchProfile sends only the rest, with no name or e-mail.
 * Answers are stored encrypted. Before adding a question, name the rule that reads it.
 */
final class ApplicationSchema
{
    public const SHS_STRANDS = [
        'stem' => 'STEM', 'abm' => 'ABM', 'humss' => 'HUMSS', 'gas' => 'GAS', 'tvl' => 'TVL',
        'arts_design' => 'Arts and Design', 'sports' => 'Sports', 'als' => 'ALS / Philippine Educational Placement Test', 'other' => 'Other / old curriculum',
    ];

    public const YES_NO = ['yes' => 'Yes', 'no' => 'No'];

    /**
     * Combined yearly gross income of parents / guardian. Five simple brackets (owner, 2026-11-19: "simplify"),
     * and every edge sits exactly on an income limit that a scholarship publishes, so each income rule gets a clear
     * pass or fail and never an "overlaps the limit" confirm:
     *   ₱300,000 LANDBANK ILP, CoScho · ₱350,000 Cebuana (CLFI) · ₱500,000 CMSP · ₱2,000,000 BPMSP
     * Providers with no cap (CHED TDP, DNSC) or a per-capita ranking (TES) need no edge, and BPMSP's finer income
     * steps only change its ranking points, not who qualifies. When a scholarship rule gets a new cap, add an edge
     * here (IncomeBracketTest fails otherwise).
     */
    public const INCOME = [
        // Label "Below ₱300k" (owner, 2026-11-21); the key and the limits (0 – 300,000) are unchanged.
        'up_to_300k' => ['label' => 'Below ₱300k', 'min' => 0, 'max' => 300000],
        '300k_350k' => ['label' => '₱300,001 – ₱350,000', 'min' => 300001, 'max' => 350000],
        '350k_500k' => ['label' => '₱350,001 – ₱500,000', 'min' => 350001, 'max' => 500000],
        '500k_2m' => ['label' => '₱500,001 – ₱2,000,000', 'min' => 500001, 'max' => 2000000],
        'over_2m' => ['label' => 'Over ₱2,000,000', 'min' => 2000001, 'max' => PHP_INT_MAX],
    ];

    /**
     * Brackets used by earlier versions of the form. They are no longer offered, but a saved form that holds one
     * still reads correctly (with its old range, so a cut-off inside it asks for proof of income). The student
     * picks a current bracket the next time they save the form.
     */
    public const LEGACY_INCOME = [
        'up_to_250k' => ['label' => 'Up to ₱250,000', 'min' => 0, 'max' => 250000],
        '250k_300k' => ['label' => '₱250,001 – ₱300,000', 'min' => 250001, 'max' => 300000],
        '250k_350k' => ['label' => '₱250,001 – ₱350,000', 'min' => 250001, 'max' => 350000],
        '350k_400k' => ['label' => '₱350,001 – ₱400,000', 'min' => 350001, 'max' => 400000],
        '400k_500k' => ['label' => '₱400,001 – ₱500,000', 'min' => 400001, 'max' => 500000],
        '500k_750k' => ['label' => '₱500,001 – ₱750,000', 'min' => 500001, 'max' => 750000],
        '750k_1m' => ['label' => '₱750,001 – ₱1,000,000', 'min' => 750001, 'max' => 1000000],
        '1m_1250k' => ['label' => '₱1,000,001 – ₱1,250,000', 'min' => 1000001, 'max' => 1250000],
        '1250k_1500k' => ['label' => '₱1,250,001 – ₱1,500,000', 'min' => 1250001, 'max' => 1500000],
        '1500k_1750k' => ['label' => '₱1,500,001 – ₱1,750,000', 'min' => 1500001, 'max' => 1750000],
        '1750k_2m' => ['label' => '₱1,750,001 – ₱2,000,000', 'min' => 1750001, 'max' => 2000000],
    ];

    /** The income bracket for a saved answer (current or legacy key), or null. */
    public static function incomeBracket(?string $key): ?array
    {
        return self::INCOME[$key ?? ''] ?? self::LEGACY_INCOME[$key ?? ''] ?? null;
    }

    /** Home province: Davao Region first (DNSC's catchment); LANDBANK asks for Davao del Norte residents. */
    public const PROVINCES = [
        'davao_del_norte' => 'Davao del Norte', 'davao_de_oro' => 'Davao de Oro', 'davao_del_sur' => 'Davao del Sur',
        'davao_oriental' => 'Davao Oriental', 'davao_occidental' => 'Davao Occidental', 'other' => 'A province outside the Davao Region',
    ];

    public const PROGRAM_FIELDS = [
        'it' => 'Information Technology / Information Systems / Computer Science',
        'engineering_electrical' => 'Electrical Engineering',
        'engineering_civil' => 'Civil Engineering',
        'engineering_mechanical' => 'Mechanical Engineering',
        'engineering_industrial' => 'Industrial Engineering',
        'engineering_other' => 'Other Engineering',
        'architecture' => 'Architecture',
        'accountancy_finance' => 'Accountancy / Finance',
        'business' => 'Business / Management',
        'hospitality' => 'Hospitality / Hotel and Restaurant Management',
        'tourism' => 'Tourism Management',
        'psychology' => 'Psychology',
        'education' => 'Education / Physical Education',
        'criminology' => 'Criminology',
        'agriculture_forestry' => 'Agriculture / Agroforestry / Forestry',
        'environment_sustainability' => 'Environmental Science / Sustainability',
        'disaster_resilience' => 'Disaster Resilience and Management',
        'stem_other' => 'Other science, math, or marine biology',
        'health_medical' => 'Health / Medical',
        'social_science_humanities' => 'Social Work / Social Science / Humanities',
        'other' => 'Another program',
        'unrecognized' => 'A program BAKR could not place in a field',
    ];

    public const SPECIAL_GROUPS = [
        'listahanan' => 'My household is in the Listahanan (DSWD poverty database)',
        'four_ps' => 'My household is a Pantawid Pamilyang Pilipino Program (4Ps) member',
        'pwd' => 'I am a person with disability (PWD)',
        'solo_parent' => 'I am a child of a solo parent',
        'indigenous_people' => 'I belong to an Indigenous Peoples group',
        'ofw_seafarer_parent' => 'My parent or guardian is an OFW or seafarer',
        'first_generation' => 'I am the first in my family to go to college',
        'none' => 'None of these',
    ];

    public const ASSISTANCE = [
        'tes_tdp_ched' => 'CHED scholarship, TES, or Tulong Dunong (TDP)',
        'other_government' => 'Another national government scholarship or grant',
        'private_foundation' => 'A private or foundation scholarship',
        'dnsc_internal' => 'A DNSC scholarship or grant',
        'none' => 'None of these',
    ];

    /**
     * Parent / guardian registered in an agriculture or fisheries registry (added 2026-11-18). Each option is
     * checked by a scholarship rule: RSBSA / ARB → LANDBANK ILP, NCFRS coconut farmer → CoScho, FishR → BFAR FSP.
     */
    public const FARM_FISHER = [
        'rsbsa' => 'My parent or grandparent is a farmer or fisherfolk registered in the RSBSA, or an Agrarian Reform Beneficiary (ARB)',
        'coconut' => 'My parent or guardian is a coconut farmer registered in the NCFRS (Philippine Coconut Authority)',
        'fishr' => 'My parent or guardian is a registered municipal fisherfolk (FishR / Mangingisdang Juan)',
        'none' => 'None of these',
    ];

    /** "None of these" (2026-11-21): every tick-box group is required, so a student who has none says so. */
    public const TALENTS = ['music' => 'Music', 'dance' => 'Dance', 'arts' => 'Arts', 'sports' => 'Sports', 'none' => 'None of these'];

    public const ROLES = [
        'ssc_officer' => 'Student Supreme Council officer',
        'publication_board' => 'Student Publication Editorial Board member',
        'none' => 'None of these',
    ];

    /** Grade points (DNSC 1.0–5.0 scale) mapped to the lower end of the usual percentage band. */
    private const DNSC_SCALE = [[1.0, 98], [1.25, 94], [1.5, 91], [1.75, 88], [2.0, 85], [2.25, 82], [2.5, 79], [2.75, 76], [3.0, 75], [5.0, 60]];

    /**
     * The matching form: every part (I, II, …) with its questions, options, validation and "when" conditions.
     * This is the single source of truth for the form, the validator, the sanitizer and the AI profile.
     */
    public static function sections(): array
    {
        $yn = self::YES_NO;

        return [
            'personal' => ['part' => 'I', 'title' => 'Personal Information', 'icon' => 'bi-person', 'intro' => 'Your name and e-mail come from your BAKR account. Only what a scholarship checks is asked here. Your exact birth date stays in BAKR: the AI matcher only sees your age.', 'fields' => [
                'birth_date' => ['type' => 'date', 'label' => 'Date of birth', 'required' => true, 'ai' => false, 'help' => 'Some scholarships have an age limit (BFAR: 25). The AI matcher sees only your age.'],
                'citizenship' => ['type' => 'radio', 'label' => 'Citizenship', 'options' => ['filipino' => 'Filipino citizen', 'other' => 'Other'], 'required' => true],
                'home_province' => ['type' => 'select', 'label' => 'Province where your family lives', 'options' => self::PROVINCES, 'required' => true, 'help' => 'LANDBANK asks for residents of Davao del Norte.'],
            ]],
            'education' => ['part' => 'II', 'title' => 'Educational Background', 'icon' => 'bi-mortarboard', 'intro' => 'Your enrolment at DNSC. Most scholarships depend on your year level and program.', 'fields' => [
                'enrollment_status' => ['type' => 'radio', 'label' => 'Your status at DNSC', 'options' => ['incoming_first_year' => 'Incoming first-year (not yet started)', 'currently_enrolled' => 'Currently enrolled'], 'required' => true],
                'year_level' => ['type' => 'select', 'label' => 'Year level', 'options' => ['1' => '1st year', '2' => '2nd year', '3' => '3rd year', '4' => '4th year', '5' => '5th year or higher'], 'required' => true],
                'campus_id' => ['type' => 'campus', 'label' => 'Your DNSC campus', 'required' => true],
                'program_id' => ['type' => 'program', 'label' => 'Your program', 'required' => true, 'help' => 'The list shows the programs DNSC offers at the campus you chose.'],
                'previous_degree' => ['type' => 'radio', 'label' => 'Do you already have a bachelor\'s degree?', 'options' => $yn, 'required' => true],
                'previous_units' => ['type' => 'radio', 'label' => 'Before your current program, did you earn college units in another program?', 'options' => $yn, 'required' => true, 'help' => 'Scholarships for first-time college students ask this.',
                    'info' => 'College units are the credits you earn for passing college subjects. Answer Yes if you studied in another college program before, even without finishing it, or if you shifted or transferred and some subjects were credited. DOST-SEI, CMSP, BPMSP and BFAR are only for students with no college units yet.'],
                'within_years' => ['type' => 'radio', 'label' => 'Are you still within the normal length of your program?', 'options' => $yn, 'when' => 'upper', 'required_if' => ['year_level' => ['2', '3', '4', '5']], 'help' => 'For example, still within 4 years in a 4-year program (a 1-year grace is usually allowed).',
                    'info' => 'CHED calls this the Maximum Residency Rule: the normal number of years of your program plus one extra year. A 4-year program, for example, allows up to 5 years. CHED TDP and TES check it.'],
                'shs_strand' => ['type' => 'select', 'label' => 'Senior high school strand', 'options' => self::SHS_STRANDS, 'when' => 'incoming', 'required_if' => ['enrollment_status' => 'incoming_first_year'], 'help' => 'Science scholarships (for example DOST-SEI) look at your strand.',
                    'info' => 'The track you took in Grade 11 and 12, such as STEM or ABM. DOST-SEI takes STEM graduates, and non-STEM graduates who are in the top 5% of their class. If you passed the ALS exam instead, choose ALS.'],
                'shs_country' => ['type' => 'radio', 'label' => 'Did you finish (or will you finish) senior high school in the Philippines?', 'options' => ['ph' => 'Yes, in the Philippines', 'abroad' => 'No, outside the Philippines'], 'when' => 'first_year', 'required_if' => ['year_level' => '1'], 'help' => 'The CHED Merit Scholarship (CMSP) is only for graduates of a Philippine senior high school.'],
            ]],
            'academic' => ['part' => 'III', 'title' => 'Academic Standing and Conduct', 'icon' => 'bi-journal-text', 'intro' => 'Only what applies to your year level is asked. If your grades are on the 1.0–5.0 scale, choose that scale and BAKR will estimate the percentage.', 'fields' => [
                'shs_gwa' => ['type' => 'number', 'label' => 'Grade 12 average (percent)', 'min' => 60, 'max' => 100, 'step' => '0.01', 'placeholder' => 'e.g., 92.5', 'suffix' => '%', 'when' => 'first_year', 'required_if' => ['year_level' => '1'], 'help' => 'First-year scholarships (CMSP, GrabScholar, BPMSP, BFAR) look at your Grade 12 grade.',
                    'info' => 'Your general average for Grade 12, as written on your report card (Form 138). Type it as a percentage, for example 92.5.'],
                'top_five' => ['type' => 'radio', 'label' => 'Were you in the Top 5 of your Grade 12 graduating class?', 'options' => ['yes' => 'Yes', 'no' => 'No', 'unsure' => 'Not sure'], 'when' => 'incoming', 'required_if' => ['enrollment_status' => 'incoming_first_year'],
                    'info' => 'Yes only if your school ranked you among the five highest students of your whole Grade 12 graduating class. It is one of the ways to qualify for BPMSP. Not sure? Ask your adviser, or choose "Not sure".'],
                'college_gwa' => ['type' => 'number', 'label' => 'Latest college GWA', 'min' => 1, 'max' => 100, 'step' => '0.01', 'when' => 'upper', 'required_if' => ['year_level' => ['2', '3', '4', '5']], 'help' => 'Your DNSC GWA as it appears on your grades, e.g. 1.75 or 88.',
                    'info' => 'GWA (General Weighted Average) is the average of your final grades, where subjects with more units count more. Use the one for your latest semester, as shown on your grades from the Registrar.'],
                'college_gwa_scale' => ['type' => 'select', 'label' => 'GWA scale', 'options' => ['percent' => 'Percent (60–100, higher is better)', 'dnsc' => 'DNSC 1.0 – 5.0 scale (1.0 is the highest, 5.0 is failing)'], 'when' => 'upper', 'required_if' => ['year_level' => ['2', '3', '4', '5']], 'help' => 'DNSC grades use 1.0 as the highest mark. Choose the scale your GWA is written in.',
                    'info' => 'On the DNSC scale 1.0 is the highest grade, 3.0 is the lowest passing grade and 5.0 is failing. Choose Percent only if your GWA is written as a percentage, like 88.'],
                'failing_grade' => ['type' => 'radio', 'label' => 'Do you have a failing mark in any subject in your last semester or school?', 'options' => $yn, 'required' => true,
                    'info' => 'A failing mark is a final grade of 5.0 (below 75% on a percentage scale) in any subject. Cebuana and LANDBANK require no failing marks, and LANDBANK also counts conditional grades.'],
                'disciplinary' => ['type' => 'radio', 'label' => 'Did you receive a disciplinary sanction in senior high school?', 'options' => $yn, 'when' => 'first_year', 'required_if' => ['year_level' => '1'],
                    'info' => 'A sanction is an official penalty from your school for misconduct, such as a suspension, that is written in your record. BPMSP, GrabScholar and BFAR ask for good conduct.'],
                'dost_exam' => ['type' => 'select', 'label' => 'DOST-SEI Scholarship Examination', 'options' => ['not_taken' => 'I have not taken it', 'taken_waiting' => 'I took it and am waiting for the result', 'passed' => 'I passed / received an award'], 'when' => 'incoming', 'required_if' => ['enrollment_status' => 'incoming_first_year'],
                    'info' => 'A yearly exam of the DOST Science Education Institute for Grade 12 students. DOST-SEI picks its scholars by this exam, so taking it is how you apply.'],
            ]],
            'family' => ['part' => 'IV', 'title' => 'Family and Finances', 'icon' => 'bi-house-heart', 'intro' => 'Private. Used only to check income limits and priority groups. Choose a range, not an exact amount.', 'fields' => [
                'household_income' => ['type' => 'select', 'label' => 'Combined yearly gross income of your parents or guardian', 'options' => array_map(fn ($i) => $i['label'], self::INCOME), 'required' => true, 'help' => 'Add up what your parents or guardian earn in a year before tax, then pick the range. The ranges follow the income limits of the scholarships.',
                    'info' => 'Gross income is income before taxes and deductions. Add up what both parents (or your guardian) earn in a year from work, business, farming or fishing and other regular sources. Most providers ask for proof of income when you apply.'],
                'special_groups' => ['type' => 'checkbox', 'label' => 'Do any of these apply? (tick all that do)', 'options' => self::SPECIAL_GROUPS, 'required' => true, 'exclusive' => 'none',
                    'info' => 'Listahanan is the DSWD list of poor households. 4Ps (Pantawid Pamilyang Pilipino Program) is the government\'s cash aid for poor families. CHED TES gives priority to these households, Indigenous Peoples households and dependents of solo parents; BPMSP gives extra ranking points to these groups.'],
                'farm_fisher_family' => ['type' => 'checkbox', 'label' => 'Is your parent, grandparent or guardian a registered farmer or fisherfolk? (tick all that apply)', 'options' => self::FARM_FISHER, 'required' => true, 'exclusive' => 'none', 'help' => 'Asked by the LANDBANK, CoScho (coconut farmers) and BFAR fisheries scholarships.',
                    'info' => 'RSBSA: the Registry System for Basic Sectors in Agriculture (Department of Agriculture). ARB: Agrarian Reform Beneficiary (DAR). NCFRS: the National Coconut Farmers Registry System of the Philippine Coconut Authority. FishR: the BFAR registry of municipal fisherfolk. Not sure? Ask your parent or your municipal agriculture office.'],
            ]],
            'other_aid' => ['part' => 'V', 'title' => 'Other Scholarships and Grants', 'icon' => 'bi-wallet2', 'intro' => 'Many scholarships do not allow two government grants at the same time. Tell BAKR what you already have.', 'fields' => [
                'existing_assistance' => ['type' => 'checkbox', 'label' => 'Are you already receiving any of these?', 'options' => self::ASSISTANCE, 'required' => true, 'exclusive' => 'none',
                    'info' => 'National government grants include TES, TDP and the CHED and DOST scholarships. One-time aid (such as AICS) and free tuition under the Free Higher Education law do not count. Most government scholarships do not allow two national government grants at once.'],
            ]],
            'involvement' => ['part' => 'VI', 'title' => 'Achievements and Activities', 'icon' => 'bi-trophy', 'intro' => 'These open the DNSC Grant-in-Aid and Student Assistant programs.', 'fields' => [
                'talent_categories' => ['type' => 'checkbox', 'label' => 'Do you have a skill in any of these?', 'options' => self::TALENTS, 'required' => true, 'exclusive' => 'none'],
                // Asked (and required) only after a real skill is ticked ('when' => 'talent', checked in the request).
                'has_awards' => ['type' => 'radio', 'label' => 'Do you have awards or achievements in that skill?', 'options' => $yn, 'when' => 'talent'],
                'leadership_roles' => ['type' => 'checkbox', 'label' => 'Do you hold any of these roles?', 'options' => self::ROLES, 'required' => true, 'exclusive' => 'none'],
                'willing_student_assistant' => ['type' => 'radio', 'label' => 'Are you willing to work as a student assistant for DNSC?', 'options' => $yn, 'required' => true,
                    'info' => 'The DNSC Student Assistant program (Category C) is for students from financially disadvantaged families who help with approved work at the college in return for assistance.'],
            ]],
            'foundation' => ['part' => 'VII', 'title' => 'Provider-Specific Questions', 'icon' => 'bi-bank', 'intro' => 'Some foundation scholarships ask about their own services.', 'fields' => [
                'cebuana_client' => ['type' => 'radio', 'label' => 'Are you, or a close relative, a Cebuana Lhuillier client?', 'options' => ['self' => 'Yes, I am', 'relative' => 'Yes, a close relative is', 'no' => 'No'], 'required' => true,
                    'info' => 'A client is someone with a Cebuana Lhuillier 24K Loyalty Card. The Cebuana Lhuillier Foundation scholarship asks for this card when you apply.'],
                'cebuana_microsavings' => ['type' => 'radio', 'label' => 'Do you have a Cebuana Lhuillier Microsavings account?', 'options' => ['yes' => 'Yes', 'willing_to_open' => 'No, but I am willing to open one', 'no' => 'No'], 'required' => true,
                    'info' => 'A small savings account you can open at Cebuana Lhuillier. It was on the foundation\'s earlier list of qualifications; its current FAQ does not require it, so it only helps.'],
            ]],
        ];
    }

    /**
     * Whether a question applies to this student. Only what a scholarship can use is asked (owner, 2026-11-19:
     * "if a student is already in college, why ask for senior high school information?"):
     *   first_year  Grade 12 grade, Philippine SHS, SHS conduct: incoming AND current 1st-years (CMSP and
     *               GrabScholar accept current first-years and read their Grade 12 grade)
     *   incoming    strand, Top 5, DOST exam: only students not yet in college (DOST-SEI, BPMSP, BFAR)
     *   upper       college GWA, "within the normal length": 2nd year and up
     *   talent      "awards in that skill": only after a real skill is ticked (not "None of these", 2026-11-21)
     * A question with no 'when' always applies.
     *
     * @param  array<string,mixed>  $answers  enrollment_status and year_level (an incoming student is year 1)
     */
    public static function applies(?string $when, array $answers): bool
    {
        $incoming = ($answers['enrollment_status'] ?? null) === 'incoming_first_year';
        $year = $incoming ? '1' : (string) ($answers['year_level'] ?? '');

        return match ($when) {
            'first_year' => $year === '1',
            'incoming' => $incoming,
            'upper' => ! $incoming && in_array($year, ['2', '3', '4', '5'], true),
            'talent' => array_diff((array) ($answers['talent_categories'] ?? []), ['none']) !== [],
            default => true,
        };
    }

    /** @return array<string,array> flat map of every field */
    public static function fields(): array
    {
        $all = [];
        foreach (self::sections() as $section) {
            $all += $section['fields'];
        }

        return $all;
    }

    /** Laravel validation rules (plain arrays and strings, no framework classes needed). */
    public static function rules(): array
    {
        $rules = [];
        foreach (self::fields() as $key => $f) {
            $r = [];
            if (! empty($f['required'])) {
                $r[] = 'required';
            } elseif (! empty($f['required_if'])) {
                $field = array_key_first($f['required_if']);
                $r[] = 'required_if:'.$field.','.implode(',', (array) $f['required_if'][$field]);
            } else {
                $r[] = 'nullable';
            }
            switch ($f['type']) {
                case 'radio':
                case 'select':
                    $r[] = 'in:'.implode(',', array_keys($f['options']));
                    break;
                case 'number':
                    $r[] = 'numeric';
                    $r[] = 'between:'.$f['min'].','.$f['max'];
                    break;
                case 'text':
                    $r[] = 'string';
                    $r[] = 'max:'.$f['max'];
                    break;
                case 'date':
                    // College students: at least 14, born after 1950.
                    $r[] = 'date';
                    $r[] = 'after:1950-01-01';
                    $r[] = 'before_or_equal:'.now()->subYears(14)->toDateString();
                    break;
                case 'checkbox':
                    $rules[$key.'.*'] = ['in:'.implode(',', array_keys($f['options']))];
                    $r = array_merge(['array'], array_diff($r, ['nullable']), ! empty($f['required']) ? ['min:1'] : ['nullable']);
                    break;
            }
            $rules[$key] = array_values(array_unique($r));
        }
        // Required with the GWA (2026-11-21: every question is answered); "has awards" is checked in the request
        // (it applies only after a real skill is ticked, which a required_if rule cannot express for a tick-box group).
        $rules['college_gwa_scale'] = ['required_with:college_gwa', 'required_if:year_level,2,3,4,5', 'nullable', 'in:percent,dnsc'];

        return $rules;
    }

    /**
     * The words a student chose for an answer (a list is joined with commas, an empty list = "None").
     *
     * @param  string  $field  the question key
     * @param  mixed  $value  the stored option key or keys
     */
    public static function optionLabel(string $field, mixed $value): string
    {
        $options = self::fields()[$field]['options'] ?? ($field === 'program_field' ? self::PROGRAM_FIELDS : []);
        if ($field === 'household_income') {
            $options += array_map(fn ($i) => $i['label'], self::LEGACY_INCOME);   // forms saved before 2026-11-19
        }
        if (is_array($value)) {
            return implode(', ', array_map(fn ($v) => (string) ($options[$v] ?? $v), $value)) ?: 'None';
        }

        return (string) ($options[$value] ?? $value);
    }

    /**
     * A saved answer as the student should read it (profile page, printable form), or null when
     * the question was not answered. college_gwa_scale is folded into college_gwa.
     */
    public static function shownAnswer(string $key, array $answers): ?string
    {
        $f = self::fields()[$key] ?? null;
        $v = $answers[$key] ?? null;
        if ($f === null || $v === null || $v === '' || $v === []) {
            return null;
        }

        return match ($f['type']) {
            'number' => $key === 'college_gwa'
                ? $v.(($answers['college_gwa_scale'] ?? 'percent') === 'dnsc' ? ' (1.0 to 5.0 scale)' : '%')
                : $v.($f['suffix'] ?? ''),
            'campus' => $answers['campus_name'] ?? null,
            'program' => $answers['program_name'] ?? null,
            'date' => self::formatDate((string) $v),
            'text' => (string) $v,
            default => self::optionLabel($key, $v),
        };
    }

    /**
     * The questions whose shown answer differs between two saved versions (student version history).
     * Compared as the student reads them (shownAnswer), so 90 vs 90.0 or a re-ordered tick list is no change;
     * college_gwa_scale is folded into college_gwa.
     *
     * @return list<string> field keys, in form order
     */
    public static function changedFields(array $old, array $new): array
    {
        $changed = [];
        foreach (array_keys(self::fields()) as $key) {
            if ($key !== 'college_gwa_scale' && self::shownAnswer($key, $old) !== self::shownAnswer($key, $new)) {
                $changed[] = $key;
            }
        }

        return $changed;
    }

    /** Roman numeral of the certification part (one after the last section). */
    public static function certificationPart(): string
    {
        return ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'][count(self::sections())] ?? (string) (count(self::sections()) + 1);
    }

    /** Age in whole years on $today from a Y-m-d birth date, or null when unreadable. */
    public static function age(?string $birthDate, ?\DateTimeInterface $today = null): ?int
    {
        if (! $birthDate || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthDate)) {
            return null;
        }
        try {
            return (new \DateTimeImmutable($birthDate))->diff($today ?? new \DateTimeImmutable('today'))->y;
        } catch (\Exception) {
            return null;
        }
    }

    /** A stored Y-m-d date as "March 14, 2005" (unreadable text is returned unchanged). */
    private static function formatDate(string $v): string
    {
        try {
            return (new \DateTimeImmutable($v))->format('F j, Y');
        } catch (\Exception) {
            return $v;
        }
    }

    /** Estimate a percentage from a 1.0–5.0 grade by interpolating the usual conversion bands. */
    public static function dnscToPercent(float $grade): float
    {
        $pts = self::DNSC_SCALE;
        if ($grade <= $pts[0][0]) {
            return (float) $pts[0][1];
        }
        for ($i = 1; $i < count($pts); $i++) {
            [$g1, $p1] = $pts[$i];
            [$g0, $p0] = $pts[$i - 1];
            if ($grade <= $g1) {
                return round($p0 + ($p1 - $p0) * (($grade - $g0) / ($g1 - $g0)), 2);
            }
        }

        return (float) end($pts)[1];
    }
}
