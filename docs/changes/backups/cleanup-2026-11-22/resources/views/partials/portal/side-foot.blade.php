{{-- Bottom of EVERY portal sidebar (student, scholar, provider, admin): who is signed in, and the one
     Sign out, always visible (never hidden in a collapsible group). Opens the shared red confirmation
     (partials/signout-dialog, included by each layout).
     $name, $sub: the two lines. Avatar: $logo (image path) | $icon (bootstrap icon) | $initials. --}}
<div class="dash-side-foot">
    @include('partials.pwa-install')
    <div class="dash-user">
        <span class="dash-avatar is-lg is-1" aria-hidden="true">
            @if (! empty($logo))<img src="{{ asset($logo) }}" alt="">
            @elseif (! empty($icon))<i class="bi {{ $icon }}"></i>
            @else{{ $initials ?? 'B' }}@endif
        </span>
        <span class="dash-user-text"><strong title="{{ $name }}">{{ $name }}</strong><small>{{ $sub }}</small></span>
    </div>
    <a href="#confirm-signout" class="dash-signout" data-open-dialog="confirm-signout"><i class="bi bi-box-arrow-right" aria-hidden="true"></i>Sign out</a>
</div>
