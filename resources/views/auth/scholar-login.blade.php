{{-- Scholar login: ScholarAuthController. Scholar accounts are created by providers when they add a
     scholar; separate from the student (matching) login. --}}
@extends('layouts.auth')

@section('title', 'Scholar Login')
@section('body', 'is-scholar')
@section('portal', 'Scholar Portal')

@section('intro_eyebrow', 'BAKR Scholar Portal')
@section('intro_title')Your scholarship, <em>all in one place.</em>@endsection
@section('intro_text', 'Check your status, follow your stipends and keep your maintaining documents complete every semester.')
@section('intro_points')
    <li><i class="bi bi-patch-check" aria-hidden="true"></i><span>See your scholarship status and what you need to keep it.</span></li>
    <li><i class="bi bi-cash-coin" aria-hidden="true"></i><span>Track each stipend release, from pending to released.</span></li>
    <li><i class="bi bi-cloud-arrow-up" aria-hidden="true"></i><span>Upload the documents your provider asks for each semester.</span></li>
@endsection

@section('heading', 'Scholar login')
@section('help', 'Sign in with the e-mail your provider registered for you. We e-mailed your first password to that address.')

@section('card')
    @include('auth.partials.login-form', ['action' => 'scholar.login', 'from' => 'scholar', 'remember' => true])
@endsection

@section('foot')
    <p>Not a scholar yet? Go to the <a href="{{ route('student.login') }}">Student login</a> to find scholarships.</p>
    <p>No scholar account? <a href="{{ route('contact') }}">Contact the BAKR team</a></p>
    {{-- Lost e-mail (2026-11-23): the provider has the scholar's mobile number and can change the sign-in e-mail. --}}
    <p>Lost access to your e-mail? Call or text your scholarship provider: they have your mobile number and can change your sign-in e-mail.</p>
@endsection
