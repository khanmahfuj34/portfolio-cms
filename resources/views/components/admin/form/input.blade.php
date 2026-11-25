@props([
    'label' => '',
    'name' => '',
    'type' => 'text',
    'placeholder' => '',
    'value' => null,
    'required' => false,
    'helper' => null,
    'prefix' => null,
    'id' => null,
])

@php
$inputId = $id ?? $name;
@endphp

<div class="space-y-1.5">
    @if($label)
        <label for="{{ $inputId }}" class="flex items-center justify-between text-xs font-medium text-ink-soft">
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

    <div class="relative rounded-lg">
        @if($prefix)
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-ink-mute text-xs font-medium">
                {{ $prefix }}
            </div>
        @endif

        <input
            type="{{ $type }}"
            name="{{ $name }}"
            id="{{ $inputId }}"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            @if($required) required @endif
            {{ $attributes->merge([
                'class' => 'w-full py-2.5 px-3.5 text-xs sm:text-sm bg-surface text-ink placeholder-ink-mute border border-line rounded-lg focus:outline-none focus:border-brass focus:ring-2 focus:ring-brass/20 transition-colors disabled:opacity-50 disabled:cursor-not-allowed ' . ($prefix ? 'pl-9' : '')
            ]) }}
        />
    </div>

    @if($helper)
        <p class="text-[11px] text-ink-mute leading-normal">{{ $helper }}</p>
    @endif

    @if(isset($errors) && $errors->has($name))
        <p class="text-[11px] text-[#9C4A3E] font-medium mt-1 flex items-center gap-1">
            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span>{{ $errors->first($name) }}</span>
        </p>
    @endif
</div>
