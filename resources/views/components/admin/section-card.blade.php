@props([
    'title' => '',
    'description' => '',
    'icon' => null,
    'badge' => null,
])

<div {{ $attributes->merge(['class' => 'rounded-xl bg-surface border border-line p-6 sm:p-7 space-y-6']) }}>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-5 border-b border-line">
        <div class="flex items-start gap-3">
            @if($icon)
                <div class="w-9 h-9 rounded-md bg-brass-wash text-brass-ink flex items-center justify-center flex-shrink-0">
                    {{ $icon }}
                </div>
            @endif
            <div class="min-w-0">
                <h3 class="text-sm font-medium text-ink">{{ $title }}</h3>
                @if($description)
                    <p class="text-xs text-ink-mute mt-0.5 leading-relaxed">{{ $description }}</p>
                @endif
            </div>
        </div>

        @if(isset($headerActions) || $badge)
            <div class="flex items-center gap-3 self-start sm:self-center">
                @if($badge)
                    {{ $badge }}
                @endif
                @if(isset($headerActions))
                    {{ $headerActions }}
                @endif
            </div>
        @endif
    </div>

    {{ $slot }}
</div>
