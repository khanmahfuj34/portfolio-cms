@props([
    'active' => false,
    'href' => '#',
    'badge' => null,
    'badgeColor' => 'gold',
    'title' => ''
])

@php
$baseClasses = 'group flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 relative select-none';
$activeClasses = $active 
    ? 'bg-gradient-to-r from-[#D4AF69]/20 to-[#D4AF69]/5 text-[#D4AF69] font-semibold shadow-[inset_0_1px_0_rgba(212,175,105,0.2)] border border-[#D4AF69]/30' 
    : 'text-slate-400 hover:text-slate-100 hover:bg-slate-800/60 border border-transparent';
$iconActiveClasses = $active ? 'text-[#D4AF69]' : 'text-slate-400 group-hover:text-[#D4AF69] transition-colors';
@endphp

<a href="{{ $href }}" 
   class="{{ $baseClasses }} {{ $activeClasses }}" 
   title="{{ $title }}"
   {{ $attributes }}>
    @if($active)
        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-6 bg-[#D4AF69] rounded-r-full shadow-[0_0_10px_#D4AF69]" aria-hidden="true"></span>
    @endif
    
    <span class="flex-shrink-0 w-5 h-5 flex items-center justify-center {{ $iconActiveClasses }}">
        {{ $slot }}
    </span>

    <span class="sidebar-text truncate flex-1 tracking-wide text-[13.5px] transition-opacity duration-200">
        {{ $title }}
    </span>

    @if($badge)
        <span class="sidebar-badge ml-auto px-2 py-0.5 text-[11px] font-semibold rounded-full tracking-wide transition-opacity duration-200
            {{ $badgeColor === 'gold' ? 'bg-[#D4AF69]/20 text-[#D4AF69] border border-[#D4AF69]/30' : 'bg-slate-800 text-slate-300' }}">
            {{ $badge }}
        </span>
    @endif
</a>
