@props([
    'portfolio',
    'profile' => null,
    'ownerName',
    'headline' => '',
    'sectionTitle' => 'About Me',
    'sectionId' => 'about',
])

@php
    $bio = trim((string) ($profile?->bio ?? ''));
    $portfolioDesc = trim((string) ($portfolio->description ?? ''));
@endphp

<section id="{{ $sectionId }}" class="rm-section-alt py-20 px-6 scroll-mt-20 border-t border-ink/10">
    <div class="max-w-4xl mx-auto rm-reveal">
        <h2 class="font-display text-3xl font-bold text-ink mb-2">{{ $sectionTitle }}</h2>
        <div class="w-16 h-1 bg-[#C2542D] rounded-full mb-8"></div>

        @if ($bio !== '' || $portfolioDesc !== '')
            <div class="space-y-5">
                @if ($bio !== '')
                    <p class="text-muted leading-relaxed whitespace-pre-line text-base sm:text-lg">{{ $bio }}</p>
                @endif

                @if ($portfolioDesc !== '' && $portfolioDesc !== $bio)
                    <p class="text-muted/90 leading-relaxed whitespace-pre-line text-sm sm:text-base border-l-2 border-[#C79A45]/50 pl-4">{{ $portfolioDesc }}</p>
                @endif
            </div>
        @else
            <p class="text-muted text-sm">No biography details added yet.</p>
        @endif

        @if ($headline !== '' || ($profile?->location ?? '') !== '')
            <div class="mt-8 pt-6 border-t border-ink/10 flex flex-wrap gap-6 text-xs sm:text-sm text-muted">
                <div>
                    <span class="block text-[11px] uppercase tracking-wider text-muted/75">Name</span>
                    <span class="font-medium text-ink">{{ $ownerName }}</span>
                </div>
                @if ($headline !== '')
                    <div>
                        <span class="block text-[11px] uppercase tracking-wider text-muted/75">Role</span>
                        <span class="font-medium text-[#C2542D]">{{ $headline }}</span>
                    </div>
                @endif
                @if ($profile?->location)
                    <div>
                        <span class="block text-[11px] uppercase tracking-wider text-muted/75">Location</span>
                        <span class="font-medium text-ink">{{ $profile->location }}</span>
                    </div>
                @endif
            </div>
        @endif
    </div>
</section>
