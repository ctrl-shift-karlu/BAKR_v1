@extends('scholar.layout')
@section('title', 'Scholarships | BAKR')

{{-- Scholar portal › Explore › All scholarships: the same catalogue as the public page, without
     match results (scholar accounts have no matching). --}}

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/bakr-scholarships.css') }}">
@endpush

@section('hero_title')
All <em>scholarships</em>
@endsection
@section('hero_sub')
Every scholarship BAKR tracks. Open a card for the full details.
@endsection

@section('portal')
<div class="sch-in-portal">
    @include('partials.scholarship-catalogue', ['matches' => collect()])
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/bakr-scholarships.js') }}"></script>
@endpush
