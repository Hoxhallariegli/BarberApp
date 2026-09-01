<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Berber App') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Premium Typography: Bebas Neue (display), Fraunces (editorial serif), Manrope (body/UI) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;1,9..144,400;1,9..144,500&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        html{ scroll-behavior:smooth; }
        .font-display{ font-family:var(--font-display); }
        .font-serif{ font-family:var(--font-serif); font-optical-sizing:auto; }
    </style>
</head>
<body class="bg-paper dark:bg-ink font-body antialiased">

<script>
    // Theme Loader
    (function() {
        const savedTheme = localStorage.getItem('theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        if (savedTheme === 'dark' || savedTheme === 'light') {
            document.documentElement.classList.toggle('dark', savedTheme === 'dark');
        } else if (prefersDark) {
            document.documentElement.classList.add('dark');
            localStorage.setItem('theme', 'dark');
        } else {
            localStorage.setItem('theme', 'light');
        }
    })();

    // Global Notification Request Fallback
    window.requestNotificationPermission = function() {
        console.warn('Firebase is not configured or enabled. Notification request ignored.');
    };
</script>

@if(config('firebase_enabled') && config('firebase_web_config'))
    <script src="https://www.gstatic.com/firebasejs/9.1.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.1.1/firebase-messaging-compat.js"></script>
    <script>
        (function() {
            const rawConfig = `{!! config('firebase_web_config') !!}`;
            try {
                const jsonConfig = JSON.parse(rawConfig.trim().replace(/([{,]\s*)([a-zA-Z0-9_]+)\s*:/g, '$1"$2":').replace(/'/g, '"').replace(/,\s*}/g, '}'));
                firebase.initializeApp(jsonConfig);
                const messaging = firebase.messaging();

                messaging.onMessage((payload) => {
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: {
                            message: payload.notification.title + ": " + payload.notification.body,
                            type: 'info'
                        }
                    }));

                    if (Notification.permission === "granted") {
                        new Notification(payload.notification.title, {
                            body: payload.notification.body,
                            icon: '/favicon.ico'
                        });
                    }
                });

                window.requestNotificationPermission = function() {
                    if (!('Notification' in window)) {
                        console.error('This browser does not support desktop notification');
                        return;
                    }

                    Notification.requestPermission().then((permission) => {
                        if (permission === 'granted') {
                            navigator.serviceWorker.ready.then((registration) => {
                                messaging.getToken({ serviceWorkerRegistration: registration }).then((token) => {
                                    if (window.Livewire) {
                                        window.Livewire.dispatch('fcm-token-received', { token: token });
                                    }
                                }).catch((err) => {
                                    console.error('Token error:', err);
                                });
                            });
                        }
                    });
                };

                if ('serviceWorker' in navigator) {
                    navigator.serviceWorker.register('/firebase-messaging-sw.js').then((registration) => {
                        messaging.getToken({ serviceWorkerRegistration: registration }).then((token) => {
                            if (window.Livewire) {
                                window.Livewire.dispatch('fcm-token-received', { token: token });
                            }
                        });
                    });
                }
            } catch (e) { console.error('Firebase Error:', e.message); }
        })();
    </script>
@endif

{{-- Top auth nav --}}
@unless(request()->routeIs('front.berber-app'))
    <x-layouts.landing-header />
@endunless

<div class="{{ request()->routeIs('front.berber-app') ? '' : 'pt-20' }}">
    {{ $slot }}
</div>

</body>
</html>
