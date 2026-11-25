@props([
    'active' => false,
    'href' => '#',
    'badge' => null,
    'badgeColor' => 'gold',
    'title' => ''
])

@php
$baseClasses = 'group flex items-center gap-2.5 px-3 py-2 rounded-md text-[13px] font-medium transition-colors duration-150 relative select-none';
$activeClasses = $active
    ? 'bg-[#221F17] text-[#F3F1EC]'
    : 'text-[#B0AA9C] hover:text-[#F3F1EC] hover:bg-[#1E1C15]';
$iconActiveClasses = $active ? 'text-brass' : 'text-[#8C877A] group-hover:text-[#CFC8B8]';
@endphp

<a href="{{ $href }}"
   class="{{ $baseClasses }} {{ $activeClasses }}"
   title="{{ $title }}"
   @if($active) aria-current="page" @endif
   {{ $attributes }}>
    @if($active)
        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-0.5 h-4 bg-brass rounded-r-[1px]" aria-hidden="true"></span>
    @endif

    <span class="flex-shrink-0 w-5 h-5 flex items-center justify-center [&_svg]:w-[18px] [&_svg]:h-[18px] {{ $iconActiveClasses }}">
        {{ $slot }}
    </span>

    <span class="sidebar-text truncate flex-1 transition-opacity duration-150">
        {{ $title }}
    </span>

    @if($badge)
        <span class="sidebar-badge ml-auto text-[11px] font-medium tabular {{ $active ? 'text-brass' : 'text-[#8C877A]' }}">
            {{ $badge }}
        </span>
    @endif
</a>
