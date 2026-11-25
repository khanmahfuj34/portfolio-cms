@php
    $oldNavItems = old('nav_items');
    $hasOldNavItems = is_array($oldNavItems) && count($oldNavItems) > 0;
    $homeIndex = 0;
    if ($hasOldNavItems) {
        foreach ($oldNavItems as $k => $v) {
            if ((isset($v['section_key']) && $v['section_key'] === 'home') || (isset($v['label']) && strcasecmp($v['label'], 'Home') === 0)) {
                $homeIndex = $k;
                break;
            }
        }
    }
    $initialCount = $hasOldNavItems ? count($oldNavItems) : 1;
@endphp

<div class="rounded-xl bg-surface border border-line p-6 sm:p-7 space-y-6">

    <!-- Section header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-5 border-b border-line">
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-md bg-brass-wash text-brass-ink flex items-center justify-center flex-shrink-0">
                <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7" />
                </svg>
            </div>
            <div class="min-w-0">
                <h3 class="text-sm font-medium text-ink">Navbar Configuration</h3>
                <p class="text-xs text-ink-mute mt-0.5 leading-relaxed">
                    Header links for this portfolio. Internal anchors or external URLs.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3 self-start sm:self-center">
            <span id="navbar-counter-badge" class="text-[11px] text-ink-mute tabular" title="Maximum 10 items">
                <span id="navbar-counter-label"><span id="navbar-count-display">{{ $initialCount }}</span> / 10 Items</span>
            </span>

            <button type="button"
                    id="btn-add-navbar-item"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-[#14130F] bg-brass hover:bg-[#B89149] rounded-md transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-brass {{ $initialCount >= 10 ? 'opacity-40 cursor-not-allowed' : '' }}"
                    {{ $initialCount >= 10 ? 'disabled' : '' }}>
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Add Navbar Item</span>
            </button>
        </div>
    </div>

    <!-- Collection-level error -->
    @error('nav_items')
        <div class="p-3.5 rounded-lg bg-[#FBF1EF] border border-[#E9D2CC] border-l-2 border-l-[#B4553F] text-xs flex items-center gap-2.5">
            <svg class="w-4 h-4 text-[#9C4A3E] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="flex-1 font-medium text-[11px] sm:text-xs text-ink">
                {{ $message }}
            </div>
        </div>
    @enderror

    <!-- Limit reached -->
    <div id="navbar-limit-message" class="{{ $initialCount >= 10 ? '' : 'hidden' }} p-3.5 rounded-lg bg-brass-wash/60 border border-[#E7DCC1] text-xs flex items-center gap-2.5">
        <svg class="w-4 h-4 text-brass-ink flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div class="flex-1 text-[11px] sm:text-xs text-ink-soft">
            <span class="font-medium text-ink">Maximum of 10 navbar items reached.</span> Delete an item to add a different link.
        </div>
    </div>

    <!-- Items list -->
    <div id="navbar-items-container" class="space-y-3">

        <!-- Default Home item -->
        <div class="navbar-item-card rounded-lg border border-line bg-paper-sunk/40 p-4 space-y-3"
             data-id="item-home"
             data-is-default="true">

            <div class="flex items-center justify-between gap-3 pb-2.5 border-b border-line">

                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-0.5">
                        <button type="button"
                                class="btn-move-up p-1 text-line-strong cursor-not-allowed rounded-md"
                                title="Move up"
                                disabled>
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                            </svg>
                        </button>
                        <button type="button"
                                class="btn-move-down p-1 text-ink-mute hover:text-ink hover:bg-paper rounded-md transition-colors"
                                title="Move down">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    </div>

                    <span class="sort-order-badge inline-flex items-center justify-center min-w-[24px] h-6 px-1.5 rounded border border-line bg-surface text-ink-soft font-mono text-[11px] tabular">
                        #1
                    </span>

                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-brass-wash text-brass-ink text-[10px] font-medium">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Default Home
                    </span>
                </div>

                @php
                    $homeIsVisible = filter_var(old("nav_items.{$homeIndex}.is_visible", '1'), FILTER_VALIDATE_BOOLEAN);
                @endphp
                <div class="flex items-center gap-2">
                    <button type="button"
                            class="btn-toggle-visibility inline-flex items-center gap-1.5 px-2 py-1 rounded-md text-[11px] font-medium {{ $homeIsVisible ? 'bg-[#EEF3EC] text-[#3F5A38] border border-[#DAE4D5]' : 'bg-paper text-ink-mute border border-line' }} transition-colors"
                            data-visible="{{ $homeIsVisible ? 'true' : 'false' }}"
                            title="Toggle visibility">
                        <svg class="w-3.5 h-3.5 icon-eye" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            @if($homeIsVisible)
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                            @endif
                        </svg>
                        <span class="visibility-label">{{ $homeIsVisible ? 'Visible' : 'Hidden' }}</span>
                    </button>
                    <input type="hidden" name="nav_items[{{ $homeIndex }}][is_visible]" class="input-is-visible" value="{{ $homeIsVisible ? '1' : '0' }}" />
                    <input type="hidden" name="nav_items[{{ $homeIndex }}][sort_order]" class="input-sort-order" value="{{ old("nav_items.{$homeIndex}.sort_order", 1) }}" />

                    <button type="button"
                            class="p-1.5 text-line-strong cursor-not-allowed rounded-md"
                            title="The default Home item cannot be removed."
                            disabled>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-start">
                <div class="md:col-span-4 space-y-1">
                    <label class="block text-[11px] font-medium text-ink-soft">
                        Link label <span class="text-brass-ink">*</span>
                    </label>
                    <input type="text"
                           name="nav_items[{{ $homeIndex }}][label]"
                           value="{{ old("nav_items.{$homeIndex}.label", 'Home') }}"
                           class="w-full py-2 px-3 text-xs bg-surface text-ink placeholder-ink-mute border @error("nav_items.{$homeIndex}.label") border-[#C98A7B] ring-1 ring-[#C98A7B]/30 @else border-line @enderror rounded-lg focus:outline-none focus:border-brass focus:ring-2 focus:ring-brass/20 transition-colors font-medium"
                           placeholder="Home"
                           required />
                    @error("nav_items.{$homeIndex}.label")
                        <p class="text-[11px] text-[#9C4A3E] font-medium mt-1 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                <div class="md:col-span-4 space-y-1">
                    <label class="block text-[11px] font-medium text-ink-soft">
                        Destination type <span class="text-brass-ink">*</span>
                    </label>
                    <div class="relative">
                        <select name="nav_items[{{ $homeIndex }}][type]"
                                class="destination-type-select appearance-none w-full py-2 pl-3 pr-8 text-xs bg-surface text-ink border border-line rounded-lg focus:outline-none focus:border-brass focus:ring-2 focus:ring-brass/20 transition-colors cursor-pointer font-medium">
                            <option value="section" selected>Internal section (#anchor)</option>
                            <option value="url">External URL (https://)</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-ink-mute">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-4 space-y-1 destination-value-wrapper">
                    <div class="section-key-container">
                        <label class="block text-[11px] font-medium text-ink-soft">
                            Section key <span class="text-brass-ink">*</span>
                        </label>
                        <div class="relative rounded-lg">
                            <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-brass-ink font-mono text-xs">
                                #
                            </div>
                            <input type="text"
                                   name="nav_items[{{ $homeIndex }}][section_key]"
                                   value="{{ old("nav_items.{$homeIndex}.section_key", 'home') }}"
                                   class="w-full py-2 pl-7 pr-3 text-xs bg-surface text-ink placeholder-ink-mute border @error("nav_items.{$homeIndex}.section_key") border-[#C98A7B] ring-1 ring-[#C98A7B]/30 @else border-line @enderror rounded-lg focus:outline-none focus:border-brass focus:ring-2 focus:ring-brass/20 transition-colors font-mono"
                                   placeholder="home"
                                   required />
                        </div>
                        @error("nav_items.{$homeIndex}.section_key")
                            <p class="text-[11px] text-[#9C4A3E] font-medium mt-1 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <div class="external-url-container hidden">
                        <label class="block text-[11px] font-medium text-ink-soft">
                            External URL <span class="text-brass-ink">*</span>
                        </label>
                        <div class="relative rounded-lg">
                            <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-ink-mute">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                </svg>
                            </div>
                            <input type="url"
                                   name="nav_items[{{ $homeIndex }}][url]"
                                   value="{{ old("nav_items.{$homeIndex}.url", '') }}"
                                   class="w-full py-2 pl-7 pr-3 text-xs bg-surface text-ink placeholder-ink-mute border border-line rounded-lg focus:outline-none focus:border-brass focus:ring-2 focus:ring-brass/20 transition-colors font-mono"
                                   placeholder="https://example.com"
                                   disabled />
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Custom items from old input -->
        @if($hasOldNavItems)
            @foreach($oldNavItems as $index => $item)
                @if($index == $homeIndex)
                    @continue
                @endif
                @php
                    $itemType = old("nav_items.{$index}.type", $item['type'] ?? 'section');
                    $isSection = ($itemType === 'section');
                    $itemOrder = old("nav_items.{$index}.sort_order", $item['sort_order'] ?? $loop->iteration);
                    $itemVisible = filter_var(old("nav_items.{$index}.is_visible", $item['is_visible'] ?? true), FILTER_VALIDATE_BOOLEAN);
                @endphp
                <div class="navbar-item-card rounded-lg border border-line bg-surface p-4 space-y-3"
                     data-id="item-{{ $index }}"
                     data-is-default="false">

                    <div class="flex items-center justify-between gap-3 pb-2.5 border-b border-line">
                        <div class="flex items-center gap-2">
                            <div class="flex items-center gap-0.5">
                                <button type="button"
                                        class="btn-move-up p-1 text-ink-mute hover:text-ink hover:bg-paper rounded-md transition-colors"
                                        title="Move up">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                                    </svg>
                                </button>
                                <button type="button"
                                        class="btn-move-down p-1 text-ink-mute hover:text-ink hover:bg-paper rounded-md transition-colors"
                                        title="Move down">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                            </div>

                            <span class="sort-order-badge inline-flex items-center justify-center min-w-[24px] h-6 px-1.5 rounded border border-line bg-paper text-ink-soft font-mono text-[11px] tabular">
                                #{{ $itemOrder }}
                            </span>

                            <span class="item-type-pill inline-flex items-center px-1.5 py-0.5 rounded bg-paper text-ink-mute text-[10px] font-medium border border-line">
                                Custom
                            </span>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button"
                                    class="btn-toggle-visibility inline-flex items-center gap-1.5 px-2 py-1 rounded-md text-[11px] font-medium {{ $itemVisible ? 'bg-[#EEF3EC] text-[#3F5A38] border border-[#DAE4D5]' : 'bg-paper text-ink-mute border border-line' }} transition-colors"
                                    data-visible="{{ $itemVisible ? 'true' : 'false' }}"
                                    title="Toggle visibility">
                                <svg class="w-3.5 h-3.5 icon-eye" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    @if($itemVisible)
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                    @endif
                                </svg>
                                <span class="visibility-label">{{ $itemVisible ? 'Visible' : 'Hidden' }}</span>
                            </button>
                            <input type="hidden" name="nav_items[{{ $index }}][is_visible]" class="input-is-visible" value="{{ $itemVisible ? '1' : '0' }}" />
                            <input type="hidden" name="nav_items[{{ $index }}][sort_order]" class="input-sort-order" value="{{ $itemOrder }}" />

                            <button type="button"
                                    class="btn-remove-item p-1.5 text-ink-mute hover:text-[#9C4A3E] hover:bg-[#F7ECE9] rounded-md transition-colors"
                                    title="Remove this navbar link">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-start">
                        <div class="md:col-span-4 space-y-1">
                            <label class="block text-[11px] font-medium text-ink-soft">
                                Link label <span class="text-brass-ink">*</span>
                            </label>
                            <input type="text"
                                   name="nav_items[{{ $index }}][label]"
                                   value="{{ old("nav_items.{$index}.label", $item['label'] ?? '') }}"
                                   class="input-nav-label w-full py-2 px-3 text-xs bg-surface text-ink placeholder-ink-mute border @error("nav_items.{$index}.label") border-[#C98A7B] ring-1 ring-[#C98A7B]/30 @else border-line @enderror rounded-lg focus:outline-none focus:border-brass focus:ring-2 focus:ring-brass/20 transition-colors font-medium"
                                   placeholder="About, Projects, Contact"
                                   required />
                            @error("nav_items.{$index}.label")
                                <p class="text-[11px] text-[#9C4A3E] font-medium mt-1 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <span>{{ $message }}</span>
                                </p>
                            @enderror
                        </div>

                        <div class="md:col-span-4 space-y-1">
                            <label class="block text-[11px] font-medium text-ink-soft">
                                Destination type <span class="text-brass-ink">*</span>
                            </label>
                            <div class="relative">
                                <select name="nav_items[{{ $index }}][type]"
                                        class="destination-type-select appearance-none w-full py-2 pl-3 pr-8 text-xs bg-surface text-ink border border-line rounded-lg focus:outline-none focus:border-brass focus:ring-2 focus:ring-brass/20 transition-colors cursor-pointer font-medium">
                                    <option value="section" {{ $isSection ? 'selected' : '' }}>Internal section (#anchor)</option>
                                    <option value="url" {{ ! $isSection ? 'selected' : '' }}>External URL (https://)</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-ink-mute">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="md:col-span-4 space-y-1 destination-value-wrapper">
                            <div class="section-key-container {{ $isSection ? '' : 'hidden' }}">
                                <label class="block text-[11px] font-medium text-ink-soft">
                                    Section key <span class="text-brass-ink">*</span>
                                </label>
                                <div class="relative rounded-lg">
                                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-brass-ink font-mono text-xs">
                                        #
                                    </div>
                                    <input type="text"
                                           name="nav_items[{{ $index }}][section_key]"
                                           value="{{ old("nav_items.{$index}.section_key", $item['section_key'] ?? '') }}"
                                           class="input-section-key w-full py-2 pl-7 pr-3 text-xs bg-surface text-ink placeholder-ink-mute border @error("nav_items.{$index}.section_key") border-[#C98A7B] ring-1 ring-[#C98A7B]/30 @else border-line @enderror rounded-lg focus:outline-none focus:border-brass focus:ring-2 focus:ring-brass/20 transition-colors font-mono"
                                           placeholder="about, skills, contact"
                                           {{ $isSection ? 'required' : 'disabled' }} />
                                </div>
                                @error("nav_items.{$index}.section_key")
                                    <p class="text-[11px] text-[#9C4A3E] font-medium mt-1 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        <span>{{ $message }}</span>
                                    </p>
                                @enderror
                            </div>

                            <div class="external-url-container {{ $isSection ? 'hidden' : '' }}">
                                <label class="block text-[11px] font-medium text-ink-soft">
                                    External URL <span class="text-brass-ink">*</span>
                                </label>
                                <div class="relative rounded-lg">
                                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-ink-mute">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                        </svg>
                                    </div>
                                    <input type="url"
                                           name="nav_items[{{ $index }}][url]"
                                           value="{{ old("nav_items.{$index}.url", $item['url'] ?? '') }}"
                                           class="input-external-url w-full py-2 pl-7 pr-3 text-xs bg-surface text-ink placeholder-ink-mute border @error("nav_items.{$index}.url") border-[#C98A7B] ring-1 ring-[#C98A7B]/30 @else border-line @enderror rounded-lg focus:outline-none focus:border-brass focus:ring-2 focus:ring-brass/20 transition-colors font-mono"
                                           placeholder="https://github.com/example"
                                           {{ $isSection ? 'disabled' : 'required' }} />
                                </div>
                                @error("nav_items.{$index}.url")
                                    <p class="text-[11px] text-[#9C4A3E] font-medium mt-1 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        <span>{{ $message }}</span>
                                    </p>
                                @enderror
                            </div>
                        </div>
                    </div>

                </div>
            @endforeach
        @endif

    </div>

    <!-- JS template -->
    <template id="navbar-item-template">
        <div class="navbar-item-card rounded-lg border border-line bg-surface p-4 space-y-3"
             data-id="__ID__"
             data-is-default="false">

            <div class="flex items-center justify-between gap-3 pb-2.5 border-b border-line">

                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-0.5">
                        <button type="button"
                                class="btn-move-up p-1 text-ink-mute hover:text-ink hover:bg-paper rounded-md transition-colors"
                                title="Move up">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                            </svg>
                        </button>
                        <button type="button"
                                class="btn-move-down p-1 text-ink-mute hover:text-ink hover:bg-paper rounded-md transition-colors"
                                title="Move down">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    </div>

                    <span class="sort-order-badge inline-flex items-center justify-center min-w-[24px] h-6 px-1.5 rounded border border-line bg-paper text-ink-soft font-mono text-[11px] tabular">
                        #__ORDER__
                    </span>

                    <span class="item-type-pill inline-flex items-center px-1.5 py-0.5 rounded bg-paper text-ink-mute text-[10px] font-medium border border-line">
                        Custom
                    </span>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button"
                            class="btn-toggle-visibility inline-flex items-center gap-1.5 px-2 py-1 rounded-md text-[11px] font-medium bg-[#EEF3EC] text-[#3F5A38] border border-[#DAE4D5] transition-colors"
                            data-visible="true"
                            title="Toggle visibility">
                        <svg class="w-3.5 h-3.5 icon-eye" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <span class="visibility-label">Visible</span>
                    </button>
                    <input type="hidden" name="nav_items[__INDEX__][is_visible]" class="input-is-visible" value="1" />
                    <input type="hidden" name="nav_items[__INDEX__][sort_order]" class="input-sort-order" value="__ORDER__" />

                    <button type="button"
                            class="btn-remove-item p-1.5 text-ink-mute hover:text-[#9C4A3E] hover:bg-[#F7ECE9] rounded-md transition-colors"
                            title="Remove this navbar link">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-start">
                <div class="md:col-span-4 space-y-1">
                    <label class="block text-[11px] font-medium text-ink-soft">
                        Link label <span class="text-brass-ink">*</span>
                    </label>
                    <input type="text"
                           name="nav_items[__INDEX__][label]"
                           value="__LABEL__"
                           class="input-nav-label w-full py-2 px-3 text-xs bg-surface text-ink placeholder-ink-mute border border-line rounded-lg focus:outline-none focus:border-brass focus:ring-2 focus:ring-brass/20 transition-colors font-medium"
                           placeholder="About, Projects, Contact"
                           required />
                </div>

                <div class="md:col-span-4 space-y-1">
                    <label class="block text-[11px] font-medium text-ink-soft">
                        Destination type <span class="text-brass-ink">*</span>
                    </label>
                    <div class="relative">
                        <select name="nav_items[__INDEX__][type]"
                                class="destination-type-select appearance-none w-full py-2 pl-3 pr-8 text-xs bg-surface text-ink border border-line rounded-lg focus:outline-none focus:border-brass focus:ring-2 focus:ring-brass/20 transition-colors cursor-pointer font-medium">
                            <option value="section" selected>Internal section (#anchor)</option>
                            <option value="url">External URL (https://)</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-ink-mute">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-4 space-y-1 destination-value-wrapper">
                    <div class="section-key-container">
                        <label class="block text-[11px] font-medium text-ink-soft">
                            Section key <span class="text-brass-ink">*</span>
                        </label>
                        <div class="relative rounded-lg">
                            <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-brass-ink font-mono text-xs">
                                #
                            </div>
                            <input type="text"
                                   name="nav_items[__INDEX__][section_key]"
                                   value="__SECTION_KEY__"
                                   class="input-section-key w-full py-2 pl-7 pr-3 text-xs bg-surface text-ink placeholder-ink-mute border border-line rounded-lg focus:outline-none focus:border-brass focus:ring-2 focus:ring-brass/20 transition-colors font-mono"
                                   placeholder="about, skills, contact"
                                   required />
                        </div>
                    </div>

                    <div class="external-url-container hidden">
                        <label class="block text-[11px] font-medium text-ink-soft">
                            External URL <span class="text-brass-ink">*</span>
                        </label>
                        <div class="relative rounded-lg">
                            <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-ink-mute">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                </svg>
                            </div>
                            <input type="url"
                                   name="nav_items[__INDEX__][url]"
                                   value=""
                                   class="input-external-url w-full py-2 pl-7 pr-3 text-xs bg-surface text-ink placeholder-ink-mute border border-line rounded-lg focus:outline-none focus:border-brass focus:ring-2 focus:ring-brass/20 transition-colors font-mono"
                                   placeholder="https://github.com/example"
                                   disabled />
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </template>
</div>
