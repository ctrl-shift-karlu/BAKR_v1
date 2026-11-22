@extends('student.layout')
@section('title', 'Help | BAKR')

@section('hero_title')
Help &amp; <em>contact</em>
@endsection
@section('hero_sub', 'What BAKR is, how matching works, and a direct line to the BAKR team, without leaving your portal.')

@section('portal')
@include('partials.portal.help', [
    'faqTitle' => 'Questions you may have',
    'messageRoute' => 'student.help.message',
    'faqs' => [
        ['Does an "Eligible" result mean I got the scholarship?', 'No. BAKR only compares your answers with each scholarship\'s published requirements. The DNSC Scholarship and Grants Unit or the provider makes the final decision after you apply with them.'],
        ['How do I apply?', 'Open My matches, choose an eligible or nearly eligible match, and follow "How to apply" in its details. You apply with the provider directly; BAKR does not send your answers.'],
        ['Why does a scholarship say "Needs more information"?', 'One of its required checks needs an answer you have not given yet. Open My matching form, answer it, then come back to your matches.'],
        ['Who decides my matches, the rules or the AI?', 'Both. BAKR\'s rules check each scholarship\'s published limits (year level, grades, income, program). Then the Gemini AI matcher reads your whole application against each scholarship\'s eligibility and requirements. BAKR shows the stricter of the two, so the AI can never pass you on a limit you fail. The AI never sees your name, e-mail or birth date.'],
        ['Can I change my answers?', 'Yes, any time. BAKR saves a new version and re-checks every scholarship. Your previous versions stay in My profile.'],
        ['Who can see my answers?', 'Only you. Answers are encrypted and are never sent to providers. Providers see only aggregate counts.'],
        ['What happens when a provider accepts me?', 'The provider adds you to BAKR, which makes your e-mail a scholar account. From then on you sign in at the Scholar login to follow your scholarship, stipends and the documents to submit each semester. Matching is not part of the scholar portal.'],
    ],
])
@endsection
