/* BAKR — home page only. */
(() => {
    'use strict';
    const { tag } = window.BAKR || {};
    if (tag) {
        tag('.bakr-intro-inner', 'nx-reveal');
        tag('.bakr-stats-heading', 'nx-reveal');
        tag('.bakr-stats-grid', 'nx-reveal-stagger');
        tag('.bakr-gallery-track', 'nx-reveal-stagger', 'zoom');
        tag('.nx-strip-heading', 'nx-reveal');
        tag('#process .col-lg-5', 'nx-reveal', 'left');
    }

    /* Welcome pop-up: once per browser session.
       Swap sessionStorage for localStorage if it should only ever appear once. */
    const modalEl = document.getElementById('welcomeModal');
    if (!modalEl || !window.bootstrap) return;
    let seen = false;
    try { seen = sessionStorage.getItem('bakrWelcomeSeen') === '1'; } catch (e) { /* storage unavailable */ }
    if (seen) return;
    setTimeout(() => {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
        try { sessionStorage.setItem('bakrWelcomeSeen', '1'); } catch (e) { /* storage unavailable */ }
    }, 1700);
})();
