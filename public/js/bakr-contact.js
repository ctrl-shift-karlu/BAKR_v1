/* BAKR — Contact page: show the feedback-only fields, count characters, stop double sends. */
(() => {
    'use strict';
    const form = document.getElementById('contactForm');
    if (!form) return;

    const extras = document.getElementById('feedbackExtras');
    const syncTopic = () => {
        const topic = form.querySelector('input[name="topic"]:checked');
        if (extras) extras.hidden = !(topic && topic.value === 'feedback');
    };
    form.querySelectorAll('input[name="topic"]').forEach(r => r.addEventListener('change', syncTopic));
    syncTopic();

    const msg = document.getElementById('cMessage');
    const count = document.getElementById('cCount');
    const updateCount = () => { if (msg && count) count.textContent = `${msg.value.length} / ${msg.maxLength}`; };
    msg?.addEventListener('input', updateCount);
    updateCount();

    form.addEventListener('submit', () => {
        const btn = document.getElementById('cSubmit');
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="bi bi-hourglass-split" aria-hidden="true"></i> Sending…'; }
    });

    // After a send or a validation error the page reloads at #message: move focus to the notice for screen readers.
    const notice = document.getElementById('msgSent') || document.getElementById('msgErrors');
    notice?.focus({ preventScroll: true });
})();
