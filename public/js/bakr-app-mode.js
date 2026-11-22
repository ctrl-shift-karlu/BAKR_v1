/*
 * BAKR "app mode": runs in <head> (NOT deferred, via partials/pwa-head) before the page paints.
 * Inside the installed app (display-mode standalone) it sets html.is-app + bk-intro-seen, so
 *   - public/css/bakr-app-mode.css switches off decorative motion, blur and scroll-reveal,
 *   - the B-A-K-R preloader never shows (bk-intro-seen),
 *   - public/js/bakr-core.js treats motion as reduced (instant scroll, no count-up, no waits),
 *   - page changes skip the cross-document view transition (no snapshot, no fade).
 * In a normal browser tab nothing changes.
 */
(function () {
    'use strict';

    var standalone = window.matchMedia('(display-mode: standalone)').matches
        || window.matchMedia('(display-mode: minimal-ui)').matches
        || window.navigator.standalone === true;
    if (!standalone) return;

    document.documentElement.classList.add('is-app', 'bk-intro-seen');
    try { sessionStorage.setItem('bakrIntroSeen', '1'); } catch (e) { /* storage unavailable */ }

    var skip = function (event) {
        if (event.viewTransition) event.viewTransition.skipTransition();
    };
    window.addEventListener('pageswap', skip);
    window.addEventListener('pagereveal', skip);
})();
