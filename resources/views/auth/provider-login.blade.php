{{-- Provider login: ProviderAuthController. One account per administering provider (config/providers.php). --}}
@extends('layouts.auth')

@section('title', 'Provider Login')
@section('body', 'is-provider')
@section('portal', 'Provider Portal')

@section('intro_eyebrow', 'BAKR Provider Portal')
@section('intro_title')Your scholars, <em>all semester long.</em>@endsection
@section('intro_text', 'Sign in to keep your scholarship information current and follow your scholars\' documents and stipends.')
@section('intro_points')
    <li><i class="bi bi-people" aria-hidden="true"></i><span>Add your official scholars; each one gets their own BAKR login.</span></li>
    <li><i class="bi bi-folder-check" aria-hidden="true"></i><span>Review your scholars' maintaining documents and release their stipends once everything is approved.</span></li>
    <li><i class="bi bi-journal-check" aria-hidden="true"></i><span>Keep the scholarship details that students see on the public page up to date.</span></li>
@endsection

@section('heading', 'Provider login')
@section('help', 'Sign in with the account BAKR created for your office.')

@section('card')
    @include('auth.partials.login-form', ['action' => 'provider.login', 'from' => 'provider'])
@endsection

@section('foot')
    <p>Need an account? <a href="{{ route('contact') }}">Contact the BAKR team</a></p>
@endsection
