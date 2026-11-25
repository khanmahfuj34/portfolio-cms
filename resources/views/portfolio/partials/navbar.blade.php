{{-- Reusable Public Navbar Component --}}
@props(['portfolio', 'navItems'])

<header class="sticky top-0 z-40 backdrop-blur-md bg-[#0D0F14]/90 border-b border-white/10 transition-colors duration-150">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            
            <!-- Branding / Name -->
            <a href="#home" class="flex items-center gap-3 group">
                <span class="w-7 h-7 rounded-md bg-[#C5A059] text-[#0D0F14] font-bold text-xs flex items-center justify-center shrink-0">
                    {{ strtoupper(substr($portfolio->profile?->full_name ?? $portfolio->name, 0, 1)) }}
                </span>
                <span class="flex flex-col">
                    <span class="text-sm font-semibold text-white tracking-tight group-hover:text-[#C5A059] transition-colors">
                        {{ $portfolio->profile?->full_name ?? $portfolio->name }}
                    </span>
                    @if ($portfolio->profile?->professional_title)
                        <span class="text-[11px] text-slate-400 font-normal">
                            {{ $portfolio->profile->professional_title }}
                        </span>
                    @endif
                </span>
            </a>

            <!-- Desktop Navigation Links -->
            <nav class="hidden md:flex items-center gap-1 sm:gap-1.5">
                @foreach ($navItems as $item)
                    @if ($item->section_key)
                        <a href="#{{ $item->section_key }}" 
                           class="px-3 py-1.5 rounded-md text-xs font-medium text-slate-300 hover:text-white hover:bg-white/5 transition-colors">
                            {{ $item->label }}
                        </a>
                    @elseif ($item->url)
                        <a href="{{ $item->url }}" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-medium text-[#C5A059] hover:text-[#E2C78E] hover:bg-white/5 transition-colors">
                            <span>{{ $item->label }}</span>
                            <svg class="w-3 h-3 text-[#C5A059]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                    @endif
                @endforeach
            </nav>

            <!-- Mobile Hamburger Button -->
            <div class="flex md:hidden">
                <button type="button" 
                        id="mobile-nav-toggle" 
                        aria-label="Toggle Navigation Menu" 
                        class="p-2 rounded-md text-slate-400 hover:text-white hover:bg-white/5 focus:outline-none focus:ring-1 focus:ring-[#C5A059] transition-colors">
                    <svg id="mobile-nav-icon-open" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg id="mobile-nav-icon-close" class="w-5 h-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Collapsible Menu -->
        <div id="mobile-nav-menu" class="hidden md:hidden pb-3 pt-1 border-t border-white/10 space-y-0.5">
            @foreach ($navItems as $item)
                @if ($item->section_key)
                    <a href="#{{ $item->section_key }}" 
                       class="mobile-nav-link block px-3 py-2 rounded-md text-xs font-medium text-slate-300 hover:text-white hover:bg-white/5 transition-colors">
                        {{ $item->label }}
                    </a>
                @elseif ($item->url)
                    <a href="{{ $item->url }}" 
                       target="_blank" 
                       rel="noopener noreferrer" 
                       class="mobile-nav-link flex items-center justify-between px-3 py-2 rounded-md text-xs font-medium text-[#C5A059] hover:text-[#E2C78E] hover:bg-white/5 transition-colors">
                        <span>{{ $item->label }}</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>
                @endif
            @endforeach
        </div>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleBtn = document.getElementById('mobile-nav-toggle');
        const menu = document.getElementById('mobile-nav-menu');
        const iconOpen = document.getElementById('mobile-nav-icon-open');
        const iconClose = document.getElementById('mobile-nav-icon-close');
        const links = document.querySelectorAll('.mobile-nav-link');

        if (toggleBtn && menu) {
            toggleBtn.addEventListener('click', function () {
                const isOpen = !menu.classList.contains('hidden');
                if (isOpen) {
                    menu.classList.add('hidden');
                    iconOpen.classList.remove('hidden');
                    iconClose.classList.add('hidden');
                } else {
                    menu.classList.remove('hidden');
                    iconOpen.classList.add('hidden');
                    iconClose.classList.remove('hidden');
                }
            });

            links.forEach(link => {
                link.addEventListener('click', function () {
                    menu.classList.add('hidden');
                    iconOpen.classList.remove('hidden');
                    iconClose.classList.add('hidden');
                });
            });
        }
    });
</script>
