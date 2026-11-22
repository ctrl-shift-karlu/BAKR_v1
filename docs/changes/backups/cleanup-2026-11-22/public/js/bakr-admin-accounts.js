// BAKR — Admin › Accounts: the filter selects (scholarship, campus, sort) apply as soon as they change.
// Without JavaScript the <noscript> Apply button submits the same form.
document.querySelectorAll('.ac-filters select[data-autosubmit]').forEach((select) => {
  select.addEventListener('change', () => {
    if (select.form) select.form.requestSubmit ? select.form.requestSubmit() : select.form.submit();
  });
});
