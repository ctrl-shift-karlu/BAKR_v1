<?php

return [
    // The project's full name (owner, 2026-11-20) and the same without the "BAKR - " prefix (for places
    // that already show "BAKR" next to it: navbar, sign-in card, footer). The APP's name stays just "BAKR"
    // (APP_NAME, manifest name / short_name, home-screen label).
    'system_name' => 'BAKR - DNSC Scholarship Matching and Monitoring System',
    'tagline' => 'DNSC Scholarship Matching and Monitoring System',

    // Where messages from the Contact page are emailed. Set BAKR_CONTACT_TO in .env.
    'contact_to' => env('BAKR_CONTACT_TO', 'bakr.dnsc@gmail.com'),

    // The official DNSC scholarship office. Scholars of providers that do not collect maintaining
    // documents in BAKR (features without 'requirements') submit them there in person.
    'scholarship_office' => env('BAKR_SCHOLARSHIP_OFFICE', 'DNSC Scholarship and Grants Unit'),

    // Privacy Policy (resources/views/privacy.blade.php, route 'privacy'), written under the Data Privacy Act of 2012
    // (Republic Act No. 10173). Change the date whenever the policy text changes.
    'privacy_updated' => '2026-11-21',

    // The administrator account, created by AdminAccountSeeder (there is no admin sign-up).
    // Set ADMIN_EMAIL / ADMIN_PASSWORD in .env (password: at least 12 characters). Never commit them.
    'admin' => [
        'name' => env('ADMIN_NAME', 'BAKR Administrator'),
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],
];
