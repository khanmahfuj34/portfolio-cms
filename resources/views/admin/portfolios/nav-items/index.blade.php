@extends('layouts.admin')

@section('title', 'Manage Navigation — ' . $portfolio->name . ' — Portfolio CMS')
@section('page_title', 'Navigation')

@section('content')
@php
    $isDashboard = request()->routeIs('dashboard.*');
    $routePrefix = $isDashboard ? 'dashboard' : 'admin';
    $homeRoute = $isDashboard ? route('dashboard.index') : route('admin.dashboard');
    $listRoute = $isDashboard ? route('dashboard.portfolios.index') : route('admin.profiles.index');
@endphp
<div class="space-y-6">

    <!-- Header -->
    <section class="pb-5 border-b border-line">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div class="space-y-1.5">
                <div class="flex items-center gap-2 text-[11px] text-ink-mute flex-wrap">
                    <a href="{{ $homeRoute }}" class="hover:text-ink transition-colors">{{ $isDashboard ? 'Dashboard' : 'Admin' }}</a>
                    <span class="text-line-strong">/</span>
                    <a href="{{ $listRoute }}" class="hover:text-ink transition-colors">{{ $isDashboard ? 'My Portfolios' : 'Profiles' }}</a>
                    <span class="text-line-strong">/</span>
                    <span class="text-ink-soft truncate max-w-[150px] sm:max-w-xs">{{ $portfolio->name }}</span>
                    <span class="text-line-strong">/</span>
                    <span class="text-ink-soft">Navigation</span>
                </div>
                <div class="flex items-center gap-2.5">
                    <h2 class="font-display text-3xl leading-none text-ink">Navigation Management</h2>
                    <x-admin.status-badge :status="$portfolio->status" />
                </div>
                <p class="max-w-2xl text-sm text-ink-soft">
                    Navbar links, section anchors, external URLs, order and visibility for <span class="font-medium text-ink">{{ $portfolio->name }}</span>.
                </p>
            </div>

            <div class="flex items-center gap-2 flex-shrink-0">
                <a href="{{ $listRoute }}"
                   class="px-3.5 py-2 rounded-lg text-xs font-medium text-ink-soft bg-surface border border-line hover:bg-paper-sunk transition-colors inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-ink-mute" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>Back</span>
                </a>
                <a href="{{ $isDashboard && $portfolio->status === 'draft' ? route('dashboard.portfolios.preview', $portfolio) : ($portfolio->primaryDomain ? app(\App\Services\PortfolioDomainService::class)->publicUrlFor($portfolio->primaryDomain, request()) : url('/' . $portfolio->slug)) }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-medium bg-brass text-[#14130F] hover:bg-[#B89149] transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <span>Preview</span>
                </a>
            </div>
        </div>
    </section>

    @if (isset($errors) && $errors->any())
        <div class="p-4 rounded-lg bg-[#FBF1EF] border border-[#E9D2CC] border-l-2 border-l-[#B4553F] text-xs">
            <div class="font-medium text-[#9C4A3E] mb-1">Please fix the following navigation errors.</div>
            <ul class="list-disc list-inside space-y-0.5 text-ink-soft">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Add link -->
        <div class="lg:col-span-4">
            <div class="rounded-xl bg-surface border border-line p-5 lg:sticky lg:top-6">
                <div class="flex items-center gap-2.5 pb-3 mb-4 border-b border-line">
                    <div class="w-7 h-7 rounded-md bg-brass-wash text-brass-ink flex items-center justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-ink">Add link</h3>
                        <p class="text-[11px] text-ink-mute">Append to the navbar</p>
                    </div>
                </div>

                <form method="POST" action="{{ route($routePrefix . '.portfolios.nav-items.store', $portfolio) }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="new_item_label" class="block text-[11px] font-medium text-ink-soft mb-1">
                            Link label <span class="text-brass-ink">*</span>
                        </label>
                        <input type="text"
                               id="new_item_label"
                               name="label"
                               value="{{ old('label') }}"
                               required
                               maxlength="80"
                               placeholder="About, Projects, Contact"
                               class="w-full px-3 py-2 text-xs bg-surface text-ink placeholder-ink-mute border border-line rounded-lg focus:outline-none focus:border-brass focus:ring-2 focus:ring-brass/20 transition-colors"
                        >
                    </div>

                    <div>
                        <label class="block text-[11px] font-medium text-ink-soft mb-1.5">
                            Destination type <span class="text-brass-ink">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-1 p-1 bg-paper-sunk rounded-lg text-xs font-medium">
                            <button type="button"
                                    id="type_btn_section"
                                    onclick="switchNewItemType('section')"
                                    class="py-1.5 px-3 rounded-md text-center transition-colors bg-surface text-ink shadow-xs">
                                Section anchor
                            </button>
                            <button type="button"
                                    id="type_btn_url"
                                    onclick="switchNewItemType('url')"
                                    class="py-1.5 px-3 rounded-md text-center transition-colors text-ink-soft hover:text-ink">
                                External URL
                            </button>
                        </div>
                    </div>

                    <div id="section_key_container">
                        <label for="new_item_section_key" class="block text-[11px] font-medium text-ink-soft mb-1">
                            Section key <span class="text-brass-ink">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-brass-ink font-mono text-xs">#</span>
                            <input type="text"
                                   id="new_item_section_key"
                                   name="section_key"
                                   value="{{ old('section_key', 'about') }}"
                                   placeholder="about, projects, contact"
                                   pattern="^[a-z][a-z0-9_-]*$"
                                   class="w-full pl-7 pr-3 py-2 text-xs font-mono bg-surface text-ink placeholder-ink-mute border border-line rounded-lg focus:outline-none focus:border-brass focus:ring-2 focus:ring-brass/20 transition-colors"
                            >
                        </div>
                        <p class="text-[10px] text-ink-mute mt-1">Lowercase letters, numbers, dashes, underscores.</p>
                    </div>

                    <div id="url_container" class="hidden">
                        <label for="new_item_url" class="block text-[11px] font-medium text-ink-soft mb-1">
                            External URL <span class="text-brass-ink">*</span>
                        </label>
                        <input type="url"
                               id="new_item_url"
                               name="url"
                               value="{{ old('url') }}"
                               placeholder="https://example.com/resume.pdf"
                               class="w-full px-3 py-2 text-xs font-mono bg-surface text-ink placeholder-ink-mute border border-line rounded-lg focus:outline-none focus:border-brass focus:ring-2 focus:ring-brass/20 transition-colors"
                        >
                        <p class="text-[10px] text-ink-mute mt-1">Full URL starting with <code>http://</code> or <code>https://</code>.</p>
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox"
                               id="new_item_visible"
                               name="is_visible"
                               value="1"
                               {{ old('is_visible', '1') ? 'checked' : '' }}
                               class="w-3.5 h-3.5 rounded border-line text-brass-ink focus:ring-brass/30"
                        >
                        <label for="new_item_visible" class="text-xs text-ink-soft select-none">
                            Visible on the public portfolio
                        </label>
                    </div>

                    <button type="submit"
                            class="w-full py-2.5 px-4 rounded-lg text-xs font-medium text-paper bg-[#14130F] hover:bg-[#2A261C] transition-colors flex items-center justify-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-brass" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Add link</span>
                    </button>
                </form>

                <div class="mt-6 pt-4 border-t border-line">
                    <p class="text-[11px] font-medium text-ink-soft mb-2">Suggested section keys</p>
                    <div class="flex flex-wrap gap-1.5 text-[10px] font-mono">
                        <button type="button" onclick="setSectionKey('home', 'Home')" class="px-2 py-0.5 rounded bg-paper hover:bg-paper-sunk text-ink-soft border border-line transition-colors">#home</button>
                        <button type="button" onclick="setSectionKey('about', 'About')" class="px-2 py-0.5 rounded bg-paper hover:bg-paper-sunk text-ink-soft border border-line transition-colors">#about</button>
                        <button type="button" onclick="setSectionKey('projects', 'Projects')" class="px-2 py-0.5 rounded bg-paper hover:bg-paper-sunk text-ink-soft border border-line transition-colors">#projects</button>
                        <button type="button" onclick="setSectionKey('skills', 'Skills')" class="px-2 py-0.5 rounded bg-paper hover:bg-paper-sunk text-ink-soft border border-line transition-colors">#skills</button>
                        <button type="button" onclick="setSectionKey('experience', 'Experience')" class="px-2 py-0.5 rounded bg-paper hover:bg-paper-sunk text-ink-soft border border-line transition-colors">#experience</button>
                        <button type="button" onclick="setSectionKey('contact', 'Contact')" class="px-2 py-0.5 rounded bg-paper hover:bg-paper-sunk text-ink-soft border border-line transition-colors">#contact</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current links -->
        <div class="lg:col-span-8">
            <div class="rounded-xl bg-surface border border-line overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-line flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-medium text-ink">Current links</h3>
                        <p class="text-xs text-ink-mute mt-0.5">
                            <span class="tabular">{{ $navItems->count() }}</span> of 10 configured
                        </p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 text-[11px] text-ink-soft">
                        <span class="w-1.5 h-1.5 rounded-full {{ $navItems->count() > 0 ? 'bg-brass' : 'bg-line-strong' }}"></span>
                        <span class="tabular">{{ $navItems->count() }}</span>
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-line text-[11px] font-medium text-ink-mute">
                                <th scope="col" class="py-3 pl-5 pr-3 w-16">Order</th>
                                <th scope="col" class="py-3 px-3">Label</th>
                                <th scope="col" class="py-3 px-3">Destination</th>
                                <th scope="col" class="py-3 px-3">Visibility</th>
                                <th scope="col" class="py-3 pl-3 pr-5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line">
                            @forelse($navItems as $item)
                                <tr class="hover:bg-paper transition-colors">
                                    <td class="py-3 pl-5 pr-3">
                                        <span class="w-6 h-6 rounded bg-paper text-ink-soft font-mono text-[11px] flex items-center justify-center border border-line tabular">
                                            {{ $item->sort_order }}
                                        </span>
                                    </td>

                                    <td class="py-3 px-3">
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-ink">{{ $item->label }}</span>
                                            @if($item->section_key === 'home')
                                                <span class="px-1.5 py-0.5 rounded bg-brass-wash text-brass-ink text-[10px] font-medium">
                                                    Default
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="py-3 px-3">
                                        @if($item->section_key)
                                            <span class="inline-flex items-center gap-0.5 font-mono text-[11px] text-ink-soft">
                                                <span class="text-brass-ink">#</span>{{ $item->section_key }}
                                            </span>
                                        @elseif($item->url)
                                            <a href="{{ $item->url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 font-mono text-[11px] text-ink-mute hover:text-brass-ink transition-colors max-w-[200px] truncate" title="{{ $item->url }}">
                                                <span class="truncate">{{ $item->url }}</span>
                                                <svg class="w-3 h-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            </a>
                                        @else
                                            <span class="text-ink-mute italic">None</span>
                                        @endif
                                    </td>

                                    <td class="py-3 px-3">
                                        <form method="POST" action="{{ route($routePrefix . '.portfolios.nav-items.visibility', [$portfolio, $item->id]) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md text-[11px] font-medium border transition-colors {{ $item->is_visible ? 'bg-[#EEF3EC] text-[#3F5A38] border-[#DAE4D5] hover:bg-[#E4EDDF]' : 'bg-paper text-ink-mute border-line hover:bg-paper-sunk' }}"
                                                    title="Toggle visibility">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $item->is_visible ? 'bg-[#5B7A4B]' : 'bg-ink-mute' }}"></span>
                                                <span>{{ $item->is_visible ? 'Visible' : 'Hidden' }}</span>
                                            </button>
                                        </form>
                                    </td>

                                    <td class="py-3 pl-3 pr-5 text-right relative">
                                        <div class="inline-flex items-center gap-1">
                                            <button type="button"
                                                    onclick="toggleEditRow({{ $item->id }})"
                                                    class="p-1.5 text-ink-mute hover:text-ink hover:bg-paper-sunk rounded-md transition-colors"
                                                    title="Edit item">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>

                                            <form method="POST"
                                                  action="{{ route($routePrefix . '.portfolios.nav-items.destroy', [$portfolio, $item->id]) }}"
                                                  data-confirm="Delete navigation link '{{ $item->label }}'?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="p-1.5 text-ink-mute hover:text-[#9C4A3E] hover:bg-[#F7ECE9] rounded-md transition-colors"
                                                        title="Delete item">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <tr id="edit_row_{{ $item->id }}" class="hidden bg-paper-sunk border-t border-b border-brass/30">
                                    <td colspan="5" class="p-4">
                                        <form method="POST" action="{{ route($routePrefix . '.portfolios.nav-items.update', [$portfolio, $item->id]) }}" class="space-y-3">
                                            @csrf
                                            @method('PUT')

                                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                                <div>
                                                    <label class="block text-[11px] font-medium text-ink-soft mb-1">Label</label>
                                                    <input type="text" name="label" value="{{ $item->label }}" required maxlength="80" class="w-full px-2.5 py-1.5 text-xs bg-surface text-ink border border-line rounded-md focus:outline-none focus:border-brass focus:ring-2 focus:ring-brass/20">
                                                </div>

                                                <div>
                                                    <label class="block text-[11px] font-medium text-ink-soft mb-1">Section key (#)</label>
                                                    <input type="text" name="section_key" value="{{ $item->section_key }}" placeholder="about" class="w-full px-2.5 py-1.5 text-xs font-mono bg-surface text-ink border border-line rounded-md focus:outline-none focus:border-brass focus:ring-2 focus:ring-brass/20">
                                                </div>

                                                <div>
                                                    <label class="block text-[11px] font-medium text-ink-soft mb-1">External URL</label>
                                                    <input type="url" name="url" value="{{ $item->url }}" placeholder="https://" class="w-full px-2.5 py-1.5 text-xs font-mono bg-surface text-ink border border-line rounded-md focus:outline-none focus:border-brass focus:ring-2 focus:ring-brass/20">
                                                </div>
                                            </div>

                                            <div class="flex items-center justify-between pt-1 gap-3">
                                                <p class="text-[10px] text-ink-mute">Specify one destination: section key or external URL.</p>
                                                <div class="flex items-center gap-2 flex-shrink-0">
                                                    <button type="button" onclick="toggleEditRow({{ $item->id }})" class="px-3 py-1 rounded-md text-xs font-medium text-ink-soft bg-surface border border-line hover:bg-paper transition-colors">
                                                        Cancel
                                                    </button>
                                                    <button type="submit" class="px-3 py-1 rounded-md text-xs font-medium text-paper bg-[#14130F] hover:bg-[#2A261C] transition-colors">
                                                        Save changes
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
                                            <div class="w-10 h-10 rounded-md bg-paper border border-line flex items-center justify-center text-ink-mute mb-2">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/>
                                                </svg>
                                            </div>
                                            <h4 class="text-xs font-medium text-ink mb-0.5">No navigation items</h4>
                                            <p class="text-[11px] text-ink-mute">
                                                Add the first link using the form on the left.
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

        const activeClass = 'py-1.5 px-3 rounded-md text-center transition-colors bg-surface text-ink shadow-xs';
        const inactiveClass = 'py-1.5 px-3 rounded-md text-center transition-colors text-ink-soft hover:text-ink';

        if (type === 'section') {
            btnSection.className = activeClass;
            btnUrl.className = inactiveClass;
            secContainer.classList.remove('hidden');
            urlContainer.classList.add('hidden');
            urlInput.value = '';
        } else {
            btnUrl.className = activeClass;
            btnSection.className = inactiveClass;
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
