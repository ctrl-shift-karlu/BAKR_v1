/*
 * BAKR as an installable app (PWA). Loaded on every page by resources/views/partials/pwa-head.
 *  1. Registers the service worker (public/sw.js; URL from this script's data-sw).
 *  2. "Install app" buttons ([data-pwa-install], partials/pwa-install) stay hidden until the
 *     browser says BAKR can be installed (Chrome / Edge / Android: beforeinstallprompt).
 *     On iPhone / iPad Safari, which has no install prompt, the button shows how to
 *     "Add to Home Screen" instead. Inside the installed app the buttons never show.
 */
(function () {
    'use strict';

    var script = document.currentScript;
    var swUrl = script && script.getAttribute('data-sw');

    if ('serviceWorker' in navigator && swUrl && window.isSecureContext) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register(swUrl).catch(function () { /* the site works without it */ });
        });
    }

    var standalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    if (standalone) {
        document.documentElement.classList.add('is-installed-app');
        prefetchLinks();
        return;
    }

    /*
     * Faster page changes in the app: Chrome / Edge start fetching a BAKR page the moment a finger
     * touches (or the mouse rests on) its link, so it is usually ready when the tap ends.
     * Prefetch only (no prerender: no page script runs early). Never: downloads, file previews,
     * new tabs, other sites, or links marked data-no-busy. Sign-out is a form (POST), so never prefetched.
     */
    function prefetchLinks() {
        if (!window.HTMLScriptElement || !HTMLScriptElement.supports || !HTMLScriptElement.supports('speculationrules')) return;
        var rules = document.createElement('script');
        rules.type = 'speculationrules';
        rules.textContent = JSON.stringify({
            prefetch: [{
                source: 'document',
                eagerness: 'moderate',
                where: { and: [
                    { href_matches: '/*' },
                    // (patterns checked in Chrome: a "*" after "?" or after a (group) changes their meaning)
                    { not: { href_matches: { search: '*inline=*' } } },
                    { not: { href_matches: ['/*/file', '/*/template*', '/*/download*', '/*/export*'] } },
                    { not: { href_matches: '/*logout*' } },
                    { not: { selector_matches: '[download], [target="_blank"], [data-no-busy], [data-open-dialog]' } }
                ] }
            }]
        });
        document.head.appendChild(rules);
    }

    var isIos = /iphone|ipad|ipod/i.test(navigator.userAgent)
        || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    var deferredPrompt = null;

    function buttons() {
        return document.querySelectorAll('[data-pwa-install]');
    }

    function show(on) {
        buttons().forEach(function (btn) { btn.hidden = !on; });
    }

    function iosHint(btn) {
        var hint = btn.parentElement && btn.parentElement.querySelector('[data-pwa-ios-hint]');
        if (!hint) return;
        hint.hidden = !hint.hidden;
        btn.setAttribute('aria-expanded', String(!hint.hidden));
    }

    window.addEventListener('beforeinstallprompt', function (event) {
        event.preventDefault();
        deferredPrompt = event;
        show(true);
    });

    window.addEventListener('appinstalled', function () {
        deferredPrompt = null;
        show(false);
    });

    document.addEventListener('click', function (event) {
        var btn = event.target.closest('[data-pwa-install]');
        if (!btn) return;
        event.preventDefault();

        if (deferredPrompt) {
            deferredPrompt.prompt();
            deferredPrompt.userChoice.finally(function () {
                deferredPrompt = null;
                show(false);
            });
        } else if (isIos) {
            iosHint(btn);
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        if (isIos) show(true);
    });
})();
