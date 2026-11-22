@props([
    'title' => '',
    'value' => '0',
    'change' => null,
    'changeType' => 'up', // up, down, neutral, gold
    'subtitle' => null,
    'iconBg' => 'bg-[#D4AF69]/10 text-[#D4AF69]'
])

@php
$trendColors = match($changeType) {
    'up' => 'text-emerald-700 bg-emerald-50 border-emerald-200/60',
    'down' => 'text-rose-700 bg-rose-50 border-rose-200/60',
    'gold' => 'text-[#9A7326] bg-[#D4AF69]/15 border-[#D4AF69]/30',
    default => 'text-slate-600 bg-slate-100 border-slate-200',
};
@endphp

<div class="relative overflow-hidden rounded-2xl bg-white border border-slate-200/80 p-5 shadow-sm hover:shadow-md hover:border-[#D4AF69]/40 transition-all duration-300 group">
    <!-- Top subtle ambient highlight -->
    <div class="absolute -top-12 -right-12 w-28 h-28 bg-[#D4AF69]/5 rounded-full blur-2xl group-hover:bg-[#D4AF69]/15 transition-all duration-500 pointer-events-none"></div>

    <div class="flex items-start justify-between gap-4">
        <div class="space-y-1.5">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                {{ $title }}
            </p>
            <h3 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 group-hover:text-slate-950 transition-colors">
                {{ $value }}
            </h3>
        </div>

        <div class="flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center {{ $iconBg }} border border-[#D4AF69]/20 shadow-sm transition-transform duration-300 group-hover:scale-105">
            {{ $slot }}
        </div>
    </div>

    @if($change || $subtitle)
        <div class="mt-4 pt-3.5 border-t border-slate-100 flex items-center justify-between text-xs">
            @if($change)
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full font-medium border {{ $trendColors }}">
                    @if($changeType === 'up')
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    @elseif($changeType === 'down')
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                        </svg>
                    @endif
                    {{ $change }}
                </span>
            @endif

            @if($subtitle)
                <span class="text-slate-500 font-medium">
                    {{ $subtitle }}
                </span>
            @endif
        </div>
    @endif
</div>
