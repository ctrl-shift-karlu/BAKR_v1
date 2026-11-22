<?php
/*
 * ScholarshipFinder.php — the Scholarship Finder page.
 * Single file: questionnaire data and matching engine (formerly finder.php),
 * followed by the page controller and markup (formerly studentFinder.php).
 */
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

/* =========================================================
 * QUESTIONS  (was: questions.php)
 * Controlled choices only: no "Other", no free text. Keys are what gets
 * stored and matched.
 * ========================================================= */
$yn  = ['yes' => 'Yes', 'no' => 'No'];
$avg = ['a97' => '97 and above', 'a95' => '95–96', 'a93' => '93–94', 'a90' => '90–92', 'a85' => '85–89', 'lt85' => 'Below 85'];

$Q = [
    'year'       => ['s' => 1, 'q' => 'What is your current year level?', 'o' => ['incoming' => 'Incoming First Year', 'y1' => '1st Year', 'y2' => '2nd Year', 'y3' => '3rd Year', 'y4' => '4th Year']],
    'institute'  => ['s' => 1, 'q' => 'Which DNSC institute are you enrolled in or planning to enroll in?', 'o' => [
        'IC'    => 'Institute of Computing (IC)',
        'IAAS'  => 'Institute of Aquatic and Applied Sciences (IAAS)',
        'IBPA' => 'Institute of Business and Public Affairs (IBPA)',
        'ITED'  => 'Institute of Teacher Education (ITED)',
    ]],
    'course'     => ['s' => 1, 'q' => 'What is your course?', 'o' => null],   // filled from the chosen institute
    'avg'        => ['s' => 1, 'q' => 'What is your latest academic average?', 'o' => $avg],
    'g12'        => ['s' => 1, 'q' => 'What was your Grade 12 academic average?', 'o' => $avg + ['na' => 'Not Applicable']],
    'rank'       => ['s' => 1, 'q' => 'What was your graduating class rank?', 'o' => ['top1' => 'Top 1', 'top2' => 'Top 2', 'top3' => 'Top 3', 'top4' => 'Top 4', 'top5' => 'Top 5', '6plus' => '6th or lower', 'none' => 'No class ranking', 'na' => 'Not Applicable']],
    'distinction'=> ['s' => 1, 'q' => 'Did you receive an academic distinction?', 'o' => ['none' => 'None', 'honors' => 'With Honors', 'high' => 'With High Honors', 'highest' => 'With Highest Honors']],
    'college'    => ['s' => 1, 'q' => 'Which best describes your college background?', 'o' => ['none' => 'No previous college units', 'units' => 'Has previous college units', 'degree' => 'Already has a bachelor\'s degree']],

    'income'     => ['s' => 2, 'q' => 'What is your estimated annual family income?', 'o' => ['lt100' => 'Below ₱100,000', 'i100' => '₱100,000–₱199,999', 'i200' => '₱200,000–₱299,999', 'i300' => '₱300,000 and above']],
    'has_sch'    => ['s' => 2, 'q' => 'Are you currently receiving any scholarship or educational assistance?', 'o' => $yn],
    'sch_type'   => ['s' => 2, 'q' => 'What type of scholarship or educational assistance are you currently receiving?', 'o' => ['government' => 'Government Scholarship', 'dnsc' => 'DNSC Scholarship', 'private' => 'Private/Foundation Scholarship']],

    'leadership' => ['s' => 3, 'q' => 'Do you currently hold a leadership or publication position?', 'o' => ['none' => 'None', 'ssc' => 'Student Supreme Council Officer', 'pub' => 'Student Publication / Editorial Board']],
    'talent'     => ['s' => 3, 'q' => 'Do you have a notable talent or achievement in any of the following areas?', 'o' => ['none' => 'None', 'music' => 'Music', 'dance' => 'Dance', 'arts' => 'Arts', 'sports' => 'Sports']],
    'sa'         => ['s' => 3, 'q' => 'Are you willing to serve as a DNSC Student Assistant if selected?', 'o' => $yn],
    'failing'    => ['s' => 3, 'q' => 'Do you have a failing mark in your latest academic record?', 'o' => $yn],
    'discipline' => ['s' => 3, 'q' => 'Did you receive any disciplinary sanction during Senior High School?', 'o' => $yn],

    'card'       => ['s' => 4, 'q' => 'Who has a Cebuana Lhuillier 24K Loyalty Card?', 'o' => ['self' => 'Myself', 'relative' => 'A Qualified Relative', 'both' => 'Both', 'neither' => 'Neither']],
    'micro'      => ['s' => 4, 'q' => 'Do you or your qualifying relative have a Cebuana Lhuillier Microsavings account?', 'o' => $yn],
    'solo'       => ['s' => 4, 'q' => 'Are you a dependent of a solo parent?', 'o' => $yn],
    'pwd'        => ['s' => 4, 'q' => 'Are you a person with disability (PWD)?', 'o' => $yn],
    'citizen'    => ['s' => 4, 'q' => 'Are you a Filipino citizen?', 'o' => $yn],
    'strand'     => ['s' => 4, 'q' => 'What was your Senior High School strand?', 'o' => ['stem' => 'STEM', 'abm' => 'ABM', 'humss' => 'HUMSS', 'gas' => 'GAS', 'tvl' => 'TVL', 'arts' => 'Arts and Design', 'sports' => 'Sports']],
];

/* =========================================================
 * RULES + COURSES  (was: rules.php)
 * DATA ONLY. No matching logic lives here.
 * Rule = [field, op, value, label]
 *   in / notin  : answer is (not) one of the listed values
 *   gwa_min     : the WHOLE selected average bracket must be >= value
 *   income_max  : the WHOLE selected income bracket must be <= value
 *   anyof       : value is a list of rules; at least one must pass
 * A scholarship is listed only if EVERY rule passes. Missing/unclear = fail.
 * Later: one row per rule in scholarship_rules(scholarship_id, field, op, value, label)
 * and replace this section's array with a DB query. engine.php logic below stays the same.
 * "VERIFY" = threshold I could not confirm from your project files.
 * ========================================================= */
$cit   = ['citizen', 'in', ['yes'], 'Filipino citizen'];
$inc1  = ['year', 'in', ['incoming'], 'Incoming first-year student'];
$cont  = ['year', 'in', ['y1', 'y2', 'y3', 'y4'], 'Currently enrolled DNSC student'];
$noGov = ['sch_type', 'notin', ['government'], 'No conflicting government scholarship'];
$first = ['college', 'in', ['none'], 'No previous college units'];
$noBach= ['college', 'notin', ['degree'], 'Does not yet hold a bachelor\'s degree'];
$clean = ['discipline', 'in', ['no'], 'No disciplinary sanction in Senior High School'];
$noFail= ['failing', 'notin', ['yes'], 'No failing mark in latest record'];

$R = [
    // Real DNSC undergraduate programs by institute.
    'courses' => [
        'IC' => [
            'Bachelor of Science in Information Systems (BSIS)',
            'Bachelor of Science in Information Technology (BSIT)',
        ],
        'IAAS' => [
            'Bachelor of Science in Agro-Forestry (BSAF)',
            'Bachelor of Science in Fisheries and Aquatic Sciences (BSFAS)',
            'Bachelor of Science in Food Technology (BSFT)',
            'Bachelor of Science in Marine Biology (BSMB)',
        ],
        'ILEGG' => [
            'Bachelor of Public Administration (BPA)',
            'Bachelor of Science in Criminology (BSCrim)',
            'Bachelor of Science in Disaster and Resilience Management (BSDRM)',
            'Bachelor of Science in Entrepreneurship (BS ENTREP)',
            'Bachelor of Science in Social Work (BSSW)',
            'Bachelor of Science in Tourism Management (BSTM)',
        ],
        'ITED' => [
            'Bachelor of Arts in Communication (BACOMM)',
            'Bachelor of Secondary Education (BSeD) - English',
            'Bachelor of Secondary Education (BSeD) - Mathematics',
            'Bachelor of Secondary Education (BSeD) - Science',
            'Bachelor of Technology and Livelihood Education (BTLEd)',
            'Bachelor of Physical Education (BPE)',
        ],
    ],

    'scholarships' => [
        ['id' => 'dnscAcademic', 'name' => 'DNSC Academic Scholarship', 'category' => 'DNSC Internal', 'logo' => 'dnss_logo.jpg',
         'desc' => "Scholarship assistance for DNSC students who meet the institution's academic excellence requirements.",
         'rules' => [$cont, ['avg', 'gwa_min', 90, 'Latest GWA of 90 or higher'], $noFail]],           // VERIFY 90

        ['id' => 'giaA', 'name' => 'DNSC Grant-in-Aid (GIA) – Category A', 'category' => 'DNSC Internal', 'logo' => 'dnss_logo.jpg',
         'desc' => 'Financial assistance for students with exceptional skills in music, dance, arts, and sports.',
         'rules' => [['talent', 'in', ['music', 'dance', 'arts', 'sports'], 'Talent in music, dance, arts, or sports'], $noFail]],

        ['id' => 'giaB', 'name' => 'DNSC Grant-in-Aid (GIA) – Category B', 'category' => 'DNSC Internal', 'logo' => 'dnss_logo.jpg',
         'desc' => 'Financial assistance for Student Supreme Council officers and Student Publication Editorial Board members.',
         'rules' => [['leadership', 'in', ['ssc', 'pub'], 'Holds an SSC or Student Publication position'], $noFail]],

        ['id' => 'studentAssistant', 'name' => 'DNSC Student Assistant (SA) – Category C', 'category' => 'DNSC Internal', 'logo' => 'dnss_logo.jpg',
         'desc' => 'Assistance for students from financially disadvantaged families willing to perform approved institutional support services.',
         'rules' => [['sa', 'in', ['yes'], 'Willing to serve as a Student Assistant'],
                     ['income', 'income_max', 299999, 'Family income below ₱300,000']]],               // VERIFY threshold

        ['id' => 'chedTdp', 'name' => 'CHED Tulong Dunong Program (TDP)', 'category' => 'Government / External', 'logo' => 'CHED-Logo.png',
         'desc' => 'Financial assistance under UniFAST for qualified Filipino undergraduate students.',
         'rules' => [$cit, $cont, $noBach, ['income', 'income_max', 299999, 'Family income below ₱300,000'], $noGov]], // VERIFY

        ['id' => 'chedTes', 'name' => 'CHED Tertiary Education Subsidy (TES)', 'category' => 'Government / External', 'logo' => 'CHED-Logo.png',
         'desc' => 'Government financial assistance for qualified students enrolled in eligible higher education institutions.',
         'rules' => [$cit, $cont, $noBach, ['income', 'income_max', 299999, 'Family income below ₱300,000'], $noGov]], // VERIFY

        ['id' => 'dostSei', 'name' => 'DOST-SEI Undergraduate Scholarship', 'category' => 'Government / External', 'logo' => 'DOST-Logo.png',
         'desc' => 'Scholarship for qualified Filipino students pursuing priority Science and Technology degree programs.',
         'url' => 'https://science-scholarships.ph/',
         'rules' => [$cit, $inc1, $first, ['strand', 'in', ['stem'], 'STEM strand graduate'],
                     ['g12', 'gwa_min', 85, 'Grade 12 average of 85 or higher'], $clean, $noGov]],      // VERIFY strand/85

        ['id' => 'chedMerit', 'name' => 'CHED Merit Scholarship Program', 'category' => 'Government / External', 'logo' => 'CHED-Logo.png',
         'desc' => 'Competitive CHED scholarship for academically talented incoming first-year college students.',
         'rules' => [$cit, $inc1, $first, ['g12', 'gwa_min', 93, 'Grade 12 average of 93 or higher'],
                     ['income', 'income_max', 299999, 'Family income below ₱300,000'], $noGov]],         // VERIFY income

        ['id' => 'bagongPilipinas', 'name' => 'Bagong Pilipinas Merit Scholarship Program', 'category' => 'Government / External', 'logo' => 'CHED-Logo.png',
         'desc' => 'Merit-based scholarship by CHED, DepEd, and TESDA for academically talented Filipino students.',
         'url' => 'https://bpms.ched.gov.ph/', 'closed' => 'The AY 2026–2027 application period has ended.',
         'rules' => [$cit, $inc1, $first,
                     ['g12', 'anyof', [['rank', 'in', ['top1', 'top2', 'top3', 'top4', 'top5'], ''], ['g12', 'gwa_min', 95, '']], 'Top 5 graduating rank or Grade 12 average of 95+'],
                     ['income', 'income_max', 299999, 'Family income below ₱300,000'], $noGov]],         // VERIFY income

        ['id' => 'grabScholar', 'name' => 'GrabScholar College Scholarship', 'category' => 'Private / NGO / Foundation', 'logo' => 'GRAB-Logo.svg',
         'desc' => 'Full-ride merit scholarship for incoming first-year Filipino students in eligible programs.',
         'url' => 'https://www.grab.com/ph/community/grabscholar-college-scholarship/', 'closed' => 'This application window is currently closed.',
         'rules' => [$cit, $inc1, $first, ['g12', 'gwa_min', 90, 'Grade 12 average of 90 or higher'], $clean]],

        ['id' => 'cebuana', 'name' => 'Cebuana Lhuillier Foundation National Scholarship Program', 'category' => 'Private / NGO / Foundation', 'logo' => 'CEBUANA-Logo.jpg',
         'desc' => 'College scholarship grants for economically challenged Filipino students in qualified public universities.',
         'url' => 'https://cebuanafoundation.org/qualification.php',
         'rules' => [$cit, ['income', 'income_max', 350000, 'Family income of ₱350,000 or below'],
                     ['avg', 'gwa_min', 85, 'Academic average of 85 or higher'], $noFail, $clean,
                     ['card', 'in', ['self', 'relative', 'both'], 'Cebuana 24K Loyalty Card (own or qualified relative)'],
                     ['micro', 'in', ['yes'], 'Cebuana Microsavings account']]],
    ],
];

/* =========================================================
 * ENGINE  (was: engine.php)
 * Eligibility engine. Answers in, qualified scholarships out. No percentages.
 * Rule of thumb: if an answer cannot PROVE a requirement, the requirement fails.
 * ========================================================= */
const SF_AVG_LOW    = ['a97' => 97, 'a95' => 95, 'a93' => 93, 'a90' => 90, 'a85' => 85, 'lt85' => 0];     // lowest value in each bracket
const SF_INCOME_TOP = ['lt100' => 99999, 'i100' => 199999, 'i200' => 299999, 'i300' => PHP_INT_MAX];      // highest value in each bracket

// Turn raw answers into the fields the rules read.
function sf_effective(array $a): array
{
    if (($a['year'] ?? '') === 'incoming') {      // incoming students: latest average IS the Grade 12 average
        $a['g12'] = $a['avg'] ?? '';
        $a['failing'] = 'na';
    }
    $a['sch_type'] = ($a['has_sch'] ?? '') === 'yes' ? ($a['sch_type'] ?? '') : 'none';
    return $a;
}

function sf_rule_ok(array $a, array $r): bool
{
    [$field, $op, $val] = $r;
    if ($op === 'anyof') {
        foreach ($val as $sub) { if (sf_rule_ok($a, $sub)) return true; }
        return false;
    }
    $x = $a[$field] ?? '';
    if ($x === '') return false;                                   // unanswered = not proven
    switch ($op) {
        case 'in':         return in_array($x, $val, true);
        case 'notin':      return !in_array($x, $val, true);
        case 'gwa_min':    return isset(SF_AVG_LOW[$x]) && SF_AVG_LOW[$x] >= $val;       // whole bracket must clear the bar
        case 'income_max': return isset(SF_INCOME_TOP[$x]) && SF_INCOME_TOP[$x] <= $val; // whole bracket must fit under the cap
    }
    return false;
}

// A scholarship passes only if ALL rules pass. Returns the labels of the rules it met.
function sf_match(array $a, array $scholarships): array
{
    $out = [];
    foreach ($scholarships as $s) {
        foreach ($s['rules'] as $r) { if (!sf_rule_ok($a, $r)) continue 2; }
        $s['met'] = array_column($s['rules'], 3);
        $out[] = $s;
    }
    return $out;
}

/*
 * FUTURE FINALS POINT SYSTEM
 * Called only AFTER sf_match(), so it can reorder qualified scholarships but can never
 * add one back. Not an eligibility percentage. Today it returns the list unchanged.
 */
function sf_prioritize(array $qualified, array $a): array
{
    return $qualified;
}

/* =========================================================
 * PAGE (was: studentFinder.php)
 * ========================================================= */

// Blade runs this view inside a function, so top-level variables are not true
// globals. The helpers below use `global $Q, $R`, so publish them explicitly.
$GLOBALS['Q'] = $Q;
$GLOBALS['R'] = $R;

$_SESSION['csrf'] = $_SESSION['csrf'] ?? bin2hex(random_bytes(16));
if (!isset($_SESSION['sf']) || !is_array($_SESSION['sf'])) $_SESSION['sf'] = [];
$A = &$_SESSION['sf'];

const SECTIONS = [1 => 'Academic Background', 2 => 'Financial Background', 3 => 'Qualifications & Activities', 4 => 'Other Eligibility Information'];
const SECTION_ICON = [1 => 'bi-mortarboard-fill', 2 => 'bi-cash-coin', 3 => 'bi-award-fill', 4 => 'bi-info-circle-fill'];

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
/** Official destination fallback when a scholarship-specific page is not configured. */
function sf_official_url(array $s): string {
    if (!empty($s['url']) && filter_var($s['url'], FILTER_VALIDATE_URL)) return $s['url'];
    if (($s['category'] ?? '') === 'DNSC Internal') return 'https://www.facebook.com/DNSCScholarshipAndFinancialGrants';
    if (str_contains(strtolower((string)($s['id'] ?? '')), 'dost')) return 'https://www.science-scholarships.ph/';
    if (str_contains(strtolower((string)($s['id'] ?? '')), 'ched') || str_contains(strtolower((string)($s['id'] ?? '')), 'bagong')) return 'https://ched.gov.ph/';
    if (($s['category'] ?? '') === 'Government / External') return 'https://ched.gov.ph/';
    return 'https://www.facebook.com/DNSCScholarshipAndFinancialGrants';
}
/** True when the request came from the questionnaire's fetch() calls. */
function sf_is_ajax(): bool { return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'; }
/** Redirect helper. Normal requests get a 302; AJAX requests get {ok, redirect} so the page can swap the step in place. */
function go($u) {
    $url = asset('scholarship-finder') . $u;
    if (sf_is_ajax()) {
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-store');
        echo json_encode(['ok' => true, 'redirect' => $url]);
        exit;
    }
    header('Location: ' . $url); exit;
}

function opts($k, $A) {
    global $Q, $R;
    if ($k === 'course') { $c = $R['courses'][$A['institute'] ?? ''] ?? []; return array_combine($c, $c); }
    return $Q[$k]['o'];
}
// Questions that apply to this student, in order.
function active($A) {
    global $Q;
    $inc = ($A['year'] ?? '') === 'incoming';
    return array_values(array_filter(array_keys($Q), function ($k) use ($A, $inc) {
        if ($k === 'sch_type') return ($A['has_sch'] ?? '') === 'yes';
        if ($inc && in_array($k, ['g12', 'failing'], true)) return false;   // not applicable to incoming students
        return true;
    }));
}
function valid($k, $A) { return isset($A[$k]) && is_string($A[$k]) && array_key_exists($A[$k], opts($k, $A)); }
function firstMissing($A) { foreach (active($A) as $k) if (!valid($k, $A)) return $k; return null; }

function qText($k, $A) {
    global $Q;
    if ($k === 'avg' && ($A['year'] ?? '') === 'incoming') return 'What is your Grade 12 academic average?';
    return $Q[$k]['q'];
}
function qHint($k, $A) {
    if ($k === 'avg') return ($A['year'] ?? '') === 'incoming' ? 'As an incoming student, choose your Grade 12 average.' : 'Choose your latest college GWA.';
    if ($k === 'income') return 'Choose the range that fits. We never treat a range as an exact amount.';
    return '';
}

// Side panel shown on the intro screen and every question step. It's a
// placeholder built from CSS + an icon so the page never ships a broken
// image link — swap it for a real photo by setting a background-image on
// .sf-media in public/css/bakr-finder.css (the gradient stays as a fallback).
function sf_media($caption) {
    echo '<aside class="sf-media">'
       . '<i class="bi bi-mortarboard-fill sf-media-icon" aria-hidden="true"></i>'
       . '<div class="sf-media-cap"><small>DNSC · Davao del Norte State College</small>' . h($caption) . '</div>'
       . '</aside>';
}

const SF_CAT_CLASS = ['DNSC Internal' => 'green', 'Government / External' => 'gold', 'Private / NGO / Foundation' => 'sage'];

/* ---------- POST: validate on the server, then save ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], (string)($_POST['csrf'] ?? ''))) go('');
    $act = $_POST['act'] ?? '';
    if ($act === 'answer') {
        $k = (string)($_POST['k'] ?? ''); $v = $_POST['v'] ?? null;
        if (!isset($Q[$k]) || !in_array($k, active($A), true)) go('');
        if (is_string($v) && array_key_exists($v, opts($k, $A))) {
            $A[$k] = $v;
            if ($k === 'institute' && !in_array($A['course'] ?? '', $R['courses'][$v] ?? [], true)) unset($A['course']);
            if ($k === 'has_sch' && $v === 'no') unset($A['sch_type']);
            $ks = active($A); $next = $ks[array_search($k, $ks, true) + 1] ?? null;
            $miss = firstMissing($A);
            if (!empty($_POST['r'])) go($miss ? '?q=' . $miss . '&r=1' : '?v=review');   // came from Review: go back there
            go($next ? '?q=' . $next : '?v=review');
        }
        go('?q=' . urlencode($k) . '&e=1' . (!empty($_POST['r']) ? '&r=1' : ''));      // missing or invalid answer
    }
    if ($act === 'finish') {
        $miss = firstMissing($A);
        if ($miss) go('?q=' . $miss . '&e=1');
        $_SESSION['sf_done'] = true; go('?v=done');
    }
    if ($act === 'reset') { $_SESSION['sf'] = []; unset($_SESSION['sf_done']); go(''); }
    go('');
}

/* ---------- GET: decide what to show ---------- */
$v = $_GET['v'] ?? (isset($_GET['q']) ? 'q' : 'intro');
if ($v === 'intro' && (!empty($_SESSION['sf']) || !empty($_SESSION['sf_done']))) {   // opening the finder starts fresh: drop old answers
    $_SESSION['sf'] = []; unset($_SESSION['sf_done']);
}
$keys = active($A); $total = count($keys);
if ($v === 'q') { $k = $_GET['q'] ?? ''; if (!in_array($k, $keys, true)) go(''); }
if (in_array($v, ['done', 'results'], true) && (empty($_SESSION['sf_done']) || firstMissing($A))) go('?v=review');
if ($v === 'review' && ($m = firstMissing($A))) go('?q=' . $m);

$hits = [];
if ($v === 'results') { $eff = sf_effective($A); $hits = sf_prioritize(sf_match($eff, $R['scholarships']), $eff); }
$csrf = h($_SESSION['csrf']);
?>
@extends('layouts.app')

@section('title', 'Find a Scholarship | BAKR')
@section('description', 'Answer a few questions and BAKR lists the DNSC, government, and private scholarships whose published requirements fit your answers.')
@section('page', 'finder')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/bakr-finder.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('js/bakr-finder.js') }}"></script>
@endpush

@section('content')
<div class="sf-page<?= $v === 'q' ? ' sf-page--q' : '' ?>">
<?php if ($v === 'intro'): ?>
    <div class="sf-shell">
        <?php sf_media('Find scholarships that fit your story.'); ?>
        <div class="sf-card">
            <h1>Find scholarships that fit your story.</h1>
            <p>Answer a few questions about your academic background, finances, activities, and other qualifications. Your answers are checked against each scholarship's eligibility requirements.</p>
            <p class="sf-err" style="background:var(--gold-tint);color:var(--green-900);"><i class="bi bi-info-circle me-1"></i> Results are based on what you tell us. Many programs also require document verification or screening, which this questionnaire cannot do. No documents are needed here.</p>
            <div class="sf-nav" style="border:0;padding:0;margin-top:24px;justify-content:flex-start">
                <a class="sf-btn" href="?q=<?= h($keys[0]) ?>">Let's get started <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="sf-intro-grid">
                <div><i class="bi bi-mortarboard-fill" aria-hidden="true"></i><span>Academic Background</span></div>
                <div><i class="bi bi-cash-coin" aria-hidden="true"></i><span>Financial Background</span></div>
                <div><i class="bi bi-award-fill" aria-hidden="true"></i><span>Qualifications &amp; Activities</span></div>
                <div><i class="bi bi-info-circle-fill" aria-hidden="true"></i><span>Other Eligibility Info</span></div>
            </div>
        </div>
    </div>

<?php elseif ($v === 'q'): $i = array_search($k, $keys, true); $o = opts($k, $A); $r = !empty($_GET['r']); $cur = $r ? (string)($A[$k] ?? '') : ''; /* pre-select only when editing from Review */ ?>
    <div class="sf-shell">
        <?php sf_media(SECTIONS[$Q[$k]['s']]); ?>
        <form class="sf-card sf-card-q" method="POST" action="{{ asset('scholarship-finder') }}" novalidate autocomplete="off" id="qForm">
            <input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="act" value="answer">
            <input type="hidden" name="k" value="<?= h($k) ?>"><?php if ($r): ?><input type="hidden" name="r" value="1"><?php endif; ?>
            <div class="sf-top"><span class="sf-top-section">Section 0<?= $Q[$k]['s'] ?> · <?= h(SECTIONS[$Q[$k]['s']]) ?></span><span class="sf-top-count">Question <?= $i + 1 ?> of <?= $total ?></span></div>
            <div class="sf-track" role="progressbar" aria-valuemin="0" aria-valuemax="<?= $total ?>" aria-valuenow="<?= $i + 1 ?>"><div class="sf-fill" style="width:<?= round(($i + 1) / $total * 100) ?>%"></div></div>
            <div class="sf-qbody">
                <h2><?= h(qText($k, $A)) ?></h2>
                <p class="sf-hint sf-hint-slot"><?= h(qHint($k, $A)) ?></p>
                <div class="mt-2" id="answers">
                <?php if (!empty($_GET['e'])): ?><div class="sf-err" role="alert">Please choose an answer to continue.</div><?php endif; ?>
                <?php if ($k === 'course'): ?>
                    <select name="v" required aria-label="Course"><option value="">Select your course</option>
                    <?php foreach ($o as $val => $lab): ?><option value="<?= h($val) ?>" <?= $cur === (string)$val ? 'selected' : '' ?>><?= h($lab) ?></option><?php endforeach; ?></select>
                <?php else: foreach ($o as $val => $lab): ?>
                    <label class="sf-opt"><input type="radio" name="v" value="<?= h($val) ?>" required <?= $cur === (string)$val ? 'checked' : '' ?>><span class="sf-opt-label"><?= h($lab) ?></span><i class="bi bi-check-circle-fill sf-opt-check" aria-hidden="true"></i></label>
                <?php endforeach; endif; ?>
                </div>
            </div>
            <div class="sf-nav">
                <?php $prev = $i > 0 ? '?q=' . $keys[$i - 1] : '?v=intro'; ?>
                <a class="sf-btn ghost" href="<?= $r ? '?v=review' : h($prev) ?>"><i class="bi bi-arrow-left"></i> <?= $r ? 'Cancel' : 'Back' ?></a>
                <button class="sf-btn" type="submit"><?= $r ? 'Save' : ($i + 1 === $total ? 'Review answers' : 'Next') ?> <i class="bi bi-arrow-right"></i></button>
            </div>
        </form>
    </div>

<?php elseif ($v === 'review' || $v === 'done'): ?>
    <div class="sf-card sf-review-card">
        <div class="sf-review-head">
            <span class="sf-review-kicker"><i class="bi bi-shield-check"></i> Final Step</span>
            <h2>Review your answers</h2>
            <p>Check everything before you finish. Use Edit to change an answer without starting over.</p>
        </div>
        <div class="sf-review-form">
        <?php $sec = 0; foreach ($keys as $key): if ($Q[$key]['s'] !== $sec) { $sec = $Q[$key]['s']; ?>
            <div class="sf-rev-sec"><span class="sf-rev-sec-n">0<?= $sec ?></span><span class="sf-rev-sec-label"><i class="bi <?= h(SECTION_ICON[$sec] ?? 'bi-dot') ?> me-1"></i> <?= h(SECTIONS[$sec]) ?></span></div>
        <?php } ?>
            <div class="sf-rev-field">
                <label><?= h(qText($key, $A)) ?></label>
                <div class="sf-rev-value">
                    <span class="sf-rev-answer"><i class="bi bi-check-circle-fill"></i><span><?= h(opts($key, $A)[$A[$key]]) ?></span></span>
                    <a class="sf-rev-edit" href="?q=<?= h($key) ?>&r=1"><i class="bi bi-pencil-fill"></i> Edit</a>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
        <form method="POST" action="{{ asset('scholarship-finder') }}" class="sf-nav">
            <input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="act" value="finish">
            <a class="sf-btn ghost" href="?q=<?= h(end($keys)) ?>"><i class="bi bi-arrow-left"></i> Back</a>
            <button class="sf-btn gold" type="submit">Complete questionnaire</button>
        </form>
    </div>
    <?php if ($v === 'done'): ?>
    <div class="sf-modal" role="dialog" aria-modal="true" aria-labelledby="doneTitle">
        <div class="sf-card sf-done-card">
            <div class="sf-done-badge" aria-hidden="true">
                <span class="sf-confetti c1"></span><span class="sf-confetti c2"></span><span class="sf-confetti c3"></span>
                <span class="sf-confetti c4"></span><span class="sf-confetti c5"></span><span class="sf-confetti c6"></span>
                <i class="bi bi-check-lg"></i>
            </div>
            <h2 id="doneTitle">You're All Set!</h2>
            <p>We checked your answers against the eligibility requirements for every scholarship in the system — your matches are ready to view.</p>
            <span class="sf-done-stat"><i class="bi bi-list-check"></i> <?= $total ?> of <?= $total ?> questions answered</span>
            <div class="sf-done-actions">
                <a class="sf-btn gold" href="?v=results">View My Results <i class="bi bi-arrow-right"></i></a>
                <a class="sf-btn ghost" href="{{ asset('/') }}">Go to Homepage</a>
            </div>
        </div>
    </div>
    <?php endif; ?>

<?php elseif ($v === 'results'): ?>
    <section class="sf-results-hero" aria-labelledby="resultsTitle">
        <div class="sf-results-kicker">Your path forward. <span aria-hidden="true"></span></div>
        <div class="sf-results-hero-row">
            <div class="sf-results-hero-text">
                <h2 id="resultsTitle" class="sf-results-title"><?= $hits ? 'Your Scholarship <em>Matches</em>' : 'No Matching Scholarships Found' ?></h2>
                <?php if ($hits): ?><p class="sf-results-intro">Based on the information you provided, these scholarships meet the eligibility criteria currently encoded in the system. This is not a guarantee of selection.</p>
                <?php else: ?><p class="sf-results-intro">Based on the information you provided, we could not identify a scholarship that currently meets all of the eligibility criteria available in the system.</p><p class="sf-hint">Some scholarship programs may require document verification, screening, ranking, available slots, or other qualifications that cannot be determined through this questionnaire.</p><?php endif; ?>
            </div>
            <?php if ($hits): ?><div class="sf-results-count"><i class="bi bi-card-checklist"></i><strong><b data-count="<?= count($hits) ?>"><?= count($hits) ?></b> found</strong><span>Scholarships that match<br>your profile</span></div><?php endif; ?>
        </div>
    </section>
    <?php if ($hits): ?><div class="sf-results-list">
    <?php foreach ($hits as $idx => $s): $cc = SF_CAT_CLASS[$s['category']] ?? 'green'; ?>
    <article class="sf-result-row sf-result-row--<?= $cc ?><?= $idx === 0 ? ' sf-result-row--featured' : '' ?>" style="--i:<?= (int)$idx ?>">
        <?php if ($idx === 0): ?><div class="sf-best-match"><i class="bi bi-star-fill"></i><strong>BEST<br>MATCH</strong><span><i class="bi bi-award-fill"></i></span></div><?php endif; ?>
        <div class="sf-result-main">
            <span class="sf-res-seal"><img src="images/logo/<?= h($s['logo']) ?>" alt="<?= h($s['name']) ?> logo"></span>
            <div class="sf-result-copy">
                <div class="sf-result-meta"><span class="sf-cat"><?= h($s['category']) ?></span><span class="sf-badge"><i class="bi bi-check2-circle me-1"></i>Meets Eligibility Criteria</span></div>
                <h3><?= h($s['name']) ?></h3>
                <p><?= h($s['desc']) ?></p>
                <div class="sf-result-chips"><?php foreach ($s['met'] as $m): ?><span class="sf-chip"><i class="bi bi-check2 me-1"></i><?= h($m) ?></span><?php endforeach; ?></div>
                <?php if (!empty($s['closed'])): ?><p class="sf-closed mb-0"><i class="bi bi-calendar-x me-1"></i><?= h($s['closed']) ?></p><?php endif; ?>
            </div>
        </div>
        <div class="sf-result-action"><span class="sf-top-label"><?= $idx === 0 ? 'Your top match' : 'Profile fit' ?></span><?php if ($idx === 0): ?><strong>An excellent fit for your profile!</strong><p>You meet most of the eligibility criteria for this scholarship.</p><?php endif; ?><button type="button" class="sf-btn<?= $idx === 0 ? '' : ' ghost' ?>" data-bs-toggle="modal" data-bs-target="#sfDetails-<?= h($s['id']) ?>">View Details <i class="bi bi-arrow-right"></i></button><a class="sf-official-link" href="<?= h(sf_official_url($s)) ?>" target="_blank" rel="noopener">Official Website <i class="bi bi-box-arrow-up-right"></i></a></div>
    </article>
    <?php endforeach; ?></div><?php endif; ?>

    <?php foreach ($hits as $s): ?>
    <div class="modal fade" id="sfDetails-<?= h($s['id']) ?>" tabindex="-1" aria-hidden="true" aria-labelledby="sfDetailsTitle-<?= h($s['id']) ?>">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content sf-dmodal">
                <div class="sf-dmodal-head">
                    <span class="sf-dmodal-sticker">BAKR Match</span>
                    <div class="sf-dmodal-brand">
                        <img src="images/logo/<?= h($s['logo']) ?>" alt="<?= h($s['name']) ?> logo">
                        <div>
                            <span class="sf-dmodal-cat"><?= h($s['category']) ?></span>
                            <h3 id="sfDetailsTitle-<?= h($s['id']) ?>"><?= h($s['name']) ?></h3>
                            <p><?= h($s['desc']) ?></p>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body sf-dmodal-body">
                    <?php if (!empty($s['closed'])): ?>
                        <div class="sf-dmodal-section"><p class="sf-closed mb-0"><i class="bi bi-calendar-x me-1"></i><?= h($s['closed']) ?></p></div>
                    <?php endif; ?>
                    <div class="sf-dmodal-section">
                        <div class="sf-dmodal-section-title"><i class="bi bi-check2-circle"></i>Why You Qualify</div>
                        <div class="sf-dmodal-pills">
                        <?php foreach ($s['met'] as $m): ?><span class="sf-dmodal-pill"><i class="bi bi-check2"></i><?= h($m) ?></span><?php endforeach; ?>
                        </div>
                    </div>
                    <div class="sf-dmodal-section">
                        <div class="sf-dmodal-section-title"><i class="bi bi-folder2-open"></i>Possible Documents to Prepare</div>
                        <p class="sf-doc-note">These are common examples only, not a confirmed checklist for this specific scholarship. Follow the current official announcement for exact requirements.</p>
                        <ul class="sf-dmodal-docs">
                            <li><i class="bi bi-file-earmark-check"></i> Scholarship application form, if required</li>
                            <li><i class="bi bi-card-text"></i> Certificate of Registration / proof of enrollment or admission</li>
                            <li><i class="bi bi-journal-text"></i> Latest grades, report card, or Transcript of Records</li>
                            <li><i class="bi bi-person-vcard"></i> Valid ID and/or birth certificate, if requested</li>
                            <li><i class="bi bi-house-heart"></i> Proof of family income or certificate of indigency, if applicable</li>
                            <li><i class="bi bi-image"></i> Recent ID photo and any program-specific forms or certificates</li>
                        </ul>
                    </div>
                    <div class="sf-dmodal-section mb-0">
                        <div class="sf-dmodal-section-title"><i class="bi bi-info-circle"></i>Good to Know</div>
                        <p>This match is based only on the answers you gave in the questionnaire. Some programs still require document verification, screening, ranking, interviews, or available slots before an award is final. Requirements and application periods can change, so confirm the current details with the scholarship provider.</p>
                    </div>
                </div>
                <div class="sf-dmodal-foot">
                    <button type="button" class="sf-btn ghost" data-bs-dismiss="modal">Close</button>
                    <a class="sf-btn gold" href="<?= h(sf_official_url($s)) ?>" target="_blank" rel="noopener">Official Website <i class="bi bi-box-arrow-up-right"></i></a>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <div class="sf-results-foot d-flex justify-content-center gap-2 flex-wrap mt-4">
        <a class="sf-btn ghost" href="?v=review">Review My Answers</a>
        <a class="sf-btn" href="{{ asset('/') }}">Back to Home</a>
    </div>
<?php endif; ?>
</div>
@endsection
