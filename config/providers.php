<?php

/*
 * Scholarship providers who can sign in to the Provider Portal (/providerLogin).
 * This is the ONLY place that maps a provider to its scholarships (by slug from
 * config/scholarships.php). A provider account sees only these slugs.
 *
 * Account e-mails and passwords come from .env (PROVIDER_<KEY>_EMAIL / _PASSWORD) and are
 * used only by ProviderAccountSeeder, which hashes the password. Never commit real values.
 *
 * 'features': the OPTIONAL portal pages this provider uses (ProviderScope::OPTIONAL_FEATURES):
 *   disbursements  stipend releases per semester / month
 *   requirements   maintenance (renewal) requirements scholars upload each term
 * Overview, Scholars, My scholarships and Help are always on (there is no Applicants page). A provider
 * without a feature has no menu item, gets 404 on its pages, and its scholars don't see it.
 *
 * 'website': the provider's OWN official homepage (owner, 2026-11-21), opened by the portal sidebar's
 * "Official website" link (ProviderScope::website()). Checked 2026-11-21; ched.gov.ph and dnsc.edu.ph refuse
 * automated requests (403) but open in a browser. Change it here only to the provider's own official domain.
 */

return [
    'accounts' => [
        'ched' => [
            'name' => 'CHED / UniFAST',
            'logo' => 'images/logo/CHED-Logo.png',
            'icon' => 'bi-bank',
            'website' => 'https://ched.gov.ph/',
            // Every CHED-run scholarship lives here, CoScho included (owner, 2026-11-18): one account per
            // ADMINISTERING provider, never a separate account per scholarship of the same provider.
            'slugs' => ['ched-tdp', 'ched-tes', 'ched-cmsp', 'bpmsp', 'coscho'],
            'features' => ['disbursements', 'requirements'],
            'email' => env('PROVIDER_CHED_EMAIL'),
            'password' => env('PROVIDER_CHED_PASSWORD'),
        ],
        'dost' => [
            'name' => 'DOST – Science Education Institute',
            'logo' => 'images/logo/DOST-Logo.png',
            'icon' => 'bi-cpu',
            'website' => 'https://www.sei.dost.gov.ph/',
            'slugs' => ['dost-sei'],
            'features' => ['disbursements', 'requirements'],
            'email' => env('PROVIDER_DOST_EMAIL'),
            'password' => env('PROVIDER_DOST_PASSWORD'),
        ],
        'cebuana' => [
            'name' => 'Cebuana Lhuillier Foundation',
            'logo' => 'images/logo/CEBUANA-Logo.jpg',
            'icon' => 'bi-heart',
            'website' => 'https://cebuanafoundation.org/',
            'slugs' => ['cebuana-clfi'],
            'features' => ['disbursements', 'requirements'],
            'email' => env('PROVIDER_CEBUANA_EMAIL'),
            'password' => env('PROVIDER_CEBUANA_PASSWORD'),
        ],
        'dnsc' => [
            'name' => 'DNSC Scholarship and Grants Unit',
            'logo' => 'images/logo/dnss_logo.jpg',
            'icon' => 'bi-mortarboard',
            'website' => 'https://dnsc.edu.ph/',
            'slugs' => ['dnsc-academic', 'dnsc-gia-a', 'dnsc-gia-b', 'dnsc-sa-c'],
            'features' => [],   // owner's decision 2026-11-17: no Disbursements or Requirements pages
            'email' => env('PROVIDER_DNSC_EMAIL'),
            'password' => env('PROVIDER_DNSC_PASSWORD'),
        ],
        // Added 2026-11-18. LANDBANK and BFAR (Department of Agriculture) are their own administering
        // providers, so they keep their own accounts. CoScho is CHED's and is in 'ched' above (its old
        // 'coscho' login was deactivated by migration 2026_11_18_000002). Logos added 2026-11-19: the official
        // LANDBANK and BFAR marks (Wikimedia Commons, public domain, credited to landbank.com / bfar.da.gov.ph).
        'landbank' => [
            'name' => 'LANDBANK Scholarship Program',
            'logo' => 'images/logo/LANDBANK-Logo.svg',
            'icon' => 'bi-bank2',
            'website' => 'https://www.landbank.com/',
            'slugs' => ['landbank-ilp'],
            'features' => ['disbursements', 'requirements'],
            'email' => env('PROVIDER_LANDBANK_EMAIL'),
            'password' => env('PROVIDER_LANDBANK_PASSWORD'),
        ],
        'bfar' => [
            'name' => 'BFAR – Fisheries Scholarship Program',
            'logo' => 'images/logo/BFAR-Logo.svg',
            'icon' => 'bi-water',
            'website' => 'https://www.bfar.da.gov.ph/',
            'slugs' => ['bfar-fsp'],
            'features' => ['disbursements', 'requirements'],
            'email' => env('PROVIDER_BFAR_EMAIL'),
            'password' => env('PROVIDER_BFAR_PASSWORD'),
        ],
        'grab' => [
            'name' => 'Grab Philippines',
            'logo' => 'images/logo/GRAB-Logo.svg',
            'icon' => 'bi-briefcase',
            'website' => 'https://www.grab.com/ph/',
            'slugs' => ['grabscholar'],
            'features' => ['disbursements', 'requirements'],
            'email' => env('PROVIDER_GRAB_EMAIL'),
            'password' => env('PROVIDER_GRAB_PASSWORD'),
        ],
    ],
];
