/*
 * BAKR service worker: makes BAKR installable as an app (PWA).
 *
 * PRIVACY: pages (HTML) are NEVER cached. Every portal page holds personal data
 * (matches, scholar details, documents), so pages always come from the network.
 * Offline, a page request gets the static offline.html instead.
 * Only BAKR's own static files (css/, js/, images/) are cached, so the app opens fast.
 * Bump VERSION whenever offline.html or this file changes.
 */
const VERSION = 'bakr-v11';   // 2026-11-21: per-scholarship semester deadlines on Requirements (bakr-provider.css)
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

    // Pages: always the network; the offline page only when there is no connection.
    if (request.mode === 'navigate') {
        event.respondWith(fetch(request).catch(() => caches.match(OFFLINE_URL)));
        return;
    }

    if (!STATIC_DIRS.some((dir) => url.pathname.startsWith(dir))) return; // file previews, JSON, etc.

    // Images: cache first. CSS / JS: network first so changes show at once, cache when offline.
    const isImage = url.pathname.startsWith(new URL('images/', BASE).pathname);
    event.respondWith(isImage ? cacheFirst(request) : networkFirst(request));
});

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
