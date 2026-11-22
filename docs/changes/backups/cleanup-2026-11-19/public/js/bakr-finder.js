/* BAKR — Scholarship Finder: in-place question steps and the results count-up.
   Moved out of scholarshipFinder.blade.php; both blocks do nothing on screens without their elements. */

// Question steps load via fetch(): only the card inside .sf-shell is swapped, so the
// .sf-media panel never reloads. Any failure falls back to a normal page load.
(function () {
    var form = document.getElementById('qForm');
    if (!form) return;
    var shell = form.closest('.sf-shell');
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var actionPath = new URL(form.getAttribute('action'), location.href).pathname;
    var last = form.querySelector('input[name=v]:checked');   // for click-again-to-uncheck
    var busy = false, token = 0;

    function wait(ms) { return new Promise(function (r) { setTimeout(r, reduce ? 0 : ms); }); }
    // A "question step" is ?q=... on this page. Review/intro/results still do a normal load.
    function isStep(url) {
        try { var u = new URL(url, location.href); return u.pathname === actionPath && u.searchParams.has('q') && !u.searchParams.has('v'); }
        catch (e) { return false; }
    }
    function showError() {
        var box = document.getElementById('answers');
        var d = form.querySelector('.sf-err') || document.createElement('div');
        d.className = 'sf-err'; d.setAttribute('role', 'alert');
        d.textContent = 'Please choose an answer to continue.';
        box.prepend(d);
    }

    // Fetch a step page and swap it in. dir: 1 = forward, -1 = back. Rejects on failure so callers can fall back.
    function loadStep(url, dir, push) {
        var my = ++token;
        var oldFill = form.querySelector('.sf-fill');
        var oldW = oldFill ? oldFill.style.width : '';
        form.style.setProperty('--sf-shift', (-14 * dir) + 'px');
        form.classList.add('is-busy', 'is-swapping');

        var req = fetch(url, { credentials: 'same-origin', cache: 'no-store' }).then(function (res) {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.text().then(function (html) { return { url: res.url, html: html }; });
        });
        return Promise.all([req, wait(160)]).then(function (r) {
            if (my !== token) return;                        // a newer navigation took over
            var out = r[0], doc = new DOMParser().parseFromString(out.html, 'text/html');
            var nf = doc.getElementById('qForm');
            if (!nf || !isStep(out.url)) { location.href = out.url; return; }   // server sent us elsewhere (review, intro...)

            var cap = shell.querySelector('.sf-media-cap'), ncap = doc.querySelector('.sf-media-cap');
            var capChanged = !!(cap && ncap && cap.innerHTML !== ncap.innerHTML);   // new section -> new caption only
            if (capChanged) cap.classList.add('is-swapping');

            return (capChanged ? wait(160) : Promise.resolve()).then(function () {
                if (my !== token) return;
                if (capChanged) { cap.innerHTML = ncap.innerHTML; cap.classList.remove('is-swapping'); }

                form.innerHTML = nf.innerHTML;               // same <form>, so the listeners below keep working
                form.style.setProperty('--sf-shift', (14 * dir) + 'px');
                var fill = form.querySelector('.sf-fill'), target = fill ? fill.style.width : '';
                if (fill && oldW) fill.style.width = oldW;   // start where the old bar ended, then animate
                void form.offsetWidth;                       // commit the hidden state before fading in
                if (fill) fill.style.width = target;
                form.classList.remove('is-swapping', 'is-busy');

                last = form.querySelector('input[name=v]:checked');
                if (push) history.pushState({ sf: 1 }, '', out.url);
                var h2 = form.querySelector('h2');
                if (h2) { h2.setAttribute('tabindex', '-1'); h2.focus({ preventScroll: true }); }
            });
        });
    }

    // Next / Save: validate, POST the answer, then swap in whatever step the server points to.
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (busy) return;
        var sel = form.querySelector('select[name=v]');
        var ok = sel ? sel.value !== '' : !!form.querySelector('input[name=v]:checked');
        if (!ok) { showError(); return; }
        busy = true;
        fetch(form.getAttribute('action'), {
            method: 'POST', body: new FormData(form), credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        }).then(function (res) {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        }).then(function (data) {
            if (!data || !data.redirect) throw new Error('Bad response');
            if (isStep(data.redirect)) return loadStep(data.redirect, 1, true);
            location.href = data.redirect;                   // review page: normal navigation
        }).catch(function () {
            HTMLFormElement.prototype.submit.call(form);     // fallback: classic full-page submit
        }).then(function () { busy = false; });
    });

    form.addEventListener('click', function (e) {
        // Click a selected radio again to un-check it.
        if (e.target.matches && e.target.matches('input[type=radio][name=v]')) {
            if (last === e.target) { e.target.checked = false; last = null; } else { last = e.target; }
            return;
        }
        // Back: swap the previous step in place. (Cancel/intro links go to non-step URLs and load normally.)
        var a = e.target.closest ? e.target.closest('a.sf-btn') : null;
        if (!a || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || !isStep(a.href)) return;
        e.preventDefault();
        if (busy) return;
        busy = true;
        loadStep(a.href, -1, true).catch(function () { location.href = a.href; }).then(function () { busy = false; });
    });

    form.addEventListener('change', function () { var d = form.querySelector('.sf-err'); if (d) d.remove(); });

    // Browser back/forward between steps swaps in place too.
    history.replaceState({ sf: 1 }, '');
    window.addEventListener('popstate', function () {
        if (!isStep(location.href)) { location.reload(); return; }
        loadStep(location.href, -1, false).catch(function () { location.reload(); });
    });
})();

// Count the "N found" number up from 0 (skipped when the visitor prefers reduced motion).
(function () {
    var el = document.querySelector('[data-count]');
    if (!el || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    var n = parseInt(el.getAttribute('data-count'), 10), t0 = null;
    if (!n) return;
    el.textContent = '0';
    function step(t) {
        if (t0 === null) t0 = t;
        var p = Math.min((t - t0) / 900, 1);
        el.textContent = Math.round(n * (1 - Math.pow(1 - p, 3)));
        if (p < 1) requestAnimationFrame(step);
    }
    setTimeout(function () { requestAnimationFrame(step); }, 450);
})();
