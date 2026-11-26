@extends('layouts.admin')

@section('title', 'Theme Management — Super Admin')
@section('page_title', 'Theme Management')

@section('content')
@php
    $activeStatus = (string) ($filters['status'] ?? '');
    $activeCategory = (string) ($filters['category'] ?? '');
    $activeFeatured = (string) ($filters['featured'] ?? ($filters['is_featured'] ?? ''));
    $activeSearch = (string) ($filters['search'] ?? '');
    $hasActiveFilters = $activeStatus !== '' || $activeCategory !== '' || $activeFeatured !== '' || $activeSearch !== '';

    $statusTabs = [
        '' => ['label' => 'All', 'count' => $stats['total']],
        \App\Enums\ThemeStatus::Draft->value => ['label' => 'Draft', 'count' => $stats['draft']],
        \App\Enums\ThemeStatus::Published->value => ['label' => 'Published', 'count' => $stats['published']],
        \App\Enums\ThemeStatus::Archived->value => ['label' => 'Archived', 'count' => $stats['archived']],
    ];
@endphp

<div class="max-w-7xl mx-auto space-y-6">

    <!-- Page Header (Read & Lifecycle Governance Only — No Create / Edit / Upload) -->
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
        <div>
            <p class="text-[11px] text-ink-mute mb-1">Catalog / Themes</p>
            <h1 class="font-display text-3xl font-normal tracking-tight text-ink">
                Theme Management
            </h1>
            <p class="text-sm text-ink-soft mt-1">
                Manage available portfolio themes.
            </p>
        </div>

        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-surface border border-line text-xs text-ink-soft self-start sm:self-auto">
            <span class="w-1.5 h-1.5 rounded-full bg-brass"></span>
            <span>Preview &amp; Lifecycle Control</span>
        </div>
    </div>

    <!-- Flash Feedback Alerts -->
    @if (session('success'))
        <div role="status" class="rounded-lg bg-[#EEF3EC] border border-[#D4E2CF] px-4 py-3 text-sm text-[#3F5A38] flex items-center justify-between gap-3">
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div role="alert" class="rounded-lg bg-[#F7ECE9] border border-[#E5C8C2] px-4 py-3 text-sm text-[#9C4A3E] flex items-center justify-between gap-3">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Async Action Feedback Container -->
    <div id="themes-feedback-banner" class="hidden rounded-lg px-4 py-3 text-sm flex items-center justify-between gap-3" role="status" aria-live="polite">
        <span id="themes-feedback-message"></span>
        <button type="button" id="themes-feedback-dismiss" class="text-xs font-medium underline opacity-80 hover:opacity-100 cursor-pointer">
            Dismiss
        </button>
    </div>

    <!-- Theme Statistics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <x-admin.stat-card title="Total Themes" :value="$stats['total']" subtitle="Registered templates">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4 5 5 0 015-5h1a1 1 0 011 1v1a1 1 0 001 1h1a1 1 0 001-1v-1a1 1 0 011-1h1a5 5 0 015 5 4 4 0 01-4 4H7zM16 3.13a4 4 0 010 7.75"/>
            </svg>
        </x-admin.stat-card>

        <x-admin.stat-card title="Published" :value="$stats['published']" subtitle="Visible to Individual Admins">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </x-admin.stat-card>

        <x-admin.stat-card title="Draft" :value="$stats['draft']" subtitle="Awaiting release review">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </x-admin.stat-card>

        <x-admin.stat-card title="Archived" :value="$stats['archived']" subtitle="Retired from catalog">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
            </svg>
        </x-admin.stat-card>
    </div>

    <!-- Catalog Filter Controls -->
    <div class="rounded-xl bg-surface border border-line overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-line space-y-4">
            <!-- Status Filter Tabs -->
            <div class="flex flex-wrap items-center gap-2" role="navigation" aria-label="Filter themes by status">
                @foreach ($statusTabs as $tabStatus => $tabMeta)
                    @php
                        $isTabActive = $activeStatus === $tabStatus;
                        $tabQuery = array_filter([
                            'search' => $activeSearch !== '' ? $activeSearch : null,
                            'category' => $activeCategory !== '' ? $activeCategory : null,
                            'featured' => $activeFeatured !== '' ? $activeFeatured : null,
                            'status' => $tabStatus !== '' ? $tabStatus : null,
                        ], fn ($value) => $value !== null);
                    @endphp
                    <a href="{{ route('admin.themes.index', $tabQuery) }}"
                       class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-medium transition-colors {{ $isTabActive ? 'bg-ink text-paper' : 'bg-paper text-ink-soft border border-line hover:bg-paper-sunk hover:text-ink' }}">
                        <span>{{ $tabMeta['label'] }}</span>
                        <span class="tabular px-1.5 py-0.5 rounded text-[11px] {{ $isTabActive ? 'bg-paper/15 text-paper' : 'bg-paper-sunk text-ink-mute' }}">
                            {{ $tabMeta['count'] }}
                        </span>
                    </a>
                @endforeach
            </div>

            <!-- Search + Dropdown Filters -->
            <form method="GET" action="{{ route('admin.themes.index') }}" class="flex flex-col lg:flex-row lg:items-center gap-3">
                @if ($activeStatus !== '')
                    <input type="hidden" name="status" value="{{ $activeStatus }}">
                @endif

                <div class="relative flex-1 min-w-0">
                    <label for="theme-search" class="sr-only">Search themes</label>
                    <input type="text"
                           id="theme-search"
                           name="search"
                           value="{{ $activeSearch }}"
                           placeholder="Search by theme name or slug..."
                           class="w-full h-9 pl-9 pr-3 text-sm bg-paper border border-line rounded-lg text-ink placeholder:text-ink-mute focus:outline-none focus:border-brass focus:ring-1 focus:ring-brass/25 transition-colors">
                    <svg class="w-4 h-4 text-ink-mute absolute left-3 top-2.5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                    </svg>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:flex items-center gap-2.5">
                    <div>
                        <label for="theme-category" class="sr-only">Category</label>
                        <select id="theme-category"
                                name="category"
                                class="w-full lg:w-44 h-9 px-3 text-sm bg-paper border border-line rounded-lg text-ink focus:outline-none focus:border-brass focus:ring-1 focus:ring-brass/25 transition-colors">
                            <option value="">All Categories</option>
                            @foreach ($categories as $categoryOption)
                                <option value="{{ $categoryOption }}" @selected($activeCategory === $categoryOption)>
                                    {{ $categoryOption }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="theme-featured" class="sr-only">Featured</label>
                        <select id="theme-featured"
                                name="featured"
                                class="w-full lg:w-40 h-9 px-3 text-sm bg-paper border border-line rounded-lg text-ink focus:outline-none focus:border-brass focus:ring-1 focus:ring-brass/25 transition-colors">
                            <option value="">All Visibility</option>
                            <option value="1" @selected($activeFeatured === '1' || $activeFeatured === 'true')>Featured Only</option>
                            <option value="0" @selected($activeFeatured === '0' || $activeFeatured === 'false')>Standard Only</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="submit"
                                class="h-9 px-4 text-xs font-medium bg-ink text-paper rounded-lg hover:bg-ink/90 transition-colors cursor-pointer whitespace-nowrap">
                            Apply Filters
                        </button>

                        @if ($hasActiveFilters)
                            <a href="{{ route('admin.themes.index') }}"
                               class="h-9 px-3 inline-flex items-center text-xs font-medium text-ink-soft bg-paper border border-line rounded-lg hover:bg-paper-sunk transition-colors whitespace-nowrap">
                                Clear
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <!-- Theme Cards Grid -->
        <div class="p-4 sm:p-5">
            @if ($themes->isEmpty())
                <div class="py-12 px-4 text-center max-w-md mx-auto space-y-3">
                    <div class="mx-auto w-11 h-11 rounded-full bg-paper-sunk border border-line text-ink-mute flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4 5 5 0 015-5h1a1 1 0 011 1v1a1 1 0 001 1h1a1 1 0 001-1v-1a1 1 0 011-1h1a5 5 0 015 5 4 4 0 01-4 4H7z"/>
                        </svg>
                    </div>
                    <h2 class="text-sm font-semibold text-ink">No themes match your current view</h2>
                    <p class="text-xs text-ink-soft">
                        @if ($hasActiveFilters)
                            No themes matched the selected search or status filters. Clear your filters to view the full catalog.
                        @else
                            No themes are registered in the catalog yet.
                        @endif
                    </p>
                    @if ($hasActiveFilters)
                        <div>
                            <a href="{{ route('admin.themes.index') }}"
                               class="inline-flex items-center px-3.5 py-2 text-xs font-medium bg-paper border border-line rounded-lg text-ink hover:bg-paper-sunk transition-colors">
                                Reset All Filters
                            </a>
                        </div>
                    @endif
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                    @foreach ($themes as $theme)
                        @php
                            $statusEnum = $theme->status ?? \App\Enums\ThemeStatus::Draft;
                            $statusValue = $statusEnum->value;

                            $badgeStyles = match ($statusEnum) {
                                \App\Enums\ThemeStatus::Published => 'bg-[#EEF3EC] text-[#3F5A38] border-[#D4E2CF]',
                                \App\Enums\ThemeStatus::Archived => 'bg-paper-sunk text-ink-mute border-line',
                                default => 'bg-brass-wash text-brass-ink border-brass/30',
                            };

                            $dotColor = match ($statusEnum) {
                                \App\Enums\ThemeStatus::Published => 'bg-[#4D6B45]',
                                \App\Enums\ThemeStatus::Archived => 'bg-ink-mute',
                                default => 'bg-brass',
                            };

                            $rawThumb = is_string($theme->thumbnail) ? trim($theme->thumbnail) : '';
                            $thumbUrl = null;
                            if ($rawThumb !== '' && ! str_contains($rawThumb, '..')) {
                                if (preg_match('/^(?:https?:\/\/|\/(?!\/))/i', $rawThumb)) {
                                    $thumbUrl = $rawThumb;
                                } else {
                                    $thumbUrl = asset('storage/' . ltrim($rawThumb, '/'));
                                }
                            }

                            $isRifahModern = $theme->theme_key === \App\Services\PortfolioThemeResolver::RIFAH_MODERN_KEY;
                        @endphp

                        <article data-theme-card
                                 data-theme-id="{{ $theme->id }}"
                                 data-theme-status="{{ $statusValue }}"
                                 class="flex flex-col rounded-xl bg-paper border border-line hover:border-line-strong transition-colors overflow-hidden">

                            <!-- Preview Visual Header -->
                            <div class="relative aspect-[16/10] border-b border-line overflow-hidden bg-[#14130F]">
                                @if ($thumbUrl)
                                    <img src="{{ $thumbUrl }}"
                                         alt="{{ $theme->name }} preview thumbnail"
                                         class="w-full h-full object-cover"
                                         onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');">
                                @endif

                                <!-- Stylized Structural Fallback Preview -->
                                <div class="{{ $thumbUrl ? 'hidden' : 'flex' }} w-full h-full flex-col justify-between p-4 {{ $isRifahModern ? 'bg-gradient-to-br from-[#191712] via-[#261B16] to-[#14130F]' : 'bg-gradient-to-br from-[#1A1C23] via-[#131720] to-[#0D0F14]' }}">
                                    <div class="flex items-center justify-between gap-2 pt-7">
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full {{ $isRifahModern ? 'bg-[#C2542D]' : 'bg-brass' }}"></span>
                                            <span class="w-12 h-1.5 rounded-full bg-white/20"></span>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-6 h-1 rounded-full bg-white/15"></span>
                                            <span class="w-6 h-1 rounded-full bg-white/15"></span>
                                            <span class="w-6 h-1 rounded-full bg-white/15"></span>
                                        </div>
                                    </div>

                                    <div class="space-y-2 my-auto">
                                        <span class="inline-block w-16 h-1.5 rounded-full {{ $isRifahModern ? 'bg-[#C2542D]/80' : 'bg-brass/80' }}"></span>
                                        <p class="font-display text-base font-medium text-[#EDE9DF] truncate">
                                            {{ $theme->name }}
                                        </p>
                                        <div class="grid grid-cols-3 gap-1.5 pt-1">
                                            <div class="h-6 rounded bg-white/6 border border-white/10"></div>
                                            <div class="h-6 rounded bg-white/6 border border-white/10"></div>
                                            <div class="h-6 rounded bg-white/6 border border-white/10"></div>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between text-[10px] font-mono text-[#A6A095]">
                                        <span class="truncate">{{ $theme->theme_key }}</span>
                                        <span>v{{ $theme->version }}</span>
                                    </div>
                                </div>

                                <!-- Top Status + Featured Badges -->
                                <div class="absolute top-3 left-3 right-3 flex items-center justify-between gap-2">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-medium border {{ $badgeStyles }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $dotColor }}"></span>
                                        <span>{{ $statusEnum->label() }}</span>
                                    </span>

                                    @if ($theme->is_featured)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-brass text-[#14130F] shadow-xs">
                                            Featured
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Card Content -->
                            <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                                <div class="space-y-2">
                                    <div class="flex items-start justify-between gap-2">
                                        <h2 class="font-display text-lg font-medium text-ink leading-snug">
                                            {{ $theme->name }}
                                        </h2>
                                        <span class="shrink-0 px-2 py-0.5 rounded bg-surface border border-line text-[11px] font-mono text-ink-soft">
                                            v{{ $theme->version }}
                                        </span>
                                    </div>

                                    <p class="text-xs text-ink-soft leading-relaxed line-clamp-2">
                                        {{ $theme->description ?: 'Prebuilt portfolio layout template ready for theme governance.' }}
                                    </p>
                                </div>

                                <!-- Metadata Grid -->
                                <dl class="grid grid-cols-2 gap-x-3 gap-y-2.5 pt-3 border-t border-line text-xs">
                                    <div class="min-w-0">
                                        <dt class="text-[11px] text-ink-mute">Theme Key</dt>
                                        <dd class="mt-0.5 font-mono text-ink truncate" title="{{ $theme->theme_key }}">
                                            {{ $theme->theme_key }}
                                        </dd>
                                    </div>

                                    <div class="min-w-0">
                                        <dt class="text-[11px] text-ink-mute">Category</dt>
                                        <dd class="mt-0.5 text-ink font-medium truncate">
                                            {{ $theme->category ?: 'General' }}
                                        </dd>
                                    </div>

                                    <div class="min-w-0">
                                        <dt class="text-[11px] text-ink-mute">Assigned Portfolios</dt>
                                        <dd class="mt-0.5 text-ink tabular">
                                            {{ (int) ($theme->portfolios_count ?? 0) }}
                                        </dd>
                                    </div>

                                    <div class="min-w-0">
                                        <dt class="text-[11px] text-ink-mute">Published At</dt>
                                        <dd class="mt-0.5 text-ink-soft truncate">
                                            {{ $theme->published_at ? $theme->published_at->format('M d, Y') : '—' }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            <!-- Card Footer Actions (Preview + Status-Conditional Lifecycle Button Only) -->
                            <div class="px-5 py-3.5 bg-surface border-t border-line flex flex-wrap items-center justify-between gap-2.5">
                                <a href="{{ route('admin.themes.preview', $theme) }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-ink bg-paper border border-line hover:bg-paper-sunk hover:border-line-strong transition-colors">
                                    <svg class="w-3.5 h-3.5 text-ink-soft" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span>Preview</span>
                                </a>

                                @if ($statusEnum === \App\Enums\ThemeStatus::Draft)
                                    <button type="button"
                                            data-theme-lifecycle-btn
                                            data-lifecycle-action="publish"
                                            data-action-url="{{ route('admin.themes.publish', $theme) }}"
                                            data-theme-name="{{ $theme->name }}"
                                            data-theme-key="{{ $theme->theme_key }}"
                                            class="inline-flex items-center justify-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-medium bg-[#3F5A38] text-white hover:bg-[#344B2E] transition-colors cursor-pointer">
                                        <span>Publish</span>
                                    </button>
                                @elseif ($statusEnum === \App\Enums\ThemeStatus::Published)
                                    <button type="button"
                                            data-theme-lifecycle-btn
                                            data-lifecycle-action="unpublish"
                                            data-action-url="{{ route('admin.themes.unpublish', $theme) }}"
                                            data-theme-name="{{ $theme->name }}"
                                            data-theme-key="{{ $theme->theme_key }}"
                                            class="inline-flex items-center justify-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-medium bg-paper border border-line text-ink hover:bg-[#F7ECE9] hover:text-[#9C4A3E] hover:border-[#E5C8C2] transition-colors cursor-pointer">
                                        <span>Unpublish</span>
                                    </button>
                                @elseif ($statusEnum === \App\Enums\ThemeStatus::Archived)
                                    <button type="button"
                                            data-theme-lifecycle-btn
                                            data-lifecycle-action="restore"
                                            data-action-url="{{ route('admin.themes.restore', $theme) }}"
                                            data-theme-name="{{ $theme->name }}"
                                            data-theme-key="{{ $theme->theme_key }}"
                                            class="inline-flex items-center justify-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-medium bg-paper border border-line text-ink-soft hover:bg-paper-sunk hover:text-ink transition-colors cursor-pointer">
                                        <span>Restore to Draft</span>
                                    </button>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Pagination Footer -->
        @if ($themes->hasPages() || $themes->total() > 0)
            <div class="px-4 sm:px-5 py-3.5 border-t border-line flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-ink-mute">
                <div class="tabular">
                    Showing <span class="font-medium text-ink-soft">{{ $themes->firstItem() ?? 0 }}</span>–<span class="font-medium text-ink-soft">{{ $themes->lastItem() ?? 0 }}</span> of <span class="font-medium text-ink-soft">{{ $themes->total() }}</span> themes
                </div>
                <div>
                    {{ $themes->links() }}
                </div>
            </div>
        @endif
    </div>

</div>

<!-- Lifecycle Confirmation Modal -->
<x-admin.modal id="theme-lifecycle-modal" title="Confirm Theme Action" size="max-w-md">
    <form id="theme-lifecycle-form" method="POST" action="">
        @csrf
        @method('PATCH')

        <div class="space-y-3">
            <p id="theme-lifecycle-description" class="text-sm text-ink-soft leading-relaxed"></p>

            <div class="rounded-lg bg-paper-sunk border border-line px-3.5 py-2.5 text-xs space-y-1">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-ink-mute">Theme</span>
                    <span id="theme-lifecycle-target-name" class="font-medium text-ink"></span>
                </div>
                <div class="flex items-center justify-between gap-2">
                    <span class="text-ink-mute">Theme Key</span>
                    <code id="theme-lifecycle-target-key" class="font-mono text-ink-soft"></code>
                </div>
            </div>
        </div>
    </form>

    <x-slot name="footer">
        <button type="button"
                id="theme-lifecycle-cancel"
                data-modal-close="theme-lifecycle-modal"
                class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-ink-soft bg-paper border border-line hover:bg-paper-sunk transition-colors cursor-pointer">
            Cancel
        </button>
        <button type="submit"
                form="theme-lifecycle-form"
                id="theme-lifecycle-confirm"
                class="px-4 py-1.5 rounded-lg text-xs font-medium transition-colors cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed">
            Confirm
        </button>
    </x-slot>
</x-admin.modal>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('theme-lifecycle-modal');
    const modalTitle = document.getElementById('theme-lifecycle-modal-title');
    const form = document.getElementById('theme-lifecycle-form');
    const descriptionEl = document.getElementById('theme-lifecycle-description');
    const targetNameEl = document.getElementById('theme-lifecycle-target-name');
    const targetKeyEl = document.getElementById('theme-lifecycle-target-key');
    const confirmBtn = document.getElementById('theme-lifecycle-confirm');
    const cancelBtn = document.getElementById('theme-lifecycle-cancel');
    const feedbackBanner = document.getElementById('themes-feedback-banner');
    const feedbackMsg = document.getElementById('themes-feedback-message');
    const feedbackDismiss = document.getElementById('themes-feedback-dismiss');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const ACTION_COPY = {
        publish: {
            title: 'Publish Theme',
            description: 'Publishing makes this theme selectable for Individual Admins across their portfolios. Existing portfolios remain unchanged.',
            confirmLabel: 'Confirm Publish',
            loadingLabel: 'Publishing...',
            confirmClass: 'px-4 py-1.5 rounded-lg text-xs font-medium bg-[#3F5A38] text-white hover:bg-[#344B2E] transition-colors cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed',
        },
        unpublish: {
            title: 'Unpublish Theme',
            description: 'Unpublishing hides this theme from new selection in the Individual Admin catalog while preserving existing portfolio assignments.',
            confirmLabel: 'Confirm Unpublish',
            loadingLabel: 'Unpublishing...',
            confirmClass: 'px-4 py-1.5 rounded-lg text-xs font-medium bg-[#9C4A3E] text-white hover:bg-[#823C32] transition-colors cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed',
        },
        restore: {
            title: 'Restore Theme to Draft',
            description: 'Restoring moves this archived theme back into Draft status for internal preview before publishing.',
            confirmLabel: 'Confirm Restore',
            loadingLabel: 'Restoring...',
            confirmClass: 'px-4 py-1.5 rounded-lg text-xs font-medium bg-ink text-paper hover:bg-ink/90 transition-colors cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed',
        },
    };

    function showBanner(type, message) {
        if (!feedbackBanner || !feedbackMsg) return;
        feedbackMsg.textContent = message;
        feedbackBanner.className = 'rounded-lg px-4 py-3 text-sm flex items-center justify-between gap-3 ' + (
            type === 'error'
                ? 'bg-[#F7ECE9] border border-[#E5C8C2] text-[#9C4A3E]'
                : 'bg-[#EEF3EC] border border-[#D4E2CF] text-[#3F5A38]'
        );
        feedbackBanner.setAttribute('role', type === 'error' ? 'alert' : 'status');
    }

    try {
        const savedNotice = sessionStorage.getItem('superadmin_theme_notice');
        if (savedNotice) {
            const parsed = JSON.parse(savedNotice);
            sessionStorage.removeItem('superadmin_theme_notice');
            if (parsed?.message) {
                showBanner(parsed.type || 'success', parsed.message);
            }
        }
    } catch (_) {}

    if (feedbackDismiss && feedbackBanner) {
        feedbackDismiss.addEventListener('click', () => {
            feedbackBanner.classList.add('hidden');
        });
    }

    let isSubmitting = false;

    function openModal(triggerBtn) {
        if (!modal || !form || !confirmBtn || isSubmitting) return;

        const action = triggerBtn.dataset.lifecycleAction || 'publish';
        const copy = ACTION_COPY[action] || ACTION_COPY.publish;

        form.setAttribute('action', triggerBtn.dataset.actionUrl || '');
        form.dataset.loadingLabel = copy.loadingLabel;
        if (modalTitle) modalTitle.textContent = copy.title;
        if (descriptionEl) descriptionEl.textContent = copy.description;
        if (targetNameEl) targetNameEl.textContent = triggerBtn.dataset.themeName || '';
        if (targetKeyEl) targetKeyEl.textContent = triggerBtn.dataset.themeKey || '';

        confirmBtn.textContent = copy.confirmLabel;
        confirmBtn.className = copy.confirmClass;
        confirmBtn.disabled = false;
        if (cancelBtn) cancelBtn.disabled = false;

        modal.classList.remove('hidden');
        confirmBtn.focus();
    }

    function closeModal() {
        if (!modal || isSubmitting) return;
        modal.classList.add('hidden');
    }

    document.querySelectorAll('[data-theme-lifecycle-btn]').forEach((btn) => {
        btn.addEventListener('click', () => openModal(btn));
    });

    document.querySelectorAll('[data-modal-close="theme-lifecycle-modal"], [data-modal-backdrop="theme-lifecycle-modal"]').forEach((el) => {
        el.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });

    if (form && confirmBtn) {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (isSubmitting) return;

            const actionUrl = form.getAttribute('action');
            if (!actionUrl) return;

            isSubmitting = true;
            confirmBtn.disabled = true;
            if (cancelBtn) cancelBtn.disabled = true;
            confirmBtn.textContent = form.dataset.loadingLabel || 'Updating...';

            try {
                const response = await fetch(actionUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: new FormData(form),
                    credentials: 'same-origin',
                });

                const payload = await response.json().catch(() => ({}));

                if (!response.ok || payload.success === false) {
                    isSubmitting = false;
                    closeModal();
                    showBanner('error', payload.message || 'Unable to update theme status. Please refresh and try again.');
                    return;
                }

                try {
                    sessionStorage.setItem('superadmin_theme_notice', JSON.stringify({
                        type: 'success',
                        message: payload.message || 'Theme status updated successfully.',
                    }));
                } catch (_) {}

                window.location.reload();
            } catch (_) {
                isSubmitting = false;
                closeModal();
                showBanner('error', 'Network error while updating theme status. Please try again.');
            }
        });
    }
});
</script>
@endpush
