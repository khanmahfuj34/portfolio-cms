@php
    $portfolioId = isset($portfolio) && $portfolio->exists ? $portfolio->id : null;
    $oldProjects = old('projects');
    if (is_array($oldProjects)) {
        $initialProjects = collect($oldProjects)->map(function ($item, $key) {
            $techs = $item['technologies'] ?? [];
            if (is_string($techs)) {
                $techs = array_values(array_filter(array_map('trim', explode(',', $techs))));
            }
            return (object) [
                'id' => 'proj-' . $key,
                'title' => $item['title'] ?? '',
                'description' => $item['description'] ?? '',
                'thumbnail' => $item['thumbnail'] ?? '',
                'live_url' => $item['live_url'] ?? '',
                'github_url' => $item['github_url'] ?? '',
                'technologies' => $techs,
                'is_featured' => !empty($item['is_featured']),
                'is_visible' => isset($item['is_visible']) ? (bool) $item['is_visible'] : true,
                'sort_order' => (int) ($item['sort_order'] ?? $key),
            ];
        })->filter(fn ($p) => $p->title !== '');
    } elseif (isset($portfolio) && $portfolio->exists) {
        $initialProjects = $portfolio->projects()->orderBy('sort_order')->orderBy('id')->get();
    } else {
        $initialProjects = collect();
    }
@endphp

<div id="projects-configuration-section"
     class="rounded-xl bg-surface border border-line p-6 sm:p-7 space-y-6"
     data-portfolio-id="{{ $portfolioId ?? '' }}"
     data-projects-url="{{ $portfolioId ? route('admin.portfolios.projects.index', $portfolio) : '' }}">

    <!-- Hidden form inputs container for create/edit form submissions -->
    <div id="projects-hidden-inputs">
        @foreach($initialProjects as $idx => $project)
            <input type="hidden" name="projects[{{ $idx }}][title]" value="{{ $project->title }}">
            <input type="hidden" name="projects[{{ $idx }}][description]" value="{{ $project->description }}">
            <input type="hidden" name="projects[{{ $idx }}][thumbnail]" value="{{ $project->thumbnail }}">
            <input type="hidden" name="projects[{{ $idx }}][live_url]" value="{{ $project->live_url }}">
            <input type="hidden" name="projects[{{ $idx }}][github_url]" value="{{ $project->github_url }}">
            <input type="hidden" name="projects[{{ $idx }}][sort_order]" value="{{ $project->sort_order ?? $idx }}">
            <input type="hidden" name="projects[{{ $idx }}][is_featured]" value="{{ $project->is_featured ? '1' : '0' }}">
            <input type="hidden" name="projects[{{ $idx }}][is_visible]" value="{{ $project->is_visible ? '1' : '0' }}">
            @if(is_array($project->technologies))
                @foreach($project->technologies as $tech)
                    <input type="hidden" name="projects[{{ $idx }}][technologies][]" value="{{ $tech }}">
                @endforeach
            @endif
        @endforeach
    </div>

    <!-- Section Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-5 border-b border-line">
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-md bg-brass-wash text-brass-ink flex items-center justify-center flex-shrink-0">
                <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
            <div class="min-w-0">
                <h3 class="text-sm font-medium text-ink">Projects Configuration</h3>
                <p class="text-xs text-ink-mute mt-0.5 leading-relaxed">
                    Showcase engineering projects, case studies, repositories, and applications.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3 self-start sm:self-center">
            <span id="projects-counter-badge" class="text-[11px] text-ink-mute tabular">
                <span id="projects-count-display">{{ $initialProjects->count() }}</span> {{ Str::plural('Project', $initialProjects->count()) }}
            </span>

            <button type="button"
                    id="btn-open-add-project"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-[#14130F] bg-brass hover:bg-[#B89149] rounded-md transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-brass">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Add Project</span>
            </button>
        </div>
    </div>

    <!-- Alert / Status Container -->
    <div id="projects-alert-container" class="hidden"></div>

    @if(isset($errors) && $errors->has('projects.*'))
        <div class="p-3 rounded-lg bg-[#FBF1EF] border border-[#E9D2CC] text-[#9C4A3E] text-xs space-y-1">
            @foreach($errors->get('projects.*') as $projErrors)
                @foreach($projErrors as $err)
                    <p>{{ $err }}</p>
                @endforeach
            @endforeach
        </div>
    @endif

    <!-- Repeatable Projects Container -->
    <div id="projects-items-container" class="space-y-3.5">
        @forelse($initialProjects as $project)
            <div class="project-item-card rounded-lg border border-line bg-paper-sunk/40 hover:bg-paper-sunk/60 p-4 transition-colors space-y-3"
                 data-project-id="{{ $project->id }}"
                 data-project-data="{{ json_encode($project) }}">

                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 pb-3 border-b border-line">
                    <div class="flex items-start gap-3 min-w-0">
                        @if($project->thumbnail)
                            <img src="{{ $project->thumbnail }}" alt="{{ $project->title }}" class="w-12 h-12 rounded-md object-cover border border-line flex-shrink-0 bg-surface">
                        @else
                            <div class="w-10 h-10 rounded-md bg-surface border border-line flex items-center justify-center flex-shrink-0 text-ink-mute">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        @endif

                        <div class="min-w-0 space-y-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h4 class="project-title-display text-xs sm:text-sm font-semibold text-ink truncate">
                                    {{ $project->title }}
                                </h4>
                                @if($project->is_featured)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-brass-wash text-brass-ink border border-brass/30">
                                        Featured
                                    </span>
                                @endif
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium {{ $project->is_visible ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500 border border-slate-200' }}">
                                    {{ $project->is_visible ? 'Visible' : 'Hidden' }}
                                </span>
                                <span class="text-[10px] text-ink-mute font-mono">
                                    Order: {{ $project->sort_order ?? 0 }}
                                </span>
                            </div>

                            @if($project->description)
                                <p class="text-xs text-ink-soft line-clamp-2 leading-relaxed">
                                    {{ $project->description }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <!-- Card Actions -->
                    <div class="flex items-center gap-1.5 self-end sm:self-start flex-shrink-0">
                        <button type="button"
                                class="btn-edit-project inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-ink-soft hover:text-ink bg-surface border border-line hover:bg-paper-sunk rounded-md transition-colors"
                                title="Edit project">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                            </svg>
                            <span>Edit</span>
                        </button>
                        <button type="button"
                                class="btn-delete-project inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-[#9C4A3E] hover:bg-[#FBF1EF] border border-[#E9D2CC] rounded-md transition-colors"
                                title="Delete project">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            <span>Delete</span>
                        </button>
                    </div>
                </div>

                <!-- Project Meta: Technologies & Links -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 text-[11px] text-ink-mute">
                    <div class="flex flex-wrap gap-1.5 items-center">
                        @if(is_array($project->technologies) && count($project->technologies) > 0)
                            @foreach($project->technologies as $tech)
                                <span class="px-2 py-0.5 rounded bg-surface border border-line text-ink-soft text-[11px] font-mono">
                                    {{ $tech }}
                                </span>
                            @endforeach
                        @else
                            <span class="text-ink-mute/70 italic">No technologies listed</span>
                        @endif
                    </div>

                    <div class="flex items-center gap-3">
                        @if($project->live_url)
                            <a href="{{ $project->live_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-brass-ink hover:underline">
                                <span>Live preview</span>
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                            </a>
                        @endif
                        @if($project->github_url)
                            <a href="{{ $project->github_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-ink-soft hover:text-ink hover:underline">
                                <span>Repository</span>
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>

            </div>
        @empty
            <div id="projects-empty-state" class="w-full text-center py-8 px-4 rounded-lg border border-dashed border-line text-ink-mute">
                <p class="text-xs">No projects configured yet.</p>
                <p class="text-[11px] text-ink-mute/70 mt-1">Click "Add Project" above to create your first portfolio project.</p>
            </div>
        @endforelse
    </div>

    <!-- Add/Edit Project Modal -->
    <x-admin.modal id="modal-project" title="Add Project" size="max-w-2xl">
        <div id="form-project" class="space-y-4">
            <input type="hidden" id="project-form-id" value="">

            <div id="project-modal-error" class="p-3 rounded-lg bg-[#FBF1EF] border border-[#E9D2CC] text-[#9C4A3E] text-xs hidden space-y-1"></div>

            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                <!-- Title -->
                <div class="sm:col-span-8">
                    <label for="project-title-input" class="block text-xs font-medium text-ink-soft mb-1">
                        Project title <span class="text-brass-ink">*</span>
                    </label>
                    <input type="text"
                           id="project-title-input"
                           maxlength="150"
                           placeholder="e.g. Distributed Task Queue & Orchestrator"
                           class="w-full py-2 px-3 text-xs bg-surface text-ink placeholder-ink-mute border border-line rounded-lg focus:outline-none focus:border-brass focus:ring-2 focus:ring-brass/20 transition-colors"
                    >
                </div>

                <!-- Sort Order -->
                <div class="sm:col-span-4">
                    <label for="project-sort-order-input" class="block text-xs font-medium text-ink-soft mb-1">
                        Sort order
                    </label>
                    <input type="number"
                           id="project-sort-order-input"
                           min="0"
                           value="0"
                           class="w-full py-2 px-3 text-xs bg-surface text-ink placeholder-ink-mute border border-line rounded-lg focus:outline-none focus:border-brass focus:ring-2 focus:ring-brass/20 transition-colors"
                    >
                </div>
            </div>

            <!-- Description -->
            <div>
                <label for="project-description-input" class="block text-xs font-medium text-ink-soft mb-1">
                    Description
                </label>
                <textarea id="project-description-input"
                          rows="3"
                          placeholder="Brief summary of the architecture, engineering decisions, and impact..."
                          class="w-full py-2 px-3 text-xs bg-surface text-ink placeholder-ink-mute border border-line rounded-lg focus:outline-none focus:border-brass focus:ring-2 focus:ring-brass/20 transition-colors resize-y"
                ></textarea>
            </div>

            <!-- Thumbnail URL -->
            <div>
                <label for="project-thumbnail-input" class="block text-xs font-medium text-ink-soft mb-1">
                    Thumbnail URL
                </label>
                <input type="text"
                       id="project-thumbnail-input"
                       maxlength="255"
                       placeholder="https://example.com/thumbnails/project.jpg"
                       class="w-full py-2 px-3 text-xs bg-surface text-ink placeholder-ink-mute border border-line rounded-lg focus:outline-none focus:border-brass focus:ring-2 focus:ring-brass/20 transition-colors"
                >
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Live URL -->
                <div>
                    <label for="project-live-url-input" class="block text-xs font-medium text-ink-soft mb-1">
                        Live application URL
                    </label>
                    <input type="url"
                           id="project-live-url-input"
                           maxlength="255"
                           placeholder="https://app.example.com"
                           class="w-full py-2 px-3 text-xs bg-surface text-ink placeholder-ink-mute border border-line rounded-lg focus:outline-none focus:border-brass focus:ring-2 focus:ring-brass/20 transition-colors"
                    >
                </div>

                <!-- GitHub URL -->
                <div>
                    <label for="project-github-url-input" class="block text-xs font-medium text-ink-soft mb-1">
                        GitHub / Repository URL
                    </label>
                    <input type="url"
                           id="project-github-url-input"
                           maxlength="255"
                           placeholder="https://github.com/username/project"
                           class="w-full py-2 px-3 text-xs bg-surface text-ink placeholder-ink-mute border border-line rounded-lg focus:outline-none focus:border-brass focus:ring-2 focus:ring-brass/20 transition-colors"
                    >
                </div>
            </div>

            <!-- Technologies -->
            <div>
                <label for="project-technologies-input" class="block text-xs font-medium text-ink-soft mb-1">
                    Technologies
                    <span class="text-[11px] font-normal text-ink-mute ml-1">(comma-separated names, e.g. Laravel, Redis, Vue.js, Docker)</span>
                </label>
                <input type="text"
                       id="project-technologies-input"
                       placeholder="Laravel, PostgreSQL, TailwindCSS, Docker"
                       class="w-full py-2 px-3 text-xs bg-surface text-ink placeholder-ink-mute border border-line rounded-lg focus:outline-none focus:border-brass focus:ring-2 focus:ring-brass/20 transition-colors"
                >
            </div>

            <!-- Toggles: Featured & Visible -->
            <div class="pt-2 border-t border-line grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="flex items-center justify-between p-3 rounded-lg bg-paper-sunk/30 border border-line">
                    <span class="text-xs font-medium text-ink">Featured project</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="project-featured-input" class="sr-only peer">
                        <div class="w-9 h-5 bg-paper-sunk border border-line peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-brass peer-checked:border-brass"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between p-3 rounded-lg bg-paper-sunk/30 border border-line">
                    <span class="text-xs font-medium text-ink">Visible on public portfolio</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="project-visible-input" checked class="sr-only peer">
                        <div class="w-9 h-5 bg-paper-sunk border border-line peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-brass peer-checked:border-brass"></div>
                    </label>
                </div>
            </div>

        </div>

        <x-slot:footer>
            <button type="button"
                    data-modal-close="modal-project"
                    class="px-3 py-1.5 text-xs font-medium text-ink-soft hover:text-ink hover:bg-paper rounded-md transition-colors">
                Cancel
            </button>
            <button type="button"
                    id="btn-save-project"
                    class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-medium text-[#14130F] bg-brass hover:bg-[#B89149] rounded-md transition-colors">
                <span id="btn-save-project-text">Save Project</span>
                <svg id="btn-save-project-spinner" class="animate-spin -ml-1 mr-1 h-3.5 w-3.5 text-ink hidden" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
            </button>
        </x-slot:footer>
    </x-admin.modal>

    <!-- Delete Project Modal -->
    <x-admin.confirm-delete id="modal-delete-project"
                            title="Delete Project"
                            message="Are you sure you want to delete this project? All associated details will be permanently removed." />

</div>
