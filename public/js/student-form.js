/* BAKR — scholarship matching form.
   One page, every section visible: scroll down, or use the sticky section bar to jump.
   The bar shows which section you are in and which are finished; one button saves at the end.
   JavaScript hides questions that do not apply and checks answers before sending.
   The server validates everything again, so this is only a convenience. */
(() => {
    'use strict';
    const form = document.getElementById('appForm');
    if (!form) return;

    const sections = [...form.querySelectorAll('.sp-section')];
    const stepButtons = [...document.querySelectorAll('#stepper [data-goto]')];
    const bar = document.getElementById('progress');
    const barText = document.getElementById('progressText');

    const val = (name) => (form.querySelector(`input[name="${name}"]:checked`) || {}).value || '';

    /* ---- show / hide the questions that do not apply to this student (same rules as ApplicationSchema::applies) ----
       first_year  senior high school grade, country, conduct: incoming AND current 1st-years
       incoming    strand, Top 5, DOST exam: only students not yet in college
       upper       college GWA, "within the normal length": 2nd year and up
       The year-level question itself is hidden for an incoming student (always year 1). */
    function applyConditions() {
        const status = val('enrollment_status');
        const incoming = status === 'incoming_first_year';
        const yearSel = form.querySelector('select[name="year_level"]');
        const year = incoming ? '1' : (yearSel ? yearSel.value : '');
        const talents = [...form.querySelectorAll('input[name="talent_categories[]"]:checked')].map((i) => i.value);
        const applies = {
            first_year: year === '1',
            incoming,
            upper: !incoming && ['2', '3', '4', '5'].includes(year),
            talent: talents.some((v) => v !== 'none'),   // "awards in that skill": only after a real skill
        };
        form.querySelectorAll('.sp-field[data-when], .sp-field[data-hide-when]').forEach((el) => {
            let visible = true;
            if (el.dataset.when) visible = !!applies[el.dataset.when];
            if (el.dataset.hideWhen && status === el.dataset.hideWhen) visible = false;
            el.classList.toggle('sp-hidden', !visible);
            el.querySelectorAll('input, select').forEach((i) => { i.disabled = !visible; });   // hidden questions are not sent
        });
    }

    /* ---- "None of these" cannot be combined with a real choice ---- */
    form.querySelectorAll('.sp-field[data-exclusive]').forEach((field) => {
        const none = field.dataset.exclusive;
        field.addEventListener('change', (e) => {
            if (e.target.type !== 'checkbox') return;
            const boxes = [...field.querySelectorAll('input[type=checkbox]')];
            if (e.target.value === none && e.target.checked) boxes.forEach((b) => { if (b !== e.target) b.checked = false; });
            else if (e.target.checked) boxes.forEach((b) => { if (b.value === none) b.checked = false; });
        });
    });

    /* ---- "i" next to a question: a tap / click opens or closes its note (hover and keyboard focus also show it on a
       desktop, in CSS). One note open at a time; Esc or a tap elsewhere closes it. ---- */
    const infoButtons = [...form.querySelectorAll('[data-info-toggle]')];
    const setInfo = (btn, open) => {
        const tip = document.getElementById(btn.getAttribute('aria-controls'));
        if (!tip) return;
        btn.setAttribute('aria-expanded', String(open));
        tip.classList.toggle('is-open', open);
    };
    infoButtons.forEach((btn) => btn.addEventListener('click', () => {
        const open = btn.getAttribute('aria-expanded') !== 'true';
        infoButtons.forEach((b) => { if (b !== btn) setInfo(b, false); });
        setInfo(btn, open);
    }));
    document.addEventListener('click', (e) => {
        if (e.target.closest('[data-info-toggle], .sp-tip')) return;
        infoButtons.forEach((b) => setInfo(b, false));
    });
    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        const open = infoButtons.find((b) => b.getAttribute('aria-expanded') === 'true');
        if (open) { setInfo(open, false); open.focus(); }
    });

    /* ---- campus -> program: only the programs DNSC offers at the chosen campus ---- */
    const campusSel = form.querySelector('[data-campus-select]');
    const programSel = form.querySelector('[data-program-select]');
    if (campusSel && programSel) {
        const placeholder = programSel.options[0];
        const all = Array.from(programSel.options).slice(1);
        const syncPrograms = () => {
            const keep = programSel.value;
            programSel.innerHTML = '';
            programSel.appendChild(placeholder);
            all.filter((o) => o.dataset.campus === campusSel.value).forEach((o) => programSel.appendChild(o));
            programSel.value = Array.from(programSel.options).some((o) => o.value === keep) ? keep : '';
            programSel.disabled = !campusSel.value;
            placeholder.textContent = campusSel.value ? 'Choose your program' : 'Choose your campus first';
        };
        campusSel.addEventListener('change', syncPrograms);
        if (campusSel.options.length === 2 && !campusSel.value) campusSel.selectedIndex = 1;   // only one campus: pick it
        syncPrograms();
    }

    /* ---- checking answers ---- */
    function isAnswered(el) {
        const type = el.dataset.type;
        if (type === 'radio' || type === 'checkbox') return !!el.querySelector('input:checked');
        if (['select', 'campus', 'program'].includes(type)) return !!el.querySelector('select').value;
        const i = el.querySelector('input');
        return !!i && i.value.trim() !== '';
    }

    function fieldError(el) {
        if (el.classList.contains('sp-hidden') || el.dataset.required !== '1') return '';
        const type = el.dataset.type;
        if (!isAnswered(el)) {
            if (el.dataset.field === 'privacy_consent') return 'Please read and agree to the Privacy Policy so you can continue.';
            if (type === 'checkbox') return 'Pick at least one option. Choose "None of these" if it does not apply.';
            if (type === 'radio') return 'Please choose an answer.';
            if (['select', 'campus', 'program'].includes(type)) return 'Please choose from the list.';
            return 'Please answer this question.';
        }
        if (type === 'number') {
            const i = el.querySelector('input');
            if (Number(i.value) < Number(i.min) || Number(i.value) > Number(i.max)) return `Enter a number from ${i.min} to ${i.max}.`;
        }
        if (type === 'date') {
            const i = el.querySelector('input');
            if ((i.min && i.value < i.min) || (i.max && i.value > i.max)) return 'Please check your date of birth.';
        }
        if (el.dataset.accountName) {   // certification: the typed name must match the account (server checks again)
            const norm = (s) => s.trim().replace(/\s+/g, ' ').toLowerCase();
            if (norm(el.querySelector('input').value) !== norm(el.dataset.accountName)) return `Type your full name exactly as it appears on your account: ${el.dataset.accountName}.`;
        }
        return '';
    }

    function showError(el, message) {
        el.classList.toggle('has-error', !!message);
        let p = el.querySelector('.sp-err[data-js]');
        if (message && !p) {
            p = document.createElement('p');
            p.className = 'sp-err';
            p.dataset.js = '1';
            p.setAttribute('role', 'alert');
            el.appendChild(p);
        }
        if (p) { p.textContent = message; if (!message) p.remove(); }
    }

    const requiredFields = (root) => [...root.querySelectorAll('.sp-field[data-required="1"]')].filter((el) => !el.classList.contains('sp-hidden'));

    /* ---- progress: finished sections and the bar ---- */
    function updateProgress() {
        let done = 0, total = 0;
        sections.forEach((s, i) => {
            const req = requiredFields(s);
            const answered = req.filter(isAnswered).length;
            done += answered; total += req.length;
            const complete = req.length > 0 && answered === req.length;
            if (stepButtons[i]) stepButtons[i].classList.toggle('done', complete);
        });
        const pct = total ? Math.round((done / total) * 100) : 0;
        if (bar) bar.style.setProperty('--w', pct + '%');
        if (barText) barText.textContent = total ? `${done} of ${total} required questions answered` : '';
    }

    form.addEventListener('input', (e) => {
        const el = e.target.closest('.sp-field');
        if (el && el.classList.contains('has-error') && !fieldError(el)) showError(el, '');
        updateProgress();
    });
    form.addEventListener('change', (e) => {
        if (['enrollment_status', 'year_level', 'talent_categories[]'].includes(e.target.name)) applyConditions();
        updateProgress();
    });

    /* ---- jump to a section from the bar ---- */
    stepButtons.forEach((b, i) => b.addEventListener('click', () => {
        const heading = sections[i].querySelector('h2');
        sections[i].scrollIntoView({ behavior: 'smooth', block: 'start' });
        if (heading) { heading.setAttribute('tabindex', '-1'); heading.focus({ preventScroll: true }); }
    }));

    /* ---- the bar follows the section you are reading ---- */
    const stepper = document.getElementById('stepper');
    function setActive(index) {
        stepButtons.forEach((b, i) => b.setAttribute('aria-current', i === index ? 'step' : 'false'));
        const active = stepButtons[index];
        // Scroll the step bar horizontally without touching the viewport scroll position
        if (stepper && active) {
            const target = active.offsetLeft - (stepper.clientWidth / 2) + (active.offsetWidth / 2);
            stepper.scrollTo({ left: Math.max(0, target), behavior: 'smooth' });
        }
    }
    if ('IntersectionObserver' in window) {
        const seen = new Map();
        const io = new IntersectionObserver((entries) => {
            entries.forEach((en) => seen.set(en.target, en.isIntersecting ? en.intersectionRatio : 0));
            let best = 0, bestRatio = -1;
            sections.forEach((s, i) => { const r = seen.get(s) || 0; if (r > bestRatio) { best = i; bestRatio = r; } });
            if (bestRatio > 0) setActive(best);
        }, { rootMargin: '-25% 0px -45% 0px', threshold: [0, .1, .3, .6, 1] });
        sections.forEach((s) => io.observe(s));
    }

    /* ---- review before submitting (student form only): a summary of every visible answer ---- */
    const review = document.getElementById('reviewDialog');
    const reviewBody = document.getElementById('reviewBody');
    let reviewed = false;

    const shownValue = (el) => {
        const type = el.dataset.type;
        if (type === 'radio' || type === 'checkbox') {
            return [...el.querySelectorAll('input:checked')].map((i) => i.nextElementSibling.textContent.trim()).join(', ');
        }
        const sel = el.querySelector('select');
        if (sel) return sel.value ? sel.options[sel.selectedIndex].textContent.trim() : '';
        const i = el.querySelector('input');
        if (i && type === 'date' && i.value) {
            const d = new Date(i.value + 'T00:00:00');
            return isNaN(d) ? i.value : d.toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' });
        }
        return i ? i.value.trim() : '';
    };

    function buildReview() {
        reviewBody.replaceChildren();   // text nodes only: answers are never put in as HTML
        sections.forEach((s) => {
            const part = document.createElement('section');
            part.className = 'bf-review-part';
            const h = document.createElement('h3');
            const small = document.createElement('small');
            small.textContent = s.dataset.part || '';
            h.append(small, (s.querySelector('h2 > span') || s.querySelector('h2')).lastChild.textContent.trim());
            const dl = document.createElement('dl');
            s.querySelectorAll('.sp-field').forEach((el) => {
                if (el.classList.contains('sp-hidden') || el.dataset.field === 'college_gwa_scale') return;
                let value = shownValue(el);
                if (el.dataset.field === 'college_gwa' && value) {
                    const scale = form.querySelector('[name="college_gwa_scale"]');
                    value += scale && scale.value === 'dnsc' ? ' (1.0 to 5.0 scale)' : '%';
                }
                if (el.dataset.field === 'shs_gwa' && value) value += '%';
                const row = document.createElement('div');
                const dt = document.createElement('dt');
                const dd = document.createElement('dd');
                dt.textContent = el.dataset.label || '';
                dd.textContent = value || 'Not answered';
                if (!value) dd.className = 'is-empty';
                row.append(dt, dd);
                dl.append(row);
            });
            part.append(h, dl);
            reviewBody.append(part);
        });
    }

    const submitBtn = form.querySelector('[type=submit]');
    if (review && typeof review.showModal === 'function' && submitBtn) {
        const label = submitBtn.querySelector('span');
        if (label && submitBtn.dataset.reviewLabel) label.textContent = submitBtn.dataset.reviewLabel;
        document.getElementById('reviewConfirm').addEventListener('click', () => {
            reviewed = true;
            review.close();
            // requestSubmit fires the submit event, so the "Saving…" card (bakr-busy.js, data-busy) shows and the
            // handler below disables the button; form.submit() skipped both. The answers were checked before the review.
            if (typeof form.requestSubmit === 'function') form.requestSubmit();
            else { submitBtn.disabled = true; form.submit(); }
        });
    }
    // Back button (page restored from the cache): the save button works again.
    window.addEventListener('pageshow', () => { reviewed = false; if (submitBtn) submitBtn.disabled = false; });

    /* ---- save: check everything, then jump to the first problem ---- */
    form.addEventListener('submit', (e) => {
        let first = null;
        form.querySelectorAll('.sp-field').forEach((el) => {
            const msg = fieldError(el);
            showError(el, msg);
            if (msg && !first) first = el;
        });
        if (first) {
            e.preventDefault();
            const target = first.querySelector('input:not([disabled]), select');
            first.scrollIntoView({ behavior: 'smooth', block: 'center' });
            if (target) target.focus({ preventScroll: true });
            return;
        }
        if (review && reviewBody && typeof review.showModal === 'function' && !reviewed) {
            e.preventDefault();
            buildReview();
            review.showModal();
            return;
        }
        const btn = form.querySelector('[type=submit]');
        if (btn) btn.disabled = true;   // no double saves
    });

    applyConditions();
    updateProgress();
    const firstError = form.querySelector('.has-error');
    if (firstError) firstError.scrollIntoView({ block: 'center' });
})();
