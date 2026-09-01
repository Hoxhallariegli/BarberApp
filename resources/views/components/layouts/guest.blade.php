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
<body class="bg-paper dark:bg-ink font-body antialiased grain">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
        <div class="blade-stripe w-full fixed top-0"></div>

        <div class="w-full sm:max-w-md mt-6 px-6 py-8 bg-paper-soft dark:bg-ink-soft shadow-xl overflow-hidden sm:rounded-2xl border border-gray-200 dark:border-gray-700 relative z-10">
            {{ $slot }}
        </div>
    </div>
</body>
</html>
