@props([
    'projects',
    'sectionTitle' => 'Projects',
    'sectionId' => 'projects',
])

@php
    $allTechs = $projects
        ->flatMap(fn ($p) => is_array($p->technologies) ? $p->technologies : [])
        ->map(fn ($t) => is_string($t) ? trim($t) : '')
        ->filter()
        ->unique(fn ($t) => strtolower($t))
        ->take(6)
        ->values();

    $hasFeatured = $projects->contains(fn ($p) => (bool) $p->is_featured);
@endphp

<section id="{{ $sectionId }}" class="rm-section-alt py-20 px-6 scroll-mt-20 border-t border-ink/10">
    <div class="max-w-6xl mx-auto">
        <div class="rm-reveal flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-10">
            <div>
                <h2 class="font-display text-3xl font-bold text-ink mb-2">{{ $sectionTitle }}</h2>
                <div class="w-16 h-1 bg-[#C2542D] rounded-full"></div>
            </div>

            @if ($projects->count() > 1 && ($hasFeatured || $allTechs->isNotEmpty()))
                <div class="flex flex-wrap gap-2" role="group" aria-label="Filter projects">
                    <button type="button" data-rm-filter="all" class="rm-filter-btn is-active px-3 py-1 rounded-full text-xs font-medium border border-ink/15 cursor-pointer">
                        All
                    </button>
                    @if ($hasFeatured)
                        <button type="button" data-rm-filter="featured" class="rm-filter-btn px-3 py-1 rounded-full text-xs font-medium border border-ink/15 text-muted hover:text-ink cursor-pointer">
                            Featured
                        </button>
                    @endif
                    @foreach ($allTechs as $tech)
                        <button type="button" data-rm-filter="{{ $tech }}" class="rm-filter-btn px-3 py-1 rounded-full text-xs font-medium border border-ink/15 text-muted hover:text-ink cursor-pointer">
                            {{ $tech }}
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        @if ($projects->isNotEmpty())
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-7">
                @foreach ($projects as $index => $project)
                    @php
                        $technologies = is_array($project->technologies)
                            ? array_values(array_filter(array_map(fn ($t) => is_string($t) ? trim($t) : '', $project->technologies)))
                            : [];
                        $techLowerJoined = implode('|', array_map(fn ($t) => strtolower($t), $technologies));

                        $liveUrl = is_string($project->live_url) && preg_match('/^https?:\/\//i', trim($project->live_url))
                            ? trim($project->live_url)
                            : null;
                        $githubUrl = is_string($project->github_url) && preg_match('/^https?:\/\//i', trim($project->github_url))
                            ? trim($project->github_url)
                            : null;

                        $rawThumb = is_string($project->thumbnail) ? trim($project->thumbnail) : '';
                        $thumbnail = null;
                        if ($rawThumb !== '') {
                            if (preg_match('/^(?:https?:\/\/|\/(?!\/))/i', $rawThumb)) {
                                $thumbnail = $rawThumb;
                            } elseif (! str_contains($rawThumb, '..')) {
                                $thumbnail = asset('storage/' . ltrim($rawThumb, '/'));
                            }
                        }

                        $coverTone = ($index % 3 === 2)
                            ? 'bg-[#C79A45] text-[#1A1310]'
                            : 'bg-[#C2542D] text-white';
                        $shortTitle = \Illuminate\Support\Str::words($project->title, 2, '');
                    @endphp

                    <article data-rm-project-card
                             data-rm-featured="{{ $project->is_featured ? '1' : '0' }}"
                             data-rm-techs="{{ $techLowerJoined }}"
                             class="rm-reveal rm-project-card bg-surface rounded-xl overflow-hidden border border-ink/10 shadow-sm hover:shadow-xl flex flex-col h-full"
                             style="--rm-delay: {{ min($index * 75, 450) }}ms;">

                        @if ($thumbnail)
                            <div class="w-full h-44 overflow-hidden bg-paper border-b border-ink/10 relative">
                                <img src="{{ $thumbnail }}"
                                     alt="{{ $project->title }}"
                                     loading="lazy"
                                     class="w-full h-full object-cover"
                                     onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');">
                                <div class="hidden w-full h-full flex items-center justify-center px-6 text-center {{ $coverTone }}">
                                    <span class="font-display text-2xl font-bold tracking-tight line-clamp-2">{{ $shortTitle }}</span>
                                </div>
                            </div>
                        @else
                            <div class="w-full h-44 flex items-center justify-center px-6 text-center {{ $coverTone }}">
                                <span class="font-display text-2xl sm:text-3xl font-bold tracking-tight line-clamp-2">{{ $shortTitle }}</span>
                            </div>
                        @endif

                        <div class="p-6 flex flex-col flex-1">
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <h3 class="font-display font-semibold text-lg text-ink leading-snug">
                                    {{ $project->title }}
                                </h3>
                                @if ($project->is_featured)
                                    <span class="shrink-0 px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wide bg-[#C79A45]/20 text-[#9B701C] dark:text-[#E2B967]">
                                        Featured
                                    </span>
                                @endif
                            </div>

                            @if ($project->description)
                                <p class="text-muted text-sm mb-4 flex-1 leading-relaxed line-clamp-3">
                                    {{ $project->description }}
                                </p>
                            @else
                                <div class="flex-1"></div>
                            @endif

                            @if (count($technologies) > 0)
                                <div class="flex flex-wrap gap-1.5 mb-4">
                                    @foreach (array_slice($technologies, 0, 4) as $tech)
                                        <span class="text-xs bg-ink/5 text-muted px-2 py-1 rounded border border-ink/10">
                                            {{ $tech }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            <div class="mt-auto pt-3 border-t border-ink/10 flex flex-wrap items-center gap-2">
                                <button type="button"
                                        data-rm-open-project
                                        data-title="{{ $project->title }}"
                                        data-description="{{ $project->description ?? '' }}"
                                        data-technologies="{{ implode('|', $technologies) }}"
                                        data-live-url="{{ $liveUrl ?? '' }}"
                                        data-github-url="{{ $githubUrl ?? '' }}"
                                        class="flex-1 text-center bg-[#C2542D] text-white px-4 py-2 rounded-lg text-xs sm:text-sm font-medium hover:bg-[#C2542D]/90 transition-colors cursor-pointer">
                                    View Details
                                </button>

                                @if ($liveUrl)
                                    <a href="{{ $liveUrl }}"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       class="px-3 py-2 rounded-lg border border-ink/15 text-xs sm:text-sm font-medium text-ink hover:border-[#C2542D] hover:text-[#C2542D] transition-colors">
                                        Live
                                    </a>
                                @endif

                                @if ($githubUrl)
                                    <a href="{{ $githubUrl }}"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       class="px-3 py-2 rounded-lg border border-ink/15 text-xs sm:text-sm font-medium text-muted hover:text-ink transition-colors">
                                        Code
                                    </a>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="rm-reveal p-6 rounded-xl bg-paper border border-ink/10">
                <p class="text-sm text-muted">No projects have been published yet.</p>
            </div>
        @endif
    </div>

    <div data-rm-project-modal
         role="dialog"
         aria-modal="true"
         aria-labelledby="rm-modal-heading"
         class="hidden fixed inset-0 z-50 bg-black/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="w-full max-w-xl rounded-2xl bg-surface border border-ink/15 shadow-2xl p-6 sm:p-8 space-y-5 relative max-h-[88vh] overflow-y-auto">
            <div class="flex items-start justify-between gap-4">
                <h3 id="rm-modal-heading" data-rm-modal-title class="font-display text-2xl font-bold text-ink"></h3>
                <button type="button"
                        data-rm-modal-close
                        aria-label="Close project details"
                        class="w-9 h-9 rounded-lg border border-ink/15 flex items-center justify-center text-muted hover:text-ink cursor-pointer shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <p data-rm-modal-desc class="text-muted text-sm sm:text-base whitespace-pre-line leading-relaxed"></p>

            <div data-rm-modal-techs class="flex flex-wrap gap-2"></div>

            <div class="pt-4 border-t border-ink/10 flex flex-wrap gap-3">
                <a data-rm-modal-live href="#" target="_blank" rel="noopener noreferrer"
                   class="hidden inline-flex items-center gap-2 bg-[#C2542D] text-white px-5 py-2.5 rounded-lg text-sm font-medium hover:bg-[#C2542D]/90 transition-colors">
                    <span>Open Live Project</span>
                </a>
                <a data-rm-modal-github href="#" target="_blank" rel="noopener noreferrer"
                   class="hidden inline-flex items-center gap-2 border border-ink/20 px-5 py-2.5 rounded-lg text-sm font-medium text-ink hover:border-[#C2542D] hover:text-[#C2542D] transition-colors">
                    <span>View Repository</span>
                </a>
            </div>
        </div>
    </div>
</section>
