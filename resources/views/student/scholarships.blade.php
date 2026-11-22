@extends('student.layout')
@section('title', 'Scholarships | BAKR')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/bakr-scholarships.css') }}">
@endpush

@section('hero_title')
All <em>scholarships</em>
@endsection
@section('hero_sub')
Every scholarship BAKR tracks, with your own result on each card. Open a card for the full details.
@endsection

@section('portal')
<div class="sch-in-portal">
    @include('partials.scholarship-catalogue', ['matches' => $matches])
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/bakr-scholarships.js') }}"></script>
@endpush
