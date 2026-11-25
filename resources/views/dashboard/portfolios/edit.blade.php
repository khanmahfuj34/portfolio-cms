@extends('layouts.admin')
@inject('domainService', 'App\Services\PortfolioDomainService')

@section('title', 'Edit Portfolio — ' . $portfolio->name . ' — Portfolio CMS')
@section('page_title', 'Edit Portfolio')

@section('content')
<div class="space-y-6 max-w-6xl mx-auto pb-12">

    <!-- Header -->
    <section class="pb-5 border-b border-line">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div class="space-y-1.5">
                <div class="flex items-center gap-2 text-[11px] text-ink-mute flex-wrap">
                    <a href="{{ route('dashboard.index') }}" class="hover:text-ink transition-colors">Dashboard</a>
                    <span class="text-line-strong">/</span>
                    <a href="{{ route('dashboard.portfolios.index') }}" class="hover:text-ink transition-colors">My Portfolios</a>
                    <span class="text-line-strong">/</span>
                    <span class="text-ink-soft truncate max-w-[150px] sm:max-w-xs">{{ $portfolio->name }}</span>
                    <span class="text-line-strong">/</span>
                    <span class="text-ink-soft">Edit</span>
                </div>
                <div class="flex items-center gap-2.5">
                    <h2 class="font-display text-3xl leading-none text-ink">{{ $portfolio->name }}</h2>
                    <x-admin.status-badge :status="$portfolio->status" />
                </div>
                @if ($portfolio->primaryDomain)
                    <p class="text-xs text-ink-mute font-mono">
                        Subdomain: {{ $portfolio->primaryDomain->domain }}
                    </p>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-2 flex-shrink-0">
                <a href="{{ route('dashboard.portfolios.index') }}"
                   class="px-3.5 py-2 rounded-lg text-xs font-medium text-ink-soft bg-surface border border-line hover:bg-paper-sunk transition-colors inline-flex items-center gap-1.5">
                    <span>Back to portfolios</span>
                </a>

                @if ($portfolio->status === 'published')
                    <form method="POST" action="{{ route('dashboard.portfolios.unpublish', $portfolio) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="px-3.5 py-2 rounded-lg text-xs font-medium bg-surface text-ink-soft hover:text-[#9C4A3E] border border-line cursor-pointer">
                            Unpublish
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('dashboard.portfolios.publish', $portfolio) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="px-3.5 py-2 rounded-lg text-xs font-medium bg-surface text-ink-soft hover:text-brass-ink border border-line cursor-pointer">
                            Publish
                        </button>
                    </form>
                @endif

                @if ($portfolio->primaryDomain && $portfolio->status === 'published')
                    <a href="{{ $domainService->publicUrlFor($portfolio->primaryDomain, request()) }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-medium bg-brass text-[#14130F] hover:bg-[#B89149] transition-colors">
                        <span>View Live</span>
                    </a>
                @elseif ($portfolio->status === 'draft')
                    <a href="{{ route('dashboard.portfolios.preview', $portfolio) }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-medium bg-brass text-[#14130F] hover:bg-[#B89149] transition-colors">
                        <span>Preview Draft</span>
                    </a>
                @endif
            </div>
        </div>
    </section>

    @if(isset($errors) && $errors->any())
        <div class="p-4 rounded-lg bg-[#FBF1EF] border border-[#E9D2CC] border-l-2 border-l-[#B4553F] text-ink text-xs space-y-1">
            <p class="font-medium text-[#9C4A3E]">Please correct the errors below.</p>
            <ul class="list-disc list-inside text-[11px] text-ink-soft space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Core Portfolio & Profile Update Form -->
    <form method="POST" action="{{ route('dashboard.portfolios.update', $portfolio) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <div class="lg:col-span-6">
                @include('admin.portfolios.partials.portfolio-information')
            </div>

            <div class="lg:col-span-6">
                @include('admin.portfolios.partials.profile-information')
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-xs sm:text-sm font-medium bg-brass text-[#14130F] hover:bg-[#B89149] transition-colors cursor-pointer">
                <span>Save Portfolio Details</span>
            </button>
        </div>
    </form>

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

                <a href="{{ route('dashboard.portfolios.nav-items.index', $portfolio) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-[#14130F] bg-brass hover:bg-[#B89149] rounded-md transition-colors self-start sm:self-auto">
                    <span>Manage Navigation ({{ $portfolio->navItems->count() }})</span>
                </a>
            </div>

            <div class="flex flex-wrap gap-2 pt-1">
                @forelse($portfolio->navItems as $item)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs bg-paper-sunk/60 border border-line text-ink">
                        <span class="w-1.5 h-1.5 rounded-full {{ $item->is_visible ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                        <span class="font-medium">{{ $item->label }}</span>
                    </span>
                @empty
                    <p class="text-xs text-ink-mute italic">No navigation items configured yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Skills Configuration Section (AJAX Mode via dashboard.* endpoints) -->
    <div>
        @include('admin.portfolios.partials.skills-configuration', ['routePrefix' => 'dashboard'])
    </div>

    <!-- Projects Configuration Section (AJAX Mode via dashboard.* endpoints) -->
    <div>
        @include('admin.portfolios.partials.projects-configuration', ['routePrefix' => 'dashboard'])
    </div>

    <!-- Danger Zone: Delete Portfolio -->
    <div class="rounded-xl bg-surface border border-[#E9D2CC] p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h3 class="text-sm font-medium text-[#9C4A3E]">Delete portfolio</h3>
            <p class="text-xs text-ink-mute mt-0.5">Permanently removes this portfolio, its subdomain, profile, navigation, skills, and projects.</p>
        </div>
        <form method="POST"
              action="{{ route('dashboard.portfolios.destroy', $portfolio) }}"
              data-confirm='Are you sure you want to delete "{{ $portfolio->name }}"? This action cannot be undone.'>
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="px-4 py-2 rounded-lg text-xs font-medium text-white bg-[#B4553F] hover:bg-[#9C4A3E] transition-colors cursor-pointer">
                Delete Portfolio
            </button>
        </form>
    </div>

</div>
@endsection
