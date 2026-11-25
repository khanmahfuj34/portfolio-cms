@extends('layouts.admin')
@inject('domainService', 'App\Services\PortfolioDomainService')

@section('title', 'Edit Portfolio — ' . $portfolio->name . ' — Super Admin')
@section('page_title', 'Edit Portfolio')

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
                    <span class="text-ink-soft">Edit</span>
                </div>
                <div class="flex items-center gap-2.5">
                    <h2 class="font-display text-3xl leading-none text-ink">{{ $portfolio->name }}</h2>
                    <x-admin.status-badge :status="$portfolio->status" />
                </div>
                <p class="max-w-2xl text-sm text-ink-soft">
                    Manage profile metadata, navigation items, skills inventory, and project portfolio.
                </p>
            </div>

            <div class="flex items-center gap-2 flex-shrink-0">
                <a href="{{ route('admin.profiles.index') }}"
                   class="px-3.5 py-2 rounded-lg text-xs font-medium text-ink-soft bg-surface border border-line hover:bg-paper-sunk transition-colors inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-ink-mute" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>Back to profiles</span>
                </a>
                <a href="{{ $portfolio->primaryDomain ? $domainService->publicUrlFor($portfolio->primaryDomain, request()) : url('/' . $portfolio->slug) }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-medium bg-brass text-[#14130F] hover:bg-[#B89149] transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <span>View Live</span>
                </a>
            </div>
        </div>
    </section>

    <!-- Core Profile Information -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <div class="lg:col-span-6">
            @include('admin.portfolios.partials.portfolio-information')
        </div>

        <div class="lg:col-span-6">
            @include('admin.portfolios.partials.profile-information')
        </div>
    </div>

    <!-- Navigation Configuration -->
    <div>
        <div class="rounded-xl bg-surface border border-line p-6 sm:p-7 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-line">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-md bg-brass-wash text-brass-ink flex items-center justify-center flex-shrink-0">
                        <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-medium text-ink">Navbar Configuration</h3>
                        <p class="text-xs text-ink-mute mt-0.5 leading-relaxed">
                            Organize navigation links and destination anchors for this portfolio.
                        </p>
                    </div>
                </div>

                <a href="{{ route('admin.portfolios.nav-items.index', $portfolio) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-[#14130F] bg-brass hover:bg-[#B89149] rounded-md transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-brass self-start sm:self-auto">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>Manage Navigation ({{ $portfolio->navItems->count() }})</span>
                </a>
            </div>

            <!-- Preview of Nav items -->
            <div class="flex flex-wrap gap-2 pt-1">
                @forelse($portfolio->navItems as $item)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs bg-paper-sunk/60 border border-line text-ink">
                        <span class="w-1.5 h-1.5 rounded-full {{ $item->is_visible ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                        <span class="font-medium">{{ $item->label }}</span>
                        <span class="text-[10px] text-ink-mute font-mono">({{ $item->destination }})</span>
                    </span>
                @empty
                    <p class="text-xs text-ink-mute italic">No navigation items configured yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Skills Configuration Section -->
    <div>
        @include('admin.portfolios.partials.skills-configuration')
    </div>

    <!-- Projects Configuration Section -->
    <div>
        @include('admin.portfolios.partials.projects-configuration')
    </div>

</div>
@endsection
