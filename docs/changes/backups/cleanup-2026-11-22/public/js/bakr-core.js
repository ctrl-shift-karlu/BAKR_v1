/* BAKR — shared behaviour: navbar, preloader, in-page scrolling, scroll-reveal.
   Loaded on every page. Page scripts tag elements, then this runs on DOMContentLoaded. */
(() => {
    'use strict';
    // Installed app (html.is-app, public/js/bakr-app-mode.js): no motion, no waits.
    const isApp = document.documentElement.classList.contains('is-app');
    const reduceMotion = isApp || window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ---------- Navbar: compact on scroll, mark the current page ---------- */
    const navbar = document.querySelector('.bk-navbar');
    if (navbar) {
        const update = () => navbar.classList.toggle('is-scrolled', window.scrollY > 24);
        update();
        window.addEventListener('scroll', update, { passive: true });

        const page = document.body.dataset.page;
        const menu = navbar.querySelector('.navbar-collapse');
        navbar.querySelectorAll('.nav-link:not(.dropdown-toggle), .dropdown-item').forEach(a => a.addEventListener('click', () => {
            if (menu && menu.classList.contains('show') && window.bootstrap) bootstrap.Collapse.getOrCreateInstance(menu).hide();
        }));
        const current = page && navbar.querySelector(`[data-nav="${page}"]`);
        if (current) current.setAttribute('aria-current', 'page');
    }

    /* ---------- Preloader: once per session, never waits on images ---------- */
    const loaders = document.querySelectorAll('.nx-preloader');
    if (loaders.length) {
        let hidden = false;
        const hide = () => {
            if (hidden) return;
            hidden = true;
            loaders.forEach(el => { el.classList.add('nx-preloader-hide'); setTimeout(() => el.remove(), 700); });
        };
        try { sessionStorage.setItem('bakrIntroSeen', '1'); } catch (e) { /* storage unavailable */ }
        if (document.documentElement.classList.contains('bk-intro-seen') || reduceMotion) {
            hide();
        } else {
            // Wait for the HTML (not every photo), plus enough time for B-A-K-R to finish spelling.
            const t0 = performance.now(), MIN = 2000;
            const hideSoon = () => setTimeout(hide, Math.max(0, MIN - (performance.now() - t0)));
            if (document.readyState !== 'loading') hideSoon();
            else document.addEventListener('DOMContentLoaded', hideSoon, { once: true });
            setTimeout(hide, 2600);                                                // hard cap
        }
        window.addEventListener('pageshow', (e) => { if (e.persisted) hide(); });  // back/forward cache
    }

    /* ---------- Same-page links (#faqs ...) land below the fixed navbar ---------- */
    const offset = () => parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--nav-offset')) || 84;
    // The nearest ancestor that scrolls by itself (in the portals on desktop that is .dash-main, not the window).
    const scrollParent = (el) => {
        for (let p = el.parentElement; p && p !== document.body && p !== document.documentElement; p = p.parentElement) {
            const oy = getComputedStyle(p).overflowY;
            if ((oy === 'auto' || oy === 'scroll') && p.scrollHeight > p.clientHeight) return p;
        }
        return null;
    };
    const scrollToTarget = (target, smooth = true) => {
        const behavior = smooth && !reduceMotion ? 'smooth' : 'auto';
        const box = scrollParent(target);
        if (box) {                             // portal pages: scroll the panel (no fixed navbar there)
            const top = target.getBoundingClientRect().top - box.getBoundingClientRect().top + box.scrollTop - 16;
            box.scrollTo({ top: Math.max(top, 0), behavior });
            return;
        }
        const top = target.getBoundingClientRect().top + window.scrollY - offset();
        window.scrollTo({ top: Math.max(top, 0), behavior });
    };
    // "#2026" or "#a%20b" is not a valid CSS selector: querySelector threw, which stopped this whole script
    // (scroll-reveal never ran, so the page's sections stayed hidden). Look the id up instead.
    const byHash = (hash) => {
        try { return hash && hash.length > 1 ? document.getElementById(decodeURIComponent(hash.slice(1))) : null; } catch (e) { return null; }
    };
    document.querySelectorAll('a[href^="#"]').forEach(link => {
        const hash = link.getAttribute('href');
        if (!hash || hash === '#' || hash === '#main') return;
        link.addEventListener('click', (event) => {
            const target = byHash(hash);
            if (!target) return;
            event.preventDefault();
            const menu = document.getElementById('mainNavigation');
            if (menu?.classList.contains('show') && window.bootstrap) bootstrap.Collapse.getOrCreateInstance(menu).hide();
            scrollToTarget(target);
            history.pushState(null, '', hash);
            if (!target.hasAttribute('tabindex')) target.setAttribute('tabindex', '-1');
            target.focus({ preventScroll: true });
        });
    });
    if (location.hash) {                       // direct visits like /#faqs
        const target = byHash(location.hash);
        if (target) window.addEventListener('load', () => setTimeout(() => scrollToTarget(target, false), isApp ? 0 : 700));
    }

    /* ---------- Scroll-reveal ---------- */
    const tag = (selector, cls, variant) => document.querySelectorAll(selector).forEach(el => {
        el.classList.add(cls);
        if (variant) el.setAttribute('data-reveal', variant);
    });
    window.BAKR = Object.assign(window.BAKR || {}, { tag });

    const countUp = (group) => {
        if (reduceMotion || !group.classList.contains('bk-ledger-grid')) return;
        group.querySelectorAll('.bk-ledger-num[data-count]').forEach(el => {
            const target = parseInt(el.dataset.count, 10);
            if (!Number.isFinite(target)) return;
            const start = performance.now(), duration = 1100;
            const tick = (now) => {
                const t = Math.min((now - start) / duration, 1);
                el.textContent = Math.round(target * (1 - Math.pow(1 - t, 3)));
                if (t < 1) requestAnimationFrame(tick);
            };
            el.textContent = '0';
            requestAnimationFrame(tick);
        });
    };

    const initReveal = () => {
        document.querySelectorAll('.nx-reveal-stagger').forEach(group => {
            Array.from(group.children).forEach((child, i) => child.style.setProperty('--i', Math.min(i, 8)));
        });
        const els = document.querySelectorAll('.nx-reveal, .nx-reveal-stagger');
        if (!els.length) return;
        if (isApp || !('IntersectionObserver' in window)) { els.forEach(el => el.classList.add('is-visible')); return; }
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                countUp(entry.target);
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.08, rootMargin: '0px 0px -8% 0px' });
        els.forEach(el => observer.observe(el));
    };
    document.addEventListener('DOMContentLoaded', initReveal);
})();
