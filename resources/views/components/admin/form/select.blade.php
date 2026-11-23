@props([
    'label' => '',
    'name' => '',
    'required' => false,
    'helper' => null,
    'id' => null,
    'options' => []
])

@php
$selectId = $id ?? $name;
@endphp

<div class="space-y-1.5">
    @if($label)
        <label for="{{ $selectId }}" class="flex items-center justify-between text-xs font-semibold tracking-wide text-slate-700">
            <span>
                {{ $label }}
                @if($required)
                    <span class="text-rose-500 font-bold ml-0.5">*</span>
                @endif
            </span>
            @if(!$required)
                <span class="text-[11px] font-normal text-slate-400">Optional</span>
            @endif
        </label>
    @endif

    <div class="relative rounded-xl shadow-xs">
        <select 
            name="{{ $name }}" 
            id="{{ $selectId }}"
            @if($required) required @endif
            {{ $attributes->merge([
                'class' => 'appearance-none w-full py-2.5 pl-3.5 pr-10 text-xs sm:text-sm bg-slate-50/70 hover:bg-slate-50 focus:bg-white text-slate-800 border border-slate-200 rounded-xl focus:outline-none focus:border-[#D4AF69] focus:ring-2 focus:ring-[#D4AF69]/20 transition-all duration-200 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed'
            ]) }}
        >
            {{ $slot }}
        </select>

        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
    </div>

    @if($helper)
        <p class="text-[11px] text-slate-400 leading-normal">{{ $helper }}</p>
    @endif

    @if(isset($errors) && $errors->has($name))
        <p class="text-[11px] text-rose-600 font-medium mt-1 flex items-center gap-1">
            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span>{{ $errors->first($name) }}</span>
        </p>
    @endif
</div>
