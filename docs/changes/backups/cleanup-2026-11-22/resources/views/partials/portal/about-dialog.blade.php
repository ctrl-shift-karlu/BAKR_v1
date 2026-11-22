{{-- "About BAKR" dialog for the dashboards. Opens from any [data-open-dialog="about-bakr"] link
     and, once per browser, on a person's first dashboard visit (public/js/bakr-dashboard.js).
     $audience: 'student' | 'scholar' | 'provider'; $helpRoute: the portal's Help page. --}}
<dialog class="dash-dialog" id="about-bakr" aria-labelledby="about-bakr-title" data-first-visit="{{ $audience }}">
    <div class="dash-dialog-head">
        <img src="{{ asset('images/img/dnsc_campus.jpg') }}" alt="" class="dash-dialog-photo">
        <div>
            <div class="dash-kicker">Welcome to BAKR</div>
            <h2 id="about-bakr-title">Your DNSC <em>scholarship guide</em></h2>
        </div>
        <button type="button" class="dash-dialog-close" data-close-dialog aria-label="Close"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </div>
    <div class="dash-dialog-body">
        @include('partials.portal.about-bakr', ['audience' => $audience])
    </div>
    <div class="dash-dialog-foot">
        <a class="dash-btn is-soft" href="{{ route($helpRoute) }}"><i class="bi bi-question-circle" aria-hidden="true"></i>Help &amp; contact</a>
        <button type="button" class="dash-btn" data-close-dialog>Got it</button>
    </div>
</dialog>
