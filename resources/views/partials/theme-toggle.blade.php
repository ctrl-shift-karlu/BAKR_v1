{{-- Light / dark switch. public/js/bakr-theme.js flips it and sets aria-checked; the look is in
     public/css/bakr-dark.css (.bakr-switch). It always sits on a dark green or photo background.
     $class:     extra classes for where it sits (e.g. "is-nav", "is-auth")
     $withLabel: show the words "Dark mode" next to it (navbar menu on phones) --}}
<button type="button" class="bakr-switch {{ $class ?? '' }}" role="switch" aria-checked="false" aria-label="Dark mode" title="Dark mode" data-theme-toggle>
    <span class="bakr-switch-track" aria-hidden="true">
        <i class="bi bi-sun-fill bakr-switch-sun"></i>
        <i class="bi bi-moon-stars-fill bakr-switch-moon"></i>
        <span class="bakr-switch-knob"></span>
    </span>
    @isset($withLabel)<span class="bakr-switch-label">Dark mode</span>@endisset
</button>
