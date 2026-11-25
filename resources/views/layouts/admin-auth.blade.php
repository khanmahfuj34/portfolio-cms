<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-paper">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">

    <title>@yield('title', 'Super Admin Login — Portfolio CMS')</title>

    <!-- Google Fonts: Arimo (UI) + Urbanist (display) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Arimo:ital,wght@0,400..700;1,400..700&family=Urbanist:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

    <!-- Vite Assets -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    @endif

    <style>
        @media (prefers-reduced-motion: reduce) {
            *, ::before, ::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
        }
    </style>

    @stack('styles')
</head>
<body class="bg-paper text-ink antialiased font-sans min-h-screen flex flex-col justify-between selection:bg-brass/30 selection:text-ink">

    <main class="flex-1 flex items-center justify-center p-4 sm:p-6 lg:p-8">
        @yield('content')
    </main>

    <footer class="py-6 text-center text-[11px] text-ink-mute">
        <p>&copy; {{ date('Y') }} {{ config('app.name', 'Portfolio CMS') }}</p>
    </footer>

    @stack('scripts')
</body>
</html>
