@extends('layouts.admin')

@section('title', 'Skills Management — ' . $portfolio->name . ' — Portfolio CMS')
@section('page_title', 'Skills')

@section('content')
<div class="space-y-6 max-w-6xl mx-auto pb-12">

    <!-- Header -->
    <section class="pb-5 border-b border-line">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div class="space-y-1.5">
                <div class="flex items-center gap-2 text-[11px] text-ink-mute flex-wrap">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-ink transition-colors">Admin</a>
                    <span class="text-line-strong">/</span>
                    <a href="{{ route('admin.profiles.index') }}" class="hover:text-ink transition-colors">Profiles</a>
                    <span class="text-line-strong">/</span>
                    <span class="text-ink-soft truncate max-w-[150px] sm:max-w-xs">{{ $portfolio->name }}</span>
                    <span class="text-line-strong">/</span>
                    <span class="text-ink-soft">Skills</span>
                </div>
                <div class="flex items-center gap-2.5">
                    <h2 class="font-display text-3xl leading-none text-ink">Skills Management</h2>
                    <x-admin.status-badge :status="$portfolio->status" />
                </div>
                <p class="max-w-2xl text-sm text-ink-soft">
                    Manage technical proficiencies and competencies for <span class="font-medium text-ink">{{ $portfolio->name }}</span>.
                </p>
            </div>

            <div class="flex items-center gap-2 flex-shrink-0">
                <a href="{{ route('admin.portfolios.edit', $portfolio) }}"
                   class="px-3.5 py-2 rounded-lg text-xs font-medium text-ink-soft bg-surface border border-line hover:bg-paper-sunk transition-colors inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-ink-mute" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>Portfolio Editor</span>
                </a>
            </div>
        </div>
    </section>

    <!-- Skills Configuration Partial -->
    @include('admin.portfolios.partials.skills-configuration')

</div>
@endsection
