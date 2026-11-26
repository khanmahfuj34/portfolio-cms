@props([
    'portfolio',
    'profile' => null,
    'sectionTitle' => 'Education',
    'sectionId' => 'education',
])

<section id="{{ $sectionId }}" class="py-20 px-6 max-w-4xl mx-auto scroll-mt-20">
    <div class="rm-reveal">
        <h2 class="font-display text-3xl font-bold text-ink mb-2">{{ $sectionTitle }}</h2>
        <div class="w-16 h-1 bg-[#C2542D] rounded-full mb-10"></div>
    </div>

    <div class="rm-reveal border-l-2 border-[#C2542D]/35 pl-6 py-1">
        <p class="text-muted text-sm leading-relaxed">
            No education records have been added to this portfolio yet.
        </p>
    </div>
</section>
