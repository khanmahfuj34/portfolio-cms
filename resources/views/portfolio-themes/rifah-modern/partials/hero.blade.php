@props([
    'portfolio',
    'profile' => null,
    'ownerName',
    'headline' => '',
    'bioText' => '',
    'projectsCount' => 0,
    'hasAboutSection' => false,
])

@php
    $photoPath = is_string($profile?->profile_photo_path) ? trim($profile->profile_photo_path) : '';
    $avatarUrl = null;
    if ($photoPath !== '') {
        if (preg_match('/^(?:https?:\/\/|\/(?!\/))/i', $photoPath)) {
            $avatarUrl = $photoPath;
        } elseif (! str_contains($photoPath, '..')) {
            $avatarUrl = asset('storage/' . ltrim($photoPath, '/'));
        }
    }

    $nameParts = preg_split('/\s+/', trim($ownerName)) ?: [];
    $initials = collect($nameParts)
        ->filter()
        ->take(2)
        ->map(fn (string $part) => strtoupper(mb_substr($part, 0, 1)))
        ->implode('');
    $initials = $initials !== '' ? $initials : 'P';
@endphp

<section id="home" class="relative overflow-hidden min-h-[calc(100vh-4.5rem)] flex items-center py-16 md:py-24 px-6 scroll-mt-20">
    <div class="rm-blob w-72 h-72 bg-[#C2542D] -top-10 -left-10" aria-hidden="true"></div>
    <div class="rm-blob w-72 h-72 bg-[#C79A45] bottom-0 right-0" aria-hidden="true"></div>
    <canvas data-rm-connections aria-hidden="true" class="absolute inset-0 w-full h-full pointer-events-none"></canvas>

    <div class="relative z-10 max-w-6xl mx-auto grid md:grid-cols-2 gap-12 items-center w-full">
        <div class="rm-reveal is-visible">
            <p class="text-[#C2542D] font-medium mb-3 text-base sm:text-lg">
                Hi, I'm {{ $ownerName }}
            </p>

            <h1 class="font-display text-4xl sm:text-5xl font-bold leading-tight text-ink mb-4 tracking-tight">
                {{ $headline !== '' ? $headline : $portfolio->name }}
            </h1>

            @if ($bioText !== '')
                <p class="text-muted text-base sm:text-lg mb-8 max-w-lg leading-relaxed">
                    {{ \Illuminate\Support\Str::limit($bioText, 220) }}
                </p>
            @endif

            <div class="flex flex-wrap items-center gap-4">
                @if ($projectsCount > 0)
                    <a href="#projects"
                       class="inline-flex items-center gap-2 bg-[#C2542D] text-white px-6 py-3 rounded-lg text-sm font-medium hover:bg-[#C2542D]/90 transition-all hover:scale-[1.03] active:scale-[0.98] shadow-sm">
                        <span>Explore Projects</span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </a>
                @elseif ($hasAboutSection)
                    <a href="#about"
                       class="inline-flex items-center gap-2 bg-[#C2542D] text-white px-6 py-3 rounded-lg text-sm font-medium hover:bg-[#C2542D]/90 transition-all hover:scale-[1.03] active:scale-[0.98] shadow-sm">
                        <span>About Me</span>
                    </a>
                @endif

                @if ($profile?->email)
                    <a href="mailto:{{ $profile->email }}"
                       class="inline-flex items-center gap-2 border border-ink/20 px-6 py-3 rounded-lg text-sm font-medium text-ink hover:border-[#C2542D] hover:text-[#C2542D] transition-all hover:scale-[1.03] active:scale-[0.98]">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span>Contact Me</span>
                    </a>
                @endif
            </div>
        </div>

        <div class="rm-reveal is-visible flex justify-center md:justify-end" style="--rm-delay: 120ms;">
            <div class="w-60 h-60 sm:w-76 sm:h-76 rounded-full overflow-hidden border-4 border-[#C2542D]/25 shadow-xl bg-surface flex items-center justify-center relative">
                @if ($avatarUrl)
                    <img src="{{ $avatarUrl }}"
                         alt="{{ $ownerName }}"
                         class="w-full h-full object-cover"
                         onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');">
                    <div class="hidden w-full h-full bg-gradient-to-br from-[#C2542D] to-[#C79A45] text-white flex items-center justify-center font-display text-5xl sm:text-6xl font-bold tracking-tight select-none" aria-hidden="true">
                        {{ $initials }}
                    </div>
                @else
                    <div class="w-full h-full bg-gradient-to-br from-[#C2542D] to-[#C79A45] text-white flex flex-col items-center justify-center select-none p-6 text-center">
                        <span class="font-display text-5xl sm:text-6xl font-bold tracking-tight">{{ $initials }}</span>
                        @if ($headline !== '')
                            <span class="mt-2 text-[11px] uppercase tracking-widest text-white/85 font-medium max-w-[12rem] truncate">
                                {{ $headline }}
                            </span>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
