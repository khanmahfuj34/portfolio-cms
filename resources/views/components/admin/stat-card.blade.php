@props([
    'title' => '',
    'value' => '0',
    'change' => null,
    'changeType' => 'up', // kept for compatibility; meaning is carried by the copy
    'subtitle' => null,
    'iconBg' => null
])

<div class="rounded-xl bg-surface border border-line p-5 hover:border-line-strong transition-colors">
    <div class="flex items-start justify-between gap-3">
        <p class="text-xs font-medium text-ink-mute">{{ $title }}</p>

        <span class="flex-shrink-0 w-8 h-8 rounded-md bg-brass-wash text-brass-ink flex items-center justify-center [&_svg]:w-[18px] [&_svg]:h-[18px]">
            {{ $slot }}
        </span>
    </div>

    <p class="mt-3 text-[32px] leading-none font-medium tracking-tight text-ink tabular">
        {{ $value }}
    </p>

    @if($change || $subtitle)
        <div class="mt-4 pt-3 border-t border-line flex items-baseline justify-between gap-3 text-xs">
            @if($change)
                <span class="font-medium text-ink-soft">{{ $change }}</span>
            @endif

            @if($subtitle)
                <span class="text-ink-mute">{{ $subtitle }}</span>
            @endif
        </div>
    @endif
</div>
