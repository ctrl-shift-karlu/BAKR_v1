<?php

/*
 * DNSC campuses and degree programs for the matching form (campus → program lists).
 *
 * Programs per campus: DNSC's official page "Program Offering by Campus",
 * https://dnsc.edu.ph/program-offering-by-campus/ (read 2026-11-19; the page itself shows no date), checked
 * against DNSC and CHED RO XI news (Carmen: BPEd + BS Criminology; Asuncion: BSIT first; Talaingod: Agro-Forestry
 * only). Notes: docs/sources/dnsc/program-offering-by-campus/NOTES.md. When DNSC opens a program at a campus,
 * add it to that campus below, then run:  php artisan db:seed --class=DnscCatalogSeeder
 * The form then shows only those programs for that campus, the server refuses any other program for it,
 * and programs removed from a campus disappear from the list. Do not guess: use DNSC's official list.
 *
 * The $institutes list (from the owner's officer-dashboard prototype) is only the master list of program names.
 *
 * Program names are written EXACTLY as the program_in rules in config/scholarships.php expect
 * (no "(BSIT)" suffix), because DOST-SEI matching compares the program name exactly.
 * Matching uses only the program name; the campus is shown on the student's answers.
 */

$institutes = [
    'Institute of Computing (IC)' => [
        'Bachelor of Science in Information Systems',
        'Bachelor of Science in Information Technology',
    ],
    'Institute of Aquatic and Applied Sciences (IAAS)' => [
        'Bachelor of Science in Agro-Forestry',
        'Bachelor of Science in Fisheries and Aquatic Sciences',
        'Bachelor of Science in Food Technology',
        'Bachelor of Science in Marine Biology',
    ],
    'Institute of Leadership, Entrepreneurship, and Good Governance (ILEGG)' => [
        'Bachelor of Public Administration',
        'Bachelor of Science in Criminology',
        'Bachelor of Science in Disaster Resilience and Management',
        'Bachelor of Science in Entrepreneurship',
        'Bachelor of Science in Social Work',
        'Bachelor of Science in Tourism Management',
    ],
    'Institute of Teacher Education (ITED)' => [
        'Bachelor of Arts in Communication',
        'Bachelor of Secondary Education - English',
        'Bachelor of Secondary Education - Mathematics',
        'Bachelor of Secondary Education - Science',
        'Bachelor of Technology and Livelihood Education',
        'Bachelor of Physical Education',
    ],
];

// The three programs that the Main (Panabo) campus does NOT offer: each has its own campus below.
$notAtMain = [
    'Bachelor of Science in Agro-Forestry',   // Talaingod
    'Bachelor of Science in Criminology',     // Carmen
    'Bachelor of Physical Education',         // Carmen
];

return [
    'institutes' => $institutes,

    // campus name => [location, programs offered]. The first campus is the main one (id 1 in the database).
    'campuses' => [
        'Main (Panabo Campus)' => ['location' => 'Panabo City', 'programs' => array_values(array_diff(array_merge(...array_values($institutes)), $notAtMain))],   // 15 programs
        'Carmen Campus' => ['location' => 'Carmen', 'programs' => [
            'Bachelor of Physical Education',
            'Bachelor of Science in Criminology',
        ]],
        'Samal Campus' => ['location' => 'Island Garden City of Samal', 'programs' => [   // "Igacos Campus" on some DNSC pages
            'Bachelor of Science in Information Technology',
            'Bachelor of Science in Marine Biology',
        ]],
        'Talaingod Campus' => ['location' => 'Talaingod', 'programs' => [   // "DJAL Talaingod Campus" on some DNSC pages
            'Bachelor of Science in Agro-Forestry',
        ]],
        'Asuncion Campus' => ['location' => 'Asuncion', 'programs' => [   // extension classes began AY 2026-2027; more programs are planned
            'Bachelor of Science in Information Technology',
        ]],
        'Santo Tomas Campus' => ['location' => 'Santo Tomas', 'programs' => [
            'Bachelor of Secondary Education - Mathematics',
        ]],
    ],
];
