@props([
    'name' => '',
    'label' => '',
    'description' => null,
    'checked' => false,
    'id' => null,
])

@php
$toggleId = $id ?? 'toggle-' . $name . '-' . uniqid();
@endphp

<div class="flex items-start justify-between gap-3">
    <div class="space-y-0.5">
        <label for="{{ $toggleId }}" class="text-xs font-medium text-ink cursor-pointer select-none">
            {{ $label }}
        </label>
        @if($description)
            <p class="text-[11px] text-ink-mute leading-normal">{{ $description }}</p>
        @endif
    </div>

    <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
        <input
            type="checkbox"
            name="{{ $name }}"
            id="{{ $toggleId }}"
            value="1"
            class="sr-only peer"
            @checked(old($name, $checked))
            {{ $attributes }}
        >
        <div class="w-9 h-5 bg-paper-sunk border border-line peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-brass/30 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-brass peer-checked:border-brass"></div>
    </label>
</div>
