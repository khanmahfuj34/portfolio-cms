@props([
    'skills',
    'sectionTitle' => 'Skills',
    'sectionId' => 'skills',
])

<section id="{{ $sectionId }}" class="py-16 border-t border-white/10">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="space-y-1">
            <p class="text-xs font-medium text-[#C5A059]">Capabilities</p>
            <h2 class="text-2xl font-semibold text-white tracking-tight">{{ $sectionTitle }}</h2>
        </div>

        <div class="p-6 rounded-xl bg-[#131720] border border-white/10">
            @if ($skills->isNotEmpty())
                <div class="flex flex-wrap gap-2.5">
                    @foreach ($skills as $skill)
                        <span class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-[#0D0F14] border border-white/10 hover:border-[#C5A059]/40 text-xs sm:text-sm font-medium text-slate-200 transition-colors">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#C5A059]"></span>
                            <span>{{ $skill->name }}</span>
                        </span>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-slate-400 leading-relaxed">
                    No skills have been added yet.
                </p>
            @endif
        </div>
    </div>
</section>
