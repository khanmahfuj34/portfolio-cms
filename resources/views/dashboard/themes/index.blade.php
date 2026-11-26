@extends('layouts.admin')

@section('title', 'Theme Gallery — Portfolio CMS')
@section('page_title', 'Theme Gallery')

@section('content')
@php
    $activeSearch = (string) ($filters['search'] ?? '');
    $activeCategory = (string) ($filters['category'] ?? '');
    $activeFeatured = (string) ($filters['featured'] ?? '');
    $hasActiveFilters = $activeSearch !== '' || $activeCategory !== '' || $activeFeatured !== '';
    $selectedThemeId = $selectedPortfolio?->theme_id;
    $selectedThemeName = $selectedPortfolio?->theme?->name ?? 'Default Theme';
    $activePortfolioPayload = $selectedPortfolio ? [
        'id' => $selectedPortfolio->id,
        'name' => $selectedPortfolio->name,
        'themeId' => $selectedPortfolio->theme_id,
        'themeName' => $selectedThemeName,
        'applyTemplate' => route('dashboard.portfolios.themes.apply', ['portfolio' => $selectedPortfolio->id, 'theme' => '__THEME__']),
    ] : null;
@endphp

<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header -->
    <section class="pb-5 border-b border-line">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div class="space-y-1.5">
                <div class="flex items-center gap-2 text-[11px] text-ink-mute">
                    <a href="{{ route('dashboard.index') }}" class="hover:text-ink transition-colors">Dashboard</a>
                    <span class="text-line-strong">/</span>
                    <span class="text-ink-soft">Themes</span>
                </div>
                <h2 class="font-display text-3xl leading-none text-ink">Theme Gallery</h2>
                <p class="max-w-xl text-sm text-ink-soft">
                    Choose a published theme for your portfolio. Switching themes preserves all profile, skills, navigation, and project content.
                </p>
            </div>

            <a href="{{ route('dashboard.portfolios.index') }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-medium text-ink-soft bg-surface border border-line hover:bg-paper-sunk hover:text-ink transition-colors self-start sm:self-auto">
                <span>My Portfolios</span>
            </a>
        </div>
    </section>

    <!-- Action Feedback Banner -->
    <div id="gallery-feedback-banner" class="hidden rounded-lg px-4 py-3 text-sm flex items-center justify-between gap-3" role="status" aria-live="polite">
        <span id="gallery-feedback-message"></span>
        <button type="button" id="gallery-feedback-dismiss" class="text-xs font-medium underline opacity-80 hover:opacity-100 cursor-pointer">
            Dismiss
        </button>
    </div>

    <!-- Portfolio Selection & Current Theme Context -->
    <section data-portfolio-selector-card class="rounded-xl bg-surface border border-line p-4 sm:p-5">
        @if ($portfolios->isEmpty())
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="space-y-1">
                    <p class="text-sm font-medium text-ink">No portfolios created yet</p>
                    <p class="text-xs text-ink-soft">
                        You can preview any published theme now. Create your first portfolio to apply a theme to it.
                    </p>
                </div>
                <a href="{{ route('dashboard.portfolios.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-medium bg-brass text-[#14130F] hover:bg-[#B89149] transition-colors shrink-0">
                    <span>Create Portfolio</span>
                </a>
            </div>
        @else
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex-1 min-w-0 space-y-1">
                    @if ($portfolios->count() === 1)
                        <p class="text-[11px] font-medium uppercase tracking-wider text-ink-mute">Target Portfolio</p>
                        <div class="flex flex-wrap items-center gap-2.5">
                            <span id="selected-portfolio-label" class="text-base font-medium text-ink">
                                {{ $selectedPortfolio->name }}
                            </span>
                            <span class="px-2 py-0.5 rounded bg-paper border border-line text-[11px] font-mono text-ink-soft">
                                /{{ $selectedPortfolio->slug }}
                            </span>
                        </div>
                    @else
                        <label for="target-portfolio-select" class="block text-[11px] font-medium uppercase tracking-wider text-ink-mute mb-1.5">
                            Select Target Portfolio ({{ $portfolios->count() }} owned)
                        </label>
                        <select id="target-portfolio-select"
                                class="w-full sm:max-w-md h-9 px-3 text-sm bg-paper border border-line rounded-lg text-ink focus:outline-none focus:border-brass focus:ring-1 focus:ring-brass/25 transition-colors">
                            @foreach ($portfolios as $ownedPortfolio)
                                @php
                                    $ownedThemeLabel = $ownedPortfolio->theme?->name ?? 'Default Theme';
                                @endphp
                                <option value="{{ $ownedPortfolio->id }}"
                                        data-portfolio-name="{{ $ownedPortfolio->name }}"
                                        data-theme-id="{{ $ownedPortfolio->theme_id ?? '' }}"
                                        data-theme-name="{{ $ownedThemeLabel }}"
                                        data-apply-template="{{ route('dashboard.portfolios.themes.apply', ['portfolio' => $ownedPortfolio->id, 'theme' => '__THEME__']) }}"
                                        @selected($selectedPortfolio?->id === $ownedPortfolio->id)>
                                    {{ $ownedPortfolio->name }} — Current: {{ $ownedThemeLabel }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-3 shrink-0 pt-2 lg:pt-0 border-t lg:border-t-0 border-line">
                    <div class="rounded-lg bg-paper border border-line px-3.5 py-2 text-xs">
                        <span class="text-ink-mute">Current Theme:</span>
                        <span id="selected-portfolio-current-theme"
                              data-has-custom-theme="{{ $selectedThemeId ? '1' : '0' }}"
                              class="ml-1.5 font-medium text-ink">
                            {{ $selectedThemeName }}
                        </span>
                    </div>
                </div>
            </div>
        @endif
    </section>

    <!-- Published Catalog Controls & Grid -->
    <div class="rounded-xl bg-surface border border-line overflow-hidden">
        <!-- Filters -->
        <div class="p-4 sm:p-5 border-b border-line">
            <form method="GET" action="{{ route('dashboard.themes.index') }}" class="flex flex-col lg:flex-row lg:items-center gap-3">
                <input type="hidden" name="portfolio" id="filter-portfolio-input" value="{{ $selectedPortfolio?->id ?? '' }}">

                <div class="relative flex-1 min-w-0">
                    <label for="gallery-search" class="sr-only">Search published themes</label>
                    <input type="text"
                           id="gallery-search"
                           name="search"
                           value="{{ $activeSearch }}"
                           placeholder="Search published themes by name or description..."
                           class="w-full h-9 pl-9 pr-3 text-sm bg-paper border border-line rounded-lg text-ink placeholder:text-ink-mute focus:outline-none focus:border-brass focus:ring-1 focus:ring-brass/25 transition-colors">
                    <svg class="w-4 h-4 text-ink-mute absolute left-3 top-2.5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                    </svg>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:flex items-center gap-2.5">
                    <div>
                        <label for="gallery-category" class="sr-only">Category</label>
                        <select id="gallery-category"
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
                        <label for="gallery-featured" class="sr-only">Featured</label>
                        <select id="gallery-featured"
                                name="featured"
                                class="w-full lg:w-40 h-9 px-3 text-sm bg-paper border border-line rounded-lg text-ink focus:outline-none focus:border-brass focus:ring-1 focus:ring-brass/25 transition-colors">
                            <option value="">All Themes</option>
                            <option value="1" @selected($activeFeatured === '1' || $activeFeatured === 'true')>Featured Only</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="submit"
                                class="h-9 px-4 text-xs font-medium bg-ink text-paper rounded-lg hover:bg-ink/90 transition-colors cursor-pointer whitespace-nowrap">
                            Apply Filters
                        </button>

                        @if ($hasActiveFilters)
                            <a href="{{ route('dashboard.themes.index', array_filter(['portfolio' => $selectedPortfolio?->id])) }}"
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
                    <h3 class="text-sm font-semibold text-ink">No published themes available</h3>
                    <p class="text-xs text-ink-soft">
                        @if ($hasActiveFilters)
                            No published themes match your search or category filter. Clear your filters to inspect all available themes.
                        @else
                            Published portfolio themes will appear here once approved and released. Your portfolios remain safely on their existing theme.
                        @endif
                    </p>
                    @if ($hasActiveFilters)
                        <div>
                            <a href="{{ route('dashboard.themes.index', array_filter(['portfolio' => $selectedPortfolio?->id])) }}"
                               class="inline-flex items-center px-3.5 py-2 text-xs font-medium bg-paper border border-line rounded-lg text-ink hover:bg-paper-sunk transition-colors">
                                Reset Filters
                            </a>
                        </div>
                    @endif
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                    @foreach ($themes as $theme)
                        @php
                            $isCurrentTheme = $selectedPortfolio && (int) $selectedPortfolio->theme_id === (int) $theme->id;
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
                            $basePreviewUrl = route('dashboard.themes.preview', $theme);
                            $initialPreviewUrl = $selectedPortfolio
                                ? $basePreviewUrl . '?portfolio=' . $selectedPortfolio->id
                                : $basePreviewUrl;
                        @endphp

                        <article data-gallery-theme-card
                                 data-theme-id="{{ $theme->id }}"
                                 data-theme-name="{{ $theme->name }}"
                                 class="flex flex-col rounded-xl bg-paper border {{ $isCurrentTheme ? 'border-[#3F5A38] ring-1 ring-[#3F5A38]/25' : 'border-line hover:border-line-strong' }} transition-colors overflow-hidden">

                            <!-- Preview Visual Header -->
                            <div class="relative aspect-[16/10] border-b border-line overflow-hidden bg-[#14130F]">
                                @if ($thumbUrl)
                                    <img src="{{ $thumbUrl }}"
                                         alt="{{ $theme->name }} preview thumbnail"
                                         class="w-full h-full object-cover"
                                         onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');">
                                @endif

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
                                        <span class="truncate">{{ $theme->category ?: 'Portfolio Theme' }}</span>
                                        <span>v{{ $theme->version }}</span>
                                    </div>
                                </div>

                                <!-- Badges -->
                                <div class="absolute top-3 left-3 right-3 flex items-center justify-between gap-2">
                                    <span data-current-theme-badge
                                          class="{{ $isCurrentTheme ? 'inline-flex' : 'hidden' }} items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-[#EEF3EC] text-[#3F5A38] border border-[#D4E2CF] shadow-xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#4D6B45]"></span>
                                        <span>Current Theme</span>
                                    </span>

                                    @if ($theme->is_featured)
                                        <span class="ml-auto inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-brass text-[#14130F] shadow-xs">
                                            Featured
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Card Content -->
                            <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                                <div class="space-y-2">
                                    <div class="flex items-start justify-between gap-2">
                                        <h3 class="font-display text-lg font-medium text-ink leading-snug">
                                            {{ $theme->name }}
                                        </h3>
                                        <span class="shrink-0 px-2 py-0.5 rounded bg-surface border border-line text-[11px] font-mono text-ink-soft">
                                            v{{ $theme->version }}
                                        </span>
                                    </div>

                                    <p class="text-xs text-ink-soft leading-relaxed line-clamp-2">
                                        {{ $theme->description ?: 'Published portfolio layout theme.' }}
                                    </p>
                                </div>

                                <dl class="grid grid-cols-2 gap-x-3 gap-y-2 pt-3 border-t border-line text-xs">
                                    <div class="min-w-0">
                                        <dt class="text-[11px] text-ink-mute">Category</dt>
                                        <dd class="mt-0.5 text-ink font-medium truncate">
                                            {{ $theme->category ?: 'General' }}
                                        </dd>
                                    </div>

                                    <div class="min-w-0">
                                        <dt class="text-[11px] text-ink-mute">Version</dt>
                                        <dd class="mt-0.5 font-mono text-ink-soft">
                                            v{{ $theme->version }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            <!-- Card Footer Actions -->
                            <div class="px-5 py-3.5 bg-surface border-t border-line flex flex-wrap items-center justify-between gap-2.5">
                                <a href="{{ $initialPreviewUrl }}"
                                   data-theme-preview-link
                                   data-base-preview-url="{{ $basePreviewUrl }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-ink bg-paper border border-line hover:bg-paper-sunk hover:border-line-strong transition-colors">
                                    <svg class="w-3.5 h-3.5 text-ink-soft" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span>Preview</span>
                                </a>

                                @if ($portfolios->isEmpty())
                                    <span class="px-3 py-1.5 rounded-lg text-xs font-medium text-ink-mute bg-paper-sunk border border-line cursor-not-allowed">
                                        Create portfolio first
                                    </span>
                                @else
                                    <span data-current-theme-pill
                                          class="{{ $isCurrentTheme ? 'inline-flex' : 'hidden' }} items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-[#EEF3EC] text-[#3F5A38] border border-[#D4E2CF]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#4D6B45]"></span>
                                        <span>Current Theme</span>
                                    </span>

                                    <button type="button"
                                            data-apply-theme-btn
                                            data-theme-id="{{ $theme->id }}"
                                            data-theme-name="{{ $theme->name }}"
                                            class="{{ $isCurrentTheme ? 'hidden' : 'inline-flex' }} items-center justify-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-medium bg-ink text-paper hover:bg-ink/90 transition-colors cursor-pointer">
                                        <span>Apply Theme</span>
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
                    Showing <span class="font-medium text-ink-soft">{{ $themes->firstItem() ?? 0 }}</span>–<span class="font-medium text-ink-soft">{{ $themes->lastItem() ?? 0 }}</span> of <span class="font-medium text-ink-soft">{{ $themes->total() }}</span> published themes
                </div>
                <div>
                    {{ $themes->links() }}
                </div>
            </div>
        @endif
    </div>

</div>

<!-- Apply Theme Confirmation Modal -->
<x-admin.modal id="apply-theme-modal" title="Confirm Theme Change" size="max-w-md">
    <form id="apply-theme-form" method="POST" action="">
        @csrf
        @method('PATCH')

        <div class="space-y-3">
            <p class="text-sm text-ink-soft leading-relaxed">
                Apply this theme to your selected portfolio? Your profile, navigation links, skills, and project content will remain unchanged.
            </p>

            <div class="rounded-lg bg-paper-sunk border border-line px-3.5 py-2.5 text-xs space-y-1.5">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-ink-mute">Portfolio</span>
                    <span id="apply-modal-portfolio-name" class="font-medium text-ink"></span>
                </div>
                <div class="flex items-center justify-between gap-2">
                    <span class="text-ink-mute">Current Theme</span>
                    <span id="apply-modal-current-theme" class="text-ink-soft"></span>
                </div>
                <div class="flex items-center justify-between gap-2">
                    <span class="text-ink-mute">New Theme</span>
                    <span id="apply-modal-new-theme" class="font-medium text-ink"></span>
                </div>
            </div>
        </div>
    </form>

    <x-slot name="footer">
        <button type="button"
                id="apply-modal-cancel"
                data-modal-close="apply-theme-modal"
                class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-ink-soft bg-paper border border-line hover:bg-paper-sunk transition-colors cursor-pointer">
            Cancel
        </button>
        <button type="submit"
                form="apply-theme-form"
                id="apply-modal-confirm"
                class="px-4 py-1.5 rounded-lg text-xs font-medium bg-ink text-paper hover:bg-ink/90 transition-colors cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed">
            Confirm &amp; Apply
        </button>
    </x-slot>
</x-admin.modal>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const portfolioSelect = document.getElementById('target-portfolio-select');
    const currentThemeLabel = document.getElementById('selected-portfolio-current-theme');
    const filterPortfolioInput = document.getElementById('filter-portfolio-input');
    const modal = document.getElementById('apply-theme-modal');
    const form = document.getElementById('apply-theme-form');
    const modalPortfolioName = document.getElementById('apply-modal-portfolio-name');
    const modalCurrentTheme = document.getElementById('apply-modal-current-theme');
    const modalNewTheme = document.getElementById('apply-modal-new-theme');
    const confirmBtn = document.getElementById('apply-modal-confirm');
    const cancelBtn = document.getElementById('apply-modal-cancel');
    const feedbackBanner = document.getElementById('gallery-feedback-banner');
    const feedbackMessage = document.getElementById('gallery-feedback-message');
    const feedbackDismiss = document.getElementById('gallery-feedback-dismiss');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    let activePortfolio = {{ \Illuminate\Support\Js::from($activePortfolioPayload) }};

    let pendingThemeId = null;
    let isSubmitting = false;

    function showBanner(type, message) {
        if (!feedbackBanner || !feedbackMessage) return;
        feedbackMessage.textContent = message;
        feedbackBanner.className = 'rounded-lg px-4 py-3 text-sm flex items-center justify-between gap-3 ' + (
            type === 'error'
                ? 'bg-[#F7ECE9] border border-[#E5C8C2] text-[#9C4A3E]'
                : 'bg-[#EEF3EC] border border-[#D4E2CF] text-[#3F5A38]'
        );
        feedbackBanner.setAttribute('role', type === 'error' ? 'alert' : 'status');
    }

    if (feedbackDismiss && feedbackBanner) {
        feedbackDismiss.addEventListener('click', () => feedbackBanner.classList.add('hidden'));
    }

    function syncPortfolioSelectionUI() {
        if (!activePortfolio) return;

        if (currentThemeLabel) {
            currentThemeLabel.textContent = activePortfolio.themeName || 'Default Theme';
        }
        if (filterPortfolioInput) {
            filterPortfolioInput.value = String(activePortfolio.id);
        }

        document.querySelectorAll('[data-theme-preview-link]').forEach((link) => {
            const baseUrl = link.dataset.basePreviewUrl;
            if (baseUrl) {
                link.setAttribute('href', `${baseUrl}?portfolio=${activePortfolio.id}`);
            }
        });

        document.querySelectorAll('[data-gallery-theme-card]').forEach((card) => {
            const cardThemeId = Number(card.dataset.themeId);
            const isMatch = activePortfolio.themeId !== null
                && activePortfolio.themeId !== ''
                && Number(activePortfolio.themeId) === cardThemeId;

            const badge = card.querySelector('[data-current-theme-badge]');
            const pill = card.querySelector('[data-current-theme-pill]');
            const applyBtn = card.querySelector('[data-apply-theme-btn]');

            if (badge) {
                badge.classList.toggle('hidden', !isMatch);
                badge.classList.toggle('inline-flex', isMatch);
            }
            if (pill) {
                pill.classList.toggle('hidden', !isMatch);
                pill.classList.toggle('inline-flex', isMatch);
            }
            if (applyBtn) {
                applyBtn.classList.toggle('hidden', isMatch);
                applyBtn.classList.toggle('inline-flex', !isMatch);
            }

            card.classList.toggle('border-[#3F5A38]', isMatch);
            card.classList.toggle('ring-1', isMatch);
            card.classList.toggle('ring-[#3F5A38]/25', isMatch);
            card.classList.toggle('border-line', !isMatch);
        });
    }

    if (portfolioSelect) {
        portfolioSelect.addEventListener('change', () => {
            const opt = portfolioSelect.options[portfolioSelect.selectedIndex];
            if (!opt) return;

            activePortfolio = {
                id: Number(opt.value),
                name: opt.dataset.portfolioName || opt.textContent.trim(),
                themeId: opt.dataset.themeId !== '' ? Number(opt.dataset.themeId) : null,
                themeName: opt.dataset.themeName || 'Default Theme',
                applyTemplate: opt.dataset.applyTemplate || '',
            };
            syncPortfolioSelectionUI();
        });
    }

    function openApplyModal(triggerBtn) {
        if (!modal || !form || !confirmBtn || !activePortfolio || isSubmitting) return;

        pendingThemeId = triggerBtn.dataset.themeId;
        const themeName = triggerBtn.dataset.themeName || '';
        const actionUrl = (activePortfolio.applyTemplate || '').replace('__THEME__', encodeURIComponent(pendingThemeId));

        if (!actionUrl) return;

        form.setAttribute('action', actionUrl);
        if (modalPortfolioName) modalPortfolioName.textContent = activePortfolio.name;
        if (modalCurrentTheme) modalCurrentTheme.textContent = activePortfolio.themeName || 'Default Theme';
        if (modalNewTheme) modalNewTheme.textContent = themeName;

        confirmBtn.disabled = false;
        confirmBtn.textContent = 'Confirm & Apply';
        if (cancelBtn) cancelBtn.disabled = false;

        modal.classList.remove('hidden');
        confirmBtn.focus();
    }

    function closeModal() {
        if (!modal || isSubmitting) return;
        modal.classList.add('hidden');
    }

    document.querySelectorAll('[data-apply-theme-btn]').forEach((btn) => {
        btn.addEventListener('click', () => openApplyModal(btn));
    });

    document.querySelectorAll('[data-modal-close="apply-theme-modal"], [data-modal-backdrop="apply-theme-modal"]').forEach((el) => {
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
            if (isSubmitting || !activePortfolio || !pendingThemeId) return;

            const actionUrl = form.getAttribute('action');
            if (!actionUrl) return;

            isSubmitting = true;
            confirmBtn.disabled = true;
            confirmBtn.textContent = 'Applying theme...';
            if (cancelBtn) cancelBtn.disabled = true;

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
                    showBanner('error', payload.message || 'Unable to apply theme. Please refresh and try again.');
                    return;
                }

                const appliedThemeId = Number(payload?.data?.portfolio?.theme_id ?? pendingThemeId);
                const appliedThemeName = payload?.data?.theme?.name || (modalNewTheme?.textContent || 'Selected Theme');

                activePortfolio.themeId = appliedThemeId;
                activePortfolio.themeName = appliedThemeName;

                if (portfolioSelect) {
                    const selectedOption = portfolioSelect.options[portfolioSelect.selectedIndex];
                    if (selectedOption) {
                        selectedOption.dataset.themeId = String(appliedThemeId);
                        selectedOption.dataset.themeName = appliedThemeName;
                        selectedOption.textContent = `${activePortfolio.name} — Current: ${appliedThemeName}`;
                    }
                }

                isSubmitting = false;
                closeModal();
                syncPortfolioSelectionUI();
                showBanner('success', payload.message || 'Theme applied to portfolio successfully.');
            } catch (_) {
                isSubmitting = false;
                closeModal();
                showBanner('error', 'Network error while applying theme. Please try again.');
            }
        });
    }
});
</script>
@endpush
