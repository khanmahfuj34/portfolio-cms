@php
    $ownerName = trim((string) ($profile?->full_name ?: $portfolio->name ?: 'Portfolio'));
    $headline = trim((string) ($profile?->professional_title ?: ''));
    $bioText = trim((string) ($profile?->bio ?: $portfolio->description ?: ''));

    $visibleNav = ($navItems ?? collect())->values();
    $sectionKeys = $visibleNav->pluck('section_key')->filter()->map(fn ($k) => strtolower((string) $k));

    $projectsCollection = ($projects ?? collect())->values();
    $skillsCollection = ($skills ?? collect())->values();

    $aboutItem = $visibleNav->first(fn ($item) => strtolower((string) $item->section_key) === 'about');
    $skillsItem = $visibleNav->first(fn ($item) => strtolower((string) $item->section_key) === 'skills');
    $projectsItem = $visibleNav->first(fn ($item) => strtolower((string) $item->section_key) === 'projects');
    $experienceItem = $visibleNav->first(fn ($item) => strtolower((string) $item->section_key) === 'experience');
    $educationItem = $visibleNav->first(fn ($item) => strtolower((string) $item->section_key) === 'education');
    $contactItem = $visibleNav->first(fn ($item) => strtolower((string) $item->section_key) === 'contact');

    $knownKeys = ['home', 'about', 'skills', 'projects', 'experience', 'education', 'contact'];
    $extraSections = $visibleNav->filter(
        fn ($item) => $item->section_key && ! in_array(strtolower((string) $item->section_key), $knownKeys, true)
    );
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="rm-theme-root">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if (!empty($isPreview) && $isPreview)
        <meta name="referrer" content="no-referrer">
        <meta name="robots" content="noindex, nofollow">
    @endif
    <meta name="description" content="{{ \Illuminate\Support\Str::limit($bioText !== '' ? $bioText : $ownerName . ' Portfolio', 160) }}">

    <title>{{ $ownerName }}{{ $headline !== '' ? ' | ' . $headline : ' | Portfolio' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">

    <script>
        (function () {
            try {
                var saved = window.localStorage.getItem('rm-theme');
                var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (saved === 'dark' || (!saved && prefersDark) || saved === null) {
                    if (saved === 'dark' || (!saved && prefersDark)) {
                        document.documentElement.classList.add('dark');
                    }
                }
            } catch (e) {}
        })();
    </script>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/portfolio-themes/rifah-modern.css', 'resources/js/portfolio-themes/rifah-modern.js'])
    @else
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    @endif
</head>
<body class="rm-body min-h-screen flex flex-col antialiased">

    <div class="rm-progress-bar" data-rm-progress aria-hidden="true"></div>

    @if (!empty($isPreview) && $isPreview)
        <div class="bg-[#C2542D]/15 border-b border-[#C2542D]/30 px-4 py-2 text-center text-xs font-medium text-ink flex items-center justify-center gap-2 relative z-50">
            <span class="inline-block w-2 h-2 rounded-full bg-[#C2542D]"></span>
            <span>Admin Preview Mode: This portfolio is currently in <strong>Draft</strong> and is not publicly visible.</span>
            @if (auth('super_admin')->user()?->isSuperAdmin())
                <a href="{{ app(\App\Services\PortfolioDomainService::class)->baseUrlFor('/admin/profiles', request()) }}" class="underline text-[#C2542D] hover:opacity-80 ml-2">Back to dashboard</a>
            @elseif (auth('admin')->user()?->isAdmin())
                <a href="{{ app(\App\Services\PortfolioDomainService::class)->baseUrlFor('/dashboard/portfolios', request()) }}" class="underline text-[#C2542D] hover:opacity-80 ml-2">Back to dashboard</a>
            @endif
        </div>
    @endif

    @include('portfolio-themes.rifah-modern.partials.navbar', [
        'portfolio' => $portfolio,
        'profile' => $profile,
        'navItems' => $visibleNav,
        'ownerName' => $ownerName,
    ])

    <main class="flex-1">
        @include('portfolio-themes.rifah-modern.partials.hero', [
            'portfolio' => $portfolio,
            'profile' => $profile,
            'ownerName' => $ownerName,
            'headline' => $headline,
            'bioText' => $bioText,
            'projectsCount' => $projectsCollection->count(),
            'hasAboutSection' => (bool) ($aboutItem || $bioText !== ''),
        ])

        @if ($aboutItem || $bioText !== '' || ($profile?->location ?? '') !== '')
            @include('portfolio-themes.rifah-modern.partials.about', [
                'portfolio' => $portfolio,
                'profile' => $profile,
                'ownerName' => $ownerName,
                'headline' => $headline,
                'sectionTitle' => $aboutItem?->label ?: 'About Me',
                'sectionId' => $aboutItem?->section_key ?: 'about',
            ])
        @endif

        @if ($skillsItem || $skillsCollection->isNotEmpty())
            @include('portfolio-themes.rifah-modern.partials.skills', [
                'skills' => $skillsCollection,
                'sectionTitle' => $skillsItem?->label ?: 'Skills',
                'sectionId' => $skillsItem?->section_key ?: 'skills',
            ])
        @endif

        @if ($educationItem)
            @include('portfolio-themes.rifah-modern.partials.education', [
                'portfolio' => $portfolio,
                'profile' => $profile,
                'sectionTitle' => $educationItem->label ?: 'Education',
                'sectionId' => $educationItem->section_key ?: 'education',
            ])
        @endif

        @if ($experienceItem)
            @include('portfolio-themes.rifah-modern.partials.experience', [
                'portfolio' => $portfolio,
                'profile' => $profile,
                'sectionTitle' => $experienceItem->label ?: 'Experience',
                'sectionId' => $experienceItem->section_key ?: 'experience',
            ])
        @endif

        @if ($projectsItem || $projectsCollection->isNotEmpty())
            @include('portfolio-themes.rifah-modern.partials.projects', [
                'projects' => $projectsCollection,
                'sectionTitle' => $projectsItem?->label ?: 'Projects',
                'sectionId' => $projectsItem?->section_key ?: 'projects',
            ])
        @endif

        @foreach ($extraSections as $extraItem)
            <section id="{{ $extraItem->section_key }}" class="py-20 px-6 max-w-4xl mx-auto scroll-mt-24">
                <div class="rm-reveal">
                    <h2 class="font-display text-3xl font-bold mb-2">{{ $extraItem->label }}</h2>
                    <div class="w-16 h-1 bg-[#C2542D] rounded-full mb-8"></div>
                    <div class="p-6 rounded-xl bg-surface border border-ink/10">
                        <p class="text-muted text-sm leading-relaxed">
                            No items have been added to {{ $extraItem->label }} yet.
                        </p>
                    </div>
                </div>
            </section>
        @endforeach

        @if ($contactItem || ($profile?->email ?? '') !== '' || ($profile?->location ?? '') !== '')
            @include('portfolio-themes.rifah-modern.partials.contact', [
                'profile' => $profile,
                'sectionTitle' => $contactItem?->label ?: 'Get In Touch',
                'sectionId' => $contactItem?->section_key ?: 'contact',
            ])
        @endif
    </main>

    @include('portfolio-themes.rifah-modern.partials.footer', [
        'portfolio' => $portfolio,
        'profile' => $profile,
        'ownerName' => $ownerName,
    ])

</body>
</html>
