{{-- Saved versions of the student's own answers (StudentDashboardController::history), newest first.
     Earlier versions open their own page; $viewing = the version number on screen (optional). --}}
@php $viewing = $viewing ?? null; @endphp
<section class="sp-card">
    <h2>Saved versions</h2>
    <p class="sp-sub">Each time you change your answers, BAKR keeps the earlier version. Open one to see what you answered then, or to use those answers again.</p>
    <ul class="sp-hist">
        @foreach ($history as $h)
            @php
                $what = $h['changes'] === null ? 'First form' : ($h['changes'] === 0 ? 'No answers changed' : $h['changes'].' '.str('answer')->plural($h['changes']).' changed');
            @endphp
            <li @class(['is-current' => $h['is_current'], 'is-viewing' => $viewing === $h['version']])>
                @if ($h['is_current'])
                    <a href="{{ route('student.profile') }}" @if ($viewing === null) aria-current="page" @endif>
                @else
                    <a href="{{ route('student.profile.version', $h['version']) }}" @if ($viewing === $h['version']) aria-current="page" @endif>
                @endif
                    <span class="sp-hist-name"><i class="bi {{ $h['is_current'] ? 'bi-check-circle-fill' : 'bi-clock-history' }}" aria-hidden="true"></i>Version {{ $h['version'] }}{{ $h['is_current'] ? ' (current)' : '' }}</span>
                    <span class="sp-hist-meta">
                        <span>{{ $h['submitted_at']?->format('M j, Y g:i A') }}</span>
                        <span>{{ $what }}</span>
                    </span>
                    <i class="bi bi-chevron-right sp-hist-go" aria-hidden="true"></i>
                </a>
            </li>
        @endforeach
    </ul>
</section>
