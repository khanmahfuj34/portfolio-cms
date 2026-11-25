@extends('layouts.portfolio')

@section('content')

    {{-- Admin Preview Banner for Draft Portfolios --}}
    @if (!empty($isPreview) && $isPreview)
        <div class="bg-amber-500/10 border-b border-amber-500/20 px-4 py-2 text-center text-xs font-medium text-amber-300 flex items-center justify-center gap-2 relative z-50">
            <span class="inline-block w-1.5 h-1.5 rounded-full bg-amber-400"></span>
            <span>Admin Preview Mode: This portfolio is currently in <strong>Draft</strong> and is not publicly visible.</span>
            @if (auth('super_admin')->user()?->isSuperAdmin())
                <a href="{{ app(\App\Services\PortfolioDomainService::class)->baseUrlFor('/admin/profiles', request()) }}" class="underline hover:text-amber-100 ml-2">Back to dashboard</a>
            @elseif (auth('admin')->user()?->isAdmin())
                <a href="{{ app(\App\Services\PortfolioDomainService::class)->baseUrlFor('/dashboard/portfolios', request()) }}" class="underline hover:text-amber-100 ml-2">Back to dashboard</a>
            @endif
        </div>
    @endif

    {{-- Render Reusable Navbar --}}
    @include('portfolio.partials.navbar', [
        'portfolio' => $portfolio,
        'navItems'  => $navItems,
    ])

    <main class="flex-1">

        {{-- Section: Home / Hero (#home) --}}
        <section id="home" class="relative pt-20 pb-20 md:pt-28 md:pb-32">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-6">
                
                <!-- Status & Location Pill -->
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#131720] border border-white/10 text-xs">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    <span class="text-slate-300 font-normal">Available for opportunities</span>
                    @if ($profile?->location)
                        <span class="text-slate-600">•</span>
                        <span class="text-slate-400 flex items-center gap-1">
                            <svg class="w-3 h-3 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            {{ $profile->location }}
                        </span>
                    @endif
                </div>

                <!-- Main Hero Headings -->
                <div class="space-y-3 max-w-2xl mx-auto pt-2">
                    <h1 class="text-3xl sm:text-4xl md:text-5xl font-semibold tracking-tight text-white leading-tight">
                        {{ $profile?->full_name ?? $portfolio->name }}
                    </h1>
                    @if ($profile?->professional_title)
                        <p class="text-base sm:text-lg font-medium text-[#C5A059] tracking-normal">
                            {{ $profile->professional_title }}
                        </p>
                    @endif
                </div>

                <!-- Bio Summary -->
                @if ($profile?->bio || $portfolio->description)
                    <p class="max-w-xl mx-auto text-sm sm:text-base text-slate-400 leading-relaxed font-normal">
                        {{ $profile?->bio ?? $portfolio->description }}
                    </p>
                @endif

                <!-- CTA Actions -->
                <div class="flex flex-wrap items-center justify-center gap-3 pt-3">
                    @if ($profile?->email)
                        <a href="mailto:{{ $profile->email }}" 
                           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-[#C5A059] hover:bg-[#B69149] text-[#0D0F14] font-semibold text-xs tracking-wide transition-colors">
                            <svg class="w-3.5 h-3.5 text-[#0D0F14]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span>Contact</span>
                        </a>
                    @endif

                    @php
                        $aboutItem = $navItems->first(fn ($item) => $item->section_key === 'about');
                    @endphp
                    @if ($aboutItem)
                        <a href="#about" 
                           class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-lg bg-transparent hover:bg-white/5 text-slate-300 hover:text-white border border-white/10 font-medium text-xs transition-colors">
                            <span>About</span>
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </a>
                    @endif
                </div>

            </div>
        </section>

        {{-- Section: About (#about) --}}
        <section id="about" class="py-16 md:py-24 border-t border-white/10 bg-[#131720]/40">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
                
                <div class="space-y-1">
                    <p class="text-xs font-medium text-[#C5A059]">Profile overview</p>
                    <h2 class="text-2xl font-semibold text-white tracking-tight">About</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                    
                    <!-- Bio Details -->
                    <div class="md:col-span-2 space-y-4 text-slate-300 text-sm leading-relaxed">
                        @if ($profile?->bio)
                            <div class="p-6 rounded-xl bg-[#131720] border border-white/10 border-l-2 border-l-[#C5A059] space-y-3">
                                <h3 class="text-xs font-medium text-slate-400">Background</h3>
                                <p class="text-slate-200 whitespace-pre-line leading-relaxed font-normal">
                                    {{ $profile->bio }}
                                </p>
                            </div>
                        @endif

                        @if ($portfolio->description)
                            <div class="p-6 rounded-xl bg-[#131720] border border-white/10 space-y-2">
                                <h3 class="text-xs font-medium text-slate-400">Portfolio summary</h3>
                                <p class="text-slate-300 leading-relaxed font-normal">
                                    {{ $portfolio->description }}
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- Meta Information Card -->
                    <div class="md:col-span-1 p-5 rounded-xl bg-[#131720] border border-white/10 space-y-4">
                        <h3 class="text-xs font-medium text-slate-400">Details</h3>
                        
                        <dl class="space-y-3 text-xs">
                            @if ($profile?->full_name)
                                <div>
                                    <dt class="text-slate-500 font-normal">Name</dt>
                                    <dd class="text-slate-200 font-medium mt-0.5">{{ $profile->full_name }}</dd>
                                </div>
                            @endif

                            @if ($profile?->professional_title)
                                <div>
                                    <dt class="text-slate-500 font-normal">Role</dt>
                                    <dd class="text-slate-200 font-medium mt-0.5">{{ $profile->professional_title }}</dd>
                                </div>
                            @endif

                            @if ($profile?->email)
                                <div>
                                    <dt class="text-slate-500 font-normal">Email</dt>
                                    <dd class="mt-0.5">
                                        <a href="mailto:{{ $profile->email }}" class="text-[#C5A059] hover:text-[#E2C78E] transition-colors break-all">
                                            {{ $profile->email }}
                                        </a>
                                    </dd>
                                </div>
                            @endif

                            @if ($profile?->location)
                                <div>
                                    <dt class="text-slate-500 font-normal">Location</dt>
                                    <dd class="text-slate-200 font-medium mt-0.5">{{ $profile->location }}</dd>
                                </div>
                            @endif

                            <div>
                                <dt class="text-slate-500 font-normal">Slug</dt>
                                <dd class="text-slate-400 font-mono text-[11px] mt-0.5">{{ $portfolio->slug }}</dd>
                            </div>
                        </dl>
                    </div>

                </div>

            </div>
        </section>

        {{-- Dynamic Anchors for configured sections (e.g. #projects, #skills, #contact) --}}
        @php
            $customSections = $navItems->filter(fn ($item) => $item->section_key && !in_array($item->section_key, ['home', 'about'], true));
            $hasProjectsSection = $customSections->contains(fn ($item) => strtolower($item->section_key) === 'projects');
            $hasSkillsSection = $customSections->contains(fn ($item) => strtolower($item->section_key) === 'skills');
            $projectsCollection = $projects ?? collect();
            $skillsCollection = $skills ?? collect();
        @endphp

        @foreach ($customSections as $sectionItem)
            @php
                $sectionKey = strtolower($sectionItem->section_key);
            @endphp

            @if ($sectionKey === 'projects')
                @include('portfolio.partials.projects', [
                    'projects' => $projectsCollection,
                    'sectionTitle' => $sectionItem->label,
                    'sectionId' => $sectionItem->section_key,
                ])
            @elseif ($sectionKey === 'skills')
                @include('portfolio.partials.skills', [
                    'skills' => $skillsCollection,
                    'sectionTitle' => $sectionItem->label,
                    'sectionId' => $sectionItem->section_key,
                ])
            @else
                <section id="{{ $sectionItem->section_key }}" class="py-16 border-t border-white/10">
                    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                        <div class="space-y-1">
                            <p class="text-xs font-medium text-[#C5A059]">Section</p>
                            <h2 class="text-2xl font-semibold text-white tracking-tight">{{ $sectionItem->label }}</h2>
                        </div>
                        <div class="p-6 rounded-xl bg-[#131720] border border-white/10 space-y-2">
                            <h3 class="text-sm font-medium text-slate-200">{{ $sectionItem->label }}</h3>
                            @if ($sectionKey === 'contact' && $profile?->email)
                                <p class="text-xs text-slate-400 max-w-lg leading-relaxed">
                                    Get in touch at
                                    <a href="mailto:{{ $profile->email }}" class="text-[#C5A059] hover:text-[#E2C78E] transition-colors">
                                        {{ $profile->email }}
                                    </a>.
                                </p>
                            @else
                                <p class="text-xs text-slate-400 max-w-lg leading-relaxed">
                                    No additional items added to this section yet.
                                </p>
                            @endif
                        </div>
                    </div>
                </section>
            @endif
        @endforeach

        @if (! $hasProjectsSection && $projectsCollection->isNotEmpty())
            @include('portfolio.partials.projects', [
                'projects' => $projectsCollection,
                'sectionTitle' => 'Projects',
                'sectionId' => 'projects',
            ])
        @endif

        @if (! $hasSkillsSection && $skillsCollection->isNotEmpty())
            @include('portfolio.partials.skills', [
                'skills' => $skillsCollection,
                'sectionTitle' => 'Skills',
                'sectionId' => 'skills',
            ])
        @endif

    </main>

    {{-- Read-Only Public Footer --}}
    <footer class="border-t border-white/10 py-6 bg-[#0D0F14]">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
            <p>© {{ date('Y') }} {{ $profile?->full_name ?? $portfolio->name }}</p>
            <p class="text-[11px] text-slate-600">Portfolio CMS</p>
        </div>
    </footer>

@endsection
