@extends('layouts.admin')

@section('title', 'Account Settings — Portfolio CMS')
@section('page_title', 'Account Settings')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto pb-12">

    <!-- Header -->
    <section class="pb-5 border-b border-line">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div class="space-y-1.5">
                <div class="flex items-center gap-2 text-[11px] text-ink-mute">
                    <a href="{{ route('dashboard.index') }}" class="hover:text-ink transition-colors">Dashboard</a>
                    <span class="text-line-strong">/</span>
                    <span class="text-ink-soft">Account</span>
                </div>
                <h2 class="font-display text-3xl leading-none text-ink">Account settings</h2>
                <p class="text-sm text-ink-soft">
                    Update your display name, sign-in email, and password.
                </p>
            </div>

            <a href="{{ route('dashboard.index') }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-medium text-ink-soft bg-surface border border-line rounded-lg hover:bg-paper-sunk transition-colors self-start sm:self-auto">
                <span>Back to dashboard</span>
            </a>
        </div>
    </section>

    <!-- Profile Details Form -->
    <form method="POST" action="{{ route('dashboard.account.update') }}" class="rounded-xl bg-surface border border-line p-6 sm:p-7 space-y-5">
        @csrf
        @method('PATCH')

        <div class="pb-4 border-b border-line">
            <h3 class="text-sm font-medium text-ink">Profile information</h3>
            <p class="text-xs text-ink-mute mt-0.5">Your account identity used across your creator workspace.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-admin.form.input
                label="Full name"
                name="name"
                id="account-name-input"
                :value="$user->name"
                :required="true"
            />

            <x-admin.form.input
                label="Email address"
                name="email"
                type="email"
                id="account-email-input"
                :value="$user->email"
                :required="true"
            />
        </div>

        <div class="flex justify-end pt-2">
            <button type="submit"
                    class="px-5 py-2.5 rounded-lg text-xs sm:text-sm font-medium bg-brass text-[#14130F] hover:bg-[#B89149] transition-colors cursor-pointer">
                Save Profile
            </button>
        </div>
    </form>

    <!-- Password Update Form -->
    <form method="POST" action="{{ route('dashboard.account.password') }}" class="rounded-xl bg-surface border border-line p-6 sm:p-7 space-y-5">
        @csrf
        @method('PUT')

        <div class="pb-4 border-b border-line">
            <h3 class="text-sm font-medium text-ink">Change password</h3>
            <p class="text-xs text-ink-mute mt-0.5">Verify your current password before choosing a new password.</p>
        </div>

        <div class="space-y-4 max-w-md">
            <x-admin.form.input
                label="Current password"
                name="current_password"
                type="password"
                id="current-password-input"
                :required="true"
            />

            <x-admin.form.input
                label="New password"
                name="password"
                type="password"
                id="new-password-input"
                :required="true"
                helper="Minimum 8 characters."
            />

            <x-admin.form.input
                label="Confirm new password"
                name="password_confirmation"
                type="password"
                id="confirm-password-input"
                :required="true"
            />
        </div>

        <div class="flex justify-end pt-2">
            <button type="submit"
                    class="px-5 py-2.5 rounded-lg text-xs sm:text-sm font-medium bg-[#14130F] text-[#F3F1EC] hover:bg-[#2A261C] transition-colors cursor-pointer">
                Update Password
            </button>
        </div>
    </form>

</div>
@endsection
