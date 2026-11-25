@extends('layouts.admin')
@inject('domainService', 'App\Services\PortfolioDomainService')

@section('title', 'Dashboard — Portfolio CMS')
@section('page_title', 'Dashboard')

@section('content')
<div class="space-y-8">

    <!-- Workspace header -->
    <section class="pb-6 border-b border-line">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-5">
            <div class="space-y-2">
                <p class="text-xs text-ink-mute tabular">{{ date('l, F j, Y') }}</p>
                <h2 class="font-display text-4xl leading-none text-ink">Welcome, {{ auth()->user()->name }}</h2>
                <p class="max-w-xl text-sm text-ink-soft">
                    Manage your personal portfolios, custom subdomains, and publication status.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('dashboard.portfolios.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium bg-brass text-[#14130F] hover:bg-[#B89149] transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-brass focus-visible:ring-offset-2 focus-visible:ring-offset-paper">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>New portfolio</span>
                </a>
                <a href="{{ route('dashboard.portfolios.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium bg-surface text-ink-soft border border-line hover:bg-paper-sunk transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-brass">
                    <span>All portfolios</span>
                </a>
            </div>
        </div>
    </section>

    <!-- Owner-scoped metrics -->
    <section>
        <div class="flex items-baseline justify-between gap-4 mb-4">
            <h3 class="text-sm font-medium text-ink">Overview</h3>
            <span class="text-xs text-ink-mute tabular">{{ $stats['total'] ?? 0 }} total</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-admin.stat-card
                title="My portfolios"
                value="{{ $stats['total'] ?? 0 }}"
                subtitle="Owned by you">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </x-admin.stat-card>

            <x-admin.stat-card
                title="Published"
                value="{{ $stats['published'] ?? 0 }}"
                change="{{ ($stats['total'] ?? 0) > 0 ? round((($stats['published'] ?? 0) / $stats['total']) * 100) : 0 }}% of yours"
                subtitle="Live subdomains">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </x-admin.stat-card>

            <x-admin.stat-card
                title="Drafts"
                value="{{ $stats['draft'] ?? 0 }}"
                subtitle="In progress">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </x-admin.stat-card>

            <x-admin.stat-card
                title="Subdomains"
                value="{{ $stats['domains'] ?? 0 }}"
                subtitle="Active hosts">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9"/>
                </svg>
            </x-admin.stat-card>
        </div>
    </section>

    <!-- Owned Portfolios Table -->
    <section class="rounded-xl bg-surface border border-line overflow-hidden">
        <div class="p-5 border-b border-line flex items-center justify-between gap-4">
            <div>
                <h3 class="text-sm font-medium text-ink">Your portfolios</h3>
                <p class="text-xs text-ink-mute mt-0.5">Create multiple portfolios, each with its own dedicated subdomain.</p>
            </div>
            <a href="{{ route('dashboard.portfolios.index') }}" class="text-xs font-medium text-brass-ink hover:underline">
                Manage all
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-line text-[11px] font-medium text-ink-mute">
                        <th scope="col" class="py-3 pl-5 pr-3">Portfolio</th>
                        <th scope="col" class="py-3 px-3">Navigation</th>
                        <th scope="col" class="py-3 px-3">Status</th>
                        <th scope="col" class="py-3 px-3">Created</th>
                        <th scope="col" class="py-3 pl-3 pr-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse($portfolios as $portfolio)
                        <tr class="hover:bg-paper transition-colors">
                            <td class="py-3 pl-5 pr-3">
                                <div class="font-medium text-ink truncate">{{ $portfolio->name }}</div>
                                <div class="mt-0.5 flex flex-wrap items-center gap-2 text-[11px]">
                                    @if ($portfolio->primaryDomain)
                                        <a href="{{ $domainService->publicUrlFor($portfolio->primaryDomain, request()) }}"
                                           target="_blank"
                                           rel="noopener noreferrer"
                                           class="inline-flex items-center gap-1 text-brass-ink hover:underline font-mono">
                                            <span>{{ $portfolio->primaryDomain->domain }}</span>
                                        </a>
                                    @else
                                        <span class="font-mono text-ink-mute">/{{ $portfolio->slug }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-3">
                                <a href="{{ route('dashboard.portfolios.nav-items.index', $portfolio) }}"
                                   class="text-[11px] font-medium text-ink-soft hover:text-ink">
                                    {{ $portfolio->nav_items_count }} {{ Str::plural('link', $portfolio->nav_items_count) }}
                                </a>
                            </td>
                            <td class="py-3 px-3">
                                <x-admin.status-badge :status="$portfolio->status" />
                            </td>
                            <td class="py-3 px-3 text-ink-soft tabular">
                                {{ $portfolio->created_at->format('M j, Y') }}
                            </td>
                            <td class="py-3 pl-3 pr-5 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    @if ($portfolio->status === 'published')
                                        <form method="POST" action="{{ route('dashboard.portfolios.unpublish', $portfolio) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="px-2.5 py-1 rounded-md text-[11px] font-medium bg-surface text-ink-soft hover:text-[#9C4A3E] border border-line cursor-pointer">
                                                Unpublish
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('dashboard.portfolios.publish', $portfolio) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="px-2.5 py-1 rounded-md text-[11px] font-medium bg-surface text-ink-soft hover:text-brass-ink border border-line cursor-pointer">
                                                Publish
                                            </button>
                                        </form>
                                    @endif

                                    <a href="{{ route('dashboard.portfolios.edit', $portfolio) }}"
                                       class="px-2.5 py-1 rounded-md text-[11px] font-medium bg-surface text-ink-soft hover:text-ink border border-line">
                                        Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 px-6 text-center">
                                <h4 class="text-sm font-medium text-ink mb-1">No portfolios yet</h4>
                                <p class="text-xs text-ink-mute mb-4">Create your first portfolio to receive a dedicated subdomain.</p>
                                <a href="{{ route('dashboard.portfolios.create') }}"
                                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-medium bg-brass text-[#14130F] hover:bg-[#B89149]">
                                    Create portfolio
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

</div>
@endsection
