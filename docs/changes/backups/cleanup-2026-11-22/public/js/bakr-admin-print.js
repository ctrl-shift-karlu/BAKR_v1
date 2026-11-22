/* BAKR admin Analytics: Print / Save as PDF.
   Both buttons open the browser's print window ("Save as PDF" is a printer choice there; the tip says so).
   For any print (these buttons or Ctrl+P) the page switches to light colours and goes back afterwards.
   The print styles in bakr-admin.css show every row of the collapsed tables and lists. */
(function () {
  'use strict';

  var root = document.documentElement;
  var savedTheme = null;

  window.addEventListener('beforeprint', function () {
    savedTheme = root.getAttribute('data-theme');
    root.setAttribute('data-theme', 'light');
  });
  window.addEventListener('afterprint', function () {
    if (savedTheme === null) root.removeAttribute('data-theme'); else root.setAttribute('data-theme', savedTheme);
  });

  document.querySelectorAll('[data-print]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var tip = document.getElementById('an-pdf-tip');
      var pdf = btn.hasAttribute('data-print-pdf');
      if (tip) tip.hidden = !pdf;
      // Let the tip paint first, so it is on screen behind the print window.
      window.setTimeout(function () { window.print(); }, pdf ? 60 : 0);
    });
  });
})();
