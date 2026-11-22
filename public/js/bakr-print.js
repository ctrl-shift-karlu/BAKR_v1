// BAKR: printable form toolbar.
// [data-print]    opens the browser's print window.
// [data-save-pdf] shows how to pick "Save as PDF", then opens the same window. Browsers save
//                 PDFs through their print window; the page title (the reference number) becomes the file name.
document.querySelectorAll('[data-print]').forEach((btn) => btn.addEventListener('click', () => window.print()));

document.querySelectorAll('[data-save-pdf]').forEach((btn) => btn.addEventListener('click', () => {
  const hint = document.getElementById('pdfHint');
  if (hint) hint.hidden = false;
  setTimeout(() => window.print(), 150);   // let the hint paint first, so it is there when the window closes
}));
