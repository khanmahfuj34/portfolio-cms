@extends('layouts.admin-auth')

@section('title', 'Sign In — Portfolio CMS')

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
                    Build, organize, and publish your personal portfolios with dedicated subdomains.
                </p>
            </div>

            <div class="text-[11px] text-[#7C7668]">
                Manage your own portfolios, skills, and projects.
            </div>
        </div>

        <!-- Form panel -->
        <div class="p-6 sm:p-9">
            <div class="mb-7">
                <p class="text-[11px] text-ink-mute mb-1">Welcome back</p>
                <h2 class="font-display text-3xl leading-none text-ink">Sign in</h2>
                <p class="text-xs text-ink-soft mt-2">Enter your email and password to open your dashboard.</p>
            </div>

            @if (isset($errors) && $errors->has('email') && in_array($errors->first('email'), ['Invalid email or password.', 'Your account has been deactivated.'], true))
                <div role="alert" class="mb-5 p-3.5 rounded-lg bg-[#FBF1EF] border border-[#E9D2CC] border-l-2 border-l-[#B4553F] text-xs flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-[#9C4A3E] shrink-0 mt-px" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <div class="flex-1 text-ink">
                        <span class="font-medium block text-[#9C4A3E]">Sign-in failed</span>
                        <span class="text-ink-soft">
                            {{ $errors->first('email') === 'Your account has been deactivated.' ? 'Your account has been deactivated.' : 'Invalid email or password, or your account is inactive.' }}
                        </span>
                    </div>
                </div>
            @elseif (session('status'))
                <div role="status" class="mb-5 p-3.5 rounded-lg bg-brass-wash border border-[#E7DCC1] text-xs flex items-center gap-2.5 text-ink">
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            <form action="{{ route('login.submit') }}" method="POST" class="space-y-5" novalidate>
                @csrf

                <!-- Email -->
                <div class="space-y-1.5">
                    <label for="email" class="block text-xs font-medium text-ink-soft">Email</label>
                    <div class="relative rounded-lg">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-ink-mute" aria-hidden="true">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="email"
                            placeholder="you@example.com"
                            class="w-full pl-9 pr-3 py-2.5 bg-surface border rounded-lg text-sm text-ink placeholder-ink-mute transition-colors focus:outline-none border-line hover:border-line-strong focus:border-brass focus:ring-2 focus:ring-brass/20"
                        />
                    </div>
                    @error('email')
                        @if (! in_array($message, ['Invalid email or password.', 'Your account has been deactivated.'], true))
                            <p class="text-[11px] text-[#9C4A3E] mt-1" role="alert">{{ $message }}</p>
                        @endif
                    @enderror
                </div>

                <!-- Password -->
                <div class="space-y-1.5">
                    <label for="password" class="block text-xs font-medium text-ink-soft">Password</label>
                    <div class="relative rounded-lg">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-ink-mute" aria-hidden="true">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••••••"
                            class="w-full pl-9 pr-10 py-2.5 bg-surface border rounded-lg text-sm text-ink placeholder-ink-mute transition-colors focus:outline-none border-line hover:border-line-strong focus:border-brass focus:ring-2 focus:ring-brass/20"
                        />
                    </div>
                    @error('password')
                        <p class="text-[11px] text-[#9C4A3E] mt-1" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between text-xs">
                    <label class="inline-flex items-center gap-2 text-ink-soft cursor-pointer">
                        <input type="checkbox" name="remember" value="1" class="rounded border-line text-brass focus:ring-brass">
                        <span>Keep me signed in</span>
                    </label>
                </div>

                <button
                    type="submit"
                    class="w-full py-2.5 px-5 rounded-lg bg-brass text-[#14130F] font-medium text-sm transition-colors flex items-center justify-center gap-2 cursor-pointer hover:bg-[#B89149] focus:outline-none focus-visible:ring-2 focus-visible:ring-brass focus-visible:ring-offset-2 focus-visible:ring-offset-surface"
                >
                    <span>Sign In</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </button>
            </form>

            <div class="mt-6 pt-5 border-t border-line flex items-center justify-between text-xs text-ink-mute">
                <span>Need an account?</span>
                <a href="{{ route('register') }}" class="font-medium text-brass-ink hover:underline">
                    Create account
                </a>
            </div>
        </div>

    </div>
</div>
@endsection
