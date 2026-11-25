@php
    $portfolioId = isset($portfolio) && $portfolio->exists ? $portfolio->id : null;
    $oldSkills = old('skills');
    if (is_array($oldSkills)) {
        $initialSkills = collect($oldSkills)->map(function ($item, $key) {
            $name = is_array($item) ? ($item['name'] ?? '') : $item;
            return (object) ['id' => 'skill-' . $key, 'name' => trim((string) $name)];
        })->filter(fn ($s) => $s->name !== '');
    } elseif (isset($portfolio) && $portfolio->exists) {
        $initialSkills = $portfolio->skills;
    } else {
        $initialSkills = collect();
    }
@endphp

<div id="skills-configuration-section"
     class="rounded-xl bg-surface border border-line p-6 sm:p-7 space-y-6"
     data-portfolio-id="{{ $portfolioId ?? '' }}"
     data-skills-url="{{ $portfolioId ? route('admin.portfolios.skills.index', $portfolio) : '' }}">

    <!-- Hidden form inputs container for create/edit form submissions -->
    <div id="skills-hidden-inputs">
        @foreach($initialSkills as $idx => $skill)
            <input type="hidden" class="skill-hidden-name" name="skills[{{ $idx }}][name]" value="{{ $skill->name }}">
        @endforeach
    </div>

    <!-- Section Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-5 border-b border-line">
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-md bg-brass-wash text-brass-ink flex items-center justify-center flex-shrink-0">
                <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                </svg>
            </div>
            <div class="min-w-0">
                <h3 class="text-sm font-medium text-ink">Skills Configuration</h3>
                <p class="text-xs text-ink-mute mt-0.5 leading-relaxed">
                    Technical proficiencies, frameworks and tools associated with this portfolio.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3 self-start sm:self-center">
            <span id="skills-counter-badge" class="text-[11px] text-ink-mute tabular">
                <span id="skills-count-display">{{ $initialSkills->count() }}</span> {{ Str::plural('Skill', $initialSkills->count()) }}
            </span>

            <button type="button"
                    id="btn-open-add-skill"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-[#14130F] bg-brass hover:bg-[#B89149] rounded-md transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-brass">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Add Skill</span>
            </button>
        </div>
    </div>

    <!-- Alert / Status Container -->
    <div id="skills-alert-container" class="hidden"></div>

    @if(isset($errors) && $errors->has('skills.*'))
        <div class="p-3 rounded-lg bg-[#FBF1EF] border border-[#E9D2CC] text-[#9C4A3E] text-xs space-y-1">
            @foreach($errors->get('skills.*') as $skillErrors)
                @foreach($skillErrors as $err)
                    <p>{{ $err }}</p>
                @endforeach
            @endforeach
        </div>
    @endif

    <!-- Inline Add Skill Form Card -->
    <div id="add-skill-form-card" class="hidden p-4 rounded-lg bg-paper-sunk/50 border border-line space-y-3">
        <div class="flex items-center justify-between pb-2 border-b border-line">
            <span class="text-xs font-medium text-ink">Add New Skill</span>
            <button type="button" id="btn-cancel-add-skill" class="text-xs text-ink-mute hover:text-ink">Cancel</button>
        </div>
        <div id="form-create-skill" class="flex flex-col sm:flex-row gap-2.5">
            <div class="flex-1">
                <input type="text"
                       id="skill-name-input"
                       maxlength="100"
                       placeholder="e.g. Laravel, React, Docker, TypeScript"
                       class="w-full py-2 px-3 text-xs bg-surface text-ink placeholder-ink-mute border border-line rounded-lg focus:outline-none focus:border-brass focus:ring-2 focus:ring-brass/20 transition-colors"
                >
                <p id="skill-create-error" class="text-[11px] text-[#9C4A3E] font-medium mt-1 hidden"></p>
            </div>
            <button type="button"
                    id="btn-submit-skill"
                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-xs font-medium text-[#14130F] bg-brass hover:bg-[#B89149] rounded-lg transition-colors flex-shrink-0 disabled:opacity-50">
                <span id="btn-submit-skill-text">Add Skill</span>
                <svg id="btn-submit-skill-spinner" class="animate-spin -ml-1 mr-1 h-3.5 w-3.5 text-ink hidden" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Skills List Container -->
    <div id="skills-items-container" class="flex flex-wrap gap-2.5">
        @forelse($initialSkills as $idx => $skill)
            <div class="skill-item-chip inline-flex items-center gap-2 pl-3 pr-2 py-1.5 rounded-lg border border-line bg-paper-sunk/40 hover:bg-paper-sunk text-ink text-xs transition-colors group"
                 data-skill-id="{{ $skill->id }}"
                 data-skill-name="{{ $skill->name }}">
                <span class="skill-name-text font-medium text-ink">{{ $skill->name }}</span>
                <div class="flex items-center gap-1 border-l border-line/80 pl-1.5">
                    <button type="button"
                            class="btn-edit-skill p-1 text-ink-mute hover:text-ink rounded transition-colors"
                            title="Edit skill">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                    </button>
                    <button type="button"
                            class="btn-delete-skill p-1 text-ink-mute hover:text-[#9C4A3E] rounded transition-colors"
                            title="Delete skill">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        @empty
            <div id="skills-empty-state" class="w-full text-center py-8 px-4 rounded-lg border border-dashed border-line text-ink-mute">
                <p class="text-xs">No skills configured yet.</p>
                <p class="text-[11px] text-ink-mute/70 mt-1">Click "Add Skill" above to add your first skill.</p>
            </div>
        @endforelse
    </div>

    <!-- Edit Skill Modal -->
    <x-admin.modal id="modal-edit-skill" title="Edit Skill" size="max-w-md">
        <div id="form-edit-skill" class="space-y-4">
            <input type="hidden" id="edit-skill-id" value="">
            <div>
                <label for="edit-skill-name-input" class="block text-xs font-medium text-ink-soft mb-1">
                    Skill name <span class="text-brass-ink">*</span>
                </label>
                <input type="text"
                       id="edit-skill-name-input"
                       maxlength="100"
                       class="w-full py-2 px-3 text-xs bg-surface text-ink placeholder-ink-mute border border-line rounded-lg focus:outline-none focus:border-brass focus:ring-2 focus:ring-brass/20 transition-colors"
                >
                <p id="skill-edit-error" class="text-[11px] text-[#9C4A3E] font-medium mt-1 hidden"></p>
            </div>
        </div>

        <x-slot:footer>
            <button type="button"
                    data-modal-close="modal-edit-skill"
                    class="px-3 py-1.5 text-xs font-medium text-ink-soft hover:text-ink hover:bg-paper rounded-md transition-colors">
                Cancel
            </button>
            <button type="button"
                    id="btn-confirm-edit-skill"
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-medium text-[#14130F] bg-brass hover:bg-[#B89149] rounded-md transition-colors">
                <span id="btn-confirm-edit-skill-text">Update Skill</span>
                <svg id="btn-confirm-edit-skill-spinner" class="animate-spin -ml-1 mr-1 h-3.5 w-3.5 text-ink hidden" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
            </button>
        </x-slot:footer>
    </x-admin.modal>

    <!-- Delete Skill Modal -->
    <x-admin.confirm-delete id="modal-delete-skill"
                            title="Delete Skill"
                            message="Are you sure you want to delete this skill? It will be removed from this portfolio." />

</div>
