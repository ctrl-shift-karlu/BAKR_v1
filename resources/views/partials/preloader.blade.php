{{-- Brand animation while the page opens: logo draws in, then B-A-K-R is spelled out. --}}
<div id="nxPreloader" class="nx-preloader" role="status" aria-label="Loading BAKR">
    <div class="nxp-frame" aria-hidden="true"></div>
    <div class="nxp-stage" aria-hidden="true">
        <div class="nxp-emblem">
            <svg viewBox="0 0 120 120"><circle class="nxp-ring" cx="60" cy="60" r="56" pathLength="1"/><circle class="nxp-ring2" cx="60" cy="60" r="49"/></svg>
            <img src="{{ asset('images/logo/bakr-logo.png') }}" alt="">
        </div>
        <div class="nxp-word"><span>B</span><span>A</span><span>K</span><span>R</span></div>
        <div class="nxp-rule"><i></i></div>
        <p class="nxp-sub">{{ config('bakr.tagline') }}</p>
    </div>
</div>
