@props([
    'skills',
    'sectionTitle' => 'Skills',
    'sectionId' => 'skills',
])

@php
    $chipPalettes = [
        'bg-[#C2542D]/12 text-[#C2542D] border-[#C2542D]/25',
        'bg-[#C79A45]/15 text-[#9B701C] dark:text-[#E2B967] border-[#C79A45]/30',
    ];
@endphp

<section id="{{ $sectionId }}" class="py-20 px-6 max-w-4xl mx-auto scroll-mt-20">
    <div class="rm-reveal">
        <h2 class="font-display text-3xl font-bold text-ink mb-2">{{ $sectionTitle }}</h2>
        <div class="w-16 h-1 bg-[#C2542D] rounded-full mb-10"></div>
    </div>

    @if ($skills->isNotEmpty())
        <div class="flex flex-wrap gap-3">
            @foreach ($skills as $index => $skill)
                @php
                    $chipClass = $chipPalettes[$index % count($chipPalettes)];
                    $delayMs = min($index * 45, 450);
                @endphp
                <span class="rm-reveal rm-skill-chip px-4 py-2 rounded-full text-sm font-medium border {{ $chipClass }}"
                      style="--rm-delay: {{ $delayMs }}ms;">
                    {{ $skill->name }}
                </span>
            @endforeach
        </div>
    @else
        <div class="rm-reveal p-6 rounded-xl bg-surface border border-ink/10">
            <p class="text-sm text-muted">No skills have been added yet.</p>
        </div>
    @endif
</section>
