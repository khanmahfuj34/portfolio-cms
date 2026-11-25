@props([
    'status' => 'draft' // published, draft, archived, pending
])

@php
$normalized = strtolower(trim($status));
$config = match($normalized) {
    'published', 'active' => [
        'bg' => 'bg-[#EEF3EC] text-[#3F5A38] border-[#DAE4D5]',
        'dot' => 'bg-[#5B7A4B]',
        'label' => 'Published'
    ],
    'draft', 'in_progress' => [
        'bg' => 'bg-brass-wash text-brass-ink border-[#E7DCC1]',
        'dot' => 'bg-[#B08A3E]',
        'label' => 'Draft'
    ],
    'archived', 'inactive' => [
        'bg' => 'bg-paper text-ink-soft border-line',
        'dot' => 'bg-ink-mute',
        'label' => 'Archived'
    ],
    default => [
        'bg' => 'bg-paper text-ink-soft border-line',
        'dot' => 'bg-ink-mute',
        'label' => ucfirst($status)
    ]
};
@endphp

<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-medium border {{ $config['bg'] }}">
    <span class="w-1.5 h-1.5 rounded-full {{ $config['dot'] }}" aria-hidden="true"></span>
    <span>{{ $config['label'] }}</span>
</span>
