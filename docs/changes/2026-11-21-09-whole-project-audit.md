# 2026-11-21 · Whole-project audit (UI, links, JS, logic, auth)

## How it was checked

- Full test suite (302/302 before, 306/306 after).
- A crawler (booted the app, signed in as one real account per role and every provider account, all inside a rolled-back
  DB transaction) opened ~490 pages and followed every internal link, form action and static asset: **no 500s**,
  no missing assets, no form posting to a missing route.
- 49 rendered portal pages loaded in headless Chrome at 500 / 820 / 1400px with an error recorder and an overflow probe
  (served from `public/_p/`, deleted afterwards).
- Code read: routes, middleware, every auth controller, uploads/downloads, `ProviderScope`, provider scholar / roster /
  requirement / disbursement / message / scholarship controllers, scholar portal, student dashboard + matching,
  admin actions, and the main JS files.

## Fixed

- **Home "Find Your Scholarship"** linked to `asset('student/application')`: a signed-in scholar, provider or admin got
  a 403. Now `route('find')` like every other such button.
- **Cross-portal redirect after sign-in:** opening e.g. `/provider/scholars` as a guest and then signing in at the
  Student login sent the student to that provider page (403). `Controller::intendedWithin($prefix, $default)` keeps the
  remembered page only when it belongs to the portal just signed in to; used by all four logins.
  Test: `LoginRedirectTest` (4 tests).
- **Provider Scholars and Disbursements tables** scrolled sideways inside their card between ~640 and ~1050px wide
  (tablets, small laptops). New modifier `.dash-table.is-many-cols` (bakr-dashboard.css) stacks them into the
  "label · value" blocks up to a 66rem wrapper. Re-probed: no overflow at 820 / 1100 / 1400px.
- **Matching form "Confirm & save"** used `form.submit()`, which skips the submit event: the "Saving your matching
  form…" card never showed, and after the Back button the save button stayed disabled. Now `form.requestSubmit()`
  (fallback kept) and the button is re-enabled on `pageshow`.
- **Loading bar stuck after a download:** clicking a requirement's Download (`…/requirements/{id}/file`) showed the
  top loading bar for 10 s because no page opens. `bakr-busy.js` now skips `/file` links.
- **Application period:** a results date before the closing date was accepted. `results_on` must now be on/after
  `application_closes_on` too (no existing row breaks it; checked in the local DB).
- `sw.js` → `bakr-v10`.

## Checked, no change needed

- Every URL id is looked up inside the owner's scope (student matches/versions, scholar requirements, provider
  `ProviderScope`, admin `managed()` never touches admins or yourself).
- Uploads: per-document types and size validated server-side, random names on the private disk, previews only
  PDF/JPG/PNG with `nosniff`, terminated scholars refused.
- Stipend release rule, derived Inactive, owed-only counts and the AI "stricter only" merge behave as documented.
- The About-page "video error" in headless Chrome is the headless decoder, not the app (file serves 200 video/mp4).
  Note: the 37 MB file has its `moov` atom at the end; re-encoding with fast-start (and < 6 MB) would let it start sooner.
