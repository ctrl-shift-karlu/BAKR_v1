# 2026-11-20 · App opens on the homepage, FAQ label removed, smooth "app mode"

Owner's requests: (1) the installed app opens on the homepage, not the sign-ins; (2) remove the "Need to know" pill above the FAQ; (3) make the app smooth: no animation delays or hangs.

## Changes

1. `public/manifest.json` `start_url` = `./` (homepage). `/app` (`AppStartController`, portal picker) stays as a page but is no longer where the app opens. `offline.html` "Try again" → homepage. `sw.js` `VERSION` → `bakr-v2`.
2. `home.blade.php`: the FAQ section no longer has the `nx-story-label` "Need to know".
3. **App mode** (only inside the installed app; a normal browser tab is unchanged):
   - `public/js/bakr-app-mode.js` (first in `partials/pwa-head`, NOT deferred): on `display-mode: standalone` sets `html.is-app` + `bk-intro-seen` before the first paint, and skips cross-document view transitions (`pageswap` / `pagereveal` → `skipTransition()`).
   - `public/css/bakr-app-mode.css`: no animations, no transitions, no `backdrop-filter` (the costliest effect on phone GPUs), scroll-reveal content visible at once, no preloader, no view-transition fade; firmer fill on the hero "glass" cards; loading spinners / the top bar keep moving.
   - `public/js/bakr-core.js`: `isApp` counts as reduced motion (preloader hidden at once, instant in-page scrolling, no count-up), reveal skips the observer, `#hash` landing without the 700 ms wait.
   - `public/js/bakr-pwa.js`: in the app, Speculation Rules **prefetch** (eagerness `moderate`: on touch / hover) of same-site links, never downloads / file previews (`?inline=`, `/file`, `/template`, `/download`, `/export`), new tabs, dialogs or `data-no-busy`. Prefetch only, no prerender. Patterns checked in headless Chrome.
- Test: `InstallableAppTest` (start_url `./`, app-mode assets on the page, no "Need to know"). Full suite 274/274.

## Note

An already-installed app updates its start page from the manifest by itself (Chrome checks about once a day). To see it at once: uninstall the app and install it again.
