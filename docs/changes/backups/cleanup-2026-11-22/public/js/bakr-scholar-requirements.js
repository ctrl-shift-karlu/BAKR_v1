// BAKR — Scholar portal › Requirements (resources/views/scholar/requirements.blade.php).
// Modelled on the scholar prototype's Documents view: accordion rows, an upload dialog with
// drag and drop + file check + real progress, and a file preview. Uploads are real: the file
// is POSTed to scholar.requirements.upload (Accept: application/json), which stores it for that
// scholarship's provider; the page then reloads on that document. Without this script every
// row has a plain <noscript> upload form, so submitting always works.
(() => {
  'use strict';

  // ---------- Accordion rows ----------
  const toggles = Array.from(document.querySelectorAll('[data-doc-toggle]'));
  const setOpen = (btn, open) => {
    const panel = document.getElementById(btn.getAttribute('aria-controls'));
    if (!panel) return;
    btn.setAttribute('aria-expanded', String(open));
    panel.hidden = !open;
    btn.closest('.sc-doc')?.classList.toggle('is-open', open);
  };
  toggles.forEach((btn) => {
    setOpen(btn, false);
    btn.addEventListener('click', () => setOpen(btn, btn.getAttribute('aria-expanded') !== 'true'));
  });
  // A click on the rest of the row (pill, chevron, empty space) toggles it too; buttons keep their own action.
  document.querySelectorAll('.sc-doc-head').forEach((head) => {
    head.addEventListener('click', (e) => {
      if (e.target.closest('button, a')) return;
      head.querySelector('[data-doc-toggle]')?.click();
    });
  });
  // Open the document named in the URL (#req-12, used after an upload) or every row that needs action.
  const openFromHash = () => {
    const row = location.hash.startsWith('#req-') ? document.querySelector(location.hash) : null;
    const btn = row?.querySelector('[data-doc-toggle]');
    if (!btn) return;
    row.closest('details')?.setAttribute('open', '');
    setOpen(btn, true);
    row.scrollIntoView({ block: 'center' });
  };
  openFromHash();
  window.addEventListener('hashchange', openFromHash);

  // ---------- Upload dialog ----------
  const dialog = document.getElementById('sc-upload');
  const form = document.getElementById('sc-upload-form');
  if (!dialog || !form || typeof dialog.showModal !== 'function') return;   // <dialog> is in every current browser

  const $ = (id) => document.getElementById(id);
  const input = $('sc-input');
  const dz = $('sc-dz');
  const fileCard = $('sc-file');
  const errBox = $('sc-upload-error');
  const progress = $('sc-progress');
  const bar = progress.querySelector('span');
  const current = $('sc-upload-current');
  const submitBtn = $('sc-submit');
  const submitLabel = submitBtn.querySelector('span');
  // Defaults; each Upload button carries its own document's rules (data-upload-types / -max-kb / -rules).
  let ALLOWED = (form.dataset.types || 'pdf,jpg,jpeg,png').split(',');
  let MAX_BYTES = Number(form.dataset.maxKb || 5120) * 1024;
  let RULES = 'PDF, JPG or PNG, up to 5 MB';

  let selected = null;
  let busy = false;

  const fmtSize = (b) => (b < 1048576 ? Math.max(1, Math.round(b / 1024)) + ' KB' : (b / 1048576).toFixed(1) + ' MB');
  const ext = (name) => (/\.([^.]+)$/.exec(name) || [])[1]?.toLowerCase() || '';
  const showError = (msg) => { errBox.querySelector('span').textContent = msg; errBox.hidden = false; };
  const hideError = () => { errBox.hidden = true; };

  const validate = (f) => {
    if (!ALLOWED.includes(ext(f.name))) return `"${f.name}" can't be uploaded here. This document takes ${RULES}.`;
    if (f.size === 0) return `"${f.name}" is empty. Choose a different file.`;
    if (f.size > MAX_BYTES) return `"${f.name}" is ${fmtSize(f.size)}, over the ${fmtSize(MAX_BYTES)} limit.`;
    return '';
  };

  const clearFile = () => {
    selected = null;
    input.value = '';
    fileCard.hidden = true;
    dz.hidden = false;
    hideError();
  };

  const setFile = (f) => {
    selected = f;
    $('sc-file-name').textContent = f.name;
    $('sc-file-meta').textContent = `${ext(f.name).toUpperCase()} file, ${fmtSize(f.size)}`;
    dz.hidden = true;
    fileCard.hidden = false;
    hideError();
  };

  const handleFiles = (files) => {
    if (!files || !files.length) return;
    if (files.length > 1) { showError('Add one file at a time.'); return; }
    const problem = validate(files[0]);
    if (problem) { showError(problem); return; }
    setFile(files[0]);
  };

  const setBusy = (on) => {
    busy = on;
    submitBtn.disabled = on;
    submitLabel.textContent = on ? 'Uploading…' : 'Upload';
    dialog.querySelectorAll('[data-close-dialog], #sc-change, #sc-remove, #sc-browse').forEach((b) => { b.disabled = on; });
  };

  document.querySelectorAll('[data-upload-url]').forEach((btn) => {
    btn.addEventListener('click', () => {
      form.action = btn.dataset.uploadUrl;
      ALLOWED = (btn.dataset.uploadTypes || form.dataset.types || 'pdf,jpg,jpeg,png').split(',');
      MAX_BYTES = Number(btn.dataset.uploadMaxKb || form.dataset.maxKb || 5120) * 1024;
      RULES = btn.dataset.uploadRules || RULES;
      if (btn.dataset.uploadAccept) input.accept = btn.dataset.uploadAccept;
      $('sc-dz-rules').textContent = RULES.charAt(0).toUpperCase() + RULES.slice(1) + '.';
      $('sc-upload-name').textContent = btn.dataset.uploadName || 'Document';
      $('sc-upload-meta').textContent = `Goes to ${btn.dataset.uploadProvider || 'your provider'} for review`;
      if (btn.dataset.uploadCurrent) {
        current.textContent = `Current file: ${btn.dataset.uploadCurrent}, sent ${btn.dataset.uploadCurrentDate || ''}. Uploading a new file replaces it and sends it back for review.`;
        current.hidden = false;
      } else {
        current.hidden = true;
      }
      clearFile();
      setBusy(false);
      progress.hidden = true;
      bar.style.width = '0%';
      dialog.showModal();
      $('sc-browse').focus();
    });
  });

  $('sc-browse').addEventListener('click', () => input.click());
  $('sc-change').addEventListener('click', () => input.click());
  $('sc-remove').addEventListener('click', () => { clearFile(); $('sc-browse').focus(); });
  input.addEventListener('change', () => handleFiles(input.files));

  // Drag and drop anywhere in the dialog body.
  const body = dialog.querySelector('.sc-modal-body');
  let depth = 0;
  body.addEventListener('dragenter', (e) => { e.preventDefault(); depth++; dz.classList.add('is-drag'); });
  body.addEventListener('dragover', (e) => { e.preventDefault(); if (e.dataTransfer) e.dataTransfer.dropEffect = 'copy'; });
  body.addEventListener('dragleave', () => { depth = Math.max(0, depth - 1); if (!depth) dz.classList.remove('is-drag'); });
  body.addEventListener('drop', (e) => {
    e.preventDefault();
    depth = 0;
    dz.classList.remove('is-drag');
    if (!busy) handleFiles(e.dataTransfer?.files);
  });
  // A file dropped outside the drop area must not open in the browser while the dialog is up.
  ['dragover', 'drop'].forEach((evt) => window.addEventListener(evt, (e) => { if (dialog.open) e.preventDefault(); }));

  // Escape / backdrop do nothing while the file is being sent.
  dialog.addEventListener('cancel', (e) => { if (busy) e.preventDefault(); });

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    if (busy) return;
    if (!selected) { showError('Choose a file to upload first.'); $('sc-browse').focus(); return; }

    const data = new FormData();
    data.append('_token', form.querySelector('input[name="_token"]').value);
    data.append('file', selected);

    const xhr = new XMLHttpRequest();
    xhr.open('POST', form.action);
    xhr.setRequestHeader('Accept', 'application/json');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.upload.addEventListener('progress', (ev) => {
      if (ev.lengthComputable) bar.style.width = Math.round((ev.loaded / ev.total) * 100) + '%';
    });
    xhr.addEventListener('load', () => {
      let json = {};
      try { json = JSON.parse(xhr.responseText || '{}'); } catch (err) { /* not JSON */ }
      if (xhr.status >= 200 && xhr.status < 300) {
        bar.style.width = '100%';
        // Reload on this document: the server already saved it and set the success message.
        history.replaceState(null, '', location.pathname + location.search + '#req-' + json.id);
        location.reload();
        return;
      }
      setBusy(false);
      progress.hidden = true;
      const firstError = json.errors ? Object.values(json.errors).flat()[0] : null;
      showError(firstError || ({
        409: 'This document is already approved. Nothing to replace.',
        413: `The file is too large for the server. This document takes ${RULES}.`,
        419: 'Your session expired. Reload the page and try again.',
        404: 'This document is no longer available. Reload the page.',
      }[xhr.status]) || json.message || 'The upload did not go through. Try again.');
    });
    xhr.addEventListener('error', () => {
      setBusy(false);
      progress.hidden = true;
      showError('No connection to the server. Check your internet and try again.');
    });

    hideError();
    setBusy(true);
    bar.style.width = '0%';
    progress.hidden = false;
    xhr.send(data);
  });

  // ---------- Preview ----------
  const preview = document.getElementById('sc-preview');
  const stage = document.getElementById('sc-preview-stage');
  document.querySelectorAll('[data-preview-url]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const name = btn.dataset.previewName || 'File';
      stage.replaceChildren();
      if (btn.dataset.previewType === 'image') {
        const img = document.createElement('img');
        img.src = btn.dataset.previewUrl;
        img.alt = name;
        stage.append(img);
      } else {
        const frame = document.createElement('iframe');
        frame.src = btn.dataset.previewUrl;
        frame.title = name;
        stage.append(frame);
      }
      document.getElementById('sc-preview-name').textContent = name;
      document.getElementById('sc-preview-meta').textContent = btn.dataset.previewMeta || '';
      document.getElementById('sc-preview-download').href = btn.dataset.previewDownload;
      preview.showModal();
    });
  });
  preview?.addEventListener('close', () => stage.replaceChildren());
})();
