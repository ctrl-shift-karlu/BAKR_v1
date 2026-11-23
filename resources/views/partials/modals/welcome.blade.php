{{-- Welcome pop-up: shown once per browser session by bakr-home.js.
     Frosted dark-green glass card; solid fallback when blur is off (installed app, older browsers). --}}
<div class="modal fade nx-welcome" id="welcomeModal" tabindex="-1" aria-labelledby="welcomeTitle" aria-describedby="welcomeText" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content nx-welcome-card">
            <button type="button" class="btn-close nx-welcome-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <img class="nx-welcome-logo" src="{{ asset('images/logo/bakr-logo.png') }}" alt="BAKR logo" width="72" height="72">
            <h2 class="nx-welcome-title" id="welcomeTitle">Welcome to {{ config('app.name') }}</h2>
            <p class="nx-welcome-text" id="welcomeText">Your guide to DNSC scholarships. Find the scholarships you match, or browse every opportunity, then apply with the provider.</p>
            <div class="nx-welcome-actions">
                <a class="nx-welcome-btn is-primary" href="{{ route('find') }}"><i class="bi bi-search" aria-hidden="true"></i> Find my matches</a>
                <a class="nx-welcome-btn is-ghost" href="{{ route('scholarships') }}"><i class="bi bi-journal-text" aria-hidden="true"></i> Browse scholarships</a>
            </div>
        </div>
    </div>
</div>
