<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">

    <title>@yield('title', 'Super Admin Login — Portfolio CMS')</title>

    <!-- Google / Bunny Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Vite Assets -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    @endif

    <style>
        :root {
            --color-gold: #D4AF69;
            --color-gold-light: #F2DAA4;
            --color-gold-dark: #A5803B;
            --color-charcoal-deep: #0B0D12;
            --color-charcoal-card: #131720;
            --color-charcoal-input: #181C26;
        }

        /* Ambient Glow & Background Pattern */
        .bg-mesh-dark {
            background-color: var(--color-charcoal-deep);
            background-image: 
                radial-gradient(at 0% 0%, rgba(212, 175, 105, 0.09) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(212, 175, 105, 0.05) 0px, transparent 50%),
                radial-gradient(at 50% 50%, rgba(19, 23, 32, 1) 0px, rgba(11, 13, 18, 1) 100%);
        }

        .gold-gradient-text {
            background: linear-gradient(135deg, #FFF1D0 0%, #D4AF69 50%, #B48D44 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .gold-btn-gradient {
            background: linear-gradient(135deg, #E2BF7D 0%, #D4AF69 45%, #B8934C 100%);
            box-shadow: 0 4px 20px -2px rgba(212, 175, 105, 0.35),
                        inset 0 1px 0 rgba(255, 255, 255, 0.35);
        }

        .gold-btn-gradient:hover {
            background: linear-gradient(135deg, #E8C98E 0%, #DCB874 45%, #C29D55 100%);
            box-shadow: 0 6px 28px 0 rgba(212, 175, 105, 0.55),
                        inset 0 1px 0 rgba(255, 255, 255, 0.45);
        }

        /* Subtle grid lines */
        .bg-grid-subtle {
            background-size: 40px 40px;
            background-image: 
                linear-gradient(to right, rgba(255, 255, 255, 0.02) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.02) 1px, transparent 1px);
        }

        @keyframes subtleShake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-4px); }
            40%, 80% { transform: translateX(4px); }
        }

        .animate-shake {
            animation: subtleShake 0.4s ease-in-out 1;
        }

        /* Accessibility: Reduced motion */
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
<body class="bg-mesh-dark bg-grid-subtle text-slate-200 antialiased min-h-screen flex flex-col justify-between selection:bg-[#D4AF69] selection:text-[#0B0D12]">
    <!-- Top Ambient Glow -->
    <div class="pointer-events-none fixed top-0 left-1/2 -translate-x-1/2 w-full max-w-4xl h-80 bg-[radial-gradient(ellipse_at_top,rgba(212,175,105,0.13),transparent_70%)] blur-2xl z-0" aria-hidden="true"></div>

    <!-- Main Content Slot -->
    <main class="relative z-10 flex-1 flex items-center justify-center p-4 sm:p-6 lg:p-8">
        @yield('content')
    </main>

    <!-- Global Footer -->
    <footer class="relative z-10 py-6 text-center text-xs text-slate-500">
        <div class="flex items-center justify-center gap-2 mb-2">
            <span class="inline-block w-1.5 h-1.5 rounded-full bg-[#D4AF69] shadow-[0_0_6px_#D4AF69]"></span>
            <span class="font-medium tracking-wider uppercase text-slate-400 text-[10px]">Restricted Enterprise Environment</span>
        </div>
        <p>&copy; {{ date('Y') }} {{ config('app.name', 'Portfolio CMS') }}. All rights reserved.</p>
    </footer>

    @stack('scripts')
</body>
</html>
