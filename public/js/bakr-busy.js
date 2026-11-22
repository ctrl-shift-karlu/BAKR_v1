/* BAKR — loading feedback on every page (styles: public/css/bakr-busy.css).
 *
 * 1  A form with data-busy="Signing in…" (and optional data-busy-sub="Checking your details") shows a
 *    "busy" card once it is really being sent: not when validation or a "Are you sure?" prompt stopped it.
 *    The submit buttons are disabled so nothing is sent twice. Put it on forms that can take a moment:
 *    signing in / out, saving the application, adding or importing scholars, sending an e-mail.
 * 2  Clicking a link to another BAKR page shows a thin bar along the top edge if the page takes longer
 *    than a moment to open. Links that download, open in a new tab, or only jump within the page are ignored.
 *
 * Both clear themselves when the page is shown again (back button) and after a safety timeout, so nothing
 * can stay stuck on screen. Without JavaScript the pages simply work as before.
 */
(() => {
  'use strict';

  let dialog = null;
  let titleEl = null;
  let subEl = null;
  let safety = 0;

  const build = () => {
    if (dialog) return;
    dialog = document.createElement('dialog');
    dialog.className = 'bk-busy';
    dialog.setAttribute('role', 'alertdialog');
    dialog.setAttribute('aria-live', 'polite');
    dialog.setAttribute('aria-labelledby', 'bk-busy-title');
    dialog.innerHTML = '<div class="bk-busy-card"><span class="bk-spinner" aria-hidden="true"></span>'
      + '<p class="bk-busy-title" id="bk-busy-title"></p><p class="bk-busy-sub"></p></div>';
    titleEl = dialog.querySelector('.bk-busy-title');
    subEl = dialog.querySelector('.bk-busy-sub');
    dialog.addEventListener('cancel', (e) => e.preventDefault());   // Esc does not cancel a send that already started
    document.body.appendChild(dialog);
  };

  const lockButtons = (form, on) => {
    form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach((b) => { b.disabled = on; });
  };

  const hideBusy = () => {
    window.clearTimeout(safety);
    if (dialog && dialog.open) dialog.close();
    document.querySelectorAll('form[data-busy-on]').forEach((f) => { f.removeAttribute('data-busy-on'); lockButtons(f, false); });
  };

  const showBusy = (title, sub) => {
    build();
    titleEl.textContent = title;
    subEl.textContent = sub || '';
    if (!dialog.open) {
      if (typeof dialog.showModal === 'function') dialog.showModal(); else dialog.setAttribute('open', '');
    }
    window.clearTimeout(safety);
    safety = window.setTimeout(hideBusy, 90000);   // a very slow server: give the page back
  };

  document.addEventListener('submit', (e) => {
    const form = e.target;
    if (!(form instanceof HTMLFormElement) || !form.dataset.busy) return;
    // Other handlers (validation, confirm prompts) may still cancel the submit: look after they have run.
    window.setTimeout(() => {
      if (e.defaultPrevented) return;
      form.setAttribute('data-busy-on', '');
      showBusy(form.dataset.busy, form.dataset.busySub);
      lockButtons(form, true);   // the form data was already captured, so disabling now is safe
    }, 0);
  });

  /* ---- top bar while the next page opens ---- */
  let bar = null;
  let barTimer = 0;
  let barSafety = 0;

  const barOff = () => {
    window.clearTimeout(barTimer);
    window.clearTimeout(barSafety);
    if (bar) bar.classList.remove('is-on');
  };
  const barOn = () => {
    window.clearTimeout(barTimer);
    barTimer = window.setTimeout(() => {
      if (!bar) {
        bar = document.createElement('div');
        bar.className = 'bk-topbar';
        bar.setAttribute('aria-hidden', 'true');
        document.body.appendChild(bar);
      }
      bar.classList.add('is-on');
      barSafety = window.setTimeout(barOff, 10000);   // e.g. a link that turned out to be a download
    }, 180);   // quick pages never show it
  };

  // /file = a requirement's download (scholar + provider): it never leaves the page.
  const skipHref = /\/(template|download|export|file)\b|\.(pdf|xlsx?|csv|zip|png|jpe?g)(\?|$)|[?&]inline=/i;

  document.addEventListener('click', (e) => {
    if (e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
    const a = e.target.closest ? e.target.closest('a[href]') : null;
    if (!a || a.target === '_blank' || a.hasAttribute('download') || a.hasAttribute('data-no-busy') || a.hasAttribute('data-open-dialog')) return;
    let url;
    try { url = new URL(a.href, window.location.href); } catch (err) { return; }
    if (!/^https?:$/.test(url.protocol) || url.origin !== window.location.origin) return;
    if (url.pathname === window.location.pathname && url.search === window.location.search && url.hash) return;   // jump within the page
    if (skipHref.test(url.pathname + url.search)) return;
    window.setTimeout(() => { if (!e.defaultPrevented) barOn(); }, 0);
  });

  window.addEventListener('pageshow', () => { hideBusy(); barOff(); });   // back / forward cache
  window.addEventListener('beforeunload', () => window.clearTimeout(safety));
})();
