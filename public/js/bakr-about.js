/* BAKR — About page: scroll-reveal tags and the campus video. */
(() => {
    'use strict';
    const { tag } = window.BAKR || {};
    if (tag) {
        tag('.nx-about-img', 'nx-reveal', 'left');
        tag('.nx-about-grid > div:last-child', 'nx-reveal', 'right');
        tag('.nx-points', 'nx-reveal-stagger');
        tag('.bk-insights', 'nx-reveal');
        tag('.bk-team', 'nx-reveal-stagger');
    }

    // Feedback form: say the chosen rating in words, count characters, focus the notice after a reload.
    const fb = document.getElementById('feedbackForm');
    if (fb) {
        const word = document.getElementById('fbRateWord');
        const showRating = () => {
            const picked = fb.querySelector('.bk-star-input:checked');
            if (!word) return;
            word.textContent = picked ? `${picked.value} out of 5 · ${picked.dataset.word}` : word.dataset.empty;
            word.classList.toggle('is-set', !!picked);
        };
        fb.querySelectorAll('.bk-star-input').forEach((r) => r.addEventListener('change', showRating));
        showRating();

        const msg = document.getElementById('fbMessage');
        const count = document.getElementById('fbCount');
        const updateCount = () => { if (msg && count) count.textContent = `${msg.value.length} / ${msg.maxLength}`; };
        msg?.addEventListener('input', updateCount);
        updateCount();

        fb.addEventListener('submit', () => {
            const btn = document.getElementById('fbSubmit');
            if (btn) setTimeout(() => { btn.disabled = true; }, 0);   // after the browser has read the form: stops double sends
        });

        const notice = document.getElementById('feedbackSent') || document.getElementById('feedbackErrors');
        notice?.focus({ preventScroll: true });
    }

    // Photo placeholders: show the picture only if the file exists, otherwise keep the placeholder.
    document.querySelectorAll('.bk-photo-slot[data-photo]').forEach((slot) => {
        const img = new Image();
        img.alt = slot.dataset.alt || '';
        img.onload = () => { slot.prepend(img); slot.classList.add('has-photo'); };
        img.src = slot.dataset.photo;
    });

    const v = document.querySelector('.nx-about-video');
    const btn = document.getElementById('aboutVideoSound');
    if (!v) return;
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const conn = navigator.connection || {};
    const lightData = conn.saveData === true || /(^|-)2g$|^3g$/.test(conn.effectiveType || '')
        || v.dataset.autoplay === 'false';   // server says the file is too heavy to autoplay

    // The file is large: attach the source only when it is actually needed.
    const source = v.querySelector('source[data-src]');
    let loaded = !source;
    const load = () => {
        if (loaded) return;
        loaded = true;
        source.src = source.dataset.src;
        v.load();
    };
    const play = () => { load(); const p = v.play(); if (p && p.catch) p.catch(() => {}); };

    if (reduceMotion || lightData) {
        // No autoplay: show the poster and let the student choose to play it.
        v.setAttribute('controls', '');
        v.setAttribute('aria-label', 'Video of the DNSC campus. Press play to load it.');
        if (btn) btn.hidden = true;   // native controls include sound
        // First press of play: attach the source (load() resets the element), then play again.
        v.addEventListener('play', () => { if (!loaded) play(); });
    } else if ('IntersectionObserver' in window) {
        // Load and play only while on screen.
        new IntersectionObserver((entries) => entries.forEach((entry) => {
            if (entry.isIntersecting) play(); else v.pause();
        }), { threshold: 0.35 }).observe(v);
    } else { play(); }

    if (!btn) return;
    const icon = btn.querySelector('i'), label = btn.querySelector('span');
    const render = () => {
        const on = !v.muted;
        icon.className = on ? 'bi bi-volume-up-fill' : 'bi bi-volume-mute-fill';
        label.textContent = on ? 'Sound on' : 'Sound off';
        btn.setAttribute('aria-pressed', String(on));
        btn.setAttribute('aria-label', on ? 'Turn video sound off' : 'Turn video sound on');
    };
    btn.addEventListener('click', () => {
        v.muted = !v.muted;
        if (!v.muted) { v.volume = 1; play(); }
        render();
    });
    render();
})();
