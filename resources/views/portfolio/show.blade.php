@extends('layouts.portfolio')

@section('content')

    {{-- Admin Preview Banner for Draft Portfolios --}}
    @if (!empty($isPreview) && $isPreview)
        <div class="bg-amber-500/15 border-b border-amber-500/30 px-4 py-2 text-center text-xs font-semibold text-amber-300 flex items-center justify-center gap-2 relative z-50">
            <span class="inline-block w-2 h-2 rounded-full bg-amber-400"></span>
            <span>Admin Preview Mode: This portfolio is currently in <strong>Draft</strong> status and only visible to you as Super Admin.</span>
            <a href="{{ route('admin.profiles.index') }}" class="underline hover:text-amber-200 ml-2">Back to Dashboard</a>
        </div>
    @endif

    {{-- Render Reusable Navbar --}}
    @include('portfolio.partials.navbar', [
        'portfolio' => $portfolio,
        'navItems'  => $navItems,
    ])

    <main class="flex-1">

        {{-- Section: Home / Hero (#home) --}}
        <section id="home" class="relative pt-20 pb-24 md:pt-32 md:pb-36 overflow-hidden">
            <!-- Decorative Glow Accents -->
            <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[550px] h-[350px] bg-[#D4AF69]/10 rounded-full blur-3xl pointer-events-none" aria-hidden="true"></div>
            <div class="absolute top-1/2 left-1/4 w-[300px] h-[300px] bg-indigo-500/5 rounded-full blur-3xl pointer-events-none" aria-hidden="true"></div>

            <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-8">
                
                <!-- Status & Location Pill -->
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#141822] border border-[#1E2330] text-xs shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-slate-300 font-medium">Available for Opportunities</span>
                    @if ($profile?->location)
                        <span class="text-slate-600">•</span>
                        <span class="text-slate-400 flex items-center gap-1">
                            <svg class="w-3 h-3 text-[#D4AF69]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            {{ $profile->location }}
                        </span>
                    @endif
                </div>

                <!-- Main Hero Headings -->
                <div class="space-y-4 max-w-3xl mx-auto">
                    <h1 class="text-4xl sm:text-5xl md:text-6xl font-bold tracking-tight text-white leading-tight">
                        Hi, I'm <span class="bg-gradient-to-r from-white via-slate-100 to-[#D4AF69] bg-clip-text text-transparent">{{ $profile?->full_name ?? $portfolio->name }}</span>
                    </h1>
                    @if ($profile?->professional_title)
                        <p class="text-lg sm:text-xl md:text-2xl font-semibold text-[#D4AF69] tracking-normal">
                            {{ $profile->professional_title }}
                        </p>
                    @endif
                </div>

                <!-- Bio Summary -->
                @if ($profile?->bio || $portfolio->description)
                    <p class="max-w-2xl mx-auto text-sm sm:text-base text-slate-400 leading-relaxed">
                        {{ $profile?->bio ?? $portfolio->description }}
                    </p>
                @endif

                <!-- CTA Actions -->
                <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
                    @if ($profile?->email)
                        <a href="mailto:{{ $profile->email }}" 
                           class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-[#D4AF69] hover:bg-[#C9A25B] text-slate-950 font-bold text-xs tracking-wide transition-all shadow-md shadow-[#D4AF69]/20 hover:scale-[1.02]">
                            <svg class="w-4 h-4 text-slate-950" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span>Contact Me</span>
                        </a>
                    @endif

                    @php
                        $aboutItem = $navItems->first(fn ($item) => $item->section_key === 'about');
                    @endphp
                    @if ($aboutItem)
                        <a href="#about" 
                           class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-[#141822] hover:bg-[#1E2330] text-slate-200 border border-[#1E2330] hover:border-slate-700 font-semibold text-xs transition-all">
                            <span>Learn More</span>
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </a>
                    @endif
                </div>

            </div>
        </section>

        {{-- Section: About (#about) --}}
        <section id="about" class="py-20 md:py-28 border-t border-[#1E2330]/60 bg-[#0F1219]/40 relative">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
                
                <div class="space-y-2 text-center md:text-left">
                    <span class="text-xs font-bold tracking-widest text-[#D4AF69] uppercase">Profile Overview</span>
                    <h2 class="text-2xl sm:text-3xl font-bold text-white tracking-tight">About Me</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-start">
                    
                    <!-- Bio Details -->
                    <div class="md:col-span-2 space-y-5 text-slate-300 text-sm leading-relaxed">
                        @if ($profile?->bio)
                            <div class="p-6 rounded-2xl bg-[#141822] border border-[#1E2330] shadow-xs space-y-4">
                                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                                    <svg class="w-4 h-4 text-[#D4AF69]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    Biography
                                </h3>
                                <p class="text-slate-300 whitespace-pre-line leading-relaxed">
                                    {{ $profile->bio }}
                                </p>
                            </div>
                        @endif

                        @if ($portfolio->description)
                            <div class="p-6 rounded-2xl bg-[#141822] border border-[#1E2330] shadow-xs space-y-3">
                                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                                    <svg class="w-4 h-4 text-[#D4AF69]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Portfolio Summary
                                </h3>
                                <p class="text-slate-400 leading-relaxed">
                                    {{ $portfolio->description }}
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- Meta Information Card -->
                    <div class="md:col-span-1 p-6 rounded-2xl bg-[#141822] border border-[#1E2330] shadow-xs space-y-5">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Information</h3>
                        
                        <div class="space-y-4 text-xs">
                            @if ($profile?->full_name)
                                <div>
                                    <span class="block text-slate-500 font-medium">Full Name</span>
                                    <span class="text-slate-200 font-semibold">{{ $profile->full_name }}</span>
                                </div>
                            @endif

                            @if ($profile?->professional_title)
                                <div>
                                    <span class="block text-slate-500 font-medium">Title</span>
                                    <span class="text-slate-200 font-semibold">{{ $profile->professional_title }}</span>
                                </div>
                            @endif

                            @if ($profile?->email)
                                <div>
                                    <span class="block text-slate-500 font-medium">Email</span>
                                    <a href="mailto:{{ $profile->email }}" class="text-[#D4AF69] hover:underline font-medium break-all">
                                        {{ $profile->email }}
                                    </a>
                                </div>
                            @endif

                            @if ($profile?->location)
                                <div>
                                    <span class="block text-slate-500 font-medium">Location</span>
                                    <span class="text-slate-200 font-medium">{{ $profile->location }}</span>
                                </div>
                            @endif

                            <div>
                                <span class="block text-slate-500 font-medium">Portfolio ID</span>
                                <span class="text-slate-400 font-mono text-[11px]">{{ $portfolio->slug }}</span>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </section>

        {{-- Dynamic Anchors for configured sections (e.g. #projects, #skills, #contact) --}}
        @php
            $customSections = $navItems->filter(fn ($item) => $item->section_key && !in_array($item->section_key, ['home', 'about'], true));
        @endphp

        @foreach ($customSections as $sectionItem)
            <section id="{{ $sectionItem->section_key }}" class="py-16 border-t border-[#1E2330]/60 relative">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 text-center md:text-left">
                    <div class="space-y-1">
                        <span class="text-xs font-bold tracking-widest text-[#D4AF69] uppercase">Section</span>
                        <h2 class="text-2xl font-bold text-white tracking-tight">{{ $sectionItem->label }}</h2>
                    </div>
                    <div class="p-8 rounded-2xl bg-[#141822] border border-[#1E2330] text-center space-y-3">
                        <div class="w-12 h-12 mx-auto rounded-xl bg-[#D4AF69]/10 text-[#D4AF69] flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-white">{{ $sectionItem->label }}</h3>
                        <p class="text-xs text-slate-400 max-w-md mx-auto">
                            Content for this section is managed through the portfolio workspace.
                        </p>
                    </div>
                </div>
            </section>
        @endforeach

    </main>

    {{-- Read-Only Public Footer --}}
    <footer class="border-t border-[#1E2330] py-8 bg-[#0B0D12]">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
            <p>© {{ date('Y') }} {{ $profile?->full_name ?? $portfolio->name }}. All rights reserved.</p>
            <p class="text-[11px] text-slate-600">Powered by Portfolio CMS</p>
        </div>
    </footer>

@endsection
