@props([
    'label' => '',
    'name' => '',
    'options' => [],
    'selected' => null,
    'required' => false,
    'helper' => null,
    'id' => null,
])

@php
$selectId = $id ?? $name;
@endphp

<div class="space-y-1.5">
    @if($label)
        <label for="{{ $selectId }}" class="flex items-center justify-between text-xs font-medium text-ink-soft">
            <span>
                {{ $label }}
                @if($required)
                    <span class="text-brass-ink font-medium ml-0.5">*</span>
                @endif
            </span>
            @if(!$required)
                <span class="text-[11px] font-normal text-ink-mute">Optional</span>
            @endif
        </label>
    @endif

    <select
        name="{{ $name }}"
        id="{{ $selectId }}"
        @if($required) required @endif
        {{ $attributes->merge([
            'class' => 'w-full py-2 px-3 text-xs sm:text-sm bg-surface text-ink border border-line rounded-lg focus:outline-none focus:border-brass focus:ring-2 focus:ring-brass/20 transition-colors disabled:opacity-50 disabled:cursor-not-allowed'
        ]) }}
    >
        {{ $slot }}
    </select>

    @if($helper)
        <p class="text-[11px] text-ink-mute leading-normal">{{ $helper }}</p>
    @endif
</div>
