const CACHE_NAME = 'african-market-shell-v3';
const APP_SHELL = ['/', '/shop', '/blog', '/sitemap.xml'];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(APP_SHELL)).catch(() => Promise.resolve())
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => Promise.all(
            keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
        ))
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') {
        return;
    }

    const requestUrl = new URL(event.request.url);
    if (requestUrl.origin !== self.location.origin) {
        return;
    }

    const skipDynamicCache =
        requestUrl.pathname === '/site.webmanifest'
        || requestUrl.pathname === '/favicon.svg'
        || requestUrl.pathname === '/favicon.ico'
        || requestUrl.pathname.startsWith('/favicon-')
        || requestUrl.pathname === '/apple-touch-icon.png';

    if (skipDynamicCache) {
        event.respondWith(fetch(event.request));
        return;
    }

    // Navigations (HTML pages): network-first so updated pages always load,
    // falling back to cache / offline shell only when the network is unavailable.
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request)
                .then((response) => {
                    const copy = response.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(event.request, copy)).catch(() => {});
                    return response;
                })
                .catch(() =>
                    caches.match(event.request).then(
                        (cached) => cached || caches.match('/').then((shell) => shell || Response.error())
                    )
                )
        );
        return;
    }

    // Static assets: cache-first with background refresh for performance.
    event.respondWith(
        caches.match(event.request).then((cached) => {
            if (cached) {
                fetch(event.request)
                    .then((response) => {
                        caches.open(CACHE_NAME).then((cache) => cache.put(event.request, response.clone())).catch(() => {});
                    })
                    .catch(() => {});
                return cached;
            }

            return fetch(event.request)
                .then((networkResponse) => {
                    const responseCopy = networkResponse.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(event.request, responseCopy)).catch(() => {});
                    return networkResponse;
                })
                .catch(() => caches.match('/'));
        })
    );
});
