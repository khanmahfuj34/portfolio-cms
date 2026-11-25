@extends('layouts.admin')
@inject('domainService', 'App\Services\PortfolioDomainService')

@section('title', 'Profiles List — Portfolio CMS')
@section('page_title', 'Profiles')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <section class="pb-5 border-b border-line">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div class="space-y-1.5">
                <div class="flex items-center gap-2 text-[11px] text-ink-mute">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-ink transition-colors">Admin</a>
                    <span class="text-line-strong">/</span>
                    <span class="text-ink-soft">Profiles</span>
                </div>
                <h2 class="font-display text-3xl leading-none text-ink">Client profiles</h2>
                <p class="max-w-xl text-sm text-ink-soft">
                    Portfolios, owner profiles, navigation and publication state.
                </p>
            </div>

            <a href="{{ route('admin.portfolios.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium bg-brass text-[#14130F] hover:bg-[#B89149] transition-colors flex-shrink-0 focus:outline-none focus-visible:ring-2 focus-visible:ring-brass focus-visible:ring-offset-2 focus-visible:ring-offset-paper">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                <span>New portfolio</span>
            </a>
        </div>
    </section>

    <!-- Metrics -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <x-admin.stat-card title="Total" value="{{ $stats['total'] ?? 0 }}" subtitle="Portfolios">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
        </x-admin.stat-card>

        <x-admin.stat-card title="Published" value="{{ $stats['published'] ?? 0 }}" subtitle="Public">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </x-admin.stat-card>

        <x-admin.stat-card title="Drafts" value="{{ $stats['draft'] ?? 0 }}" subtitle="In progress">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
        </x-admin.stat-card>

        <x-admin.stat-card title="Owners" value="{{ $stats['profiles'] ?? 0 }}" subtitle="Profile records">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
        </x-admin.stat-card>
    </div>

    <!-- Table container -->
    <div class="rounded-xl bg-surface border border-line overflow-hidden">

        <!-- Controls -->
        <div class="p-4 sm:p-5 border-b border-line flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <form method="GET" action="{{ route('admin.profiles.index') }}" class="relative w-full md:w-72">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-ink-mute">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Search profiles, clients, slugs"
                       class="w-full pl-8 pr-8 py-2 text-xs bg-paper hover:border-line-strong focus:bg-surface text-ink placeholder-ink-mute border border-line rounded-lg focus:outline-none focus:border-brass focus:ring-2 focus:ring-brass/20 transition-colors">
                @if(request('search'))
                    <a href="{{ route('admin.profiles.index', array_filter(['status' => request('status')])) }}"
                       class="absolute inset-y-0 right-0 pr-2 flex items-center text-ink-mute hover:text-ink" title="Clear search">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </a>
                @endif
            </form>

            <div class="flex items-center gap-1 p-1 bg-paper-sunk rounded-lg text-xs font-medium self-start md:self-auto">
                <a href="{{ route('admin.profiles.index', array_filter(['search' => request('search')])) }}"
                   class="px-2.5 py-1 rounded-md transition-colors {{ !request('status') ? 'bg-surface text-ink shadow-xs' : 'text-ink-soft hover:text-ink' }}">
                    All ({{ $stats['total'] ?? 0 }})
                </a>
                <a href="{{ route('admin.profiles.index', array_filter(['status' => 'published', 'search' => request('search')])) }}"
                   class="px-2.5 py-1 rounded-md transition-colors {{ request('status') === 'published' ? 'bg-surface text-ink shadow-xs' : 'text-ink-soft hover:text-ink' }}">
                    Published ({{ $stats['published'] ?? 0 }})
                </a>
                <a href="{{ route('admin.profiles.index', array_filter(['status' => 'draft', 'search' => request('search')])) }}"
                   class="px-2.5 py-1 rounded-md transition-colors {{ request('status') === 'draft' ? 'bg-surface text-ink shadow-xs' : 'text-ink-soft hover:text-ink' }}">
                    Drafts ({{ $stats['draft'] ?? 0 }})
                </a>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-line text-[11px] font-medium text-ink-mute">
                        <th scope="col" class="py-3 pl-5 pr-3">Portfolio / client</th>
                        <th scope="col" class="py-3 px-3">Status</th>
                        <th scope="col" class="py-3 px-3">Navigation</th>
                        <th scope="col" class="py-3 px-3">Created / admin</th>
                        <th scope="col" class="py-3 pl-3 pr-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
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
                                        <div class="flex items-center gap-1.5 text-[11px] text-ink-mute">
                                            <span class="text-ink-soft truncate">
                                                {{ $portfolio->profile?->full_name ?? 'No profile assigned' }}
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
                                                   class="inline-flex items-center gap-1 text-[11px] text-brass-ink hover:underline truncate font-mono"
                                                   title="Open portfolio subdomain">
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
                                <x-admin.status-badge :status="$portfolio->status" />
                            </td>

                            <td class="py-3 px-3">
                                <a href="{{ route('admin.portfolios.nav-items.index', $portfolio) }}"
                                   class="inline-flex items-center gap-1.5 text-[11px] font-medium text-ink-soft hover:text-ink transition-colors">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $portfolio->nav_items_count > 0 ? 'bg-brass' : 'bg-line-strong' }}"></span>
                                    <span class="tabular">{{ $portfolio->nav_items_count }} {{ Str::plural('Link', $portfolio->nav_items_count) }}</span>
                                </a>
                            </td>

                            <td class="py-3 px-3">
                                <div class="text-ink-soft font-medium tabular">{{ $portfolio->created_at->format('M j, Y') }}</div>
                                <div class="text-[10px] text-ink-mute">
                                    {{ $portfolio->created_at->diffForHumans() }} · {{ $portfolio->user?->name ?? 'Admin' }}
                                </div>
                            </td>

                            <td class="py-3 pl-3 pr-5 text-right relative">
                                <div class="inline-flex items-center gap-1">
                                    @if ($portfolio->status === 'published')
                                        <form method="POST"
                                              action="{{ route('admin.portfolios.unpublish', $portfolio) }}"
                                              class="inline"
                                              onsubmit="return confirm('Are you sure you want to unpublish &quot;{{ addslashes($portfolio->name) }}&quot;? It will return to draft and become hidden from public visitors.');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-md text-[11px] font-medium bg-surface text-ink-soft hover:text-[#9C4A3E] border border-line hover:border-[#D9BDB6] transition-colors cursor-pointer"
                                                    title="Unpublish this portfolio and move it to draft">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span>Unpublish</span>
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST"
                                              action="{{ route('admin.portfolios.publish', $portfolio) }}"
                                              class="inline"
                                              onsubmit="return confirm('Are you sure you want to publish &quot;{{ addslashes($portfolio->name) }}&quot;? It will be publicly accessible at /{{ $portfolio->slug }}.');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-md text-[11px] font-medium bg-surface text-ink-soft hover:text-brass-ink border border-line hover:border-brass/50 transition-colors cursor-pointer"
                                                    title="Publish this portfolio and make it live">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                                </svg>
                                                <span>Publish</span>
                                            </button>
                                        </form>
                                    @endif

                                    <a href="{{ route('admin.portfolios.edit', $portfolio) }}"
                                       class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-md text-[11px] font-medium bg-surface text-ink-soft hover:text-ink border border-line hover:border-line-strong transition-colors"
                                       title="Edit portfolio profile, skills & projects">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                        </svg>
                                        <span>Edit</span>
                                    </a>

                                    <a href="{{ route('admin.portfolios.nav-items.index', $portfolio) }}"
                                       class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-md text-[11px] font-medium bg-surface text-ink-soft hover:text-ink border border-line hover:border-line-strong transition-colors"
                                       title="Manage navigation and configuration">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <span>Manage</span>
                                    </a>

                                    <a href="{{ url('/' . $portfolio->slug) }}"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-md text-[11px] font-medium bg-surface text-ink-soft hover:text-ink border border-line hover:border-line-strong transition-colors"
                                       title="Open live preview">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <span>View Details</span>
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
                                            No records match your active search or status filters.
                                        @else
                                            No portfolios yet. Create the first client profile to begin.
                                        @endif
                                    </p>
                                    <div class="flex items-center gap-2">
                                        @if(request('search') || request('status'))
                                            <a href="{{ route('admin.profiles.index') }}" class="px-3 py-1.5 text-xs font-medium text-ink-soft bg-surface border border-line rounded-md hover:bg-paper-sunk transition-colors">
                                                Clear Filters
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
@endsection
