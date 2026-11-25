@extends('layouts.admin')

@section('title', 'Create Portfolio — Portfolio CMS')
@section('page_title', 'Create Portfolio')

@section('content')
<div class="space-y-6 max-w-6xl mx-auto pb-12">

    <!-- Header -->
    <section class="pb-5 border-b border-line">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div class="space-y-1.5">
                <div class="flex items-center gap-2 text-[11px] text-ink-mute">
                    <a href="{{ route('dashboard.index') }}" class="hover:text-ink transition-colors">Dashboard</a>
                    <span class="text-line-strong">/</span>
                    <a href="{{ route('dashboard.portfolios.index') }}" class="hover:text-ink transition-colors">My Portfolios</a>
                    <span class="text-line-strong">/</span>
                    <span class="text-ink-soft">New</span>
                </div>
                <h2 class="font-display text-3xl leading-none text-ink">Create a portfolio</h2>
                <p class="max-w-xl text-sm text-ink-soft">
                    Configure your portfolio identity, navigation, skills, and projects. A unique subdomain will be created automatically.
                </p>
            </div>

            <a href="{{ route('dashboard.portfolios.index') }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-medium text-ink-soft bg-surface border border-line rounded-lg hover:bg-paper-sunk transition-colors self-start sm:self-auto">
                <svg class="w-3.5 h-3.5 text-ink-mute" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Back to portfolios</span>
            </a>
        </div>
    </section>

    @if(isset($errors) && $errors->any())
        <div class="p-4 rounded-lg bg-[#FBF1EF] border border-[#E9D2CC] border-l-2 border-l-[#B4553F] text-ink text-xs flex items-start gap-3">
            <div class="space-y-1">
                <p class="font-medium text-[#9C4A3E]">Please correct the errors below.</p>
                <ul class="list-disc list-inside text-[11px] text-ink-soft space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- Shared Portfolio Form -->
    <form id="portfolio-create-form"
          action="{{ route('dashboard.portfolios.store') }}"
          method="POST"
          class="space-y-6">
        @csrf

        <input type="hidden" name="status" value="draft" />

        @include('admin.portfolios.partials.admin-assignment')

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <div class="lg:col-span-6">
                @include('admin.portfolios.partials.portfolio-information')
            </div>

            <div class="lg:col-span-6">
                @include('admin.portfolios.partials.profile-information')
            </div>
        </div>

        <div>
            @include('admin.portfolios.partials.navbar-configuration')
        </div>

        <div>
            @include('admin.portfolios.partials.skills-configuration', ['routePrefix' => 'dashboard'])
        </div>

        <div>
            @include('admin.portfolios.partials.projects-configuration', ['routePrefix' => 'dashboard'])
        </div>

        <!-- Actions -->
        <div class="sticky bottom-4 z-20 rounded-xl bg-paper/95 backdrop-blur-md border border-line p-4 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2 text-[11px] text-ink-mute self-start sm:self-auto">
                <span class="w-1.5 h-1.5 rounded-full bg-brass"></span>
                <span>Saved as draft until published</span>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                <a href="{{ route('dashboard.portfolios.index') }}"
                   id="btn-cancel-form"
                   class="px-4 py-2 text-xs sm:text-sm font-medium text-ink-soft hover:text-ink hover:bg-paper-sunk rounded-lg transition-colors">
                    Cancel
                </a>

                <button type="submit"
                        id="btn-submit-profile"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-xs sm:text-sm font-medium bg-brass text-[#14130F] hover:bg-[#B89149] transition-colors">
                    <span>Create Portfolio</span>
                </button>
            </div>
        </div>
    </form>

</div>
@endsection
