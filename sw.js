// sw.js - Versão Segura
const CACHE_NAME = 'ieccp-v3-safe'; // Mudei para v3
const urlsToCache = [
  '/',
  '/index.php',
  '/manifest.json',
  '/styles/global.css', 
  '/styles/home.css',
  '/img/logo.png',
  '/img/favicon2.png'
];

// 1. INSTALAÇÃO
self.addEventListener('install', (event) => {
  self.skipWaiting(); // Força atualização imediata
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('Cacheando arquivos essenciais...');
        return cache.addAll(urlsToCache);
      })
      .catch((err) => {
        console.error('ERRO NO CACHE: Verifique se todos os arquivos da lista existem!', err);
      })
  );
});

// 2. ATIVAÇÃO (Limpa o cache antigo do index.html)
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            console.log('Deletando cache antigo:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  self.clients.claim(); // Assume o controle imediatamente
});

// 3. INTERCEPTAÇÃO
self.addEventListener('fetch', (event) => {
  // Apenas requisições GET para o próprio site
  if (event.request.method !== 'GET' || !event.request.url.startsWith(self.location.origin)) {
    return;
  }

  event.respondWith(
    fetch(event.request)
      .then((networkResponse) => {
        // Se a rede funcionar, atualiza o cache com a versão nova
        const responseClone = networkResponse.clone();
        caches.open(CACHE_NAME).then((cache) => cache.put(event.request, responseClone));
        return networkResponse;
      })
      .catch(() => {
        // Se estiver offline, tenta o cache
        return caches.match(event.request).then((cachedResponse) => {
            return cachedResponse || caches.match('/'); // Se falhar tudo, joga pra home
        });
      })
  );
});