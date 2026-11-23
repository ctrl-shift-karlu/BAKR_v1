# 2026-11-23 — "BAKR is unreachable" and "Server error" pages

**Roadmap item(s):** owner's request: clear pages when Tailscale is off (BAKR unreachable) and when XAMPP is off.

## BAKR unreachable (Tailscale off, no connection, server down)
- The server never receives these requests, so the browser has to answer. `public/sw.js` (VERSION bakr-v15):
  page loads go to the network; on a network error, or when nothing comes back within `PAGE_TIMEOUT_MS` (20 s),
  the cached `public/offline.html` is shown (status 503, never cached). Its css/ and images/ links are made
  absolute, so it is styled at any URL (before, it was unstyled on nested pages such as student/dashboard).
- `public/offline.html`: "BAKR is unreachable" / "BAKR is unreachable or down at the moment. Please try again
  later." and one **Try again** button (reloads the same page).
- Works for every browser / installed app that has opened BAKR before (service worker installed). A first visit
  with the server unreachable shows the browser's own error page: nothing on the server can run then.

## XAMPP off (server error)
- `resources/views/errors/database.blade.php`: "Server error" / "BAKR is having a server problem at the moment.
  Please try again in a few minutes." and one **Try again** button. No "database" wording, no Home button, no
  hint or error code. `DatabaseStatus` JSON message uses the same words. `bakr-error.css`: unused rules removed.

## Tests
- `InstallableAppTest` (service worker + offline page wording, one button) and `DatabaseUnavailableTest`
  (Server error, no "database", one link) updated. Full suite + Pint below.
