@extends('layouts.admin')

@section('title', 'Create New Profile — Super Admin')
@section('page_title', 'Create New Profile')

@section('content')
<div class="space-y-6 max-w-6xl mx-auto pb-12">

    <!-- ========================================================================= -->
    <!-- 1. Breadcrumb & Page Header -->
    <!-- ========================================================================= -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200/80">
        <div class="space-y-1">
            <!-- Breadcrumbs -->
            <nav class="flex items-center gap-2 text-xs font-medium text-slate-400" aria-label="Breadcrumb">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-700 transition-colors flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span>Dashboard</span>
                </a>
                <svg class="w-3 h-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <a href="#profiles" class="hover:text-slate-700 transition-colors">Profiles</a>
                <svg class="w-3 h-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span class="text-[#9A7326] font-semibold" aria-current="page">Create New</span>
            </nav>

            <!-- Page Title & Subtitle -->
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900">
                Create New Profile
            </h1>
            <p class="text-xs sm:text-sm text-slate-500">
                Create a personal portfolio profile, configure its navigation and prepare it for publishing.
            </p>
        </div>

        <!-- Back to Dashboard Button -->
        <a href="{{ route('admin.dashboard') }}" 
           class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-xl shadow-2xs transition-colors self-start sm:self-auto focus:outline-none focus:ring-2 focus:ring-[#D4AF69]/40">
            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Back to Dashboard</span>
        </a>
    </div>

    <!-- Global Validation Alert (if any errors exist) -->
    @if(isset($errors) && $errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200/80 text-rose-800 text-xs flex items-start gap-3 shadow-xs">
            <svg class="w-5 h-5 text-rose-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="space-y-1">
                <p class="font-bold">Please correct the errors below before proceeding:</p>
                <ul class="list-disc list-inside text-[11px] text-rose-700 space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- 2. Main Form Canvas -->
    <!-- ========================================================================= -->
    <form id="portfolio-create-form" 
          action="{{ route('admin.portfolios.store') }}" 
          method="POST" 
          class="space-y-8">
        @csrf

        <!-- Fallback status for current backend validation requirements -->
        <input type="hidden" name="status" value="draft" />

        <!-- 2.1 Admin Assignment Section -->
        @include('admin.portfolios.partials.admin-assignment')

        <!-- 2.2 Two-Column Grid: Portfolio Information & Profile Information -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Left Column: Portfolio Information (6 cols) -->
            <div class="lg:col-span-6 space-y-6">
                @include('admin.portfolios.partials.portfolio-information')
            </div>

            <!-- Right Column: Profile Information (6 cols) -->
            <div class="lg:col-span-6 space-y-6">
                @include('admin.portfolios.partials.profile-information')
            </div>

        </div>

        <!-- 2.3 Full-Width Navbar Configuration Section -->
        <div>
            @include('admin.portfolios.partials.navbar-configuration')
        </div>

        <!-- ========================================================================= -->
        <!-- 3. Form Actions Footer Bar -->
        <!-- ========================================================================= -->
        <div class="sticky bottom-4 z-20 rounded-2xl bg-white/95 backdrop-blur-md border border-slate-200/90 p-4 sm:p-5 shadow-lg flex flex-col sm:flex-row items-center justify-between gap-4">
            
            <div class="flex items-center gap-2 text-xs text-slate-500 self-start sm:self-auto">
                <span class="w-2 h-2 rounded-full bg-[#D4AF69]"></span>
                <span class="font-medium text-slate-700">Admin Managed Profile</span>
                <span>&bull;</span>
                <span>Super Admin Exclusive</span>
            </div>

            <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                <!-- Cancel Button -->
                <a href="{{ route('admin.dashboard') }}" 
                   id="btn-cancel-form"
                   class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 border border-transparent transition-colors">
                    Cancel
                </a>

                <!-- Create Profile Primary Action -->
                <button type="submit" 
                        id="btn-submit-profile"
                        class="gold-btn-gradient inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-[#0B0D12] transition-transform active:scale-95 shadow-md focus:outline-none focus:ring-2 focus:ring-[#D4AF69]">
                    <svg class="w-4 h-4 text-[#0B0D12]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Create Profile</span>
                </button>
            </div>

        </div>

    </form>

</div>
@endsection
