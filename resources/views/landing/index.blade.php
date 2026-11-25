<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Portfolio CMS — Build a portfolio that gets you noticed</title>
    <meta name="description" content="Create a stunning personal portfolio, showcase your skills and projects, and build your professional identity — all in one place.">
    <meta name="theme-color" content="#06070d">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300..700;1,9..144,300..600&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/landing.css', 'resources/js/landing.js'])
    @else
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
        <style>
            body { background: #06070d; color: #f4f4ff; font-family: Inter, sans-serif; }
        </style>
    @endif

    @stack('styles')
</head>
<body class="landing-body">
    <a class="skip-link" href="#main">Skip to content</a>

    <div class="scroll-progress" data-scroll-progress aria-hidden="true"></div>
    <div class="cursor-aura" aria-hidden="true"></div>

    <div class="landing">
        @include('landing.partials.navbar')

        <main id="main">
            @include('landing.partials.hero')
            @include('landing.partials.portfolio-showcase')
            @include('landing.partials.features')
            @include('landing.partials.how-it-works')
            @include('landing.partials.customization')
            @include('landing.partials.benefits')
            @include('landing.partials.faq')
            @include('landing.partials.final-cta')
        </main>

        @include('landing.partials.footer')
    </div>

    @stack('scripts')
</body>
</html>
