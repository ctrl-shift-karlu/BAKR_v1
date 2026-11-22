@extends('layouts.app')

{{-- Public Scholarships page. Every card and modal is rendered from the `scholarships` table —
     the same rows providers edit in the Provider Portal — so the page cannot drift from them.
     The previous hand-written version is in docs/changes/backups/scholarships.blade.php.2026-11-12.bak --}}

@section('title', 'Scholarships | BAKR')
@section('description', 'Browse the '.$total.' scholarships in BAKR for DNSC students: DNSC, government and private scholarships, with who qualifies, what you receive and what you need.')
@section('page', 'scholarships')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/bakr-pages.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bakr-scholarships.css') }}">
@endpush

@section('content')
<header class="bk-page-hero" style="--page-hero-image: url('{{ asset('images/img/opt/dnsc_gate.jpg') }}')">
    <div class="container">
        <ol class="bk-crumbs" aria-label="Breadcrumb">
            <li><a href="{{ url('/') }}">Home</a></li>
            <li aria-current="page">Scholarships</li>
        </ol>
        <h1>Browse the <em>scholarship shelf</em></h1>
        <p>Browse the {{ $total }} scholarship programs tracked by BAKR. Choose a sector, then open View Details to see the full qualifications, benefits, requirements, and application information.</p>
    </div>
</header>

@include('partials.scholarship-catalogue')
@endsection

@push('scripts')
    <script src="{{ asset('js/bakr-scholarships.js') }}"></script>
@endpush
