@props(['portfolio', 'profile' => null, 'navItems', 'ownerName'])

@php
    $firstToken = strtoupper(\Illuminate\Support\Str::before(trim($ownerName), ' ') ?: $ownerName);
    $shortWord = \Illuminate\Support\Str::limit($firstToken, 10, '');
@endphp

<header class="sticky top-0 left-0 w-full z-40 bg-paper/90 backdrop-blur-md border-b border-ink/10 transition-colors">
    <nav class="max-w-6xl mx-auto flex items-center justify-between px-6 py-4 gap-4" aria-label="Primary portfolio navigation">
        <a href="#home" class="font-display text-lg sm:text-xl font-bold tracking-tight text-ink hover:opacity-90 transition-opacity truncate max-w-[14rem] sm:max-w-xs">
            <span>{{ $shortWord }}</span><span class="text-[#C2542D]">.{{ strtoupper(\Illuminate\Support\Str::limit($portfolio->slug ?: 'DEV', 6, '')) }}</span>
        </a>

        <ul class="hidden md:flex items-center gap-7 font-medium text-sm">
            @foreach ($navItems as $item)
                @if ($item->section_key)
                    <li>
                        <a href="#{{ $item->section_key }}"
                           data-rm-nav-link
                           class="rm-nav-link text-muted hover:text-[#C2542D]">
                            {{ $item->label }}
                        </a>
                    </li>
                @elseif ($item->url && preg_match('/^https?:\/\//i', trim((string) $item->url)))
                    <li>
                        <a href="{{ trim((string) $item->url) }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="inline-flex items-center gap-1 text-muted hover:text-[#C2542D] transition-colors">
                            <span>{{ $item->label }}</span>
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                    </li>
                @endif
            @endforeach
        </ul>

        <div class="flex items-center gap-3 shrink-0">
            <button type="button"
                    data-rm-theme-toggle
                    aria-label="Toggle dark mode"
                    aria-pressed="false"
                    class="w-10 h-10 flex items-center justify-center rounded-full border border-ink/15 text-ink hover:border-[#C2542D] hover:text-[#C2542D] transition-colors cursor-pointer">
                <svg data-rm-icon-sun class="w-5 h-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                </svg>
                <svg data-rm-icon-moon class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                </svg>
            </button>

            <button type="button"
                    data-rm-mobile-toggle
                    aria-label="Toggle navigation menu"
                    aria-expanded="false"
                    class="md:hidden w-10 h-10 flex items-center justify-center rounded-lg border border-ink/15 text-ink hover:border-[#C2542D] hover:text-[#C2542D] transition-colors cursor-pointer">
                <svg data-rm-mobile-open class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg data-rm-mobile-close class="w-5 h-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </nav>

    <div data-rm-mobile-menu class="hidden md:hidden px-6 pb-4 bg-paper border-t border-ink/10 space-y-1">
        @foreach ($navItems as $item)
            @if ($item->section_key)
                <a href="#{{ $item->section_key }}" class="block py-2 text-sm font-medium text-muted hover:text-[#C2542D] transition-colors">
                    {{ $item->label }}
                </a>
            @elseif ($item->url && preg_match('/^https?:\/\//i', trim((string) $item->url)))
                <a href="{{ trim((string) $item->url) }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="flex items-center justify-between py-2 text-sm font-medium text-[#C2542D] hover:opacity-85 transition-opacity">
                    <span>{{ $item->label }}</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                </a>
            @endif
        @endforeach
    </div>
</header>
