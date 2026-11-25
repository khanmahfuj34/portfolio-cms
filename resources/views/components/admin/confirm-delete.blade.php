@props([
    'id' => 'confirm-delete-modal',
    'title' => 'Confirm Deletion',
    'message' => 'Are you sure you want to delete this item? This action cannot be undone.',
])

<x-admin.modal :id="$id" :title="$title" size="max-w-md">
    <div class="space-y-3">
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-md bg-[#FBF1EF] text-[#B4553F] flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </div>
            <div class="space-y-1">
                <p id="{{ $id }}-message" class="text-xs text-ink-soft leading-relaxed">
                    {{ $message }}
                </p>
            </div>
        </div>
    </div>

    <x-slot:footer>
        <button type="button"
                data-modal-close="{{ $id }}"
                class="px-3 py-1.5 text-xs font-medium text-ink-soft hover:text-ink hover:bg-paper rounded-md transition-colors">
            Cancel
        </button>
        <button type="button"
                id="{{ $id }}-confirm-btn"
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-medium text-white bg-[#B4553F] hover:bg-[#9C4A3E] rounded-md transition-colors">
            <span>Delete</span>
        </button>
    </x-slot:footer>
</x-admin.modal>
