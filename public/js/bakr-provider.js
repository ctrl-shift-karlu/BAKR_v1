// BAKR — Provider Portal pages (the login uses layouts/auth + student-auth.js). No inline scripts;
// loaded at the end of <body>. Values from Blade arrive as data-* attributes and are applied here.

const clamp = (v, max) => Math.min(max, Math.max(0, parseFloat(v) || 0));

// Progress segments: data-w is a percentage.
document.querySelectorAll('.dash-progress [data-w], .dash-split3 [data-w], .dash-hbar [data-w]').forEach((seg) => {
  seg.style.setProperty('--w', String(clamp(seg.dataset.w, 100)));
});

// Filter selects submit their form when changed (a <noscript> button covers no-JS).
document.querySelectorAll('select[data-autosubmit]').forEach((select) => {
  select.addEventListener('change', () => select.form && select.form.submit());
});

// Add scholars: show only the grant choice and the automatic details of the scholarship picked.
// Hidden grant choices are also disabled, so they are not sent. The server re-checks everything.
document.querySelectorAll('[data-roster-form]').forEach((form) => {
  const select = form.querySelector('[data-roster-scholarship]');
  if (!select) return;
  const sync = () => {
    form.querySelectorAll('[data-for-scholarship]').forEach((el) => {
      const on = el.dataset.forScholarship === select.value;
      el.hidden = !on;
      if (el.tagName === 'FIELDSET') el.disabled = !on;
    });
  };
  select.addEventListener('change', sync);
  sync();
});

/**
 * Campus → program (Add scholars and the scholar's profile).
 * Lists only the programs DNSC offers at the chosen campus, like the student's matching form.
 * Without JavaScript every program is listed and the server still refuses one the campus does not offer.
 */
document.querySelectorAll('form').forEach((form) => {
  const campusSel = form.querySelector('[data-campus-select]');
  const programSel = form.querySelector('[data-program-select]');
  if (!campusSel || !programSel) return;
  const placeholder = programSel.options[0];
  const all = Array.from(programSel.options).slice(1);
  const sync = () => {
    const keep = programSel.value;
    programSel.innerHTML = '';
    programSel.appendChild(placeholder);
    all.filter((o) => o.dataset.campus === campusSel.value).forEach((o) => programSel.appendChild(o));
    programSel.value = Array.from(programSel.options).some((o) => o.value === keep) ? keep : '';
    programSel.disabled = !campusSel.value;
    placeholder.textContent = campusSel.value ? 'Choose the program' : 'Choose the campus first';
  };
  campusSel.addEventListener('change', sync);
  sync();
});

// File preview: [data-file-preview] opens #pv-file-preview with the uploaded document (PDF in a frame,
// JPG / PNG as an image). Without JavaScript (or <dialog>) the link opens the file in a new tab.
(() => {
  const dialog = document.getElementById('pv-file-preview');
  const stage = document.getElementById('pv-preview-stage');
  if (!dialog || !stage || typeof dialog.showModal !== 'function') return;
  const nameEl = document.getElementById('pv-preview-name');
  const metaEl = document.getElementById('pv-preview-meta');
  const iconEl = dialog.querySelector('.pv-preview-icon i');
  const openEl = document.getElementById('pv-preview-open');
  const downloadEl = document.getElementById('pv-preview-download');

  document.addEventListener('click', (event) => {
    const link = event.target.closest('[data-file-preview]');
    if (!link) return;
    event.preventDefault();
    const { filePreview: url, fileDownload: download, fileName: name = 'File', fileMeta: meta = '', fileType: type } = link.dataset;

    stage.replaceChildren();
    stage.classList.add('is-loading');
    const done = () => stage.classList.remove('is-loading');
    let view;
    if (type === 'image') {
      view = document.createElement('img');
      view.alt = name;
    } else {
      view = document.createElement('iframe');
      view.title = name;
    }
    view.addEventListener('load', done, { once: true });
    view.addEventListener('error', done, { once: true });
    view.src = url;
    stage.append(view);

    nameEl.textContent = name;
    metaEl.textContent = meta;
    iconEl.className = 'bi ' + (type === 'image' ? 'bi-file-earmark-image' : 'bi-file-earmark-pdf');
    openEl.href = url;
    downloadEl.href = download || url;
    dialog.showModal();
  });
  // Stop loading the file once the preview is closed.
  dialog.addEventListener('close', () => stage.replaceChildren());
})();

// My scholarships › Maintaining documents: "Add a document" copies the <template> row with a fresh
// index; "Remove" greys a row out (it is left out on save) and can be undone before saving.
// Without JavaScript the form always has one blank row, and Remove is a plain checkbox.
document.querySelectorAll('[data-doc-form]').forEach((form) => {
  const rows = form.querySelector('[data-doc-rows]');
  const tpl = form.querySelector('[data-doc-template]');
  const add = form.querySelector('[data-doc-add]');
  let next = Number(form.dataset.nextIndex || 100);

  const syncRemoved = (box) => {
    const row = box.closest('[data-doc-row]');
    const off = box.checked;
    row.classList.toggle('is-removed', off);
    row.querySelectorAll('input:not([data-doc-remove]), textarea, select').forEach((el) => {
      // (the name is a <textarea>; it is required unless the row is being removed)
      if (el.name.endsWith('[name]')) el.required = !off;
      el.tabIndex = off ? -1 : 0;
    });
    box.nextElementSibling.nextElementSibling.textContent = off ? 'Undo remove' : 'Remove';
  };
  form.addEventListener('change', (e) => { if (e.target.matches('[data-doc-remove]')) syncRemoved(e.target); });
  form.querySelectorAll('[data-doc-remove]').forEach(syncRemoved);

  if (!rows || !tpl || !add) return;
  add.hidden = false;
  add.addEventListener('click', () => {
    const count = rows.querySelectorAll('[data-doc-row]').length + 1;
    const html = tpl.innerHTML.replaceAll('__I__', String(next++)).replaceAll('__N__', String(count));
    rows.insertAdjacentHTML('beforeend', html);
    rows.lastElementChild.querySelector('[name$="[name]"]')?.focus();
  });
});

// Scholars list: every "Terminate" opens the one red confirmation with that scholar's name and form action.
(() => {
  const dialog = document.getElementById('confirm-terminate');
  const form = dialog?.querySelector('[data-terminate-form]');
  if (!dialog || !form || typeof dialog.showModal !== 'function') return;
  document.addEventListener('click', (event) => {
    const link = event.target.closest('[data-terminate-url]');
    if (!link) return;
    event.preventDefault();
    form.action = link.dataset.terminateUrl;
    dialog.querySelector('[data-terminate-who]').textContent = link.dataset.terminateName || 'this scholar';
    dialog.querySelector('[data-terminate-what]').textContent = (link.dataset.terminateScholarship || '') + ' scholarship';
    dialog.showModal();
  });
})();

// Disbursements: "Release" and "Mark selected as released" ask first in #confirm-release (a released
// stipend can't go back). The form is sent only after "Yes, release"; without JavaScript (or <dialog>)
// it is sent straight away and the server still checks the release rule.
(() => {
  const dialog = document.getElementById('confirm-release');
  const go = dialog?.querySelector('[data-release-go]');
  if (!dialog || !go || typeof dialog.showModal !== 'function') return;
  const whoEl = dialog.querySelector('[data-release-who]');
  const whatEl = dialog.querySelector('[data-release-what]');
  const peso = (v) => '₱' + v.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  let pending = null;

  document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || !form.dataset.confirmRelease) return;
    if (form.dataset.releaseOk === '1') { delete form.dataset.releaseOk; return; }   // confirmed: let it go

    let who = form.dataset.releaseWho || 'this stipend';
    let what = form.dataset.releaseWhat || '';
    if (form.dataset.confirmRelease === 'bulk') {
      const ticked = Array.from(document.querySelectorAll(`[data-check="release"][form="${form.id}"]:checked`));
      if (!ticked.length) return;   // nothing selected: the server answers as before
      const total = ticked.reduce((sum, b) => sum + (parseFloat(b.dataset.amount) || 0), 0);
      who = ticked.length === 1 ? '1 stipend' : `${ticked.length} stipends`;
      what = `${peso(total)} in total`;
    }
    event.preventDefault();
    pending = form;
    whoEl.textContent = who;
    whatEl.textContent = what;
    whatEl.hidden = !what;
    dialog.showModal();
  });

  go.addEventListener('click', () => {
    const form = pending;
    pending = null;
    dialog.close();
    if (!form) return;
    form.dataset.releaseOk = '1';
    if (typeof form.requestSubmit === 'function') form.requestSubmit(); else form.submit();
  });
  dialog.addEventListener('close', () => { pending = null; });
})();

// Bulk release: "select all", and the submit button is enabled only when something is ticked.
document.querySelectorAll('[data-check-all]').forEach((all) => {
  const group = all.dataset.checkAll;
  const boxes = Array.from(document.querySelectorAll(`[data-check="${group}"]`));
  const submit = document.querySelector(`[data-bulk-submit="${group}"]`);
  const sync = () => {
    const n = boxes.filter((b) => b.checked).length;
    all.checked = n > 0 && n === boxes.length;
    all.indeterminate = n > 0 && n < boxes.length;
    if (submit) {
      submit.disabled = n === 0;
      submit.lastChild.textContent = n ? `Mark ${n} as released` : 'Mark selected as released';
    }
  };
  all.addEventListener('change', () => { boxes.forEach((b) => { b.checked = all.checked; }); sync(); });
  boxes.forEach((b) => b.addEventListener('change', sync));
  if (!boxes.length) all.disabled = true;
  sync();
});

// My scholarships: a link to a folded section (e.g. #scholar_id from Add scholars) opens that section.
(() => {
  const openFolded = () => {
    let target = null;
    try { target = location.hash.length > 1 ? document.getElementById(decodeURIComponent(location.hash.slice(1))) : null; } catch (e) { /* bad hash */ }
    if (target && target.matches('details.ms-fold')) target.open = true;
  };
  openFolded();
  window.addEventListener('hashchange', openFolded);
})();

