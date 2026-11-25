@extends('layouts.admin')
@inject('domainService', 'App\Services\PortfolioDomainService')

@section('title', 'Super Admin Dashboard — Portfolio CMS')
@section('page_title', 'Dashboard')

@section('content')
<div class="space-y-8">

    <!-- ========================================================================= -->
    <!-- 1. Workspace header -->
    <!-- ========================================================================= -->
    <section class="pb-6 border-b border-line">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-5">
            <div class="space-y-2">
                <p class="text-xs text-ink-mute tabular">{{ date('l, F j, Y') }}</p>
                <h2 class="font-display text-4xl leading-none text-ink">{{ now()->format('F Y') }}</h2>
                <p class="max-w-xl text-sm text-ink-soft">
                    Portfolios, profiles and publication state for every client site.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ Route::has('admin.portfolios.create') ? route('admin.portfolios.create') : url('/admin/portfolios/create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium bg-brass text-[#14130F] hover:bg-[#B89149] transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-brass focus-visible:ring-offset-2 focus-visible:ring-offset-paper">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>New portfolio</span>
                </a>
                <a href="{{ url('/') }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium bg-surface text-ink-soft border border-line hover:bg-paper-sunk transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-brass">
                    <svg class="w-4 h-4 text-ink-mute" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    <span>Preview site</span>
                </a>
            </div>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- 2. Overview metrics -->
    <!-- ========================================================================= -->
    <section>
        <div class="flex items-baseline justify-between gap-4 mb-4">
            <h3 class="text-sm font-medium text-ink">Overview</h3>
            <span class="text-xs text-ink-mute tabular">{{ $stats['total'] ?? 0 }} total</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- 1. Total Portfolios -->
            <x-admin.stat-card
                title="Total portfolios"
                value="{{ $stats['total'] ?? 0 }}"
                subtitle="{{ $stats['profiles'] ?? 0 }} profiles">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </x-admin.stat-card>

            <!-- 2. Published Portfolios -->
            <x-admin.stat-card
                title="Published"
                value="{{ $stats['published'] ?? 0 }}"
                change="{{ ($stats['total'] ?? 0) > 0 ? round((($stats['published'] ?? 0) / $stats['total']) * 100) : 0 }}% of all"
                subtitle="Public">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </x-admin.stat-card>

            <!-- 3. Draft Portfolios -->
            <x-admin.stat-card
                title="Drafts"
                value="{{ $stats['draft'] ?? 0 }}"
                subtitle="In progress">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </x-admin.stat-card>

            <!-- 4. Client Profiles -->
            <x-admin.stat-card
                title="Client profiles"
                value="{{ $stats['profiles'] ?? 0 }}"
                subtitle="Owner records">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </x-admin.stat-card>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- 3. Shortcuts -->
    <!-- ========================================================================= -->
    <section class="rounded-xl bg-surface border border-line p-5">
        <div class="flex items-baseline justify-between gap-4 mb-4">
            <h3 class="text-sm font-medium text-ink">Shortcuts</h3>
            <span class="text-xs text-ink-mute">Jump to</span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <a href="{{ Route::has('admin.portfolios.create') ? route('admin.portfolios.create') : url('/admin/portfolios/create') }}" class="flex items-center gap-3 p-3 rounded-lg border border-line hover:border-brass hover:bg-brass-wash/50 transition-colors group">
                <div class="w-9 h-9 rounded-md bg-paper text-ink-soft flex items-center justify-center flex-shrink-0 border border-line group-hover:text-brass-ink group-hover:border-brass/40 transition-colors">
                    <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-medium text-ink truncate">Create portfolio</p>
                    <p class="text-[11px] text-ink-mute truncate">Add a client showcase</p>
                </div>
            </a>

            <a href="#media" class="flex items-center gap-3 p-3 rounded-lg border border-line hover:border-brass hover:bg-brass-wash/50 transition-colors group">
                <div class="w-9 h-9 rounded-md bg-paper text-ink-soft flex items-center justify-center flex-shrink-0 border border-line group-hover:text-brass-ink group-hover:border-brass/40 transition-colors">
                    <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-medium text-ink truncate">Media library</p>
                    <p class="text-[11px] text-ink-mute truncate">Upload assets</p>
                </div>
            </a>

            <a href="#themes" class="flex items-center gap-3 p-3 rounded-lg border border-line hover:border-brass hover:bg-brass-wash/50 transition-colors group">
                <div class="w-9 h-9 rounded-md bg-paper text-ink-soft flex items-center justify-center flex-shrink-0 border border-line group-hover:text-brass-ink group-hover:border-brass/40 transition-colors">
                    <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4 5 5 0 015-5h1a1 1 0 011 1v1a1 1 0 001 1h1a1 1 0 001-1v-1a1 1 0 011-1h1a5 5 0 015 5 4 4 0 01-4 4H7zM16 3.13a4 4 0 010 7.75"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-medium text-ink truncate">Themes</p>
                    <p class="text-[11px] text-ink-mute truncate">Styles and colors</p>
                </div>
            </a>

            <a href="#messages" class="flex items-center gap-3 p-3 rounded-lg border border-line hover:border-brass hover:bg-brass-wash/50 transition-colors group">
                <div class="w-9 h-9 rounded-md bg-paper text-ink-soft flex items-center justify-center flex-shrink-0 border border-line group-hover:text-brass-ink group-hover:border-brass/40 transition-colors">
                    <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-medium text-ink truncate">Inquiries</p>
                    <p class="text-[11px] text-ink-mute truncate">Client messages</p>
                </div>
            </a>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- 4. Main split: recent portfolios & activity -->
    <!-- ========================================================================= -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Left: Recent Portfolios Table -->
        <div class="lg:col-span-8">
            <div id="portfolios" class="rounded-xl bg-surface border border-line overflow-hidden scroll-mt-6">

                <!-- Table header & controls -->
                <div class="p-5 border-b border-line flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h3 class="text-sm font-medium text-ink">Recent portfolios</h3>
                        <p class="text-xs text-ink-mute mt-0.5">Profiles, navigation and publication state.</p>
                    </div>

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <!-- Search -->
                        <form method="GET" action="{{ route('admin.dashboard') }}" class="relative flex items-center">
                            @if(request('status'))
                                <input type="hidden" name="status" value="{{ request('status') }}">
                            @endif
                            <div class="relative w-full sm:w-48 lg:w-56">
                                <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-ink-mute">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <input type="text"
                                       name="search"
                                       id="portfolio-search-input"
                                       value="{{ request('search') }}"
                                       placeholder="Search"
                                       class="w-full py-1.5 pl-8 pr-7 text-xs bg-paper hover:border-line-strong focus:bg-surface text-ink placeholder-ink-mute border border-line rounded-lg focus:outline-none focus:border-brass focus:ring-2 focus:ring-brass/20 transition-colors">
                                @if(request('search'))
                                    <a href="{{ route('admin.dashboard', array_filter(['status' => request('status')])) }}" class="absolute inset-y-0 right-0 pr-2 flex items-center text-ink-mute hover:text-ink" title="Clear search">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </form>

                        <!-- Status filters -->
                        <div class="flex items-center gap-1 p-1 bg-paper-sunk rounded-lg text-xs font-medium self-start sm:self-auto">
                            <a href="{{ route('admin.dashboard', array_filter(['search' => request('search')])) }}"
                               class="px-2.5 py-1 rounded-md transition-colors {{ !request('status') ? 'bg-surface text-ink shadow-xs' : 'text-ink-soft hover:text-ink' }}">
                                All ({{ $stats['total'] ?? 0 }})
                            </a>
                            <a href="{{ route('admin.dashboard', array_filter(['status' => 'published', 'search' => request('search')])) }}"
                               class="px-2.5 py-1 rounded-md transition-colors {{ request('status') === 'published' ? 'bg-surface text-ink shadow-xs' : 'text-ink-soft hover:text-ink' }}">
                                Published ({{ $stats['published'] ?? 0 }})
                            </a>
                            <a href="{{ route('admin.dashboard', array_filter(['status' => 'draft', 'search' => request('search')])) }}"
                               class="px-2.5 py-1 rounded-md transition-colors {{ request('status') === 'draft' ? 'bg-surface text-ink shadow-xs' : 'text-ink-soft hover:text-ink' }}">
                                Drafts ({{ $stats['draft'] ?? 0 }})
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-line text-[11px] font-medium text-ink-mute">
                                <th scope="col" class="py-3 pl-5 pr-3">Portfolio / client</th>
                                <th scope="col" class="py-3 px-3">Navigation</th>
                                <th scope="col" class="py-3 px-3">Status</th>
                                <th scope="col" class="py-3 px-3">Created</th>
                                <th scope="col" class="py-3 pl-3 pr-5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="portfolios-table-body" class="divide-y divide-line">
                            @forelse($portfolios as $portfolio)
                                @php
                                    $displayName = $portfolio->profile?->full_name ?: $portfolio->name;
                                    $words = preg_split('/\s+/', trim($displayName));
                                    $initials = '';
                                    if (count($words) >= 2) {
                                        $initials = mb_substr($words[0], 0, 1) . mb_substr($words[1], 0, 1);
                                    } else {
                                        $initials = mb_substr($displayName, 0, 2);
                                    }
                                    $initials = strtoupper($initials);
                                @endphp
                                <tr class="portfolio-row hover:bg-paper transition-colors">
                                    <td class="py-3 pl-5 pr-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-md bg-paper text-brass-ink font-medium text-[11px] flex items-center justify-center flex-shrink-0 border border-line">
                                                {{ $initials }}
                                            </div>
                                            <div class="min-w-0">
                                                <div class="font-medium text-ink truncate">
                                                    {{ $portfolio->name }}
                                                </div>
                                                <div class="flex items-center gap-2 text-[11px] text-ink-mute">
                                                    <span class="text-ink-soft truncate">
                                                        {{ $portfolio->profile?->full_name ?? 'No profile' }}
                                                    </span>
                                                    @if($portfolio->profile?->professional_title)
                                                        <span class="text-line-strong">·</span>
                                                        <span class="truncate">{{ $portfolio->profile->professional_title }}</span>
                                                    @endif
                                                </div>
                                                <div class="mt-0.5 flex flex-wrap items-center gap-2">
                                                    @if ($portfolio->primaryDomain)
                                                        <a href="{{ $domainService->publicUrlFor($portfolio->primaryDomain, request()) }}"
                                                           target="_blank"
                                                           rel="noopener noreferrer"
                                                           class="inline-flex items-center gap-1 text-[11px] text-brass-ink hover:underline truncate font-mono">
                                                            <span>{{ $portfolio->primaryDomain->domain }}</span>
                                                            <svg class="w-3 h-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                            </svg>
                                                        </a>
                                                        <span class="text-line-strong text-[11px]">·</span>
                                                    @endif
                                                    <a href="{{ url('/' . $portfolio->slug) }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-[11px] text-ink-mute hover:text-brass-ink transition-colors truncate font-mono">
                                                        <span>/{{ $portfolio->slug }}</span>
                                                        <svg class="w-3 h-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                        </svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-3">
                                        <a href="{{ route('admin.portfolios.nav-items.index', $portfolio) }}"
                                           class="inline-flex items-center gap-1.5 text-ink-soft hover:text-ink text-[11px] font-medium transition-colors">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $portfolio->nav_items_count > 0 ? 'bg-brass' : 'bg-line-strong' }}"></span>
                                            <span class="tabular">{{ $portfolio->nav_items_count }} {{ Str::plural('link', $portfolio->nav_items_count) }}</span>
                                        </a>
                                    </td>
                                    <td class="py-3 px-3">
                                        <x-admin.status-badge :status="$portfolio->status" />
                                    </td>
                                    <td class="py-3 px-3">
                                        <div class="text-ink-soft font-medium tabular">{{ $portfolio->created_at->format('M j, Y') }}</div>
                                        <div class="text-[10px] text-ink-mute">
                                            {{ $portfolio->created_at->diffForHumans() }} · {{ $portfolio->user?->name ?? 'Admin' }}
                                        </div>
                                    </td>
                                    <td class="py-3 pl-3 pr-5 text-right relative">
                                        <div class="inline-flex items-center gap-1">
                                            <a href="{{ url('/' . $portfolio->slug) }}" target="_blank" rel="noopener noreferrer" class="p-1.5 text-ink-mute hover:text-ink hover:bg-paper-sunk rounded-md transition-colors" title="Live preview">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
                                            <a href="{{ route('admin.portfolios.nav-items.index', $portfolio) }}" class="p-1.5 text-ink-mute hover:text-ink hover:bg-paper-sunk rounded-md transition-colors" title="Manage navigation">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 px-6 text-center">
                                        <div class="max-w-sm mx-auto flex flex-col items-center">
                                            <div class="w-10 h-10 rounded-md bg-paper border border-line flex items-center justify-center text-ink-mute mb-3">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                                </svg>
                                            </div>
                                            <h4 class="text-sm font-medium text-ink mb-1">No portfolios found</h4>
                                            <p class="text-xs text-ink-mute mb-4 leading-relaxed">
                                                @if(request('search') || request('status'))
                                                    No records match your search or status filter.
                                                @else
                                                    No portfolios yet. Create the first client profile to begin.
                                                @endif
                                            </p>
                                            <div class="flex items-center gap-2">
                                                @if(request('search') || request('status'))
                                                    <a href="{{ route('admin.dashboard') }}" class="px-3 py-1.5 text-xs font-medium text-ink-soft bg-surface border border-line rounded-md hover:bg-paper-sunk transition-colors">
                                                        Clear filters
                                                    </a>
                                                @endif
                                                <a href="{{ route('admin.portfolios.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-paper bg-[#14130F] hover:bg-[#2A261C] rounded-md transition-colors">
                                                    <svg class="w-3.5 h-3.5 text-brass" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4v16m8-8H4"/>
                                                    </svg>
                                                    Create profile
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($portfolios->hasPages() || $portfolios->total() > 0)
                    <div class="p-4 border-t border-line flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-ink-mute">
                        <div class="tabular">
                            Showing <span class="font-medium text-ink-soft">{{ $portfolios->firstItem() ?? 0 }}</span>–<span class="font-medium text-ink-soft">{{ $portfolios->lastItem() ?? 0 }}</span> of <span class="font-medium text-ink-soft">{{ $portfolios->total() }}</span>
                        </div>
                        <div>
                            {{ $portfolios->links() }}
                        </div>
                    </div>
                @endif

            </div>
        </div>

        <!-- Right: Recent activity & environment -->
        <div class="lg:col-span-4 space-y-6">

            <!-- Recent Activity -->
            <div class="rounded-xl bg-surface border border-line p-5">
                <div class="flex items-baseline justify-between gap-4 pb-3 mb-4 border-b border-line">
                    <h3 class="text-sm font-medium text-ink">Recent activity</h3>
                    <a href="#activity" class="text-xs font-medium text-brass-ink hover:underline">Audit log</a>
                </div>

                <div class="relative pl-5 space-y-4 before:absolute before:left-[3px] before:top-1.5 before:bottom-1.5 before:w-px before:bg-line">
                    @forelse($portfolios->take(5) as $activity)
                        <div class="relative">
                            <div class="absolute -left-[21px] top-1.5 w-[7px] h-[7px] rounded-full {{ $activity->status === 'published' ? 'bg-[#5B7A4B]' : 'bg-brass' }} ring-2 ring-surface"></div>
                            <p class="text-xs text-ink leading-relaxed">
                                <strong class="font-medium">{{ $activity->user?->name ?? 'Super Admin' }}</strong>
                                {{ $activity->status === 'published' ? 'published' : 'created' }}
                                <span class="text-brass-ink">“{{ $activity->name }}”</span>
                            </p>
                            <div class="flex items-center gap-2 mt-0.5 text-[11px] text-ink-mute">
                                <span class="tabular">{{ $activity->created_at->diffForHumans() }}</span>
                                <span class="text-line-strong">·</span>
                                <span class="capitalize">{{ $activity->status }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="py-4 text-xs text-ink-mute">
                            No activity recorded yet.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Environment -->
            <div class="rounded-xl bg-surface border border-line p-5">
                <div class="flex items-baseline justify-between gap-4 pb-3 mb-3 border-b border-line">
                    <h3 class="text-sm font-medium text-ink">Environment</h3>
                    <span class="w-1.5 h-1.5 rounded-full bg-[#7FA46A]" aria-hidden="true"></span>
                </div>

                <dl class="space-y-2.5 text-xs">
                    <div class="flex items-baseline justify-between gap-3">
                        <dt class="text-ink-mute">PHP</dt>
                        <dd class="font-mono text-ink-soft tabular">{{ PHP_VERSION }}</dd>
                    </div>
                    <div class="flex items-baseline justify-between gap-3">
                        <dt class="text-ink-mute">Laravel</dt>
                        <dd class="font-mono text-ink-soft tabular">v{{ Illuminate\Foundation\Application::VERSION }}</dd>
                    </div>
                    <div class="flex items-baseline justify-between gap-3">
                        <dt class="text-ink-mute">Environment</dt>
                        <dd class="font-mono text-ink-soft">{{ app()->environment() }}</dd>
                    </div>
                    <div class="flex items-baseline justify-between gap-3">
                        <dt class="text-ink-mute">Locale</dt>
                        <dd class="font-mono text-ink-soft">{{ app()->getLocale() }}</dd>
                    </div>
                </dl>
            </div>

        </div>

    </div>

</div>
@endsection
