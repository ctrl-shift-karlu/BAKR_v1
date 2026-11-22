{{-- Phone / tablet bar for every portal: brand + burger that opens the sidebar drawer
     (#dash-side). Hidden from 992px up. Behaviour: public/js/bakr-dashboard.js (Esc, backdrop
     and links close it; focus moves into the drawer and back). $home: brand link; $portal: small label. --}}
<div class="dash-mobilebar">
    <button type="button" class="dash-burger" aria-controls="dash-side" aria-expanded="false" aria-label="Open menu" data-dash-burger>
        <i class="bi bi-list" aria-hidden="true"></i>
    </button>
    <a class="dash-brand" href="{{ $home }}">
        <img src="{{ asset('images/logo/bakr-logo.png') }}" alt="" width="28" height="28">
        <span>BAKR</span>
    </a>
    <span class="dash-mobilebar-title">{{ $portal }}</span>
</div>
<div class="dash-backdrop" data-dash-close></div>
