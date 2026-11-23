/*
 * BAKR service worker: makes BAKR installable as an app (PWA).
 *
 * PRIVACY: pages (HTML) are NEVER cached. Every portal page holds personal data
 * (matches, scholar details, documents), so pages always come from the network.
 * When BAKR cannot be reached (no connection, the server's network / Tailscale off, the server down), a page
 * request gets the static offline.html ("BAKR is unreachable") instead: at once on a network error, or after
 * PAGE_TIMEOUT_MS when the connection just hangs.
 * Only BAKR's own static files (css/, js/, images/) are cached, so the app opens fast.
 * Bump VERSION whenever offline.html or this file changes.
 */
const VERSION = 'bakr-v15';   // 2026-11-23: "BAKR is unreachable" offline page + hanging-connection timeout
// A page that has not started answering after this long counts as unreachable (the request keeps going in the
// background; nothing is cancelled). Long enough for a slow but working server.
const PAGE_TIMEOUT_MS = 20000;
const STATIC_CACHE = VERSION + '-static';
const BASE = new URL('./', self.registration.scope);
const OFFLINE_URL = new URL('offline.html', BASE).href;

const PRECACHE = [
    'offline.html',
    'css/bakr-offline.css',
    'images/logo/bakr-logo.png',
    'images/app/icon-192.png',
].map((path) => new URL(path, BASE).href);

const STATIC_DIRS = ['css/', 'js/', 'images/'].map((dir) => new URL(dir, BASE).pathname);

self.addEventListener('install', (event) => {
    event.waitUntil(caches.open(STATIC_CACHE).then((cache) => cache.addAll(PRECACHE)));
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((key) => !key.startsWith(VERSION)).map((key) => caches.delete(key))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    if (request.method !== 'GET') return; // forms, uploads, sign-out: untouched

    const url = new URL(request.url);
    if (url.origin !== self.location.origin) return; // CDNs (Bootstrap, fonts): the browser handles them

    // Pages: always the network; the "unreachable" page only when BAKR cannot be reached.
    if (request.mode === 'navigate') {
        event.respondWith(pageOrUnreachable(request));
        return;
    }

    if (!STATIC_DIRS.some((dir) => url.pathname.startsWith(dir))) return; // file previews, JSON, etc.

    // Images: cache first. CSS / JS: network first so changes show at once, cache when offline.
    const isImage = url.pathname.startsWith(new URL('images/', BASE).pathname);
    event.respondWith(isImage ? cacheFirst(request) : networkFirst(request));
});

/** The page from the network, or offline.html when the network fails or does not answer within PAGE_TIMEOUT_MS. */
function pageOrUnreachable(request) {
    // offline.html is answered at the URL that was asked for (e.g. student/dashboard), so its css/ and images/
    // links are made absolute; "Try again" (empty href) then reloads that same page.
    const unreachable = () => caches.match(OFFLINE_URL).then((page) => page
        ? page.text().then((html) => new Response(
            html.replace(/(href|src)="(css|images)\//g, `$1="${BASE.href}$2/`),
            { status: 503, headers: { 'Content-Type': 'text/html; charset=utf-8', 'Cache-Control': 'no-store' } }))
        : Response.error());
    const timeout = new Promise((resolve) => setTimeout(() => resolve(null), PAGE_TIMEOUT_MS));

    return Promise.race([fetch(request), timeout])
        .then((response) => response || unreachable())
        .catch(unreachable);
}

async function cacheFirst(request) {
    const cached = await caches.match(request);
    if (cached) return cached;
    const response = await fetch(request);
    if (response.ok) (await caches.open(STATIC_CACHE)).put(request, response.clone());
    return response;
}

async function networkFirst(request) {
    try {
        const response = await fetch(request);
        if (response.ok) (await caches.open(STATIC_CACHE)).put(request, response.clone());
        return response;
    } catch (error) {
        const cached = await caches.match(request);
        if (cached) return cached;
        throw error;
    }
}
