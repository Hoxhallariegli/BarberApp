importScripts('https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.0.0/firebase-messaging-compat.js');

// Këto do të plotësohen automatikisht nga konfigurimi yt në të ardhmen
// Për momentin, Service Worker thjesht lejon marrjen e njoftimeve në background.

self.addEventListener('push', function(event) {
    const data = event.data.json();
    const options = {
        body: data.notification.body,
        icon: '/apple-touch-icon.png', // Vendos ikonën tënde këtu
        badge: '/favicon.ico'
    };

    event.waitUntil(
        self.registration.showNotification(data.notification.title, options)
    );
});
