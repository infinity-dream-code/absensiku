const CACHE_NAME = 'absensi-ict-v3';
const urlsToCache = [
  // Hanya cache static assets, JANGAN cache HTML pages
  '/manifest.json',
  '/logo-512.png',
  'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
  'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css',
  'https://cdn.jsdelivr.net/npm/sweetalert2@11',
  'https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js'
];

// Install event - cache resources
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('Opened cache');
        return cache.addAll(urlsToCache.map(url => {
          try {
            return new Request(url, {mode: 'no-cors'});
          } catch (e) {
            return url;
          }
        })).catch(err => {
          console.log('Cache addAll error:', err);
        });
      })
  );
  self.skipWaiting();
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            console.log('Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    }).then(() => {
      // Hapus semua cache HTML pages yang mungkin tersimpan
      return caches.open(CACHE_NAME).then((cache) => {
        return cache.keys().then((keys) => {
          return Promise.all(
            keys.map((request) => {
              // Hapus cache untuk HTML pages
              if (request.destination === 'document' || 
                  request.url.includes('/login') ||
                  request.url.includes('/logout') ||
                  request.url.includes('/attendance') ||
                  request.url.includes('/admin')) {
                return cache.delete(request);
              }
            })
          );
        });
      });
    })
  );
  self.clients.claim();
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', (event) => {
  // Skip cross-origin requests
  if (!event.request.url.startsWith(self.location.origin) && 
      !event.request.url.startsWith('https://cdnjs.cloudflare.com') &&
      !event.request.url.startsWith('https://cdn.jsdelivr.net')) {
    return;
  }

  // JANGAN cache HTML pages, POST requests, atau request dengan credentials
  // Ini penting untuk CSRF token dan session
  if (event.request.method === 'POST' || 
      event.request.mode === 'cors' ||
      event.request.credentials === 'include' ||
      event.request.url.includes('/csrf-token') ||
      event.request.url.includes('/clear-session-message') ||
      event.request.url.includes('/login') ||
      event.request.url.includes('/logout') ||
      event.request.destination === 'document') {
    // Langsung fetch dari network tanpa cache
    return event.respondWith(fetch(event.request));
  }

  // Hanya cache static assets (CSS, JS, images)
  event.respondWith(
    caches.match(event.request)
      .then((response) => {
        // Return cached version untuk static assets
        if (response) {
          return response;
        }
        
        return fetch(event.request).then((response) => {
          // Don't cache non-successful responses
          if (!response || response.status !== 200 || response.type !== 'basic') {
            return response;
          }

          // Hanya cache static assets
          const url = new URL(event.request.url);
          const isStaticAsset = url.pathname.match(/\.(css|js|png|jpg|jpeg|gif|svg|woff|woff2|ttf|eot|ico)$/i);

          if (isStaticAsset) {
            // Clone the response
            const responseToCache = response.clone();

            caches.open(CACHE_NAME)
              .then((cache) => {
                cache.put(event.request, responseToCache);
              });
          }

          return response;
        }).catch(() => {
          // Return cached static assets jika network error
          return caches.match(event.request);
        });
      })
  );
});
