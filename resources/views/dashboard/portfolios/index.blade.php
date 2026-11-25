@extends('layouts.admin')
@inject('domainService', 'App\Services\PortfolioDomainService')

@section('title', 'My Portfolios — Portfolio CMS')
@section('page_title', 'My Portfolios')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <section class="pb-5 border-b border-line">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div class="space-y-1.5">
                <div class="flex items-center gap-2 text-[11px] text-ink-mute">
                    <a href="{{ route('dashboard.index') }}" class="hover:text-ink transition-colors">Dashboard</a>
                    <span class="text-line-strong">/</span>
                    <span class="text-ink-soft">Portfolios</span>
                </div>
                <h2 class="font-display text-3xl leading-none text-ink">My portfolios</h2>
                <p class="max-w-xl text-sm text-ink-soft">
                    Each portfolio gets its own independent subdomain, profile, navigation, skills, and projects.
                </p>
            </div>

            <a href="{{ route('dashboard.portfolios.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium bg-brass text-[#14130F] hover:bg-[#B89149] transition-colors flex-shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                <span>New portfolio</span>
            </a>
        </div>
    </section>

    <!-- Metrics -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <x-admin.stat-card title="Total" value="{{ $stats['total'] ?? 0 }}" subtitle="Portfolios" />
        <x-admin.stat-card title="Published" value="{{ $stats['published'] ?? 0 }}" subtitle="Public" />
        <x-admin.stat-card title="Drafts" value="{{ $stats['draft'] ?? 0 }}" subtitle="In progress" />
        <x-admin.stat-card title="Subdomains" value="{{ $stats['domains'] ?? 0 }}" subtitle="Assigned" />
    </div>

    <!-- Table container -->
    <div class="rounded-xl bg-surface border border-line overflow-hidden">

        <!-- Controls -->
        <div class="p-4 sm:p-5 border-b border-line flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <form method="GET" action="{{ route('dashboard.portfolios.index') }}" class="relative w-full md:w-72">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Search portfolios, slugs"
                       class="w-full pl-3.5 pr-8 py-2 text-xs bg-paper hover:border-line-strong focus:bg-surface text-ink placeholder-ink-mute border border-line rounded-lg focus:outline-none focus:border-brass focus:ring-2 focus:ring-brass/20 transition-colors">
            </form>

            <div class="flex items-center gap-1 p-1 bg-paper-sunk rounded-lg text-xs font-medium self-start md:self-auto">
                <a href="{{ route('dashboard.portfolios.index', array_filter(['search' => request('search')])) }}"
                   class="px-2.5 py-1 rounded-md transition-colors {{ !request('status') ? 'bg-surface text-ink shadow-xs' : 'text-ink-soft hover:text-ink' }}">
                    All ({{ $stats['total'] ?? 0 }})
                </a>
                <a href="{{ route('dashboard.portfolios.index', array_filter(['status' => 'published', 'search' => request('search')])) }}"
                   class="px-2.5 py-1 rounded-md transition-colors {{ request('status') === 'published' ? 'bg-surface text-ink shadow-xs' : 'text-ink-soft hover:text-ink' }}">
                    Published ({{ $stats['published'] ?? 0 }})
                </a>
                <a href="{{ route('dashboard.portfolios.index', array_filter(['status' => 'draft', 'search' => request('search')])) }}"
                   class="px-2.5 py-1 rounded-md transition-colors {{ request('status') === 'draft' ? 'bg-surface text-ink shadow-xs' : 'text-ink-soft hover:text-ink' }}">
                    Drafts ({{ $stats['draft'] ?? 0 }})
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-line text-[11px] font-medium text-ink-mute">
                        <th scope="col" class="py-3 pl-5 pr-3">Portfolio / subdomain</th>
                        <th scope="col" class="py-3 px-3">Status</th>
                        <th scope="col" class="py-3 px-3">Navigation</th>
                        <th scope="col" class="py-3 px-3">Created</th>
                        <th scope="col" class="py-3 pl-3 pr-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse($portfolios as $portfolio)
                        <tr class="hover:bg-paper transition-colors">
                            <td class="py-3 pl-5 pr-3">
                                <div class="font-medium text-ink truncate">{{ $portfolio->name }}</div>
                                <div class="text-[11px] text-ink-mute truncate">
                                    {{ $portfolio->profile?->full_name ?? 'No profile' }}
                                </div>
                                <div class="mt-0.5 flex flex-wrap items-center gap-2 text-[11px]">
                                    @if ($portfolio->primaryDomain)
                                        <a href="{{ $domainService->publicUrlFor($portfolio->primaryDomain, request()) }}"
                                           target="_blank"
                                           rel="noopener noreferrer"
                                           class="inline-flex items-center gap-1 text-brass-ink hover:underline font-mono">
                                            <span>{{ $portfolio->primaryDomain->domain }}</span>
                                        </a>
                                    @endif
                                    <span class="font-mono text-ink-mute">/{{ $portfolio->slug }}</span>
                                </div>
                            </td>

                            <td class="py-3 px-3">
                                <x-admin.status-badge :status="$portfolio->status" />
                            </td>

                            <td class="py-3 px-3">
                                <a href="{{ route('dashboard.portfolios.nav-items.index', $portfolio) }}"
                                   class="text-[11px] font-medium text-ink-soft hover:text-ink">
                                    {{ $portfolio->nav_items_count }} {{ Str::plural('Link', $portfolio->nav_items_count) }}
                                </a>
                            </td>

                            <td class="py-3 px-3 text-ink-soft tabular">
                                {{ $portfolio->created_at->format('M j, Y') }}
                            </td>

                            <td class="py-3 pl-3 pr-5 text-right">
                                <div class="inline-flex items-center gap-1">
                                    @if ($portfolio->status === 'published')
                                        <form method="POST"
                                              action="{{ route('dashboard.portfolios.unpublish', $portfolio) }}"
                                              class="inline"
                                              data-confirm='Move "{{ $portfolio->name }}" back to draft?'>
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="px-2.5 py-1.5 rounded-md text-[11px] font-medium bg-surface text-ink-soft hover:text-[#9C4A3E] border border-line cursor-pointer">
                                                Unpublish
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST"
                                              action="{{ route('dashboard.portfolios.publish', $portfolio) }}"
                                              class="inline"
                                              data-confirm='Publish "{{ $portfolio->name }}"?'>
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="px-2.5 py-1.5 rounded-md text-[11px] font-medium bg-surface text-ink-soft hover:text-brass-ink border border-line cursor-pointer">
                                                Publish
                                            </button>
                                        </form>
                                    @endif

                                    <a href="{{ route('dashboard.portfolios.edit', $portfolio) }}"
                                       class="px-2.5 py-1.5 rounded-md text-[11px] font-medium bg-surface text-ink-soft hover:text-ink border border-line">
                                        Edit
                                    </a>

                                    @if ($portfolio->status === 'draft')
                                        <a href="{{ route('dashboard.portfolios.preview', $portfolio) }}"
                                           target="_blank"
                                           rel="noopener noreferrer"
                                           class="px-2.5 py-1.5 rounded-md text-[11px] font-medium bg-surface text-brass-ink hover:bg-brass-wash/50 border border-line">
                                            Preview
                                        </a>
                                    @endif

                                    <a href="{{ route('dashboard.portfolios.nav-items.index', $portfolio) }}"
                                       class="px-2.5 py-1.5 rounded-md text-[11px] font-medium bg-surface text-ink-soft hover:text-ink border border-line">
                                        Navbar
                                    </a>

                                    <form method="POST"
                                          action="{{ route('dashboard.portfolios.destroy', $portfolio) }}"
                                          class="inline"
                                          data-confirm='Permanently delete "{{ $portfolio->name }}"?'>
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1.5 rounded-md text-[11px] font-medium bg-surface text-[#9C4A3E] hover:bg-[#FBF1EF] border border-[#E9D2CC] cursor-pointer">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 px-6 text-center">
                                <h4 class="text-sm font-medium text-ink mb-1">No portfolios found</h4>
                                <p class="text-xs text-ink-mute mb-4">Create your first portfolio to get started.</p>
                                <a href="{{ route('dashboard.portfolios.create') }}"
                                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-medium bg-brass text-[#14130F] hover:bg-[#B89149]">
                                    New portfolio
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($portfolios->hasPages())
            <div class="p-4 border-t border-line">
                {{ $portfolios->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
