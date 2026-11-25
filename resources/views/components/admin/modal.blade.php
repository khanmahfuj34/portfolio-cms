@props([
    'id' => 'admin-modal',
    'title' => '',
    'size' => 'max-w-lg',
])

<div id="{{ $id }}"
     class="hidden fixed inset-0 z-50 overflow-y-auto"
     aria-labelledby="{{ $id }}-title"
     role="dialog"
     aria-modal="true">
    
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-[#0D0F14]/60 backdrop-blur-xs transition-opacity"
         data-modal-backdrop="{{ $id }}"></div>

    <!-- Dialog wrapper -->
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-xl bg-surface border border-line text-left shadow-xl transition-all sm:my-8 w-full {{ $size }}">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-line">
                <h3 class="text-sm font-semibold text-ink" id="{{ $id }}-title">
                    {{ $title }}
                </h3>
                <button type="button"
                        data-modal-close="{{ $id }}"
                        class="p-1 text-ink-mute hover:text-ink rounded-md hover:bg-paper-sunk transition-colors"
                        title="Close">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="px-6 py-5">
                {{ $slot }}
            </div>

            @if(isset($footer))
                <!-- Modal Footer -->
                <div class="px-6 py-3.5 bg-paper-sunk/40 border-t border-line flex items-center justify-end gap-2.5">
                    {{ $footer }}
                </div>
            @endif

        </div>
    </div>
</div>
