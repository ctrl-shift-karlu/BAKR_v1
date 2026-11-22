// Show / hide password buttons.
document.querySelectorAll('[data-toggle-password]').forEach((btn) => {
  btn.addEventListener('click', () => {
    const input = document.getElementById(btn.dataset.togglePassword);
    if (!input) return;
    const show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    btn.querySelector('i').className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
  });
});

/**
 * Keep the temporary password while it is being replaced (first sign-in of a scholar).
 * An input with data-retain="key" is remembered in sessionStorage when its form is sent. If the server sends
 * the form back with errors (new password too weak, not confirmed…), the value is put back and the focus
 * moves to the new-password field. Nothing goes to the server or any cookie: it lives only in this tab,
 * and is removed as soon as it has been restored (a successful change leaves the page, so it is never used).
 */
document.querySelectorAll('input[data-retain]').forEach((input) => {
  const key = 'bakrRetain:' + input.dataset.retain;
  const form = input.form;
  try {
    const saved = sessionStorage.getItem(key);
    sessionStorage.removeItem(key);
    if (saved && document.querySelector('.invalid-feedback, .alert-danger')) {
      input.value = saved;
      const next = document.getElementById('password');
      if (next && !input.classList.contains('is-invalid')) next.focus();
      else input.focus();
    } else {
      input.focus();
    }
  } catch (e) { /* storage blocked: the field simply stays empty */ }
  if (form) {
    form.addEventListener('submit', () => {
      try { sessionStorage.setItem(key, input.value); } catch (e) { /* ignore */ }
    });
  }
});
