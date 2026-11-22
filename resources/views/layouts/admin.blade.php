<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">

    <title>@yield('title', 'Super Admin Dashboard — Portfolio CMS')</title>

    <!-- Google / Bunny Fonts (Instrument Sans) -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Vite Assets -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    @endif

    <style>
        :root {
            --color-gold: #D4AF69;
            --color-gold-light: #F2DAA4;
            --color-gold-dark: #A5803B;
            --color-charcoal-deep: #0B0D12;
            --color-charcoal-sidebar: #0F1219;
            --color-charcoal-card: #141822;
            --color-charcoal-border: #1E2330;
        }

        /* Custom scrollbars */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.35);
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(148, 163, 184, 0.6);
        }

        .gold-gradient-text {
            background: linear-gradient(135deg, #FFF0CF 0%, #D4AF69 50%, #B48D44 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .gold-btn-gradient {
            background: linear-gradient(135deg, #E2BF7D 0%, #D4AF69 45%, #B8934C 100%);
            box-shadow: 0 4px 14px -2px rgba(212, 175, 105, 0.4),
                        inset 0 1px 0 rgba(255, 255, 255, 0.3);
        }
        .gold-btn-gradient:hover {
            background: linear-gradient(135deg, #E8C98E 0%, #DCB874 45%, #C29D55 100%);
            box-shadow: 0 6px 20px 0 rgba(212, 175, 105, 0.55),
                        inset 0 1px 0 rgba(255, 255, 255, 0.4);
        }

        /* Collapsed Sidebar Styles for Desktop */
        body.sidebar-collapsed #admin-sidebar {
            width: 5rem; /* 80px */
        }
        body.sidebar-collapsed #admin-sidebar .sidebar-text,
        body.sidebar-collapsed #admin-sidebar .sidebar-badge,
        body.sidebar-collapsed #admin-sidebar .sidebar-section-title,
        body.sidebar-collapsed #admin-sidebar .sidebar-user-details {
            display: none !important;
        }
        body.sidebar-collapsed #admin-sidebar .sidebar-logo-text {
            display: none !important;
        }
        body.sidebar-collapsed #admin-sidebar .sidebar-link-item {
            justify-content: center;
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }
        body.sidebar-collapsed #main-layout-container {
            margin-left: 5rem;
        }

        @media (max-width: 1023px) {
            #main-layout-container {
                margin-left: 0 !important;
            }
        }

        /* Reduced motion */
        @media (prefers-reduced-motion: reduce) {
            *, ::before, ::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
        }
    </style>

    @stack('styles')
</head>
<body class="h-full bg-slate-50 text-slate-800 antialiased font-sans selection:bg-[#D4AF69] selection:text-[#0B0D12]">

    <!-- ========================================================================= -->
    <!-- Mobile Sidebar Backdrop -->
    <!-- ========================================================================= -->
    <div id="mobile-sidebar-backdrop" 
         class="fixed inset-0 z-40 bg-slate-950/70 backdrop-blur-sm hidden opacity-0 transition-opacity duration-300 lg:hidden"
         aria-hidden="true"></div>

    <!-- ========================================================================= -->
    <!-- Sidebar (Off-canvas on mobile, fixed left on desktop) -->
    <!-- ========================================================================= -->
    <aside id="admin-sidebar" 
           class="fixed top-0 bottom-0 left-0 z-50 flex flex-col w-64 lg:w-72 bg-[#0F1219] text-slate-300 border-r border-[#1E2330] shadow-2xl lg:shadow-none transition-all duration-300 ease-in-out -translate-x-full lg:translate-x-0"
           aria-label="Super Admin Navigation">
        
        <!-- Sidebar Brand / Header -->
        <div class="h-16 flex items-center justify-between px-5 border-b border-[#1E2330] flex-shrink-0 bg-[#0B0D12]/60">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group focus:outline-none focus-visible:ring-2 focus-visible:ring-[#D4AF69] rounded-xl p-1">
                <!-- Monogram Emblem -->
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#E2BF7D] via-[#D4AF69] to-[#9E7B35] flex items-center justify-center shadow-lg shadow-[#D4AF69]/20 flex-shrink-0 border border-white/20 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5 text-[#0B0D12]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                    </svg>
                </div>
                <!-- Brand text -->
                <div class="sidebar-logo-text flex flex-col leading-tight">
                    <span class="font-bold text-base tracking-tight text-white group-hover:text-[#D4AF69] transition-colors">
                        Portfolio<span class="text-[#D4AF69]">CMS</span>
                    </span>
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-[#D4AF69]/80">
                        Super Admin
                    </span>
                </div>
            </a>

            <!-- Mobile Close Button -->
            <button type="button" 
                    id="mobile-sidebar-close" 
                    class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors focus:outline-none"
                    aria-label="Close sidebar">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Sidebar Navigation List -->
        <div class="flex-1 overflow-y-auto px-3.5 py-4 space-y-6">
            
            <!-- Section: Main Menu -->
            <div>
                <p class="sidebar-section-title px-3 mb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                    Core Management
                </p>
                <nav class="space-y-1">
                    <!-- 1. Dashboard -->
                    <x-admin.nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard*')" title="Dashboard" class="sidebar-link-item">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </x-admin.nav-link>

                    <!-- 2. Portfolios -->
                    <x-admin.nav-link href="#portfolios" title="Portfolios" class="sidebar-link-item">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </x-admin.nav-link>

                    <!-- 3. Themes -->
                    <x-admin.nav-link href="#themes" title="Themes" class="sidebar-link-item">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4 5 5 0 015-5h1a1 1 0 011 1v1a1 1 0 001 1h1a1 1 0 001-1v-1a1 1 0 011-1h1a5 5 0 015 5 4 4 0 01-4 4H7zM16 3.13a4 4 0 010 7.75"/>
                        </svg>
                    </x-admin.nav-link>

                    <!-- 4. Profiles -->
                    <x-admin.nav-link href="#profiles" title="Profiles" class="sidebar-link-item">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </x-admin.nav-link>

                    <!-- 5. Sections & Content -->
                    <x-admin.nav-link href="#sections" title="Sections & Content" class="sidebar-link-item">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/>
                        </svg>
                    </x-admin.nav-link>

                    <!-- 6. Media Library -->
                    <x-admin.nav-link href="#media" title="Media Library" class="sidebar-link-item">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </x-admin.nav-link>
                </nav>
            </div>

            <!-- Section: System & Marketing -->
            <div>
                <p class="sidebar-section-title px-3 mb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                    Configuration & Growth
                </p>
                <nav class="space-y-1">
                    <!-- 7. Domains -->
                    <x-admin.nav-link href="#domains" title="Domains" class="sidebar-link-item">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                        </svg>
                    </x-admin.nav-link>

                    <!-- 8. SEO Settings -->
                    <x-admin.nav-link href="#seo" title="SEO Settings" class="sidebar-link-item">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                    </x-admin.nav-link>

                    <!-- 9. Social Links -->
                    <x-admin.nav-link href="#social" title="Social Links" class="sidebar-link-item">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                    </x-admin.nav-link>

                    <!-- 10. Contact Messages -->
                    <x-admin.nav-link href="#messages" title="Contact Messages" badge="3" badgeColor="gold" class="sidebar-link-item">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </x-admin.nav-link>

                    <!-- 11. Activity Logs -->
                    <x-admin.nav-link href="#activity" title="Activity Logs" class="sidebar-link-item">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </x-admin.nav-link>

                    <!-- 12. Settings -->
                    <x-admin.nav-link href="#settings" title="Settings" class="sidebar-link-item">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </x-admin.nav-link>
                </nav>
            </div>

        </div>

        <!-- Sidebar User Footer -->
        <div class="p-3.5 border-t border-[#1E2330] bg-[#0B0D12]/70 flex-shrink-0">
            <div class="flex items-center justify-between gap-3 p-2 rounded-xl bg-slate-900/80 border border-slate-800/80">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="relative flex-shrink-0">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-[#A5803B] to-[#D4AF69] text-[#0B0D12] font-bold text-xs flex items-center justify-center border border-white/20 shadow-sm">
                            SA
                        </div>
                        <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-emerald-500 rounded-full ring-2 ring-[#0B0D12]" title="Super Admin Online"></span>
                    </div>
                    <div class="sidebar-user-details min-w-0 flex-1">
                        <p class="text-xs font-semibold text-slate-200 truncate">
                            {{ auth()->user()->name ?? 'Super Admin' }}
                        </p>
                        <p class="text-[11px] text-[#D4AF69] font-medium truncate">
                            Super Admin Console
                        </p>
                    </div>
                </div>

                <!-- Fast Logout Button -->
                <button type="button" 
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                        class="p-1.5 text-slate-400 hover:text-rose-400 hover:bg-slate-800 rounded-lg transition-colors focus:outline-none" 
                        title="Sign Out">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </div>
        </div>

    </aside>

    <!-- ========================================================================= -->
    <!-- Main Content Layout (Topnav + Page Content + Footer) -->
    <!-- ========================================================================= -->
    <div id="main-layout-container" class="lg:ml-72 flex flex-col min-h-screen transition-all duration-300">
        
        <!-- Top Navigation Bar -->
        <header class="sticky top-0 z-30 h-16 bg-white/80 backdrop-blur-md border-b border-slate-200/80 px-4 sm:px-6 lg:px-8 flex items-center justify-between transition-colors">
            
            <!-- Left: Toggle & Title -->
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                <!-- Mobile Drawer Toggle -->
                <button type="button" 
                        id="mobile-sidebar-toggle"
                        class="lg:hidden p-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#D4AF69]"
                        aria-label="Open sidebar menu">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <!-- Desktop Sidebar Collapse Toggle -->
                <button type="button" 
                        id="desktop-sidebar-toggle"
                        class="hidden lg:flex p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition-colors focus:outline-none focus:ring-2 focus:ring-[#D4AF69]"
                        title="Toggle Sidebar Collapse">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h8m-8 6h16"/>
                    </svg>
                </button>

                <!-- Title & Breadcrumb -->
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <h1 class="text-lg sm:text-xl font-bold tracking-tight text-slate-900 truncate">
                            @yield('page_title', 'Dashboard')
                        </h1>
                        <span class="hidden sm:inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#D4AF69]/15 text-[#9A7326] border border-[#D4AF69]/30">
                            Enterprise
                        </span>
                    </div>
                </div>
            </div>

            <!-- Right: Search, Live Site, Notifications, Profile Menu -->
            <div class="flex items-center gap-2 sm:gap-3">
                
                <!-- Quick Search Input (Ctrl+K) -->
                <div class="relative hidden md:block">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" 
                           id="global-search-input"
                           placeholder="Search portfolios, settings..." 
                           class="w-56 lg:w-64 pl-9 pr-12 py-1.5 text-xs bg-slate-100/80 hover:bg-slate-100 focus:bg-white text-slate-800 placeholder-slate-400 border border-slate-200 rounded-xl focus:outline-none focus:border-[#D4AF69] focus:ring-2 focus:ring-[#D4AF69]/20 transition-all">
                    <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none">
                        <kbd class="px-1.5 py-0.5 text-[10px] font-semibold text-slate-400 bg-white border border-slate-200 rounded shadow-xs">
                            ⌘K
                        </kbd>
                    </div>
                </div>

                <!-- View Live Site Link -->
                <a href="{{ url('/') }}" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 border border-slate-200 rounded-xl transition-colors focus:outline-none focus:ring-2 focus:ring-[#D4AF69]"
                   title="Preview Client Website in new tab">
                    <span>Live Site</span>
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>

                <!-- Notification Dropdown Toggle -->
                <div class="relative">
                    <button type="button" 
                            id="notifications-btn"
                            class="relative p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition-colors focus:outline-none focus:ring-2 focus:ring-[#D4AF69]"
                            aria-expanded="false" 
                            aria-haspopup="true"
                            aria-label="View notifications">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <!-- Gold Notification Badge -->
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-[#D4AF69] shadow-[0_0_6px_#D4AF69] ring-2 ring-white animate-pulse"></span>
                    </button>

                    <!-- Notifications Dropdown Panel -->
                    <div id="notifications-dropdown" 
                         class="hidden absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-2xl shadow-xl border border-slate-200/90 py-2 z-50 transition-all duration-200">
                        <div class="px-4 py-2.5 border-b border-slate-100 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Notifications</h3>
                                <span class="px-1.5 py-0.5 text-[10px] font-semibold bg-[#D4AF69]/20 text-[#9A7326] rounded-full">2 new</span>
                            </div>
                            <button type="button" class="text-[11px] font-medium text-[#A5803B] hover:text-[#835E1C]">Mark all read</button>
                        </div>
                        <div class="divide-y divide-slate-100 max-h-72 overflow-y-auto">
                            <!-- Notification item 1 -->
                            <div class="p-3.5 hover:bg-slate-50 transition-colors flex gap-3">
                                <div class="w-8 h-8 rounded-lg bg-[#D4AF69]/15 text-[#9A7326] flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-slate-800">New inquiry received from <strong>Sarah Jenkins</strong></p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">2 minutes ago &bull; Contact form</p>
                                </div>
                            </div>
                            <!-- Notification item 2 -->
                            <div class="p-3.5 hover:bg-slate-50 transition-colors flex gap-3">
                                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-slate-800">Portfolio <strong>"Apex Architecture"</strong> successfully published</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">45 minutes ago &bull; Public</p>
                                </div>
                            </div>
                        </div>
                        <div class="px-4 py-2 border-t border-slate-100 bg-slate-50/50 text-center">
                            <a href="#notifications" class="text-xs font-medium text-[#9A7326] hover:underline">View all notification history</a>
                        </div>
                    </div>
                </div>

                <!-- Admin Profile Dropdown -->
                <div class="relative">
                    <button type="button" 
                            id="user-menu-btn"
                            class="flex items-center gap-2 p-1 rounded-xl hover:bg-slate-100 transition-colors focus:outline-none focus:ring-2 focus:ring-[#D4AF69]"
                            aria-expanded="false" 
                            aria-haspopup="true"
                            aria-label="User account menu">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-[#0F1219] to-[#252C3C] text-[#D4AF69] font-bold text-xs flex items-center justify-center border border-[#D4AF69]/40 shadow-xs">
                            SA
                        </div>
                        <svg class="w-4 h-4 text-slate-400 hidden sm:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <!-- User Dropdown Panel -->
                    <div id="user-menu-dropdown" 
                         class="hidden absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-slate-200/90 py-2 z-50 transition-all duration-200">
                        <div class="px-4 py-2.5 border-b border-slate-100">
                            <p class="text-xs font-bold text-slate-900 truncate">{{ auth()->user()->name ?? 'Super Admin' }}</p>
                            <p class="text-[11px] text-slate-500 truncate">{{ auth()->user()->email ?? 'admin@portfoliocms.test' }}</p>
                            <span class="inline-block mt-1.5 px-2 py-0.5 text-[10px] font-semibold bg-[#D4AF69]/15 text-[#9A7326] rounded-md">
                                Super Administrator
                            </span>
                        </div>

                        <div class="py-1">
                            <a href="#profile" class="flex items-center gap-2.5 px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-900">
                                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Admin Profile
                            </a>
                            <a href="#settings" class="flex items-center gap-2.5 px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-900">
                                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                System Settings
                            </a>
                        </div>

                        <div class="border-t border-slate-100 py-1">
                            <button type="button" 
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                    class="w-full flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 text-left transition-colors">
                                <svg class="w-4 h-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Sign Out
                            </button>
                        </div>
                    </div>
                </div>

            </div>

        </header>

        <!-- Main Page Content Slot -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto">
            @yield('content')
        </main>

        <!-- Global Dashboard Footer -->
        <footer class="mt-auto py-5 px-4 sm:px-6 lg:px-8 border-t border-slate-200/80 bg-white/50 text-xs text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#D4AF69]"></span>
                <span class="font-medium text-slate-700">Portfolio CMS Enterprise Edition</span>
                <span>&bull;</span>
                <span>Laravel v{{ Illuminate\Foundation\Application::VERSION }}</span>
            </div>
            <div>
                &copy; {{ date('Y') }} Portfolio CMS. Protected Super Admin Environment.
            </div>
        </footer>

    </div>

    <!-- Hidden Super Admin Logout Form -->
    <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="hidden">
        @csrf
    </form>

    <!-- ========================================================================= -->
    <!-- Frontend Interactions Script -->
    <!-- ========================================================================= -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Elements
            const mobileSidebar = document.getElementById('admin-sidebar');
            const mobileBackdrop = document.getElementById('mobile-sidebar-backdrop');
            const mobileToggle = document.getElementById('mobile-sidebar-toggle');
            const mobileClose = document.getElementById('mobile-sidebar-close');
            const desktopToggle = document.getElementById('desktop-sidebar-toggle');
            const notificationsBtn = document.getElementById('notifications-btn');
            const notificationsDropdown = document.getElementById('notifications-dropdown');
            const userMenuBtn = document.getElementById('user-menu-btn');
            const userMenuDropdown = document.getElementById('user-menu-dropdown');
            const globalSearch = document.getElementById('global-search-input');

            // --- 1. Mobile Drawer Functions ---
            function openMobileSidebar() {
                mobileSidebar.classList.remove('-translate-x-full');
                mobileBackdrop.classList.remove('hidden');
                setTimeout(() => mobileBackdrop.classList.remove('opacity-0'), 10);
                document.body.classList.add('overflow-hidden');
            }

            function closeMobileSidebar() {
                mobileSidebar.classList.add('-translate-x-full');
                mobileBackdrop.classList.add('opacity-0');
                setTimeout(() => {
                    mobileBackdrop.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                }, 300);
            }

            if (mobileToggle) mobileToggle.addEventListener('click', openMobileSidebar);
            if (mobileClose) mobileClose.addEventListener('click', closeMobileSidebar);
            if (mobileBackdrop) mobileBackdrop.addEventListener('click', closeMobileSidebar);

            // --- 2. Desktop Sidebar Collapse ---
            if (localStorage.getItem('admin_sidebar_collapsed') === 'true') {
                document.body.classList.add('sidebar-collapsed');
            }

            if (desktopToggle) {
                desktopToggle.addEventListener('click', () => {
                    document.body.classList.toggle('sidebar-collapsed');
                    const isCollapsed = document.body.classList.contains('sidebar-collapsed');
                    localStorage.setItem('admin_sidebar_collapsed', isCollapsed);
                });
            }

            // --- 3. Dropdowns (Notifications & User Menu) ---
            function toggleDropdown(btn, dropdown) {
                const isOpen = !dropdown.classList.contains('hidden');
                closeAllDropdowns();
                if (!isOpen) {
                    dropdown.classList.remove('hidden');
                    btn.setAttribute('aria-expanded', 'true');
                }
            }

            function closeAllDropdowns() {
                if (notificationsDropdown) notificationsDropdown.classList.add('hidden');
                if (userMenuDropdown) userMenuDropdown.classList.add('hidden');
                if (notificationsBtn) notificationsBtn.setAttribute('aria-expanded', 'false');
                if (userMenuBtn) userMenuBtn.setAttribute('aria-expanded', 'false');
            }

            if (notificationsBtn && notificationsDropdown) {
                notificationsBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    toggleDropdown(notificationsBtn, notificationsDropdown);
                });
            }

            if (userMenuBtn && userMenuDropdown) {
                userMenuBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    toggleDropdown(userMenuBtn, userMenuDropdown);
                });
            }

            document.addEventListener('click', (e) => {
                if (!e.target.closest('#notifications-dropdown') && !e.target.closest('#notifications-btn') &&
                    !e.target.closest('#user-menu-dropdown') && !e.target.closest('#user-menu-btn')) {
                    closeAllDropdowns();
                }
            });

            // --- 4. Keyboard Shortcuts (Escape & Ctrl/Cmd + K) ---
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    closeAllDropdowns();
                    closeMobileSidebar();
                }
                if ((e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K')) {
                    e.preventDefault();
                    if (globalSearch) {
                        globalSearch.focus();
                        globalSearch.select();
                    }
                }
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
