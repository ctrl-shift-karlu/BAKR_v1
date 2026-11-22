{{-- Qualifications rendered from $scholarship->rules (via Scholarship::qualifications()).
     Used by the public Scholarships modals and the Provider Portal preview, so both always agree.
     $missing: text shown when there are no rules. --}}
@php($quals = $scholarship->qualifications())
@if (empty($quals))
    <p class="pv-missing">{{ $missing }}</p>
@else
    <ul class="pv-quals">
        @foreach ($quals as $q)
            <li class="{{ $q['confirm'] ? 'is-confirm' : '' }}">
                <div class="pv-qual-main">
                    <span class="pv-qual-label">{{ $q['label'] }}</span>
                    <span class="pv-tags">
                        <span class="pv-tag {{ $q['required'] ? 'pv-tag-req' : 'pv-tag-opt' }}">{{ $q['required'] ? 'Required' : 'Optional' }}</span>
                        @if ($q['confirm'])
                            <span class="pv-tag pv-tag-confirm">Confirm with provider</span>
                        @endif
                    </span>
                </div>
                @if ($q['any_of'])
                    <ul class="pv-anyof" aria-label="Any one of">
                        @foreach ($q['any_of'] as $sub)
                            <li>{{ $sub }}</li>
                        @endforeach
                    </ul>
                @endif
                @if ($q['note'])
                    <p class="pv-qual-note"><i class="bi bi-chat-square-text" aria-hidden="true"></i>{{ $q['note'] }}</p>
                @endif
            </li>
        @endforeach
    </ul>
@endif
