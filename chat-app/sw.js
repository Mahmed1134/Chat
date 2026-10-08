self.addEventListener('install', function (e) { self.skipWaiting(); });
self.addEventListener('activate', function (e) { e.waitUntil(self.clients.claim()); });

self.addEventListener('push', function (event) {
  let data = {};
  try { data = event.data ? event.data.json() : {}; }
  catch (e) { data = { title: 'رسالة جديدة', body: event.data ? event.data.text() : '' }; }

  const title = data.title || 'رسالة جديدة';
  const options = {
      body: data.body || '',
      tag: data.tag || undefined,
      renotify: true,
      data: { url: data.url || '/' }
  };
  event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', function (event) {
  event.notification.close();
  const targetUrl = (event.notification.data && event.notification.data.url) || '';
  event.waitUntil(
      self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (list) {
          for (const c of list) { if ('focus' in c) return c.focus(); }
          if (self.clients.openWindow) return self.clients.openWindow(targetUrl);
      })
  );
});
