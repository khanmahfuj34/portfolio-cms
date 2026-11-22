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
                <a href="#new-portfolio" class="gold-btn-gradient inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-[#0B0D12] transition-transform active:scale-95 focus:outline-none focus:ring-2 focus:ring-[#D4AF69]">
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
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 sm:gap-5">
            <!-- 1. Total Portfolios -->
            <x-admin.stat-card 
                title="Total Portfolios" 
                value="24" 
                change="+12% this mo" 
                changeType="up" 
                subtitle="16 Active Clients">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </x-admin.stat-card>

            <!-- 2. Published Portfolios -->
            <x-admin.stat-card 
                title="Published Portfolios" 
                value="18" 
                change="75% Live" 
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
                value="6" 
                change="In Progress" 
                changeType="neutral" 
                subtitle="4 Ready for Review"
                iconBg="bg-amber-50 text-amber-600 border-amber-200/50">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </x-admin.stat-card>

            <!-- 4. Total Themes -->
            <x-admin.stat-card 
                title="Total Themes" 
                value="8" 
                change="3 Custom" 
                changeType="gold" 
                subtitle="5 Core Layouts">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4 5 5 0 015-5h1a1 1 0 011 1v1a1 1 0 001 1h1a1 1 0 001-1v-1a1 1 0 011-1h1a5 5 0 015 5 4 4 0 01-4 4H7zM16 3.13a4 4 0 010 7.75"/>
                </svg>
            </x-admin.stat-card>

            <!-- 5. Contact Messages -->
            <x-admin.stat-card 
                title="Contact Messages" 
                value="42" 
                change="3 Unread" 
                changeType="gold" 
                subtitle="Latest 12m ago">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
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
            <a href="#new-portfolio" class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:border-[#D4AF69] hover:bg-[#D4AF69]/5 transition-all group">
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
            
            <div class="rounded-2xl bg-white border border-slate-200/80 shadow-sm overflow-hidden">
                
                <!-- Table Header & Filter Tabs -->
                <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 tracking-tight">Recent Portfolios</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Manage live portfolios, staging drafts, and publication states.</p>
                    </div>

                    <!-- Interactive Status Filters -->
                    <div class="flex items-center gap-1.5 p-1 bg-slate-100/90 rounded-xl self-start sm:self-auto text-xs font-semibold">
                        <button type="button" 
                                class="portfolio-filter-btn px-3 py-1 rounded-lg transition-colors bg-white text-slate-900 shadow-xs" 
                                data-filter="all">
                            All (24)
                        </button>
                        <button type="button" 
                                class="portfolio-filter-btn px-3 py-1 rounded-lg transition-colors text-slate-500 hover:text-slate-800" 
                                data-filter="published">
                            Published (18)
                        </button>
                        <button type="button" 
                                class="portfolio-filter-btn px-3 py-1 rounded-lg transition-colors text-slate-500 hover:text-slate-800" 
                                data-filter="draft">
                            Drafts (6)
                        </button>
                    </div>
                </div>

                <!-- Responsive Table Wrapper -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/70 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                <th scope="col" class="py-3.5 pl-5 pr-3">Portfolio / Client</th>
                                <th scope="col" class="py-3.5 px-3">Theme</th>
                                <th scope="col" class="py-3.5 px-3">Status</th>
                                <th scope="col" class="py-3.5 px-3">Visitors</th>
                                <th scope="col" class="py-3.5 px-3">Updated</th>
                                <th scope="col" class="py-3.5 pl-3 pr-5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="portfolios-table-body" class="divide-y divide-slate-100 font-medium">
                            
                            {{-- Static Placeholder Data: Portfolio 1 --}}
                            <tr class="portfolio-row hover:bg-slate-50/80 transition-colors" data-status="published">
                                <td class="py-3.5 pl-5 pr-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-[#1E2330] to-[#2E364A] text-[#D4AF69] font-bold text-xs flex items-center justify-center flex-shrink-0 border border-[#D4AF69]/20 shadow-xs">
                                            AN
                                        </div>
                                        <div class="min-w-0">
                                            <a href="#view-alex" class="font-bold text-slate-900 hover:text-[#9A7326] transition-colors truncate block">
                                                Alex Morgan &bull; Architectural Noir
                                            </a>
                                            <div class="flex items-center gap-2 text-[11px] text-slate-400">
                                                <span class="truncate">alexmorgan.design</span>
                                                <span>&bull;</span>
                                                <span class="text-slate-500">Architecture</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-3">
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[11px] font-medium border border-slate-200">
                                        <span class="w-2 h-2 rounded-full bg-[#D4AF69]"></span>
                                        Luxe Minimalist
                                    </span>
                                </td>
                                <td class="py-3.5 px-3">
                                    <x-admin.status-badge status="published" />
                                </td>
                                <td class="py-3.5 px-3">
                                    <div class="text-slate-700 font-semibold">1,842</div>
                                    <div class="text-[10px] text-emerald-600 font-medium">+18% this wk</div>
                                </td>
                                <td class="py-3.5 px-3 text-slate-500 text-[11px]">
                                    15 mins ago
                                </td>
                                <td class="py-3.5 pl-3 pr-5 text-right relative">
                                    <div class="inline-flex items-center gap-1">
                                        <a href="#preview" class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors" title="Preview Live">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        <a href="#edit" class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors" title="Edit Portfolio">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        <button type="button" class="action-dropdown-btn p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors" title="More options">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            {{-- Static Placeholder Data: Portfolio 2 --}}
                            <tr class="portfolio-row hover:bg-slate-50/80 transition-colors" data-status="published">
                                <td class="py-3.5 pl-5 pr-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-900 to-indigo-700 text-indigo-200 font-bold text-xs flex items-center justify-center flex-shrink-0 shadow-xs">
                                            SN
                                        </div>
                                        <div class="min-w-0">
                                            <a href="#view-dev" class="font-bold text-slate-900 hover:text-[#9A7326] transition-colors truncate block">
                                                DevNoir &bull; Fullstack Engineer
                                            </a>
                                            <div class="flex items-center gap-2 text-[11px] text-slate-400">
                                                <span class="truncate">devnoir.portfolio.me</span>
                                                <span>&bull;</span>
                                                <span class="text-slate-500">Developer</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-3">
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[11px] font-medium border border-slate-200">
                                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                        DevNoir Dark
                                    </span>
                                </td>
                                <td class="py-3.5 px-3">
                                    <x-admin.status-badge status="published" />
                                </td>
                                <td class="py-3.5 px-3">
                                    <div class="text-slate-700 font-semibold">3,290</div>
                                    <div class="text-[10px] text-emerald-600 font-medium">+24% this wk</div>
                                </td>
                                <td class="py-3.5 px-3 text-slate-500 text-[11px]">
                                    3 hours ago
                                </td>
                                <td class="py-3.5 pl-3 pr-5 text-right relative">
                                    <div class="inline-flex items-center gap-1">
                                        <a href="#preview" class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors" title="Preview Live">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        <a href="#edit" class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors" title="Edit Portfolio">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        <button type="button" class="action-dropdown-btn p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors" title="More options">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            {{-- Static Placeholder Data: Portfolio 3 --}}
                            <tr class="portfolio-row hover:bg-slate-50/80 transition-colors" data-status="draft">
                                <td class="py-3.5 pl-5 pr-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-700 to-amber-500 text-amber-100 font-bold text-xs flex items-center justify-center flex-shrink-0 shadow-xs">
                                            CS
                                        </div>
                                        <div class="min-w-0">
                                            <a href="#view-creative" class="font-bold text-slate-900 hover:text-[#9A7326] transition-colors truncate block">
                                                Creative Studio &bull; Visual Identity
                                            </a>
                                            <div class="flex items-center gap-2 text-[11px] text-slate-400">
                                                <span class="truncate">creativestudio.cms.live</span>
                                                <span>&bull;</span>
                                                <span class="text-slate-500">Creative Agency</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-3">
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[11px] font-medium border border-slate-200">
                                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                        Editorial Studio
                                    </span>
                                </td>
                                <td class="py-3.5 px-3">
                                    <x-admin.status-badge status="draft" />
                                </td>
                                <td class="py-3.5 px-3">
                                    <div class="text-slate-700 font-semibold">140</div>
                                    <div class="text-[10px] text-slate-400 font-medium">Staging only</div>
                                </td>
                                <td class="py-3.5 px-3 text-slate-500 text-[11px]">
                                    Yesterday
                                </td>
                                <td class="py-3.5 pl-3 pr-5 text-right relative">
                                    <div class="inline-flex items-center gap-1">
                                        <a href="#preview" class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors" title="Preview Live">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        <a href="#edit" class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors" title="Edit Portfolio">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        <button type="button" class="action-dropdown-btn p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors" title="More options">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            {{-- Static Placeholder Data: Portfolio 4 --}}
                            <tr class="portfolio-row hover:bg-slate-50/80 transition-colors" data-status="published">
                                <td class="py-3.5 pl-5 pr-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-900 to-emerald-700 text-emerald-200 font-bold text-xs flex items-center justify-center flex-shrink-0 shadow-xs">
                                            ER
                                        </div>
                                        <div class="min-w-0">
                                            <a href="#view-elena" class="font-bold text-slate-900 hover:text-[#9A7326] transition-colors truncate block">
                                                Elena Rostova &bull; Product Design
                                            </a>
                                            <div class="flex items-center gap-2 text-[11px] text-slate-400">
                                                <span class="truncate">elenarostova.com</span>
                                                <span>&bull;</span>
                                                <span class="text-slate-500">UI/UX</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-3">
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[11px] font-medium border border-slate-200">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        Luxe Minimalist
                                    </span>
                                </td>
                                <td class="py-3.5 px-3">
                                    <x-admin.status-badge status="published" />
                                </td>
                                <td class="py-3.5 px-3">
                                    <div class="text-slate-700 font-semibold">912</div>
                                    <div class="text-[10px] text-emerald-600 font-medium">+6% this wk</div>
                                </td>
                                <td class="py-3.5 px-3 text-slate-500 text-[11px]">
                                    2 days ago
                                </td>
                                <td class="py-3.5 pl-3 pr-5 text-right relative">
                                    <div class="inline-flex items-center gap-1">
                                        <a href="#preview" class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors" title="Preview Live">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        <a href="#edit" class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors" title="Edit Portfolio">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        <button type="button" class="action-dropdown-btn p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors" title="More options">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>

                <!-- Table Pagination Preview -->
                <div class="p-4 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
                    <div>
                        Showing <span class="font-bold text-slate-700">1</span> to <span class="font-bold text-slate-700">4</span> of <span class="font-bold text-slate-700">24</span> portfolios
                    </div>
                    <div class="inline-flex items-center gap-1">
                        <button type="button" disabled class="px-2.5 py-1 text-slate-400 bg-white border border-slate-200 rounded-lg cursor-not-allowed">Previous</button>
                        <button type="button" class="px-2.5 py-1 text-white bg-[#0F1219] rounded-lg font-bold">1</button>
                        <button type="button" class="px-2.5 py-1 text-slate-600 hover:bg-slate-100 rounded-lg">2</button>
                        <button type="button" class="px-2.5 py-1 text-slate-600 hover:bg-slate-100 rounded-lg">3</button>
                        <span class="px-1 text-slate-400">&hellip;</span>
                        <button type="button" class="px-2.5 py-1 text-slate-600 hover:bg-slate-100 rounded-lg">6</button>
                        <button type="button" class="px-2.5 py-1 text-slate-600 hover:bg-slate-100 bg-white border border-slate-200 rounded-lg">Next</button>
                    </div>
                </div>

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
                    
                    <!-- Activity 1 -->
                    <div class="relative">
                        <div class="absolute -left-[27px] top-1 w-3 h-3 rounded-full bg-[#D4AF69] ring-4 ring-white"></div>
                        <p class="text-xs text-slate-800 font-medium leading-relaxed">
                            <strong>Alex Morgan</strong> published portfolio <span class="text-[#9A7326] font-semibold">"Architectural Noir"</span>
                        </p>
                        <div class="flex items-center gap-2 mt-1 text-[11px] text-slate-400">
                            <span>15 mins ago</span>
                            <span>&bull;</span>
                            <span class="text-emerald-600 font-medium">Public</span>
                        </div>
                    </div>

                    <!-- Activity 2 -->
                    <div class="relative">
                        <div class="absolute -left-[27px] top-1 w-3 h-3 rounded-full bg-indigo-500 ring-4 ring-white"></div>
                        <p class="text-xs text-slate-800 font-medium leading-relaxed">
                            New contact submission from <strong>David Chen</strong> on <em>Tech & Dev Brand</em>
                        </p>
                        <div class="flex items-center gap-2 mt-1 text-[11px] text-slate-400">
                            <span>42 mins ago</span>
                            <span>&bull;</span>
                            <span class="text-indigo-600 font-medium">Inquiry</span>
                        </div>
                    </div>

                    <!-- Activity 3 -->
                    <div class="relative">
                        <div class="absolute -left-[27px] top-1 w-3 h-3 rounded-full bg-amber-500 ring-4 ring-white"></div>
                        <p class="text-xs text-slate-800 font-medium leading-relaxed">
                            Theme <strong>"Luxe Minimalist"</strong> typography settings updated
                        </p>
                        <div class="flex items-center gap-2 mt-1 text-[11px] text-slate-400">
                            <span>2 hours ago</span>
                            <span>&bull;</span>
                            <span class="text-amber-600 font-medium">Theme Studio</span>
                        </div>
                    </div>

                    <!-- Activity 4 -->
                    <div class="relative">
                        <div class="absolute -left-[27px] top-1 w-3 h-3 rounded-full bg-emerald-500 ring-4 ring-white"></div>
                        <p class="text-xs text-slate-800 font-medium leading-relaxed">
                            SSL certificate provisioned for domain <span class="font-mono text-[11px] text-slate-600">alexmorgan.design</span>
                        </p>
                        <div class="flex items-center gap-2 mt-1 text-[11px] text-slate-400">
                            <span>5 hours ago</span>
                            <span>&bull;</span>
                            <span class="text-slate-500">Domains</span>
                        </div>
                    </div>

                    <!-- Activity 5 -->
                    <div class="relative">
                        <div class="absolute -left-[27px] top-1 w-3 h-3 rounded-full bg-slate-400 ring-4 ring-white"></div>
                        <p class="text-xs text-slate-800 font-medium leading-relaxed">
                            Automated database snapshot & media backup completed successfully
                        </p>
                        <div class="flex items-center gap-2 mt-1 text-[11px] text-slate-400">
                            <span>1 day ago</span>
                            <span>&bull;</span>
                            <span class="text-slate-500">System</span>
                        </div>
                    </div>

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

<!-- Interactive Client-side Script for Table Filtering -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const filterBtns = document.querySelectorAll('.portfolio-filter-btn');
        const rows = document.querySelectorAll('#portfolios-table-body .portfolio-row');

        filterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const filter = btn.getAttribute('data-filter');

                // Update active button state
                filterBtns.forEach(b => {
                    b.classList.remove('bg-white', 'text-slate-900', 'shadow-xs');
                    b.classList.add('text-slate-500');
                });
                btn.classList.add('bg-white', 'text-slate-900', 'shadow-xs');
                btn.classList.remove('text-slate-500');

                // Filter table rows
                rows.forEach(row => {
                    const status = row.getAttribute('data-status');
                    if (filter === 'all' || status === filter) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });
    });
</script>
@endsection
