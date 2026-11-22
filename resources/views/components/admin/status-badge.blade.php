@props([
    'status' => 'draft' // published, draft, archived, pending
])

@php
$normalized = strtolower(trim($status));
$config = match($normalized) {
    'published', 'active' => [
        'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200/70',
        'dot' => 'bg-emerald-500 shadow-[0_0_6px_rgba(16,185,129,0.6)]',
        'label' => 'Published'
    ],
    'draft', 'in_progress' => [
        'bg' => 'bg-amber-50 text-amber-700 border-amber-200/70',
        'dot' => 'bg-amber-500 shadow-[0_0_6px_rgba(245,158,11,0.6)]',
        'label' => 'Draft'
    ],
    'archived', 'inactive' => [
        'bg' => 'bg-slate-100 text-slate-600 border-slate-200',
        'dot' => 'bg-slate-400',
        'label' => 'Archived'
    ],
    default => [
        'bg' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
        'dot' => 'bg-indigo-500',
        'label' => ucfirst($status)
    ]
};
@endphp

<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border {{ $config['bg'] }}">
    <span class="w-1.5 h-1.5 rounded-full {{ $config['dot'] }}" aria-hidden="true"></span>
    <span>{{ $config['label'] }}</span>
</span>
