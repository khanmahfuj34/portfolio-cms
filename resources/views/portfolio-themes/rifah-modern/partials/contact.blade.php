@props([
    'profile' => null,
    'sectionTitle' => 'Get In Touch',
    'sectionId' => 'contact',
])

@php
    $email = is_string($profile?->email) ? trim($profile->email) : '';
    $location = is_string($profile?->location) ? trim($profile->location) : '';
@endphp

<section id="{{ $sectionId }}" class="py-20 px-6 max-w-4xl mx-auto scroll-mt-20">
    <div class="rm-reveal">
        <h2 class="font-display text-3xl font-bold text-ink mb-2">{{ $sectionTitle }}</h2>
        <div class="w-16 h-1 bg-[#C2542D] rounded-full mb-8"></div>

        <p class="text-muted mb-10 max-w-md text-sm sm:text-base leading-relaxed">
            Interested in collaborating or discussing an opportunity? Reach out directly using the details below.
        </p>
    </div>

    @if ($email !== '' || $location !== '')
        <div class="grid sm:grid-cols-2 gap-6">
            @if ($email !== '')
                <a href="mailto:{{ $email }}"
                   class="rm-reveal flex items-center gap-4 p-5 rounded-xl bg-surface border border-ink/10 hover:border-[#C2542D] transition-colors">
                    <span class="w-10 h-10 rounded-lg bg-[#C2542D]/12 text-[#C2542D] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs text-muted">Email</p>
                        <p class="font-medium text-ink text-sm break-all">{{ $email }}</p>
                    </div>
                </a>
            @endif

            @if ($location !== '')
                <div class="rm-reveal flex items-center gap-4 p-5 rounded-xl bg-surface border border-ink/10" style="--rm-delay: 80ms;">
                    <span class="w-10 h-10 rounded-lg bg-[#C79A45]/15 text-[#C79A45] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs text-muted">Location</p>
                        <p class="font-medium text-ink text-sm">{{ $location }}</p>
                    </div>
                </div>
            @endif
        </div>
    @else
        <div class="rm-reveal p-6 rounded-xl bg-surface border border-ink/10">
            <p class="text-sm text-muted">No contact details have been added yet.</p>
        </div>
    @endif
</section>
