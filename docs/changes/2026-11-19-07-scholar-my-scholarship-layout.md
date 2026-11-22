# 2026-11-19 — Scholar "My scholarship" uses the provider's layout

Owner request: the scholar's My scholarship page should look like the provider's My scholarships page, with collapsible sections.

## Changed
- `resources/views/scholar/scholarship.blade.php`: profile header (`ms-hero`) + collapsible `details.ms-fold` sections (Scholarship information open by default, Benefits, Documents to keep your scholarship, Stipends). Read-only; same data and logic as before (`maintainingDocuments()`, `offers()`, `releaseBlocker()`).
- `resources/views/scholar/layout.blade.php`: also loads `css/bakr-provider.css` (before `bakr-dashboard.css`, same order as the provider layout) so the shared `ms-*` / `pv-*` styles apply.

## Database
None. Display only; every value is still read live from `scholarships`, `scholars` and `stipend_disbursements`, so provider edits show on this page immediately.

## Tests
`php artisan test`: 244/244 pass.
