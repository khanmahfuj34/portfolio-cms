<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-paper">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">

    <title>@yield('title', 'Super Admin Dashboard — Portfolio CMS')</title>

    <!-- Google Fonts: Arimo (UI) + Urbanist (display) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Arimo:ital,wght@0,400..700;1,400..700&family=Urbanist:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

    <!-- Vite Assets -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    @endif

    <style>
        /* Quiet scrollbars */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(139, 135, 126, 0.35);
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(139, 135, 126, 0.6);
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
<body class="h-full bg-paper text-ink antialiased font-sans selection:bg-brass/30 selection:text-ink">

    <!-- ========================================================================= -->
    <!-- Mobile Sidebar Backdrop -->
    <!-- ========================================================================= -->
    <div id="mobile-sidebar-backdrop"
         class="fixed inset-0 z-40 bg-[#14130F]/70 backdrop-blur-sm hidden opacity-0 transition-opacity duration-200 lg:hidden"
         aria-hidden="true"></div>

    <!-- ========================================================================= -->
    <!-- Sidebar (Off-canvas on mobile, fixed left on desktop) -->
    <!-- ========================================================================= -->
    <aside id="admin-sidebar"
           class="fixed top-0 bottom-0 left-0 z-50 flex flex-col w-64 lg:w-72 bg-[#14130F] text-[#C9C3B6] border-r border-[#2A261C] transition-all duration-200 ease-out -translate-x-full lg:translate-x-0"
           aria-label="Super Admin Navigation">

        <!-- Sidebar Brand / Header -->
        <div class="h-16 flex items-center justify-between px-5 border-b border-[#2A261C] flex-shrink-0">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group focus:outline-none focus-visible:ring-2 focus-visible:ring-brass rounded-md p-1">
                <!-- Monogram -->
                <div class="w-8 h-8 rounded-md bg-brass flex items-center justify-center flex-shrink-0 text-[#14130F]">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                    </svg>
                </div>
                <!-- Brand text -->
                <div class="sidebar-logo-text flex flex-col leading-tight">
                    <span class="font-medium text-sm text-[#F3F1EC] group-hover:text-brass transition-colors">
                        Portfolio CMS
                    </span>
                    <span class="text-[11px] text-[#8C877A]">
                        Admin workspace
                    </span>
                </div>
            </a>

            <!-- Mobile Close Button -->
            <button type="button"
                    id="mobile-sidebar-close"
                    class="lg:hidden p-1.5 rounded-md text-[#8C877A] hover:text-white hover:bg-[#221F17] transition-colors focus:outline-none"
                    aria-label="Close sidebar">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Sidebar Navigation List -->
        <div class="flex-1 overflow-y-auto px-3 py-4 space-y-6">

            <!-- Section: Main Menu -->
            <div>
                <p class="sidebar-section-title px-3 mb-2 text-[11px] font-medium text-[#7C7668]">
                    Core management
                </p>
                <nav class="space-y-0.5">
                    <!-- 1. Dashboard -->
                    <x-admin.nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard*')" title="Dashboard" class="sidebar-link-item">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </x-admin.nav-link>

                    <!-- 2. Portfolios -->
                    <x-admin.nav-link :href="route('admin.portfolios.index')" :active="request()->routeIs('admin.portfolios.*')" title="Portfolios" class="sidebar-link-item">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </x-admin.nav-link>

                    <!-- 3. Themes -->
                    <x-admin.nav-link href="#themes" title="Themes" class="sidebar-link-item">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4 5 5 0 015-5h1a1 1 0 011 1v1a1 1 0 001 1h1a1 1 0 001-1v-1a1 1 0 011-1h1a5 5 0 015 5 4 4 0 01-4 4H7zM16 3.13a4 4 0 010 7.75"/>
                        </svg>
                    </x-admin.nav-link>

                    <!-- 4. Profiles -->
                    <x-admin.nav-link :href="route('admin.profiles.index')" :active="request()->routeIs('admin.profiles.*')" title="Profiles" class="sidebar-link-item">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </x-admin.nav-link>

                    <!-- 5. Sections & Content -->
                    <x-admin.nav-link href="#sections" title="Sections & Content" class="sidebar-link-item">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/>
                        </svg>
                    </x-admin.nav-link>

                    <!-- 6. Media Library -->
                    <x-admin.nav-link href="#media" title="Media Library" class="sidebar-link-item">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </x-admin.nav-link>
                </nav>
            </div>

        </div>

        <!-- Sidebar User Footer -->
        <div class="p-3 border-t border-[#2A261C] flex-shrink-0">
            <div class="flex items-center justify-between gap-3 p-2 rounded-lg bg-[#191712] border border-[#2A261C]">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="relative flex-shrink-0">
                        <div class="w-8 h-8 rounded-md bg-[#221F17] text-brass font-medium text-xs flex items-center justify-center border border-[#3A3527]">
                            SA
                        </div>
                        <span class="absolute -bottom-0.5 -right-0.5 w-2 h-2 bg-[#7FA46A] rounded-full ring-2 ring-[#14130F]" title="Online"></span>
                    </div>
                    <div class="sidebar-user-details min-w-0 flex-1">
                        <p class="text-xs font-medium text-[#EDE9DF] truncate">
                            {{ auth()->user()->name ?? 'Super Admin' }}
                        </p>
                        <p class="text-[11px] text-[#8C877A] truncate">
                            Super admin
                        </p>
                    </div>
                </div>

                <!-- Fast Logout Button -->
                <button type="button"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                        class="p-1.5 text-[#8C877A] hover:text-[#E08A7A] hover:bg-[#221F17] rounded-md transition-colors focus:outline-none"
                        title="Sign out">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </div>
        </div>

    </aside>

    <!-- ========================================================================= -->
    <!-- Main Content Layout (Topnav + Page Content + Footer) -->
    <!-- ========================================================================= -->
    <div id="main-layout-container" class="lg:ml-72 flex flex-col min-h-screen transition-all duration-200">

        <!-- Top Navigation Bar -->
        <header class="sticky top-0 z-30 h-16 bg-paper/90 backdrop-blur-md border-b border-line px-4 sm:px-6 lg:px-8 flex items-center justify-between transition-colors">

            <!-- Left: Toggle & Title -->
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                <!-- Mobile Drawer Toggle -->
                <button type="button"
                        id="mobile-sidebar-toggle"
                        class="lg:hidden p-2 text-ink-soft hover:text-ink hover:bg-paper-sunk rounded-md focus:outline-none focus-visible:ring-2 focus-visible:ring-brass"
                        aria-label="Open sidebar menu">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <!-- Desktop Sidebar Collapse Toggle -->
                <button type="button"
                        id="desktop-sidebar-toggle"
                        class="hidden lg:flex p-2 text-ink-mute hover:text-ink hover:bg-paper-sunk rounded-md transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-brass"
                        title="Toggle sidebar">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h8m-8 6h16"/>
                    </svg>
                </button>

                <!-- Title -->
                <div class="min-w-0">
                    <h1 class="flex items-center gap-2.5 font-display text-[26px] leading-none text-ink truncate">
                        <span class="w-1 h-4 bg-brass rounded-[1px] shrink-0" aria-hidden="true"></span>
                        <span class="truncate">@yield('page_title', 'Dashboard')</span>
                    </h1>
                </div>
            </div>

            <!-- Right: Search, Live Site, Notifications, Profile Menu -->
            <div class="flex items-center gap-2 sm:gap-3">

                <!-- Quick Search Input (Ctrl+K) -->
                <div class="relative hidden md:block">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-ink-mute">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text"
                           id="global-search-input"
                           placeholder="Search"
                           class="w-56 lg:w-64 pl-9 pr-12 py-1.5 text-xs bg-surface hover:border-line-strong focus:border-brass text-ink placeholder-ink-mute border border-line rounded-lg focus:outline-none focus:ring-2 focus:ring-brass/20 transition-colors">
                    <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none">
                        <kbd class="px-1.5 py-0.5 text-[10px] font-medium text-ink-mute bg-paper border border-line rounded">
                            ⌘K
                        </kbd>
                    </div>
                </div>

                <!-- View Live Site Link -->
                <a href="{{ url('/') }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-ink-soft hover:text-ink hover:bg-paper-sunk border border-line rounded-lg transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-brass"
                   title="Open the public site">
                    <span>Live site</span>
                    <svg class="w-3.5 h-3.5 text-ink-mute" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>

                <!-- Notification Dropdown Toggle -->
                <div class="relative">
                    <button type="button"
                            id="notifications-btn"
                            class="relative p-2 text-ink-mute hover:text-ink hover:bg-paper-sunk rounded-md transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-brass"
                            aria-expanded="false"
                            aria-haspopup="true"
                            aria-label="View notifications">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <!-- Brass presence dot, static -->
                        <span class="absolute top-1.5 right-1.5 w-1.5 h-1.5 rounded-full bg-brass ring-2 ring-paper"></span>
                    </button>

                    <!-- Notifications Dropdown Panel -->
                    <div id="notifications-dropdown"
                         class="hidden absolute right-0 mt-2 w-80 sm:w-96 bg-surface rounded-xl shadow-sm border border-line py-2 z-50 transition-all duration-150">
                        <div class="px-4 py-2.5 border-b border-line flex items-center justify-between">
                            <h3 class="text-sm font-medium text-ink">Notifications</h3>
                            <button type="button" class="text-[11px] font-medium text-brass-ink hover:text-ink">Mark all read</button>
                        </div>
                        <div class="divide-y divide-line max-h-72 overflow-y-auto">
                            <!-- Notification item 1 -->
                            <div class="p-3.5 hover:bg-paper transition-colors flex gap-3">
                                <div class="w-8 h-8 rounded-md bg-brass-wash text-brass-ink flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs text-ink">New inquiry from <strong class="font-medium">Sarah Jenkins</strong></p>
                                    <p class="text-[11px] text-ink-mute mt-0.5">2 minutes ago · Contact form</p>
                                </div>
                            </div>
                            <!-- Notification item 2 -->
                            <div class="p-3.5 hover:bg-paper transition-colors flex gap-3">
                                <div class="w-8 h-8 rounded-md bg-paper text-ink-soft flex items-center justify-center flex-shrink-0 mt-0.5 border border-line">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs text-ink">Portfolio <strong class="font-medium">Apex Architecture</strong> published</p>
                                    <p class="text-[11px] text-ink-mute mt-0.5">45 minutes ago · Public</p>
                                </div>
                            </div>
                        </div>
                        <div class="px-4 py-2 border-t border-line text-center">
                            <a href="#notifications" class="text-xs font-medium text-brass-ink hover:underline">View all notifications</a>
                        </div>
                    </div>
                </div>

                <!-- Admin Profile Dropdown -->
                <div class="relative">
                    <button type="button"
                            id="user-menu-btn"
                            class="flex items-center gap-2 p-1 rounded-md hover:bg-paper-sunk transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-brass"
                            aria-expanded="false"
                            aria-haspopup="true"
                            aria-label="User account menu">
                        <div class="w-8 h-8 rounded-md bg-[#14130F] text-brass font-medium text-xs flex items-center justify-center">
                            SA
                        </div>
                        <svg class="w-4 h-4 text-ink-mute hidden sm:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <!-- User Dropdown Panel -->
                    <div id="user-menu-dropdown"
                         class="hidden absolute right-0 mt-2 w-56 bg-surface rounded-xl shadow-sm border border-line py-2 z-50 transition-all duration-150">
                        <div class="px-4 py-2.5 border-b border-line">
                            <p class="text-xs font-medium text-ink truncate">{{ auth()->user()->name ?? 'Super Admin' }}</p>
                            <p class="text-[11px] text-ink-mute truncate">{{ auth()->user()->email ?? 'admin@portfoliocms.test' }}</p>
                            <p class="text-[11px] text-ink-mute mt-1">Super administrator</p>
                        </div>

                        <div class="py-1">
                            <a href="#profile" class="flex items-center gap-2.5 px-4 py-2 text-xs font-medium text-ink-soft hover:bg-paper hover:text-ink">
                                <svg class="w-4 h-4 text-ink-mute" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Admin profile
                            </a>
                            <a href="#settings" class="flex items-center gap-2.5 px-4 py-2 text-xs font-medium text-ink-soft hover:bg-paper hover:text-ink">
                                <svg class="w-4 h-4 text-ink-mute" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                System settings
                            </a>
                        </div>

                        <div class="border-t border-line py-1">
                            <button type="button"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                    class="w-full flex items-center gap-2.5 px-4 py-2 text-xs font-medium text-[#9C4A3E] hover:bg-[#F7ECE9] text-left transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Sign out
                            </button>
                        </div>
                    </div>
                </div>

            </div>

        </header>

        <!-- Main Page Content Slot -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8 w-full max-w-[1440px] mx-auto">
            @if(session('success'))
                <div id="flash-success-toast" class="mb-6 p-4 rounded-lg bg-surface border border-line border-l-2 border-l-brass text-ink shadow-sm flex items-center justify-between gap-3 animate-toast">
                    <div class="flex items-center gap-3">
                        <span class="w-1.5 h-1.5 rounded-full bg-brass"></span>
                        <span class="text-xs sm:text-sm font-medium">{{ session('success') }}</span>
                    </div>
                    <button type="button" onclick="this.closest('#flash-success-toast').remove()" class="text-ink-mute hover:text-ink p-1" title="Close">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div id="flash-error-toast" class="mb-6 p-4 rounded-lg bg-surface border border-line border-l-2 border-l-[#B4553F] text-ink shadow-sm flex items-center justify-between gap-3 animate-toast">
                    <div class="flex items-center gap-3">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#B4553F]"></span>
                        <span class="text-xs sm:text-sm font-medium">{{ session('error') }}</span>
                    </div>
                    <button type="button" onclick="this.closest('#flash-error-toast').remove()" class="text-ink-mute hover:text-ink p-1" title="Close">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="mt-auto py-5 px-4 sm:px-6 lg:px-8 border-t border-line text-xs text-ink-mute flex flex-col sm:flex-row items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-brass"></span>
                <span class="text-ink-soft">Portfolio CMS</span>
                <span class="text-line-strong">·</span>
                <span class="tabular">Laravel v{{ Illuminate\Foundation\Application::VERSION }}</span>
            </div>
            <div class="tabular">
                &copy; {{ date('Y') }} Portfolio CMS
            </div>
        </footer>

    </div>

    <!-- Hidden Super Admin Logout Form -->
    <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="hidden">
        @csrf
    </form>

    <!-- ========================================================================= -->
    <!-- Frontend Interactions Script (unchanged behaviour) -->
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
                }, 200);
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
