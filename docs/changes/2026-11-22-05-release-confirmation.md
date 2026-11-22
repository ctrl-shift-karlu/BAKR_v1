# 2026-11-22 — Confirmation before releasing a stipend

**Why:** a released stipend can't go back to "Not yet released", so Release now asks first.

## What changed
- `resources/views/provider/disbursements.blade.php`: one `<dialog id="confirm-release" class="dash-dialog dash-confirm">` (same pattern as `#confirm-terminate`, green `is-ok` icon, Cancel + "Yes, release" in the sticky `.dash-dialog-foot`). Each row's Release form carries `data-confirm-release="one"` + `data-release-who` / `data-release-what` (amount · scholarship code · month or semester); the bulk form carries `data-confirm-release="bulk"` and now `data-busy="Releasing…"`; each checkbox has `data-amount`.
- `public/js/bakr-provider.js`: catches the submit, fills the dialog (bulk = "N stipends", total ₱), sends the form with `requestSubmit()` only after "Yes, release" (so `bakr-busy.js` still shows its card). Enter in the reference box also asks first.
- `public/sw.js`: VERSION `bakr-v12`.

No controller, route or database change: the server still enforces `Scholar::releaseBlocker()`. Without JavaScript the forms send straight away as before.

Tests: 323/323 pass.
