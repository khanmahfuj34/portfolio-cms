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

<div class="rounded-2xl bg-white border border-slate-200/80 p-6 sm:p-7 shadow-xs space-y-6 relative overflow-hidden group">
    <!-- Subtle Ambient Top-Right Accent -->
    <div class="absolute -top-12 -right-12 w-32 h-32 bg-[#D4AF69]/5 rounded-full blur-2xl group-hover:bg-[#D4AF69]/10 transition-all duration-500 pointer-events-none" aria-hidden="true"></div>

    <!-- Section Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-5 border-b border-slate-100">
        <div class="flex items-start gap-4">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-tr from-[#0F1219] to-[#252C3C] text-[#D4AF69] flex items-center justify-center flex-shrink-0 border border-[#D4AF69]/30 shadow-xs">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7" />
                </svg>
            </div>
            <div class="min-w-0">
                <h3 class="text-base font-bold text-slate-900 tracking-tight flex items-center gap-2">
                    <span>Navbar Configuration</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#D4AF69]/15 text-[#9A7326] border border-[#D4AF69]/25">
                        Site Navigation
                    </span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">
                    Configure header navigation links for this portfolio. Support internal section anchors and external URLs.
                </p>
            </div>
        </div>

        <!-- Live Counter & Add Button -->
        <div class="flex items-center gap-3 self-start sm:self-center">
            <!-- Counter Badge -->
            <div id="navbar-counter-badge" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 border border-slate-200 text-xs font-semibold text-slate-700 shadow-2xs">
                <span class="w-2 h-2 rounded-full bg-[#D4AF69]"></span>
                <span id="navbar-counter-label"><span id="navbar-count-display">{{ $initialCount }}</span> / 10 Items</span>
            </div>

            <!-- Add Item Button -->
            <button type="button" 
                    id="btn-add-navbar-item" 
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-bold text-slate-900 bg-[#D4AF69] hover:bg-[#C9A25B] focus:ring-2 focus:ring-[#D4AF69]/30 rounded-xl transition-all shadow-xs {{ $initialCount >= 10 ? 'opacity-40 cursor-not-allowed' : '' }}"
                    {{ $initialCount >= 10 ? 'disabled' : '' }}>
                <svg class="w-3.5 h-3.5 text-slate-900" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Add Navbar Item</span>
            </button>
        </div>
    </div>

    <!-- Collection-level error notice -->
    @error('nav_items')
        <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-2.5 shadow-2xs">
            <svg class="w-4 h-4 text-rose-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="flex-1 font-semibold text-[11px] sm:text-xs">
                {{ $message }}
            </div>
        </div>
    @enderror

    <!-- Maximum Limit Reached Notice -->
    <div id="navbar-limit-message" class="{{ $initialCount >= 10 ? '' : 'hidden' }} p-3.5 rounded-xl bg-amber-50/90 border border-amber-200 text-amber-800 text-xs flex items-center gap-2.5 shadow-2xs">
        <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div class="flex-1 text-[11px] sm:text-xs">
            <span class="font-bold">Maximum limit of 10 navbar items reached.</span> Delete an existing item if you need to add a different link.
        </div>
    </div>

    <!-- Dynamic Items List Container -->
    <div id="navbar-items-container" class="space-y-3.5">
        
        <!-- 1. Default Permanent Home Item (Item #1) -->
        <div class="navbar-item-card rounded-xl border border-slate-200/90 bg-slate-50/70 hover:bg-slate-50 p-4 transition-all duration-200 space-y-3" 
             data-id="item-home" 
             data-is-default="true">
            
            <!-- Card Header: Reorder Controls, Badge, Status & Actions -->
            <div class="flex items-center justify-between gap-3 pb-2.5 border-b border-slate-200/70">
                
                <!-- Reorder Controls & Order Badge -->
                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-1">
                        <button type="button" 
                                class="btn-move-up p-1 text-slate-300 cursor-not-allowed rounded-md transition-colors" 
                                title="Move up" 
                                disabled>
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                            </svg>
                        </button>
                        <button type="button" 
                                class="btn-move-down p-1 text-slate-500 hover:text-slate-900 hover:bg-slate-200/60 rounded-md transition-colors" 
                                title="Move down">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    </div>

                    <span class="sort-order-badge inline-flex items-center justify-center min-w-[24px] h-6 px-1.5 rounded-md bg-white border border-slate-200 text-slate-700 font-mono text-[11px] font-bold shadow-2xs">
                        #1
                    </span>

                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-[#D4AF69]/20 text-[#8C651B] border border-[#D4AF69]/30">
                        <svg class="w-3 h-3 text-[#D4AF69]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Default Home
                    </span>
                </div>

                @php
                    $homeIsVisible = filter_var(old("nav_items.{$homeIndex}.is_visible", '1'), FILTER_VALIDATE_BOOLEAN);
                @endphp
                <!-- Right Controls: Visibility & Delete (Permanent lock for Home) -->
                <div class="flex items-center gap-2">
                    <!-- Visibility Toggle -->
                    <button type="button" 
                            class="btn-toggle-visibility inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-medium {{ $homeIsVisible ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 border border-slate-200 hover:bg-slate-200/60' }} transition-colors"
                            data-visible="{{ $homeIsVisible ? 'true' : 'false' }}"
                            title="Toggle Visibility">
                        <svg class="w-3.5 h-3.5 icon-eye" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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

                    <!-- Permanent Delete Lock (Home cannot be removed) -->
                    <button type="button" 
                            class="p-1.5 text-slate-300 cursor-not-allowed rounded-lg" 
                            title="The default Home item is mandatory and cannot be removed." 
                            disabled>
                        <svg class="w-4 h-4 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Card Inputs: Label, Destination Type, and Destination Target -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3.5 items-start">
                <!-- 1. Item Label (4 cols) -->
                <div class="md:col-span-4 space-y-1">
                    <label class="block text-[11px] font-semibold text-slate-600">
                        Link Label <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           name="nav_items[{{ $homeIndex }}][label]" 
                           value="{{ old("nav_items.{$homeIndex}.label", 'Home') }}" 
                           class="w-full py-2 px-3 text-xs bg-white text-slate-800 placeholder-slate-400 border @error("nav_items.{$homeIndex}.label") border-rose-400 ring-1 ring-rose-400/30 @else border-slate-200 @enderror rounded-lg focus:outline-none focus:border-[#D4AF69] focus:ring-1 focus:ring-[#D4AF69] transition-all font-medium" 
                           placeholder="e.g. Home" 
                           required />
                    @error("nav_items.{$homeIndex}.label")
                        <p class="text-[11px] text-rose-600 font-medium mt-1 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                <!-- 2. Destination Type (4 cols) -->
                <div class="md:col-span-4 space-y-1">
                    <label class="block text-[11px] font-semibold text-slate-600">
                        Destination Type <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <select name="nav_items[{{ $homeIndex }}][type]" 
                                class="destination-type-select appearance-none w-full py-2 pl-3 pr-8 text-xs bg-white text-slate-800 border border-slate-200 rounded-lg focus:outline-none focus:border-[#D4AF69] focus:ring-1 focus:ring-[#D4AF69] transition-all cursor-pointer font-medium">
                            <option value="section" selected>Internal Section (#anchor)</option>
                            <option value="url">External Web URL (https://)</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- 3. Destination Target Value (4 cols) -->
                <div class="md:col-span-4 space-y-1 destination-value-wrapper">
                    <!-- Section Key Input (active by default) -->
                    <div class="section-key-container">
                        <label class="block text-[11px] font-semibold text-slate-600">
                            Section Key <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative rounded-lg shadow-2xs">
                            <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-[#9A7326] font-mono text-xs font-bold">
                                #
                            </div>
                            <input type="text" 
                                   name="nav_items[{{ $homeIndex }}][section_key]" 
                                   value="{{ old("nav_items.{$homeIndex}.section_key", 'home') }}" 
                                   class="w-full py-2 pl-7 pr-3 text-xs bg-white text-slate-800 placeholder-slate-400 border @error("nav_items.{$homeIndex}.section_key") border-rose-400 ring-1 ring-rose-400/30 @else border-slate-200 @enderror rounded-lg focus:outline-none focus:border-[#D4AF69] focus:ring-1 focus:ring-[#D4AF69] transition-all font-mono" 
                                   placeholder="home" 
                                   required />
                        </div>
                        @error("nav_items.{$homeIndex}.section_key")
                            <p class="text-[11px] text-rose-600 font-medium mt-1 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <!-- External URL Input (hidden by default for Home) -->
                    <div class="external-url-container hidden">
                        <label class="block text-[11px] font-semibold text-slate-600">
                            External URL <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative rounded-lg shadow-2xs">
                            <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400 font-mono text-xs">
                                🔗
                            </div>
                            <input type="url" 
                                   name="nav_items[{{ $homeIndex }}][url]" 
                                   value="{{ old("nav_items.{$homeIndex}.url", '') }}" 
                                   class="w-full py-2 pl-7 pr-3 text-xs bg-white text-slate-800 placeholder-slate-400 border border-slate-200 rounded-lg focus:outline-none focus:border-[#D4AF69] focus:ring-1 focus:ring-[#D4AF69] transition-all font-mono" 
                                   placeholder="https://example.com" 
                                   disabled />
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Custom Navbar Items (Rehydrated from old input on validation failure) -->
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
                <div class="navbar-item-card rounded-xl border border-slate-200/90 bg-white hover:bg-slate-50/50 p-4 transition-all duration-200 space-y-3 shadow-2xs" 
                     data-id="item-{{ $index }}" 
                     data-is-default="false">
                    
                    <!-- Card Header: Reorder Controls, Badge, Status & Actions -->
                    <div class="flex items-center justify-between gap-3 pb-2.5 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <div class="flex items-center gap-1">
                                <button type="button" 
                                        class="btn-move-up p-1 text-slate-500 hover:text-slate-900 hover:bg-slate-100 rounded-md transition-colors" 
                                        title="Move up">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                                    </svg>
                                </button>
                                <button type="button" 
                                        class="btn-move-down p-1 text-slate-500 hover:text-slate-900 hover:bg-slate-100 rounded-md transition-colors" 
                                        title="Move down">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                            </div>

                            <span class="sort-order-badge inline-flex items-center justify-center min-w-[24px] h-6 px-1.5 rounded-md bg-slate-100 border border-slate-200 text-slate-700 font-mono text-[11px] font-bold shadow-2xs">
                                #{{ $itemOrder }}
                            </span>

                            <span class="item-type-pill inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                Custom Link
                            </span>
                        </div>

                        <!-- Right Controls: Visibility & Delete -->
                        <div class="flex items-center gap-2">
                            <!-- Visibility Toggle -->
                            <button type="button" 
                                    class="btn-toggle-visibility inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-medium {{ $itemVisible ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 border border-slate-200 hover:bg-slate-200/60' }} transition-colors"
                                    data-visible="{{ $itemVisible ? 'true' : 'false' }}"
                                    title="Toggle Visibility">
                                <svg class="w-3.5 h-3.5 icon-eye" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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

                            <!-- Remove Item Button -->
                            <button type="button" 
                                    class="btn-remove-item p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" 
                                    title="Remove this navbar link">
                                <svg class="w-4 h-4 text-slate-400 hover:text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Card Inputs: Label, Destination Type, and Destination Target -->
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3.5 items-start">
                        <!-- 1. Item Label (4 cols) -->
                        <div class="md:col-span-4 space-y-1">
                            <label class="block text-[11px] font-semibold text-slate-600">
                                Link Label <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   name="nav_items[{{ $index }}][label]" 
                                   value="{{ old("nav_items.{$index}.label", $item['label'] ?? '') }}" 
                                   class="input-nav-label w-full py-2 px-3 text-xs bg-white text-slate-800 placeholder-slate-400 border @error("nav_items.{$index}.label") border-rose-400 ring-1 ring-rose-400/30 @else border-slate-200 @enderror rounded-lg focus:outline-none focus:border-[#D4AF69] focus:ring-1 focus:ring-[#D4AF69] transition-all font-medium" 
                                   placeholder="e.g. About, Projects, Contact" 
                                   required />
                            @error("nav_items.{$index}.label")
                                <p class="text-[11px] text-rose-600 font-medium mt-1 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <span>{{ $message }}</span>
                                </p>
                            @enderror
                        </div>

                        <!-- 2. Destination Type (4 cols) -->
                        <div class="md:col-span-4 space-y-1">
                            <label class="block text-[11px] font-semibold text-slate-600">
                                Destination Type <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <select name="nav_items[{{ $index }}][type]" 
                                        class="destination-type-select appearance-none w-full py-2 pl-3 pr-8 text-xs bg-white text-slate-800 border border-slate-200 rounded-lg focus:outline-none focus:border-[#D4AF69] focus:ring-1 focus:ring-[#D4AF69] transition-all cursor-pointer font-medium">
                                    <option value="section" {{ $isSection ? 'selected' : '' }}>Internal Section (#anchor)</option>
                                    <option value="url" {{ ! $isSection ? 'selected' : '' }}>External Web URL (https://)</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Destination Target Value (4 cols) -->
                        <div class="md:col-span-4 space-y-1 destination-value-wrapper">
                            <!-- Section Key Input -->
                            <div class="section-key-container {{ $isSection ? '' : 'hidden' }}">
                                <label class="block text-[11px] font-semibold text-slate-600">
                                    Section Key <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative rounded-lg shadow-2xs">
                                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-[#9A7326] font-mono text-xs font-bold">
                                        #
                                    </div>
                                    <input type="text" 
                                           name="nav_items[{{ $index }}][section_key]" 
                                           value="{{ old("nav_items.{$index}.section_key", $item['section_key'] ?? '') }}" 
                                           class="input-section-key w-full py-2 pl-7 pr-3 text-xs bg-white text-slate-800 placeholder-slate-400 border @error("nav_items.{$index}.section_key") border-rose-400 ring-1 ring-rose-400/30 @else border-slate-200 @enderror rounded-lg focus:outline-none focus:border-[#D4AF69] focus:ring-1 focus:ring-[#D4AF69] transition-all font-mono" 
                                           placeholder="e.g. about, skills, contact" 
                                           {{ $isSection ? 'required' : 'disabled' }} />
                                </div>
                                @error("nav_items.{$index}.section_key")
                                    <p class="text-[11px] text-rose-600 font-medium mt-1 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        <span>{{ $message }}</span>
                                    </p>
                                @enderror
                            </div>

                            <!-- External URL Input -->
                            <div class="external-url-container {{ $isSection ? 'hidden' : '' }}">
                                <label class="block text-[11px] font-semibold text-slate-600">
                                    External URL <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative rounded-lg shadow-2xs">
                                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400 font-mono text-xs">
                                        🔗
                                    </div>
                                    <input type="url" 
                                           name="nav_items[{{ $index }}][url]" 
                                           value="{{ old("nav_items.{$index}.url", $item['url'] ?? '') }}" 
                                           class="input-external-url w-full py-2 pl-7 pr-3 text-xs bg-white text-slate-800 placeholder-slate-400 border @error("nav_items.{$index}.url") border-rose-400 ring-1 ring-rose-400/30 @else border-slate-200 @enderror rounded-lg focus:outline-none focus:border-[#D4AF69] focus:ring-1 focus:ring-[#D4AF69] transition-all font-mono" 
                                           placeholder="https://github.com/example" 
                                           {{ $isSection ? 'disabled' : 'required' }} />
                                </div>
                                @error("nav_items.{$index}.url")
                                    <p class="text-[11px] text-rose-600 font-medium mt-1 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
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

    <!-- Template for Dynamically Added Navbar Items (Used by JavaScript) -->
    <template id="navbar-item-template">
        <div class="navbar-item-card rounded-xl border border-slate-200/90 bg-white hover:bg-slate-50/50 p-4 transition-all duration-200 space-y-3 shadow-2xs" 
             data-id="__ID__" 
             data-is-default="false">
            
            <!-- Card Header: Reorder Controls, Badge, Status & Actions -->
            <div class="flex items-center justify-between gap-3 pb-2.5 border-b border-slate-100">
                
                <!-- Reorder Controls & Order Badge -->
                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-1">
                        <button type="button" 
                                class="btn-move-up p-1 text-slate-500 hover:text-slate-900 hover:bg-slate-100 rounded-md transition-colors" 
                                title="Move up">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                            </svg>
                        </button>
                        <button type="button" 
                                class="btn-move-down p-1 text-slate-500 hover:text-slate-900 hover:bg-slate-100 rounded-md transition-colors" 
                                title="Move down">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    </div>

                    <span class="sort-order-badge inline-flex items-center justify-center min-w-[24px] h-6 px-1.5 rounded-md bg-slate-100 border border-slate-200 text-slate-700 font-mono text-[11px] font-bold shadow-2xs">
                        #__ORDER__
                    </span>

                    <span class="item-type-pill inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                        Custom Link
                    </span>
                </div>

                <!-- Right Controls: Visibility & Delete -->
                <div class="flex items-center gap-2">
                    <!-- Visibility Toggle -->
                    <button type="button" 
                            class="btn-toggle-visibility inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition-colors"
                            data-visible="true"
                            title="Toggle Visibility">
                        <svg class="w-3.5 h-3.5 icon-eye" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <span class="visibility-label">Visible</span>
                    </button>
                    <input type="hidden" name="nav_items[__INDEX__][is_visible]" class="input-is-visible" value="1" />
                    <input type="hidden" name="nav_items[__INDEX__][sort_order]" class="input-sort-order" value="__ORDER__" />

                    <!-- Remove Item Button -->
                    <button type="button" 
                            class="btn-remove-item p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" 
                            title="Remove this navbar link">
                        <svg class="w-4 h-4 text-slate-400 hover:text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Card Inputs: Label, Destination Type, and Destination Target -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3.5 items-start">
                <!-- 1. Item Label (4 cols) -->
                <div class="md:col-span-4 space-y-1">
                    <label class="block text-[11px] font-semibold text-slate-600">
                        Link Label <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           name="nav_items[__INDEX__][label]" 
                           value="__LABEL__" 
                           class="input-nav-label w-full py-2 px-3 text-xs bg-white text-slate-800 placeholder-slate-400 border border-slate-200 rounded-lg focus:outline-none focus:border-[#D4AF69] focus:ring-1 focus:ring-[#D4AF69] transition-all font-medium" 
                           placeholder="e.g. About, Projects, Contact" 
                           required />
                </div>

                <!-- 2. Destination Type (4 cols) -->
                <div class="md:col-span-4 space-y-1">
                    <label class="block text-[11px] font-semibold text-slate-600">
                        Destination Type <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <select name="nav_items[__INDEX__][type]" 
                                class="destination-type-select appearance-none w-full py-2 pl-3 pr-8 text-xs bg-white text-slate-800 border border-slate-200 rounded-lg focus:outline-none focus:border-[#D4AF69] focus:ring-1 focus:ring-[#D4AF69] transition-all cursor-pointer font-medium">
                            <option value="section" selected>Internal Section (#anchor)</option>
                            <option value="url">External Web URL (https://)</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- 3. Destination Target Value (4 cols) -->
                <div class="md:col-span-4 space-y-1 destination-value-wrapper">
                    <!-- Section Key Input -->
                    <div class="section-key-container">
                        <label class="block text-[11px] font-semibold text-slate-600">
                            Section Key <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative rounded-lg shadow-2xs">
                            <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-[#9A7326] font-mono text-xs font-bold">
                                #
                            </div>
                            <input type="text" 
                                   name="nav_items[__INDEX__][section_key]" 
                                   value="__SECTION_KEY__" 
                                   class="input-section-key w-full py-2 pl-7 pr-3 text-xs bg-white text-slate-800 placeholder-slate-400 border border-slate-200 rounded-lg focus:outline-none focus:border-[#D4AF69] focus:ring-1 focus:ring-[#D4AF69] transition-all font-mono" 
                                   placeholder="e.g. about, skills, contact" 
                                   required />
                        </div>
                    </div>

                    <!-- External URL Input (hidden initially for section) -->
                    <div class="external-url-container hidden">
                        <label class="block text-[11px] font-semibold text-slate-600">
                            External URL <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative rounded-lg shadow-2xs">
                            <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400 font-mono text-xs">
                                🔗
                            </div>
                            <input type="url" 
                                   name="nav_items[__INDEX__][url]" 
                                   value="" 
                                   class="input-external-url w-full py-2 pl-7 pr-3 text-xs bg-white text-slate-800 placeholder-slate-400 border border-slate-200 rounded-lg focus:outline-none focus:border-[#D4AF69] focus:ring-1 focus:ring-[#D4AF69] transition-all font-mono" 
                                   placeholder="https://github.com/example" 
                                   disabled />
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </template>
</div>
