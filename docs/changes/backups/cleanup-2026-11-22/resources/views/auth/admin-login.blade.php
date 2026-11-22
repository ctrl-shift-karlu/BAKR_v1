{{-- Admin login: AdminAuthController. The one account comes from AdminAccountSeeder (.env). --}}
@extends('layouts.auth')

@section('title', 'Admin Login')
@section('body', 'is-admin')
@section('portal', 'Admin Portal')

@section('intro_eyebrow', 'BAKR Admin Portal')
@section('intro_title')One platform, <em>every DNSCian.</em>@endsection
@section('intro_text', 'See how BAKR is used for matching and for monitoring scholars, and look after every account.')
@section('intro_points')
    <li><i class="bi bi-graph-up" aria-hidden="true"></i><span>See matching and scholar analytics, as totals only.</span></li>
    <li><i class="bi bi-people" aria-hidden="true"></i><span>Look after student, scholar and provider accounts.</span></li>
    <li><i class="bi bi-chat-left-text" aria-hidden="true"></i><span>Answer messages and approve the feedback that appears on the public site.</span></li>
@endsection

@section('heading', 'Admin login')
@section('help', 'Sign in with your BAKR administrator account.')

@section('card')
    @include('auth.partials.login-form', ['action' => 'admin.login', 'from' => 'admin'])
@endsection
