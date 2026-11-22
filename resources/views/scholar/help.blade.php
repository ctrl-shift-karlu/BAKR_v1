@extends('scholar.layout')
@section('title', 'Help | BAKR')

@section('hero_title')
Help &amp; <em>contact</em>
@endsection
@section('hero_sub', 'What BAKR is, how your scholar portal works, and a direct line to the BAKR team.')

@section('portal')
@include('partials.portal.help', [
    'faqTitle' => 'Questions scholars ask',
    'audience' => 'scholar',
    'messageRoute' => 'scholar.help.message',
    'faqs' => [
        ['Where do I submit my maintaining documents?', 'Open Requirements. Each scholarship has its own list. When your provider sets a semester\'s deadline, you get an e-mail and an Upload button appears next to every document. If they change the deadline, you are told again. Your file goes only to that scholarship\'s provider.'],
        ['What do "Waiting for review" and "Needs revision" mean?', 'Waiting for review: your provider has your file and has not checked it yet. Needs revision: your provider wants a new file; read their note, then upload again with Replace.'],
        ['When is my stipend released?', 'Only after ALL your requirements for that semester are approved by your provider. My scholarship › Stipends shows every release, and whether it is released, not yet released, or waiting for your requirements. Your provider decides the dates.'],
        ['Some scholarships say "submit directly to the provider". Why?', 'Those providers collect documents and pay stipends outside BAKR. Follow their instructions and contact their office.'],
        ['How do I change my password?', 'Open Settings › Edit password. If you forgot it, use "E-mail me a reset link" on the same page, or "Forgot password?" on the Scholar login page.'],
        ['Can I still use scholarship matching?', 'No. This is a scholar account: it follows the scholarships you already hold. Matching is for student accounts (Student login).'],
        ['My details are wrong. How do I fix them?', 'Your scholar details are kept by your provider. Open Profile and press "Request a correction": your request goes straight to that scholarship\'s provider (not the BAKR team), and their reply shows on your Profile and arrives by e-mail.'],
    ],
])
@endsection
