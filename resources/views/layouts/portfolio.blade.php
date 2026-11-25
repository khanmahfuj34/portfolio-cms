<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if (!empty($isPreview) && $isPreview)
        <meta name="referrer" content="no-referrer">
        <meta name="robots" content="noindex, nofollow">
    @endif
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
            --color-brass: #C5A059;
            --color-brass-hover: #B69149;
            --color-brass-muted: #E2C78E;
            --color-bg-base: #0D0F14;
            --color-bg-surface: #131720;
            --color-bg-elevated: #1A202C;
            --color-hairline: rgba(255, 255, 255, 0.08);
        }

        body {
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, -apple-system, sans-serif;
            background-color: var(--color-bg-base);
            color: #F1F5F9;
        }

        /* Quiet scrollbars */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #0D0F14;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.12);
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.25);
        }
    </style>
</head>
<body class="min-h-full flex flex-col bg-[#0D0F14] text-slate-100 antialiased selection:bg-[#C5A059]/25 selection:text-[#E2C78E]">

    @yield('content')

</body>
</html>
