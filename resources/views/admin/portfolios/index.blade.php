@extends('layouts.admin')

@section('title', 'Profiles List — Portfolio CMS')
@section('page_title', 'Profiles')

@section('content')
<div class="space-y-6">

    <!-- Header Section with Breadcrumb and Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200/80">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-800 transition-colors">Admin</a>
                <span>/</span>
                <span class="font-semibold text-slate-700">Profiles</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900">
                Client Profiles & Portfolios
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Manage client showcase sites, inspect profile data, configure navbar items, and monitor publishing status.
            </p>
        </div>

        <div class="flex items-center gap-2.5 flex-shrink-0">
            <a href="{{ route('admin.portfolios.create') }}" 
               class="gold-btn-gradient inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-[#0B0D12] transition-transform active:scale-95 focus:outline-hidden focus:ring-2 focus:ring-[#D4AF69] shadow-sm">
                <svg class="w-4 h-4 text-[#0B0D12]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Create New Profile</span>
            </a>
        </div>
    </div>

    <!-- Metrics Summary Pills -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="p-3.5 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Profiles</p>
                <p class="text-lg font-extrabold text-slate-900 mt-0.5">{{ $stats['total'] ?? 0 }}</p>
            </div>
            <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center text-slate-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
        </div>

        <div class="p-3.5 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Published</p>
                <p class="text-lg font-extrabold text-emerald-600 mt-0.5">{{ $stats['published'] ?? 0 }}</p>
            </div>
            <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        <div class="p-3.5 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Drafts</p>
                <p class="text-lg font-extrabold text-amber-600 mt-0.5">{{ $stats['draft'] ?? 0 }}</p>
            </div>
            <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </div>
        </div>

        <div class="p-3.5 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Owner Profiles</p>
                <p class="text-lg font-extrabold text-[#9A7326] mt-0.5">{{ $stats['profiles'] ?? 0 }}</p>
            </div>
            <div class="w-9 h-9 rounded-xl bg-[#D4AF69]/15 text-[#9A7326] flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Main Table Container -->
    <div class="rounded-2xl bg-white border border-slate-200/80 shadow-sm overflow-hidden">
        
        <!-- Table Controls: Search & Status Filters -->
        <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            
            <!-- Search Form -->
            <form method="GET" action="{{ route('admin.profiles.index') }}" class="relative w-full md:w-80">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Search portfolios, clients, slugs..." 
                       class="w-full pl-9 pr-8 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-[#D4AF69]/40 focus:border-[#D4AF69] transition-all placeholder:text-slate-400"
                >
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                @if(request('search'))
                    <a href="{{ route('admin.profiles.index', array_filter(['status' => request('status')])) }}" 
                       class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600" title="Clear search">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </a>
                @endif
            </form>

            <!-- Status Filter Pills -->
            <div class="flex items-center gap-1.5 p-1 bg-slate-100 rounded-xl text-xs font-semibold self-start md:self-auto">
                <a href="{{ route('admin.profiles.index', array_filter(['search' => request('search')])) }}" 
                   class="px-3 py-1.5 rounded-lg transition-colors {{ !request('status') ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-800' }}">
                    All ({{ $stats['total'] ?? 0 }})
                </a>
                <a href="{{ route('admin.profiles.index', array_filter(['status' => 'published', 'search' => request('search')])) }}" 
                   class="px-3 py-1.5 rounded-lg transition-colors {{ request('status') === 'published' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-800' }}">
                    Published ({{ $stats['published'] ?? 0 }})
                </a>
                <a href="{{ route('admin.profiles.index', array_filter(['status' => 'draft', 'search' => request('search')])) }}" 
                   class="px-3 py-1.5 rounded-lg transition-colors {{ request('status') === 'draft' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-800' }}">
                    Drafts ({{ $stats['draft'] ?? 0 }})
                </a>
            </div>
        </div>

        <!-- Responsive Table Wrapper -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/70 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th scope="col" class="py-3.5 pl-5 pr-3">Portfolio / Client</th>
                        <th scope="col" class="py-3.5 px-3">Status</th>
                        <th scope="col" class="py-3.5 px-3">Navigation</th>
                        <th scope="col" class="py-3.5 px-3">Created / Admin</th>
                        <th scope="col" class="py-3.5 pl-3 pr-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
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
                        <tr class="portfolio-row hover:bg-slate-50/80 transition-colors">
                            <!-- Portfolio / Client -->
                            <td class="py-3.5 pl-5 pr-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-[#1E2330] to-[#2E364A] text-[#D4AF69] font-bold text-xs flex items-center justify-center flex-shrink-0 border border-[#D4AF69]/20 shadow-xs">
                                        {{ $initials }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-900 truncate">
                                            {{ $portfolio->name }}
                                        </div>
                                        <div class="flex items-center gap-2 text-[11px] text-slate-400">
                                            <span class="text-slate-600 font-medium truncate">
                                                {{ $portfolio->profile?->full_name ?? 'No Profile Assigned' }}
                                            </span>
                                            @if($portfolio->profile?->professional_title)
                                                <span>&bull;</span>
                                                <span class="text-slate-500 truncate">{{ $portfolio->profile->professional_title }}</span>
                                            @endif
                                        </div>
                                        <div class="mt-0.5">
                                            <a href="{{ url('/' . $portfolio->slug) }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-[11px] text-slate-400 hover:text-[#9A7326] transition-colors truncate">
                                                <span>/{{ $portfolio->slug }}</span>
                                                <svg class="w-3 h-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="py-3.5 px-3">
                                <x-admin.status-badge :status="$portfolio->status" />
                            </td>

                            <!-- Navigation -->
                            <td class="py-3.5 px-3">
                                <a href="{{ route('admin.portfolios.nav-items.index', $portfolio) }}" 
                                   class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-slate-50 hover:bg-slate-100 text-slate-700 text-[11px] font-medium border border-slate-200 transition-colors">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $portfolio->nav_items_count > 0 ? 'bg-[#D4AF69]' : 'bg-slate-300' }}"></span>
                                    <span>{{ $portfolio->nav_items_count }} {{ Str::plural('Link', $portfolio->nav_items_count) }}</span>
                                </a>
                            </td>

                            <!-- Created / Admin -->
                            <td class="py-3.5 px-3">
                                <div class="text-slate-700 font-semibold">{{ $portfolio->created_at->format('M j, Y') }}</div>
                                <div class="text-[10px] text-slate-400">
                                    {{ $portfolio->created_at->diffForHumans() }} &bull; {{ $portfolio->user?->name ?? 'Admin' }}
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="py-3.5 pl-3 pr-5 text-right relative">
                                <div class="inline-flex items-center gap-1.5">
                                    @if ($portfolio->status === 'published')
                                        <!-- Unpublish Action -->
                                        <form method="POST" 
                                              action="{{ route('admin.portfolios.unpublish', $portfolio) }}" 
                                              class="inline"
                                              onsubmit="return confirm('Are you sure you want to unpublish &quot;{{ addslashes($portfolio->name) }}&quot;? It will return to draft and become hidden from public visitors.');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 hover:bg-rose-50 text-emerald-700 hover:text-rose-700 transition-colors border border-emerald-200 hover:border-rose-200 cursor-pointer group"
                                                    title="Click to unpublish this portfolio and move to draft">
                                                <svg class="w-3.5 h-3.5 text-emerald-600 group-hover:text-rose-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span>Unpublish</span>
                                            </button>
                                        </form>
                                    @else
                                        <!-- Publish Action -->
                                        <form method="POST" 
                                              action="{{ route('admin.portfolios.publish', $portfolio) }}" 
                                              class="inline"
                                              onsubmit="return confirm('Are you sure you want to publish &quot;{{ addslashes($portfolio->name) }}&quot;? It will be publicly accessible at /{{ $portfolio->slug }}.');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-amber-50 hover:bg-emerald-50 text-amber-800 hover:text-emerald-800 transition-colors border border-amber-200 hover:border-emerald-200 cursor-pointer group"
                                                    title="Click to publish this portfolio and make it live">
                                                <svg class="w-3.5 h-3.5 text-amber-600 group-hover:text-emerald-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                                </svg>
                                                <span>Publish</span>
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Manage Action -->
                                    <a href="{{ route('admin.portfolios.nav-items.index', $portfolio) }}" 
                                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-[#D4AF69]/15 text-slate-700 hover:text-[#9A7326] transition-colors border border-slate-200 hover:border-[#D4AF69]/30"
                                       title="Manage Navigation & Configuration">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <span>Manage</span>
                                    </a>

                                    <!-- View Details Action -->
                                    <a href="{{ url('/' . $portfolio->slug) }}" 
                                       target="_blank" 
                                       rel="noopener noreferrer"
                                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-white hover:bg-slate-50 text-slate-700 hover:text-slate-900 transition-colors border border-slate-200 shadow-xs"
                                       title="View Details & Live Preview">
                                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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
                                    <div class="w-12 h-12 rounded-2xl bg-amber-50 border border-[#D4AF69]/20 flex items-center justify-center text-[#9A7326] mb-3">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                        </svg>
                                    </div>
                                    <h4 class="text-sm font-bold text-slate-900 mb-1">No portfolios found</h4>
                                    <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                                        @if(request('search') || request('status'))
                                            No records match your active search or status filters. Try clearing your filters.
                                        @else
                                            No portfolios have been created yet. Launch your first client portfolio and profile to get started.
                                        @endif
                                    </p>
                                    <div class="flex items-center gap-2">
                                        @if(request('search') || request('status'))
                                            <a href="{{ route('admin.profiles.index') }}" class="px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                                                Clear Filters
                                            </a>
                                        @endif
                                        <a href="{{ route('admin.portfolios.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-white bg-[#0F1219] hover:bg-[#1E2330] rounded-lg transition-colors shadow-xs">
                                            <svg class="w-3.5 h-3.5 text-[#D4AF69]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                            Create New Profile
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Table Pagination -->
        @if($portfolios->hasPages() || $portfolios->total() > 0)
            <div class="p-4 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
                <div>
                    Showing <span class="font-bold text-slate-700">{{ $portfolios->firstItem() ?? 0 }}</span> to <span class="font-bold text-slate-700">{{ $portfolios->lastItem() ?? 0 }}</span> of <span class="font-bold text-slate-700">{{ $portfolios->total() }}</span> portfolios
                </div>
                <div>
                    {{ $portfolios->links() }}
                </div>
            </div>
        @endif

    </div>

</div>
@endsection
