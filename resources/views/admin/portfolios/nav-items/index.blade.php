@extends('layouts.admin')

@section('title', 'Manage Navigation — ' . $portfolio->name . ' — Portfolio CMS')
@section('page_title', 'Navigation')

@section('content')
<div class="space-y-6">

    <!-- Header Section with Breadcrumb and Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200/80">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-800 transition-colors">Admin</a>
                <span>/</span>
                <a href="{{ route('admin.profiles.index') }}" class="hover:text-slate-800 transition-colors">Profiles</a>
                <span>/</span>
                <span class="text-slate-600 font-medium truncate max-w-[150px] sm:max-w-xs">{{ $portfolio->name }}</span>
                <span>/</span>
                <span class="font-semibold text-slate-800">Navigation</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 flex items-center gap-2.5">
                <span>Navigation Management</span>
                <x-admin.status-badge :status="$portfolio->status" />
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Configure navbar links, internal section anchors, custom external URLs, sort order, and visibility for <strong>{{ $portfolio->name }}</strong>.
            </p>
        </div>

        <div class="flex items-center gap-2.5 flex-shrink-0">
            <a href="{{ route('admin.profiles.index') }}" 
               class="px-3.5 py-2 rounded-xl text-xs sm:text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 shadow-xs transition-colors inline-flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Back to Profiles</span>
            </a>
            <a href="{{ url('/' . $portfolio->slug) }}" 
               target="_blank" 
               rel="noopener noreferrer"
               class="gold-btn-gradient inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs sm:text-sm font-semibold text-[#0B0D12] transition-transform active:scale-95 shadow-xs">
                <svg class="w-4 h-4 text-[#0B0D12]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <span>Live Preview</span>
            </a>
        </div>
    </div>

    @if (isset($errors) && $errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
            <div class="font-bold mb-1">Please fix the following navigation errors:</div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left Column: Add New Navigation Link (4 cols on lg) -->
        <div class="lg:col-span-4">
            <div class="rounded-2xl bg-white border border-slate-200/80 p-5 shadow-sm sticky top-6">
                <div class="flex items-center gap-2 pb-3 mb-4 border-b border-slate-100">
                    <div class="w-7 h-7 rounded-lg bg-[#D4AF69]/20 text-[#9A7326] flex items-center justify-center font-bold text-xs">
                        +
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Add Navigation Link</h2>
                        <p class="text-[11px] text-slate-400">Append a new link to the navbar</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.portfolios.nav-items.store', $portfolio) }}" class="space-y-4">
                    @csrf

                    <!-- Label -->
                    <div>
                        <label for="new_item_label" class="block text-xs font-semibold text-slate-700 mb-1">
                            Link Label <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               id="new_item_label" 
                               name="label" 
                               value="{{ old('label') }}" 
                               required 
                               maxlength="80"
                               placeholder="e.g. About, Projects, Contact" 
                               class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-[#D4AF69]/40 focus:border-[#D4AF69] transition-all placeholder:text-slate-400"
                        >
                    </div>

                    <!-- Destination Type Switcher -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Destination Type <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 rounded-xl text-xs font-semibold">
                            <button type="button" 
                                    id="type_btn_section"
                                    onclick="switchNewItemType('section')" 
                                    class="py-1.5 px-3 rounded-lg text-center transition-colors bg-white text-slate-900 shadow-xs">
                                Section Anchor
                            </button>
                            <button type="button" 
                                    id="type_btn_url"
                                    onclick="switchNewItemType('url')" 
                                    class="py-1.5 px-3 rounded-lg text-center transition-colors text-slate-500 hover:text-slate-800">
                                External URL
                            </button>
                        </div>
                    </div>

                    <!-- Section Key Input Container -->
                    <div id="section_key_container">
                        <label for="new_item_section_key" class="block text-xs font-semibold text-slate-700 mb-1">
                            Section Key <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-slate-400 font-mono text-xs">#</span>
                            <input type="text" 
                                   id="new_item_section_key" 
                                   name="section_key" 
                                   value="{{ old('section_key', 'about') }}" 
                                   placeholder="e.g. about, projects, contact"
                                   pattern="^[a-z][a-z0-9_-]*$"
                                   class="w-full pl-7 pr-3 py-2 text-xs font-mono bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-[#D4AF69]/40 focus:border-[#D4AF69] transition-all placeholder:text-slate-400"
                            >
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1">Lowercase letters, numbers, dashes, underscores. e.g. <code>about</code>, <code>projects</code></p>
                    </div>

                    <!-- Custom URL Container (Hidden by default) -->
                    <div id="url_container" class="hidden">
                        <label for="new_item_url" class="block text-xs font-semibold text-slate-700 mb-1">
                            External URL <span class="text-rose-500">*</span>
                        </label>
                        <input type="url" 
                               id="new_item_url" 
                               name="url" 
                               value="{{ old('url') }}" 
                               placeholder="https://example.com/resume.pdf" 
                               class="w-full px-3 py-2 text-xs font-mono bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-[#D4AF69]/40 focus:border-[#D4AF69] transition-all placeholder:text-slate-400"
                        >
                        <p class="text-[10px] text-slate-400 mt-1">Full URL starting with <code>http://</code> or <code>https://</code></p>
                    </div>

                    <!-- Visibility Checkbox -->
                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" 
                               id="new_item_visible" 
                               name="is_visible" 
                               value="1" 
                               {{ old('is_visible', '1') ? 'checked' : '' }}
                               class="w-4 h-4 rounded text-[#9A7326] border-slate-300 focus:ring-[#D4AF69]/40"
                        >
                        <label for="new_item_visible" class="text-xs font-medium text-slate-700 select-none">
                            Make link visible on public portfolio
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                            class="w-full py-2.5 px-4 rounded-xl text-xs font-bold text-white bg-[#0F1219] hover:bg-[#1E2330] active:scale-98 transition-all shadow-xs flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4 text-[#D4AF69]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Add Link</span>
                    </button>
                </form>

                <!-- Section Reference Helper -->
                <div class="mt-6 pt-4 border-t border-slate-100">
                    <p class="text-[11px] font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Suggested Section Keys</p>
                    <div class="flex flex-wrap gap-1.5 text-[10px] font-mono">
                        <button type="button" onclick="setSectionKey('home', 'Home')" class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors">#home</button>
                        <button type="button" onclick="setSectionKey('about', 'About')" class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors">#about</button>
                        <button type="button" onclick="setSectionKey('projects', 'Projects')" class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors">#projects</button>
                        <button type="button" onclick="setSectionKey('skills', 'Skills')" class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors">#skills</button>
                        <button type="button" onclick="setSectionKey('experience', 'Experience')" class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors">#experience</button>
                        <button type="button" onclick="setSectionKey('contact', 'Contact')" class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors">#contact</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Navigation Links Table (8 cols on lg) -->
        <div class="lg:col-span-8">
            <div class="rounded-2xl bg-white border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Current Navbar Links</h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            {{ $navItems->count() }} of maximum 10 navigation items configured
                        </p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                        <span class="w-1.5 h-1.5 rounded-full {{ $navItems->count() > 0 ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                        {{ $navItems->count() }} Item(s)
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/70 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                <th scope="col" class="py-3.5 pl-5 pr-3 w-16">Order</th>
                                <th scope="col" class="py-3.5 px-3">Label</th>
                                <th scope="col" class="py-3.5 px-3">Destination</th>
                                <th scope="col" class="py-3.5 px-3">Visibility</th>
                                <th scope="col" class="py-3.5 pl-3 pr-5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @forelse($navItems as $item)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <!-- Sort Order -->
                                    <td class="py-3.5 pl-5 pr-3">
                                        <span class="w-7 h-7 rounded-lg bg-slate-100 text-slate-700 font-mono font-bold text-xs flex items-center justify-center border border-slate-200">
                                            {{ $item->sort_order }}
                                        </span>
                                    </td>

                                    <!-- Label -->
                                    <td class="py-3.5 px-3">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-slate-900">{{ $item->label }}</span>
                                            @if($item->section_key === 'home')
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-[#D4AF69]/20 text-[#9A7326] border border-[#D4AF69]/30">
                                                    Default
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Destination -->
                                    <td class="py-3.5 px-3">
                                        @if($item->section_key)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-indigo-50 text-indigo-700 font-mono text-[11px] border border-indigo-200/60">
                                                <span>#</span>{{ $item->section_key }}
                                            </span>
                                        @elseif($item->url)
                                            <a href="{{ $item->url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono text-[11px] border border-slate-200 transition-colors max-w-[200px] truncate" title="{{ $item->url }}">
                                                <span class="truncate">{{ $item->url }}</span>
                                                <svg class="w-3 h-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            </a>
                                        @else
                                            <span class="text-slate-400 italic">None</span>
                                        @endif
                                    </td>

                                    <!-- Visibility Toggle -->
                                    <td class="py-3.5 px-3">
                                        <form method="POST" action="{{ route('admin.portfolios.nav-items.visibility', [$portfolio, $item->id]) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold transition-colors {{ $item->is_visible ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 border border-slate-200 hover:bg-slate-200' }}"
                                                    title="Click to toggle visibility">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $item->is_visible ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                                <span>{{ $item->is_visible ? 'Visible' : 'Hidden' }}</span>
                                            </button>
                                        </form>
                                    </td>

                                    <!-- Actions -->
                                    <td class="py-3.5 pl-3 pr-5 text-right relative">
                                        <div class="inline-flex items-center gap-1">
                                            <!-- Edit Toggle Button -->
                                            <button type="button" 
                                                    onclick="toggleEditRow({{ $item->id }})" 
                                                    class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors"
                                                    title="Edit Item">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>

                                            <!-- Delete Button -->
                                            <form method="POST" action="{{ route('admin.portfolios.nav-items.destroy', [$portfolio, $item->id]) }}" onsubmit="return confirm('Delete navigation link \'{{ $item->label }}\'?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"
                                                        title="Delete Item">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Inline Edit Row (Hidden by default) -->
                                <tr id="edit_row_{{ $item->id }}" class="hidden bg-slate-50/90 border-t border-b border-[#D4AF69]/30">
                                    <td colspan="5" class="p-4">
                                        <form method="POST" action="{{ route('admin.portfolios.nav-items.update', [$portfolio, $item->id]) }}" class="space-y-3">
                                            @csrf
                                            @method('PUT')

                                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                                <div>
                                                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Label</label>
                                                    <input type="text" name="label" value="{{ $item->label }}" required maxlength="80" class="w-full px-2.5 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-[#D4AF69]/40">
                                                </div>

                                                <div>
                                                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Section Key (#)</label>
                                                    <input type="text" name="section_key" value="{{ $item->section_key }}" placeholder="e.g. about" class="w-full px-2.5 py-1.5 text-xs font-mono bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-[#D4AF69]/40">
                                                </div>

                                                <div>
                                                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">External URL</label>
                                                    <input type="url" name="url" value="{{ $item->url }}" placeholder="https://..." class="w-full px-2.5 py-1.5 text-xs font-mono bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-[#D4AF69]/40">
                                                </div>
                                            </div>

                                            <div class="flex items-center justify-between pt-1">
                                                <p class="text-[10px] text-slate-400">Specify exactly one destination: Section Key or External URL.</p>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" onclick="toggleEditRow({{ $item->id }})" class="px-3 py-1 rounded-lg text-xs font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-100 transition-colors">
                                                        Cancel
                                                    </button>
                                                    <button type="submit" class="px-3 py-1 rounded-lg text-xs font-bold text-white bg-[#0F1219] hover:bg-[#1E2330] transition-colors">
                                                        Save Changes
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-10 px-6 text-center">
                                        <div class="max-w-xs mx-auto flex flex-col items-center">
                                            <div class="w-10 h-10 rounded-xl bg-amber-50 border border-[#D4AF69]/20 flex items-center justify-center text-[#9A7326] mb-2">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                                                </svg>
                                            </div>
                                            <h4 class="text-xs font-bold text-slate-900 mb-0.5">No navigation items configured</h4>
                                            <p class="text-[11px] text-slate-500">
                                                Add your first navigation link using the form on the left.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</div>

<script>
    function switchNewItemType(type) {
        const btnSection = document.getElementById('type_btn_section');
        const btnUrl = document.getElementById('type_btn_url');
        const secContainer = document.getElementById('section_key_container');
        const urlContainer = document.getElementById('url_container');
        const secInput = document.getElementById('new_item_section_key');
        const urlInput = document.getElementById('new_item_url');

        if (type === 'section') {
            btnSection.className = 'py-1.5 px-3 rounded-lg text-center transition-colors bg-white text-slate-900 shadow-xs';
            btnUrl.className = 'py-1.5 px-3 rounded-lg text-center transition-colors text-slate-500 hover:text-slate-800';
            secContainer.classList.remove('hidden');
            urlContainer.classList.add('hidden');
            urlInput.value = '';
        } else {
            btnUrl.className = 'py-1.5 px-3 rounded-lg text-center transition-colors bg-white text-slate-900 shadow-xs';
            btnSection.className = 'py-1.5 px-3 rounded-lg text-center transition-colors text-slate-500 hover:text-slate-800';
            urlContainer.classList.remove('hidden');
            secContainer.classList.add('hidden');
            secInput.value = '';
        }
    }

    function setSectionKey(key, label) {
        switchNewItemType('section');
        const secInput = document.getElementById('new_item_section_key');
        const labelInput = document.getElementById('new_item_label');
        if (secInput) secInput.value = key;
        if (labelInput && !labelInput.value) labelInput.value = label;
    }

    function toggleEditRow(id) {
        const row = document.getElementById('edit_row_' + id);
        if (row) {
            row.classList.toggle('hidden');
        }
    }
</script>
@endsection
