{{-- Where a scholarship's current call stands, from the provider's dates (Scholarship::callStatus()).
     Shows nothing when the provider has not published dates, so BAKR never guesses a deadline.
     $scholarship: App\Models\Scholarship   $compact: true for a one-line pill (match rows) --}}
@php
    $callStatus = $scholarship->callStatus();
    $callIcon = ['open' => 'bi-door-open', 'upcoming' => 'bi-calendar-plus', 'closed' => 'bi-calendar-x'][$callStatus] ?? null;
@endphp
@if ($callStatus)
    @if (! empty($compact))
        <span class="bk-call is-{{ $callStatus }} is-compact"><i class="bi {{ $callIcon }}" aria-hidden="true"></i>{{ $scholarship->callLabel() }}</span>
    @else
        <p class="bk-call is-{{ $callStatus }}">
            <i class="bi {{ $callIcon }}" aria-hidden="true"></i>
            <span>
                <strong>{{ $scholarship->callLabel() }}</strong>
                @if ($scholarship->application_opens_on && $scholarship->application_closes_on)
                    <small>Applications {{ $scholarship->application_opens_on->format('M j') }} – {{ $scholarship->application_closes_on->format('M j, Y') }}</small>
                @endif
                @if ($scholarship->results_on)
                    <small>Results {{ $scholarship->results_on->isFuture() ? 'on' : 'released' }} {{ $scholarship->results_on->format('M j, Y') }}</small>
                @endif
            </span>
        </p>
    @endif
@endif
