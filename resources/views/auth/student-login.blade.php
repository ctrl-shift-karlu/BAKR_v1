{{-- Student (matching) login: StudentAuthController. Scholars have their own login. --}}
@extends('layouts.auth')

@section('title', 'Student Login')
@section('body', 'is-student')
@section('portal', 'Student Portal')

@section('intro_eyebrow', 'BAKR Student Portal')
@section('intro_title')Welcome back. Your <em>matches</em> are waiting.@endsection
@section('intro_text', 'Sign in to see your scholarship matches and keep your answers up to date.')
@section('intro_points')
    <li><i class="bi bi-ui-checks" aria-hidden="true"></i><span>Tell BAKR about your studies once and see the scholarships that fit you.</span></li>
    <li><i class="bi bi-shield-check" aria-hidden="true"></i><span>Your answers are encrypted, and you are the only one who can see them.</span></li>
    <li><i class="bi bi-arrow-repeat" aria-hidden="true"></i><span>Change your answers any time and your matches update.</span></li>
@endsection

@section('heading', 'Student login')
@section('help', 'Sign in to your BAKR student account.')

@section('card')
    @include('auth.partials.login-form', ['action' => 'student.login', 'from' => 'student', 'remember' => true])
@endsection

@section('foot')
    <p>New to BAKR? <a href="{{ route('student.register') }}">Create a student account</a></p>
    <p>Already holding a scholarship? Use the <a href="{{ route('scholar.login') }}">Scholar login</a>.</p>
@endsection
