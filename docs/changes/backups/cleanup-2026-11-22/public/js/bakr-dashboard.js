// BAKR — shared dashboard behaviour (student and provider portals). No inline scripts.

// Mobile burger: [data-dash-burger] opens the sidebar (#dash-side) as a drawer below 992px.
// [data-dash-close] (close button, backdrop), Esc, and following a link close it again.
(() => {
  const burger = document.querySelector('[data-dash-burger]');
  const side = document.getElementById('dash-side');
  const shell = side?.closest('.dash');
  if (!burger || !side || !shell) return;
  const mobile = window.matchMedia('(max-width: 991.98px)');

  const setOpen = (open, { restoreFocus = true } = {}) => {
    shell.classList.toggle('is-nav-open', open);
    document.body.classList.toggle('is-nav-locked', open);
    burger.setAttribute('aria-expanded', String(open));
    burger.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
    if (open) {
      (side.querySelector('[aria-current="page"]') || side.querySelector('a, button, summary'))?.focus({ preventScroll: true });
    } else if (restoreFocus) {
      burger.focus({ preventScroll: true });
    }
  };

  burger.addEventListener('click', () => setOpen(!shell.classList.contains('is-nav-open')));
  shell.querySelectorAll('[data-dash-close]').forEach((el) => el.addEventListener('click', () => setOpen(false)));
  side.addEventListener('click', (e) => {
    // Any link (a page, or a dialog such as "About BAKR" / "Sign out") closes the drawer first.
    if (e.target.closest('a[href]') && mobile.matches) setOpen(false, { restoreFocus: false });
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && shell.classList.contains('is-nav-open')) setOpen(false);
  });
  // Going back to desktop width: never leave the page locked.
  mobile.addEventListener('change', (e) => { if (!e.matches) setOpen(false, { restoreFocus: false }); });
})();

// Dialogs: [data-open-dialog="id"] opens <dialog id="id">; [data-close-dialog] and a click on the backdrop close it.
document.querySelectorAll('[data-open-dialog]').forEach((opener) => {
  opener.addEventListener('click', (event) => {
    const dialog = document.getElementById(opener.dataset.openDialog);
    if (!dialog || typeof dialog.showModal !== 'function') return;   // no <dialog> support: follow the link
    event.preventDefault();
    dialog.showModal();
  });
});

document.querySelectorAll('dialog.dash-dialog').forEach((dialog) => {
  dialog.querySelectorAll('[data-close-dialog]').forEach((btn) => btn.addEventListener('click', () => dialog.close()));
  dialog.addEventListener('click', (event) => { if (event.target === dialog) dialog.close(); });

  // <dialog data-auto-open>: the server asks for it on this page load (e.g. "Finish your matching form first").
  if (dialog.hasAttribute('data-auto-open') && typeof dialog.showModal === 'function') {
    dialog.showModal();
    return;
  }

  // First visit: show "About BAKR" once per browser and portal.
  const portal = dialog.dataset.firstVisit;
  if (!portal || typeof dialog.showModal !== 'function') return;
  // Never stack two modals: a page that opens its own (data-auto-open) waits for the next visit.
  if (document.querySelector('dialog[data-auto-open]')) return;
  const key = 'bakr.aboutSeen.' + portal;
  let seen = true;
  try { seen = localStorage.getItem(key) === '1'; } catch (e) { /* storage blocked: don't nag */ }
  if (!seen) {
    dialog.showModal();
    dialog.addEventListener('close', () => { try { localStorage.setItem(key, '1'); } catch (e) { /* ignore */ } }, { once: true });
  }
});

// Deep links: /student/results#match-12 opens the dialog named by that element's data-hash-dialog
// (used by the dashboard "Why?" links and after "Explain with AI").
const openDialogFromHash = () => {
  let target = null;
  try { target = location.hash.length > 1 ? document.getElementById(decodeURIComponent(location.hash.slice(1))) : null; } catch (e) { /* bad hash */ }
  const dialog = target && target.dataset.hashDialog ? document.getElementById(target.dataset.hashDialog) : null;
  if (dialog && typeof dialog.showModal === 'function' && !dialog.open) dialog.showModal();
};
openDialogFromHash();
window.addEventListener('hashchange', openDialogFromHash);

// Folded cards (<details class="ms-fold" id="…">): a link or URL naming the card, or anything inside it, opens it
// (e.g. Profile › "Request a correction" → #corrections). In-page links are handled before bakr-core scrolls.
(() => {
  const openFold = (hash) => {
    let target = null;
    try { target = hash && hash.length > 1 ? document.getElementById(decodeURIComponent(hash.slice(1))) : null; } catch (e) { /* bad hash */ }
    const fold = target?.closest('details.ms-fold, details.sc-collapse');
    if (fold && !fold.open) fold.open = true;
  };
  openFold(location.hash);
  window.addEventListener('hashchange', () => openFold(location.hash));
  document.addEventListener('click', (e) => {
    const a = e.target.closest ? e.target.closest('a[href*="#"]') : null;
    if (!a) return;
    let url;
    try { url = new URL(a.href, location.href); } catch (err) { return; }
    if (url.pathname === location.pathname && url.hash) openFold(url.hash);
  }, true);
})();

// <form data-confirm="Question?">: ask before a risky action (e.g. admin › Deactivate account).
// Without JavaScript the form simply submits.
document.addEventListener('submit', (e) => {
  const form = e.target.closest('form[data-confirm]');
  if (form && !window.confirm(form.dataset.confirm)) e.preventDefault();
});

/**
 * Table labels for phones. Narrow tables turn each row into a small block of "label  value" lines
 * (see the container query in bakr-dashboard.css). The label of a cell is its column header, copied here
 * so the views need no extra markup. A cell that already has data-label keeps it; cells under a
 * colspan (a "nothing yet" row) get none.
 */
document.querySelectorAll('.dash-table').forEach((table) => {
  const heads = Array.from(table.querySelectorAll('thead th')).map((th) => th.textContent.trim());
  if (!heads.length) return;
  table.querySelectorAll('tbody tr').forEach((row) => {
    let col = 0;
    Array.from(row.children).forEach((cell) => {
      if (cell.tagName === 'TD' && !cell.hasAttribute('data-label') && heads[col] && !(cell.colSpan > 1)) {
        cell.setAttribute('data-label', heads[col]);
      }
      col += cell.colSpan || 1;
    });
  });
});
