@extends('layouts.admin')

@section('title', 'Individual Admins — Portfolio CMS')
@section('page_title', 'Users')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <section class="pb-5 border-b border-line">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div class="space-y-1.5">
                <div class="flex items-center gap-2 text-[11px] text-ink-mute">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-ink transition-colors">Admin</a>
                    <span class="text-line-strong">/</span>
                    <span class="text-ink-soft">Users</span>
                </div>
                <h2 class="font-display text-3xl leading-none text-ink">Individual Admins</h2>
                <p class="max-w-xl text-sm text-ink-soft">
                    Inspect registered Individual Admin accounts, their portfolio counts, and account status.
                </p>
            </div>
        </div>
    </section>

    <!-- Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <x-admin.stat-card title="Total" value="{{ $stats['total'] ?? 0 }}" subtitle="Individual Admins">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </x-admin.stat-card>

        <x-admin.stat-card title="Active" value="{{ $stats['active'] ?? 0 }}" subtitle="Can access dashboard">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </x-admin.stat-card>

        <x-admin.stat-card title="Inactive" value="{{ $stats['inactive'] ?? 0 }}" subtitle="Access suspended">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
            </svg>
        </x-admin.stat-card>
    </div>

    <!-- Table container -->
    <div class="rounded-xl bg-surface border border-line overflow-hidden">

        <!-- Controls -->
        <div class="p-4 sm:p-5 border-b border-line flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <form method="GET" action="{{ route('admin.users.index') }}" class="relative w-full md:w-72">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-ink-mute">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Search by name or email"
                       class="w-full pl-8 pr-8 py-2 text-xs bg-paper hover:border-line-strong focus:bg-surface text-ink placeholder-ink-mute border border-line rounded-lg focus:outline-none focus:border-brass focus:ring-2 focus:ring-brass/20 transition-colors">
                @if(request('search'))
                    <a href="{{ route('admin.users.index', array_filter(['status' => request('status')])) }}"
                       class="absolute inset-y-0 right-0 pr-2 flex items-center text-ink-mute hover:text-ink" title="Clear search">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </a>
                @endif
            </form>

            <div class="flex items-center gap-1 p-1 bg-paper-sunk rounded-lg text-xs font-medium self-start md:self-auto">
                <a href="{{ route('admin.users.index', array_filter(['search' => request('search')])) }}"
                   class="px-2.5 py-1 rounded-md transition-colors {{ !request('status') ? 'bg-surface text-ink shadow-xs' : 'text-ink-soft hover:text-ink' }}">
                    All ({{ $stats['total'] ?? 0 }})
                </a>
                <a href="{{ route('admin.users.index', array_filter(['status' => 'active', 'search' => request('search')])) }}"
                   class="px-2.5 py-1 rounded-md transition-colors {{ request('status') === 'active' ? 'bg-surface text-ink shadow-xs' : 'text-ink-soft hover:text-ink' }}">
                    Active ({{ $stats['active'] ?? 0 }})
                </a>
                <a href="{{ route('admin.users.index', array_filter(['status' => 'inactive', 'search' => request('search')])) }}"
                   class="px-2.5 py-1 rounded-md transition-colors {{ request('status') === 'inactive' ? 'bg-surface text-ink shadow-xs' : 'text-ink-soft hover:text-ink' }}">
                    Inactive ({{ $stats['inactive'] ?? 0 }})
                </a>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-line text-[11px] font-medium text-ink-mute">
                        <th scope="col" class="py-3 pl-5 pr-3">Account</th>
                        <th scope="col" class="py-3 px-3">Status</th>
                        <th scope="col" class="py-3 px-3">Portfolios</th>
                        <th scope="col" class="py-3 px-3">Joined</th>
                        <th scope="col" class="py-3 pl-3 pr-5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse($users as $adminUser)
                        @php
                            $words = preg_split('/\s+/', trim((string) $adminUser->name));
                            if (count($words) >= 2) {
                                $initials = mb_substr($words[0], 0, 1) . mb_substr($words[1], 0, 1);
                            } else {
                                $initials = mb_substr((string) $adminUser->name, 0, 2);
                            }
                            $initials = strtoupper($initials);
                        @endphp
                        <tr class="hover:bg-paper transition-colors">
                            <td class="py-3 pl-5 pr-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-md bg-paper text-brass-ink font-medium text-[11px] flex items-center justify-center flex-shrink-0 border border-line">
                                        {{ $initials }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-medium text-ink truncate">
                                            {{ $adminUser->name }}
                                        </div>
                                        <div class="text-[11px] text-ink-mute truncate">
                                            {{ $adminUser->email }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-3">
                                @if($adminUser->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-medium border bg-[#EEF3EC] text-[#3F5A38] border-[#DAE4D5]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#5B7A4B]" aria-hidden="true"></span>
                                        <span>Active</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-medium border bg-paper text-ink-soft border-line">
                                        <span class="w-1.5 h-1.5 rounded-full bg-ink-mute" aria-hidden="true"></span>
                                        <span>Inactive</span>
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3 tabular text-ink-soft font-medium">
                                {{ $adminUser->portfolios_count }}
                            </td>
                            <td class="py-3 px-3 text-ink-mute tabular">
                                {{ $adminUser->created_at?->format('M d, Y') ?? '—' }}
                            </td>
                            <td class="py-3 pl-3 pr-5 text-right">
                                <form method="POST"
                                      action="{{ route('admin.users.toggle-status', ['user' => $adminUser, 'search' => request('search'), 'status' => request('status'), 'page' => request('page')]) }}"
                                      class="inline-block">
                                    @csrf
                                    @method('PATCH')
                                    @if($adminUser->is_active)
                                        <button type="submit"
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-md text-[11px] font-medium bg-surface text-[#9C4A3E] hover:bg-[#F7ECE9] border border-line transition-colors">
                                            Deactivate
                                        </button>
                                    @else
                                        <button type="submit"
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-md text-[11px] font-medium bg-surface text-[#3F5A38] hover:bg-[#EEF3EC] border border-line transition-colors">
                                            Activate
                                        </button>
                                    @endif
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 px-6 text-center">
                                <div class="max-w-sm mx-auto flex flex-col items-center">
                                    <h4 class="text-sm font-medium text-ink mb-1">No Individual Admin accounts found</h4>
                                    <p class="text-xs text-ink-mute mb-4 leading-relaxed">
                                        @if(request('search') || request('status'))
                                            No accounts match your active search or status filters.
                                        @else
                                            Registered Individual Admin accounts will appear here.
                                        @endif
                                    </p>
                                    @if(request('search') || request('status'))
                                        <a href="{{ route('admin.users.index') }}" class="px-3 py-1.5 text-xs font-medium text-ink-soft bg-surface border border-line rounded-md hover:bg-paper-sunk transition-colors">
                                            Clear Filters
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages() || $users->total() > 0)
            <div class="p-4 border-t border-line flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-ink-mute">
                <div class="tabular">
                    Showing <span class="font-medium text-ink-soft">{{ $users->firstItem() ?? 0 }}</span>–<span class="font-medium text-ink-soft">{{ $users->lastItem() ?? 0 }}</span> of <span class="font-medium text-ink-soft">{{ $users->total() }}</span>
                </div>
                <div>
                    {{ $users->links() }}
                </div>
            </div>
        @endif

    </div>

</div>
@endsection
