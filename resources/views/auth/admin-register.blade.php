@extends('layouts.admin-auth')

@section('title', 'Create Account — Portfolio CMS')

@section('content')
<div class="w-full max-w-4xl mx-auto">
    <div class="grid md:grid-cols-[0.9fr_1.1fr] rounded-xl overflow-hidden border border-line bg-surface">

        <!-- Brand panel -->
        <div class="hidden md:flex flex-col justify-between p-9 bg-[#1A1813] text-[#C9C3B6]">
            <div>
                <div class="w-9 h-9 rounded-md bg-brass flex items-center justify-center text-[#14130F] mb-8" aria-hidden="true">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                        <path d="M2 17l10 5 10-5"></path>
                        <path d="M2 12l10 5 10-5"></path>
                    </svg>
                </div>

                <p class="text-[11px] text-[#8C877A] mb-2">Creator workspace</p>
                <h1 class="font-display text-4xl leading-none text-[#F3F1EC]">Portfolio CMS</h1>
                <div class="mt-5 h-px w-10 bg-brass"></div>
                <p class="mt-5 text-sm leading-relaxed text-[#B0AA9C]">
                    Register to launch multiple portfolio websites with custom subdomains and full content control.
                </p>
            </div>

            <div class="text-[11px] text-[#7C7668]">
                Every portfolio receives its own dedicated subdomain.
            </div>
        </div>

        <!-- Form panel -->
        <div class="p-6 sm:p-9">
            <div class="mb-6">
                <p class="text-[11px] text-ink-mute mb-1">Get started</p>
                <h2 class="font-display text-3xl leading-none text-ink">Create account</h2>
                <p class="text-xs text-ink-soft mt-2">Create your personal workspace account.</p>
            </div>

            <form action="{{ route('register.submit') }}" method="POST" class="space-y-4" novalidate>
                @csrf

                <!-- Full Name -->
                <div class="space-y-1.5">
                    <label for="name" class="block text-xs font-medium text-ink-soft">Full name</label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="{{ old('name') }}"
                        required
                        autofocus
                        autocomplete="name"
                        placeholder="Mahfuj Khan"
                        class="w-full px-3.5 py-2.5 bg-surface border rounded-lg text-sm text-ink placeholder-ink-mute transition-colors focus:outline-none border-line hover:border-line-strong focus:border-brass focus:ring-2 focus:ring-brass/20"
                    />
                    @error('name')
                        <p class="text-[11px] text-[#9C4A3E]" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div class="space-y-1.5">
                    <label for="email" class="block text-xs font-medium text-ink-soft">Email</label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        value="{{ old('email') }}"
                        required
                        autocomplete="email"
                        placeholder="you@example.com"
                        class="w-full px-3.5 py-2.5 bg-surface border rounded-lg text-sm text-ink placeholder-ink-mute transition-colors focus:outline-none border-line hover:border-line-strong focus:border-brass focus:ring-2 focus:ring-brass/20"
                    />
                    @error('email')
                        <p class="text-[11px] text-[#9C4A3E]" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="space-y-1.5">
                    <label for="password" class="block text-xs font-medium text-ink-soft">Password</label>
                    <input
                        type="password"
                        name="password"
                        id="password"
                        required
                        autocomplete="new-password"
                        placeholder="At least 8 characters"
                        class="w-full px-3.5 py-2.5 bg-surface border rounded-lg text-sm text-ink placeholder-ink-mute transition-colors focus:outline-none border-line hover:border-line-strong focus:border-brass focus:ring-2 focus:ring-brass/20"
                    />
                    @error('password')
                        <p class="text-[11px] text-[#9C4A3E]" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="space-y-1.5">
                    <label for="password_confirmation" class="block text-xs font-medium text-ink-soft">Confirm password</label>
                    <input
                        type="password"
                        name="password_confirmation"
                        id="password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="Re-enter your password"
                        class="w-full px-3.5 py-2.5 bg-surface border rounded-lg text-sm text-ink placeholder-ink-mute transition-colors focus:outline-none border-line hover:border-line-strong focus:border-brass focus:ring-2 focus:ring-brass/20"
                    />
                </div>

                <button
                    type="submit"
                    class="w-full mt-2 py-2.5 px-5 rounded-lg bg-brass text-[#14130F] font-medium text-sm transition-colors flex items-center justify-center gap-2 cursor-pointer hover:bg-[#B89149] focus:outline-none focus-visible:ring-2 focus-visible:ring-brass focus-visible:ring-offset-2 focus-visible:ring-offset-surface"
                >
                    <span>Create Account</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </button>
            </form>

            <div class="mt-6 pt-5 border-t border-line flex items-center justify-between text-xs text-ink-mute">
                <span>Already have an account?</span>
                <a href="{{ route('login') }}" class="font-medium text-brass-ink hover:underline">
                    Sign in
                </a>
            </div>
        </div>

    </div>
</div>
@endsection
