@extends('layouts.admin')

@section('title', 'Theme Preview Unavailable — Portfolio CMS')
@section('page_title', 'Theme Preview Unavailable')

@section('content')
<div class="max-w-3xl mx-auto py-8 space-y-6">
    <div class="rounded-xl bg-surface border border-line p-8 text-center space-y-4">
        <div class="mx-auto w-12 h-12 rounded-xl bg-brass-wash border border-brass/30 text-brass-ink flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
            </svg>
        </div>

        <div class="space-y-1.5">
            <p class="text-[11px] font-medium uppercase tracking-wider text-ink-mute">Theme Preview Unavailable</p>
            <h2 class="font-display text-2xl font-normal text-ink">{{ $theme->name }}</h2>
            <p class="text-sm text-ink-soft max-w-lg mx-auto">
                Live preview cannot be rendered for this theme template right now because no matching trusted layout template is registered.
            </p>
        </div>

        <div class="pt-2 flex flex-wrap items-center justify-center gap-3">
            <a href="{{ route('dashboard.themes.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-ink text-paper text-xs font-medium hover:bg-ink/90 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                </svg>
                <span>Back to Theme Gallery</span>
            </a>
        </div>
    </div>
</div>
@endsection
