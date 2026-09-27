const CACHE_NAME = 'kuran-radyo-v4';
const ASSETS_TO_CACHE = [
  './',
  './index.php',
  './assets/css/style.css',
  './assets/js/player.js',
  './assets/js/main.js',
  './assets/images/logo.webp',
  './assets/images/icon-192.png',
  './assets/images/icon-512.png',
  './manifest.json'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(ASSETS_TO_CACHE);
    })
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys.map((key) => {
          if (key !== CACHE_NAME) {
            return caches.delete(key);
          }
        })
      );
    })
  );
  self.skipWaiting();
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  if (event.request.url.includes('.mp3') || event.request.url.includes('stream') || event.request.url.includes('radio') || event.request.url.includes('backup.qurango') || event.request.url.includes('api.php')) {
    event.respondWith(fetch(event.request));
    return;
  }
  
  event.respondWith(
    caches.match(event.request).then((cachedResponse) => {
      return cachedResponse || fetch(event.request).catch(() => cachedResponse);
    })
  );
});
