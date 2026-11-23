{{-- Reusable Public Navbar Component --}}
@props(['portfolio', 'navItems'])

<header class="sticky top-0 z-50 backdrop-blur-md bg-[#0F1219]/90 border-b border-[#1E2330] transition-colors duration-200">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-18">
            
            <!-- Branding / Logo -->
            <a href="#home" class="flex items-center gap-2.5 group transition-transform duration-200 hover:scale-[1.01]">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-[#D4AF69] to-[#F2DAA4] flex items-center justify-center font-bold text-slate-950 text-sm shadow-xs shadow-[#D4AF69]/20">
                    {{ strtoupper(substr($portfolio->profile?->full_name ?? $portfolio->name, 0, 1)) }}
                </div>
                <div class="flex flex-col">
                    <span class="text-sm font-bold text-white tracking-tight group-hover:text-[#D4AF69] transition-colors">
                        {{ $portfolio->profile?->full_name ?? $portfolio->name }}
                    </span>
                    @if ($portfolio->profile?->professional_title)
                        <span class="text-[11px] text-slate-400 font-medium tracking-wide">
                            {{ $portfolio->profile->professional_title }}
                        </span>
                    @endif
                </div>
            </a>

            <!-- Desktop Navigation Links -->
            <nav class="hidden md:flex items-center gap-1 sm:gap-2">
                @foreach ($navItems as $item)
                    @if ($item->section_key)
                        <a href="#{{ $item->section_key }}" 
                           class="px-3.5 py-1.5 rounded-lg text-xs font-semibold text-slate-300 hover:text-white hover:bg-white/5 transition-all">
                            {{ $item->label }}
                        </a>
                    @elseif ($item->url)
                        <a href="{{ $item->url }}" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold text-[#D4AF69] hover:text-[#F2DAA4] hover:bg-[#D4AF69]/10 transition-all">
                            <span>{{ $item->label }}</span>
                            <svg class="w-3 h-3 text-[#D4AF69]/80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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
                        class="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 focus:outline-hidden focus:ring-2 focus:ring-[#D4AF69]/40 transition-colors">
                    <svg id="mobile-nav-icon-open" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg id="mobile-nav-icon-close" class="w-5 h-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Collapsible Menu -->
        <div id="mobile-nav-menu" class="hidden md:hidden pb-4 pt-2 border-t border-[#1E2330] space-y-1">
            @foreach ($navItems as $item)
                @if ($item->section_key)
                    <a href="#{{ $item->section_key }}" 
                       class="mobile-nav-link block px-3 py-2 rounded-lg text-xs font-semibold text-slate-300 hover:text-white hover:bg-white/5 transition-all">
                        {{ $item->label }}
                    </a>
                @elseif ($item->url)
                    <a href="{{ $item->url }}" 
                       target="_blank" 
                       rel="noopener noreferrer" 
                       class="mobile-nav-link flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold text-[#D4AF69] hover:text-[#F2DAA4] hover:bg-[#D4AF69]/10 transition-all">
                        <span>{{ $item->label }}</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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

            // Auto-close on link click
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
