{{-- Welcome pop-up: shown once per browser session by bakr-home.js. --}}
<div class="modal fade nx-welcome" id="welcomeModal" tabindex="-1" aria-labelledby="welcomeTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content c-modal">
            <button type="button" class="btn-close nx-welcome-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="nx-modal-band"></div>
            <div class="nx-modal-body-pad">
                <div class="nx-avatar"><img src="{{ asset('images/logo/bakr-logo.png') }}" alt="BAKR logo"></div>
                <div class="nx-welcome-kicker"><span></span> YOUR DNSC SCHOLARSHIP GUIDE <span></span></div>
                <h4 id="welcomeTitle">Welcome to BAKR</h4>
                <p>Your local guide to DNSC scholarships. Browse the opportunities or find your matches, then apply through the DNSC Scholarship and Grants Unit.</p>
            </div>
        </div>
    </div>
</div>
