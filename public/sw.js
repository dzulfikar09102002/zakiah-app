/*
 * Service worker Zakiah Backoffice (PWA).
 *
 * Strategi:
 * - Halaman (navigasi): selalu ke jaringan. HTML berisi data user & token CSRF,
 *   jadi tidak di-cache; bila offline tampilkan /offline.html.
 * - Aset build Vite (/build/assets/*, nama file ber-hash): cache-first.
 * - Ikon, logo, font: stale-while-revalidate.
 * - Request lain (Inertia XHR, endpoint JSON, POST): tidak disentuh.
 */
const VERSION = 'v1';
const PRECACHE = `zakiah-precache-${VERSION}`;
const ASSETS = `zakiah-assets-${VERSION}`;
const STATIC = `zakiah-static-${VERSION}`;
const OFFLINE_URL = '/offline.html';

const PRECACHE_URLS = [
    OFFLINE_URL,
    '/manifest.webmanifest',
    '/pwa/icon-192.png',
    '/pwa/icon-512.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches
            .open(PRECACHE)
            .then((cache) => cache.addAll(PRECACHE_URLS))
            .then(() => self.skipWaiting()),
    );
});

self.addEventListener('activate', (event) => {
    const keep = [PRECACHE, ASSETS, STATIC];

    event.waitUntil(
        caches
            .keys()
            .then((keys) =>
                Promise.all(
                    keys
                        .filter((key) => !keep.includes(key))
                        .map((key) => caches.delete(key)),
                ),
            )
            .then(() => self.clients.claim()),
    );
});

const cacheFirst = async (request, cacheName) => {
    const cached = await caches.match(request);
    if (cached) {
        return cached;
    }

    const response = await fetch(request);
    if (response.ok) {
        const cache = await caches.open(cacheName);
        cache.put(request, response.clone());
    }

    return response;
};

const staleWhileRevalidate = async (request, cacheName) => {
    const cache = await caches.open(cacheName);
    const cached = await cache.match(request);

    const network = fetch(request)
        .then((response) => {
            if (response.ok || response.type === 'opaque') {
                cache.put(request, response.clone());
            }

            return response;
        })
        .catch(() => cached);

    return cached || network;
};

self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(
                async () =>
                    (await caches.match(OFFLINE_URL)) ||
                    new Response('Offline', { status: 503 }),
            ),
        );

        return;
    }

    const isFont =
        url.hostname === 'fonts.bunny.net' ||
        url.hostname === 'fonts.googleapis.com' ||
        url.hostname === 'fonts.gstatic.com';

    if (isFont) {
        event.respondWith(staleWhileRevalidate(request, STATIC));

        return;
    }

    if (url.origin !== self.location.origin) {
        return;
    }

    if (url.pathname.startsWith('/build/assets/')) {
        event.respondWith(cacheFirst(request, ASSETS));

        return;
    }

    if (
        url.pathname.startsWith('/pwa/') ||
        url.pathname.startsWith('/assets/images/')
    ) {
        event.respondWith(staleWhileRevalidate(request, STATIC));
    }
});
