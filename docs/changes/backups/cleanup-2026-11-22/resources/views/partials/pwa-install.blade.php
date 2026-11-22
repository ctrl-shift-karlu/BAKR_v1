{{-- "Install the BAKR app" button (owner, 2026-11-21: always visible, never "gone"). bakr-pwa.js: where the browser
     can install BAKR directly (Chrome / Edge / Android) a tap opens its install prompt; otherwise a tap shows how to
     install it here (iPhone / iPad: Add to Home Screen; other browsers: the browser menu; plain http address: needs a
     secure address). Hidden only inside the installed app (html.is-app, bakr-app-mode.css + bakr-pwa.js). --}}
<div class="pwa-install {{ $class ?? '' }}">
    <button type="button" class="pwa-install-btn" data-pwa-install aria-expanded="false">
        <i class="bi bi-download" aria-hidden="true"></i>Install the BAKR app
    </button>
    <p class="pwa-ios-hint" data-pwa-ios-hint hidden>
        In Safari, tap <i class="bi bi-box-arrow-up" aria-label="Share"></i> Share, then <strong>Add to Home Screen</strong>.
    </p>
    <p class="pwa-ios-hint" data-pwa-help hidden></p>
</div>
