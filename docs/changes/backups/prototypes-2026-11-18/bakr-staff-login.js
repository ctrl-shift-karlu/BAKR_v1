/* BAKR — staff prototype logins (scholarLogin, adminLogin).
   PROTOTYPE: no real authentication. Shows the "Signing in…" screen, then opens the static
   dashboard in form[data-dashboard]. A page may set window.bakrLoginCheck(email, password, remember)
   first (see bakr-scholar-login.js); it returns '' to continue or an error message to show.
   Password show/hide is handled by student-auth.js. */
(() => {
  const form = document.querySelector('form[data-dashboard]');
  const overlay = document.getElementById('signingIn');
  const errorEl = document.getElementById('loginError');
  if (!form || !overlay) return;

  const emailEl = form.querySelector('#email');
  const passEl = form.querySelector('#password');
  const rememberEl = form.querySelector('#remember');
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  let busy = false;

  const showError = (msg) => {
    errorEl.textContent = msg;
    errorEl.hidden = false;
  };
  const clearError = () => { errorEl.hidden = true; };
  emailEl.addEventListener('input', clearError);
  passEl.addEventListener('input', clearError);

  const hideOverlay = () => {
    overlay.classList.remove('is-open');
    overlay.hidden = true;
  };

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    if (busy) return;
    const email = emailEl.value.trim().toLowerCase();
    const password = passEl.value;
    if (!email || !password) { showError('Enter your email and password.'); return; }
    if (typeof window.bakrLoginCheck === 'function') {
      const problem = window.bakrLoginCheck(email, password, rememberEl.checked);
      if (problem) { showError(problem); return; }
    }
    busy = true;
    overlay.hidden = false;
    requestAnimationFrame(() => overlay.classList.add('is-open'));
    setTimeout(() => { window.location.href = form.dataset.dashboard; }, reduceMotion ? 300 : 1400);
  });

  // Back button from the dashboard: don't leave the overlay stuck on screen.
  window.addEventListener('pageshow', (e) => {
    if (e.persisted) { busy = false; hideOverlay(); }
  });
})();
