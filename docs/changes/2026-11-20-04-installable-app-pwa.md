# 2026-11-20 · BAKR as an installable app (PWA)

**Owner's choice:** an installable web app (PWA) for every role, not a native app. Everyone still signs in to their own portal; the app reuses the existing Blade portals (no Node, no API).

## What was added

- `public/manifest.json`: name, icons, `display: standalone`, theme `#143a2b`, start page `./app`, shortcuts to the Student / Scholar / Provider logins. All URLs are **relative**, so it works at `http://localhost:8000/` and at `http://localhost/BAKR/public/`.
- `public/images/app/`: icons generated from `images/logo/bakr-logo.png` (192, 512, maskable 192/512, apple-touch 180).
- `public/sw.js`: service worker. **Pages (HTML) are never cached** (they hold personal data); offline, a page request gets `public/offline.html` (+ `css/bakr-offline.css`). Only `css/`, `js/` and `images/` are cached (CSS/JS network-first, images cache-first). POSTs and CDN files are not touched. Bump `VERSION` when `sw.js` or `offline.html` changes.
- `public/js/bakr-pwa.js` + `public/css/bakr-pwa.css`: registers the worker; "Install the BAKR app" button (`partials/pwa-install`) shown only when the browser can install it; on iPhone/iPad it shows the "Share → Add to Home Screen" steps instead; hidden inside the installed app.
- `partials/pwa-head` (manifest, apple tags, css, script) in `layouts.app`, `layouts.auth`, `provider.layout`, `admin.layout` (student and scholar pages extend `layouts.app`).
- Install button: bottom of every portal sidebar (`partials/portal/side-foot`) and the public footer.
- `/app` (`AppStartController`, `app.start`): signed in → own portal home; guest → `auth/app-start` (pick Student / Scholar / Provider; admin login deliberately not listed).
- Test: `tests/Feature/InstallableAppTest.php` (5 tests). Full suite: **274/274 pass**.

## Limits / next steps

- A service worker only runs on **HTTPS** or `localhost`. On a phone over Wi-Fi (`http://192.168.x.x/...`) the install prompt will NOT appear. To install on a phone, deploy BAKR on an HTTPS host (or use a tunnel such as Cloudflare Tunnel / ngrok for a demo).
- No push notifications yet (would need web-push keys + a subscription table).
- Optional later: an Android `.apk` from the same site (Trusted Web Activity via Bubblewrap / PWABuilder), which needs the HTTPS URL and an `assetlinks.json`.
