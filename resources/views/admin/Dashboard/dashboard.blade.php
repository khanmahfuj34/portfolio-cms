@extends('layouts.admin')

@section('title', 'Super Admin Dashboard — Portfolio CMS')
@section('page_title', 'Dashboard')

@section('content')
<div class="space-y-8">

    <!-- ========================================================================= -->
    <!-- 1. Executive Welcome & Quick Action Header -->
    <!-- ========================================================================= -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#0F1219] via-[#161B26] to-[#121622] text-white p-6 sm:p-8 border border-[#1E2330] shadow-xl">
        <!-- Ambient Gold Glow in Background -->
        <div class="absolute -right-20 -top-20 w-80 h-80 bg-[radial-gradient(circle,rgba(212,175,105,0.18)_0%,transparent_70%)] blur-3xl pointer-events-none" aria-hidden="true"></div>
        <div class="absolute -left-20 -bottom-20 w-60 h-60 bg-[radial-gradient(circle,rgba(212,175,105,0.1)_0%,transparent_70%)] blur-2xl pointer-events-none" aria-hidden="true"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="space-y-2 max-w-2xl">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-[#D4AF69]/20 text-[#E8C98E] border border-[#D4AF69]/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shadow-[0_0_6px_#34D399]"></span>
                        System Operational
                    </span>
                    <span class="text-xs text-slate-400">&bull; {{ date('l, F j, Y') }}</span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                    Welcome back, <span class="gold-gradient-text">{{ auth()->user()->name ?? 'Super Admin' }}</span>
                </h2>
                <p class="text-sm text-slate-300 leading-relaxed">
                    Portfolio CMS enterprise overview. Monitor publication states, visitor engagement, system activity, and client inquiries from your central command dashboard.
                </p>
            </div>

            <!-- Header Quick Action Buttons -->
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ Route::has('admin.portfolios.create') ? route('admin.portfolios.create') : url('/admin/portfolios/create') }}" class="gold-btn-gradient inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-[#0B0D12] transition-transform active:scale-95 focus:outline-none focus:ring-2 focus:ring-[#D4AF69]">
                    <svg class="w-4 h-4 text-[#0B0D12]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>New Portfolio</span>
                </a>
                <a href="{{ url('/') }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-medium bg-white/10 hover:bg-white/15 text-white border border-white/15 backdrop-blur-sm transition-colors focus:outline-none focus:ring-2 focus:ring-white/30">
                    <svg class="w-4 h-4 text-[#D4AF69]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <span>Preview Live</span>
                </a>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 2. Overview Statistics Cards (5 Cards) -->
    <!-- ========================================================================= -->
    <div>
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">System Metrics</h3>
            <span class="text-xs text-slate-400">Updated Real-Time</span>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
            <!-- 1. Total Portfolios -->
            <x-admin.stat-card 
                title="Total Portfolios" 
                value="{{ $stats['total'] ?? 0 }}" 
                change="Live Database" 
                changeType="up" 
                subtitle="{{ $stats['profiles'] ?? 0 }} Profiles">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </x-admin.stat-card>

            <!-- 2. Published Portfolios -->
            <x-admin.stat-card 
                title="Published Portfolios" 
                value="{{ $stats['published'] ?? 0 }}" 
                change="{{ ($stats['total'] ?? 0) > 0 ? round((($stats['published'] ?? 0) / $stats['total']) * 100) : 0 }}% Live" 
                changeType="up" 
                subtitle="Public Indexable"
                iconBg="bg-emerald-50 text-emerald-600 border-emerald-200/50">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </x-admin.stat-card>

            <!-- 3. Draft Portfolios -->
            <x-admin.stat-card 
                title="Draft Portfolios" 
                value="{{ $stats['draft'] ?? 0 }}" 
                change="Initial Status" 
                changeType="neutral" 
                subtitle="Staging / In Progress"
                iconBg="bg-amber-50 text-amber-600 border-amber-200/50">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </x-admin.stat-card>

            <!-- 4. Client Profiles -->
            <x-admin.stat-card 
                title="Client Profiles" 
                value="{{ $stats['profiles'] ?? 0 }}" 
                change="Registered" 
                changeType="gold" 
                subtitle="Owner Credentials"
                iconBg="bg-indigo-50 text-indigo-600 border-indigo-200/50">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </x-admin.stat-card>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 3. Quick Action Hub -->
    <!-- ========================================================================= -->
    <div class="rounded-2xl bg-white border border-slate-200/80 p-5 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Quick Navigation & Actions</h3>
            <span class="text-xs text-slate-400">Shortcuts</span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <a href="{{ Route::has('admin.portfolios.create') ? route('admin.portfolios.create') : url('/admin/portfolios/create') }}" class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:border-[#D4AF69] hover:bg-[#D4AF69]/5 transition-all group">
                <div class="w-10 h-10 rounded-lg bg-[#D4AF69]/15 text-[#9A7326] flex items-center justify-center group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-bold text-slate-800 group-hover:text-[#9A7326] truncate">Create Portfolio</p>
                    <p class="text-[11px] text-slate-400 truncate">Add new client showcase</p>
                </div>
            </a>

            <a href="#media" class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:border-[#D4AF69] hover:bg-[#D4AF69]/5 transition-all group">
                <div class="w-10 h-10 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-bold text-slate-800 group-hover:text-[#9A7326] truncate">Media Library</p>
                    <p class="text-[11px] text-slate-400 truncate">Upload assets & photos</p>
                </div>
            </a>

            <a href="#themes" class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:border-[#D4AF69] hover:bg-[#D4AF69]/5 transition-all group">
                <div class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4 5 5 0 015-5h1a1 1 0 011 1v1a1 1 0 001 1h1a1 1 0 001-1v-1a1 1 0 011-1h1a5 5 0 015 5 4 4 0 01-4 4H7zM16 3.13a4 4 0 010 7.75"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-bold text-slate-800 group-hover:text-[#9A7326] truncate">Theme Studio</p>
                    <p class="text-[11px] text-slate-400 truncate">Customize styles & colors</p>
                </div>
            </a>

            <a href="#messages" class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:border-[#D4AF69] hover:bg-[#D4AF69]/5 transition-all group">
                <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-bold text-slate-800 group-hover:text-[#9A7326] truncate">Client Inquiries</p>
                    <p class="text-[11px] text-slate-400 truncate">3 unread submissions</p>
                </div>
            </a>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 4. Main Split: Recent Portfolios (Left) & Recent Activity (Right) -->
    <!-- ========================================================================= -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left Column: Recent Portfolios Table (8 cols on lg) -->
        <div class="lg:col-span-8 space-y-4">
            
            <div id="portfolios" class="rounded-2xl bg-white border border-slate-200/80 shadow-sm overflow-hidden scroll-mt-6">
                
                <!-- Table Header & Filter Tabs -->
                <div class="p-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-bold text-slate-900 tracking-tight">Recent Portfolios</h3>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#D4AF69]/15 text-[#9A7326] border border-[#D4AF69]/25">
                                Live Database
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Manage client portfolios, profiles, navigation, and publication states.</p>
                    </div>

                    <!-- Search Form & Status Filters -->
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <!-- Search Box Form -->
                        <form method="GET" action="{{ route('admin.dashboard') }}" class="relative flex items-center">
                            @if(request('status'))
                                <input type="hidden" name="status" value="{{ request('status') }}">
                            @endif
                            <div class="relative w-full sm:w-48 lg:w-56">
                                <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <input type="text" 
                                       name="search" 
                                       id="portfolio-search-input"
                                       value="{{ request('search') }}"
                                       placeholder="Search name, slug, owner..." 
                                       class="w-full py-1.5 pl-8 pr-7 text-xs bg-slate-50 hover:bg-slate-100/70 focus:bg-white text-slate-800 placeholder-slate-400 border border-slate-200 rounded-xl focus:outline-none focus:border-[#D4AF69] focus:ring-1 focus:ring-[#D4AF69] transition-all">
                                @if(request('search'))
                                    <a href="{{ route('admin.dashboard', array_filter(['status' => request('status')])) }}" class="absolute inset-y-0 right-0 pr-2 flex items-center text-slate-400 hover:text-slate-600" title="Clear search">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </form>

                        <!-- Interactive Status Filters -->
                        <div class="flex items-center gap-1 p-1 bg-slate-100/90 rounded-xl text-xs font-semibold self-start sm:self-auto">
                            <a href="{{ route('admin.dashboard', array_filter(['search' => request('search')])) }}" 
                               class="px-2.5 py-1 rounded-lg transition-colors {{ !request('status') ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-800' }}">
                                All ({{ $stats['total'] ?? 0 }})
                            </a>
                            <a href="{{ route('admin.dashboard', array_filter(['status' => 'published', 'search' => request('search')])) }}" 
                               class="px-2.5 py-1 rounded-lg transition-colors {{ request('status') === 'published' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-800' }}">
                                Published ({{ $stats['published'] ?? 0 }})
                            </a>
                            <a href="{{ route('admin.dashboard', array_filter(['status' => 'draft', 'search' => request('search')])) }}" 
                               class="px-2.5 py-1 rounded-lg transition-colors {{ request('status') === 'draft' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-800' }}">
                                Drafts ({{ $stats['draft'] ?? 0 }})
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Responsive Table Wrapper -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/70 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                <th scope="col" class="py-3.5 pl-5 pr-3">Portfolio / Client</th>
                                <th scope="col" class="py-3.5 px-3">Navigation</th>
                                <th scope="col" class="py-3.5 px-3">Status</th>
                                <th scope="col" class="py-3.5 px-3">Created / Admin</th>
                                <th scope="col" class="py-3.5 pl-3 pr-5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="portfolios-table-body" class="divide-y divide-slate-100 font-medium">
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
                                                        {{ $portfolio->profile?->full_name ?? 'No Profile' }}
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
                                    <td class="py-3.5 px-3">
                                        <a href="{{ route('admin.portfolios.nav-items.index', $portfolio) }}" 
                                           class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-slate-50 hover:bg-slate-100 text-slate-700 text-[11px] font-medium border border-slate-200 transition-colors">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $portfolio->nav_items_count > 0 ? 'bg-[#D4AF69]' : 'bg-slate-300' }}"></span>
                                            <span>{{ $portfolio->nav_items_count }} {{ Str::plural('Link', $portfolio->nav_items_count) }}</span>
                                        </a>
                                    </td>
                                    <td class="py-3.5 px-3">
                                        <x-admin.status-badge :status="$portfolio->status" />
                                    </td>
                                    <td class="py-3.5 px-3">
                                        <div class="text-slate-700 font-semibold">{{ $portfolio->created_at->format('M j, Y') }}</div>
                                        <div class="text-[10px] text-slate-400">
                                            {{ $portfolio->created_at->diffForHumans() }} &bull; {{ $portfolio->user?->name ?? 'Admin' }}
                                        </div>
                                    </td>
                                    <td class="py-3.5 pl-3 pr-5 text-right relative">
                                        <div class="inline-flex items-center gap-1">
                                            <a href="{{ url('/' . $portfolio->slug) }}" target="_blank" rel="noopener noreferrer" class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors" title="Live Preview">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
                                            <a href="{{ route('admin.portfolios.nav-items.index', $portfolio) }}" class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors" title="Manage Navigation">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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
                                                    <a href="{{ route('admin.dashboard') }}" class="px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
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

        <!-- Right Column: Recent Activity & System Status (4 cols on lg) -->
        <div class="lg:col-span-4 space-y-6">
            
            <!-- 5. Recent Activity Timeline Section -->
            <div class="rounded-2xl bg-white border border-slate-200/80 p-5 shadow-sm">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-bold text-slate-900 tracking-tight">Recent Activity</h3>
                        <span class="w-2 h-2 rounded-full bg-[#D4AF69] animate-pulse"></span>
                    </div>
                    <a href="#activity" class="text-xs font-semibold text-[#9A7326] hover:underline">Full Audit Log</a>
                </div>

                <!-- Timeline Items -->
                <div class="relative pl-6 space-y-5 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                    @forelse($portfolios->take(5) as $activity)
                        <div class="relative">
                            <div class="absolute -left-[27px] top-1 w-3 h-3 rounded-full {{ $activity->status === 'published' ? 'bg-emerald-500' : 'bg-[#D4AF69]' }} ring-4 ring-white"></div>
                            <p class="text-xs text-slate-800 font-medium leading-relaxed">
                                <strong>{{ $activity->user?->name ?? 'Super Admin' }}</strong> 
                                {{ $activity->status === 'published' ? 'published' : 'created' }} portfolio 
                                <span class="text-[#9A7326] font-semibold">"{{ $activity->name }}"</span>
                            </p>
                            <div class="flex items-center gap-2 mt-1 text-[11px] text-slate-400">
                                <span>{{ $activity->created_at->diffForHumans() }}</span>
                                <span>&bull;</span>
                                <span class="{{ $activity->status === 'published' ? 'text-emerald-600' : 'text-amber-600' }} font-medium capitalize">{{ $activity->status }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="py-4 text-xs text-slate-400 text-center">
                            No recent activity recorded yet.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- 6. System Infrastructure & Health Snapshot -->
            <div class="rounded-2xl bg-[#0F1219] text-white border border-[#1E2330] p-5 shadow-lg relative overflow-hidden">
                <!-- Gold Ambient Gradient -->
                <div class="absolute top-0 right-0 w-36 h-36 bg-[radial-gradient(ellipse_at_top_right,rgba(212,175,105,0.15),transparent_70%)] pointer-events-none" aria-hidden="true"></div>

                <div class="relative z-10 space-y-4">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-[#D4AF69]">System Telemetry</h4>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                            Healthy
                        </span>
                    </div>

                    <div class="space-y-2.5 text-xs">
                        <div class="flex justify-between py-1 border-b border-slate-800/80">
                            <span class="text-slate-400">PHP Version</span>
                            <span class="font-mono font-semibold text-slate-200">{{ PHP_VERSION }} (NTS x64)</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-800/80">
                            <span class="text-slate-400">Laravel Framework</span>
                            <span class="font-mono font-semibold text-slate-200">v{{ Illuminate\Foundation\Application::VERSION }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-800/80">
                            <span class="text-slate-400">Database Engine</span>
                            <span class="font-mono font-semibold text-[#D4AF69]">MySQL 26.7.0</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-800/80">
                            <span class="text-slate-400">Session Storage</span>
                            <span class="font-mono font-semibold text-emerald-400">Database Driver</span>
                        </div>
                    </div>

                    <!-- Storage Bar -->
                    <div class="pt-2">
                        <div class="flex justify-between text-[11px] mb-1.5 font-medium">
                            <span class="text-slate-400">Media Storage</span>
                            <span class="text-slate-200 font-mono">1.24 GB / 10 GB</span>
                        </div>
                        <div class="w-full h-2 bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-[#D4AF69] to-[#F2DAA4] rounded-full w-[12.4%]"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>


@endsection
