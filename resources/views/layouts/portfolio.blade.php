<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ Str::limit($profile?->bio ?? $portfolio->description ?? 'Professional Portfolio', 160) }}">

    <title>{{ $profile?->full_name ?? $portfolio->name }} | Portfolio</title>

    <!-- Google / Bunny Fonts (Instrument Sans) -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Vite Assets / Tailwind Fallback -->
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
            --color-charcoal-card: #141822;
            --color-charcoal-border: #1E2330;
        }

        body {
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, -apple-system, sans-serif;
            background-color: var(--color-charcoal-deep);
            color: #F8FAFC;
        }

        /* Custom subtle scrollbars */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #0B0D12;
        }
        ::-webkit-scrollbar-thumb {
            background: #1E2330;
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #D4AF69;
        }
    </style>
</head>
<body class="min-h-full flex flex-col bg-[#0B0D12] text-slate-100 antialiased selection:bg-[#D4AF69]/20 selection:text-[#D4AF69]">

    @yield('content')

</body>
</html>
