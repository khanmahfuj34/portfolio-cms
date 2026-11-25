@extends('layouts.admin-auth')

@section('title', 'Super Admin Login — Portfolio CMS')

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

                <p class="text-[11px] text-[#8C877A] mb-2">Admin workspace</p>
                <h1 class="font-display text-4xl leading-none text-[#F3F1EC]">Portfolio CMS</h1>
                <div class="mt-5 h-px w-10 bg-brass"></div>
                <p class="mt-5 text-sm leading-relaxed text-[#B0AA9C]">
                    One place for every client portfolio, owner profile and navigation set.
                </p>
            </div>

            <div class="text-[11px] text-[#7C7668]">
                Access is limited to administrators.
            </div>
        </div>

        <!-- Form panel -->
        <div class="p-6 sm:p-9">
            <div class="mb-7">
                <p class="text-[11px] text-ink-mute mb-1">Sign in</p>
                <h2 class="font-display text-3xl leading-none text-ink">Administrator</h2>
                <p class="text-xs text-ink-soft mt-2">Use your administrator credentials to continue.</p>
            </div>

            <!-- Error / status -->
            @if (isset($errors) && $errors->has('email') && $errors->first('email') === 'Invalid email or password.')
                <div role="alert" class="mb-5 p-3.5 rounded-lg bg-[#FBF1EF] border border-[#E9D2CC] border-l-2 border-l-[#B4553F] text-xs flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-[#9C4A3E] shrink-0 mt-px" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <div class="flex-1 text-ink">
                        <span class="font-medium block text-[#9C4A3E]">Sign-in failed</span>
                        <span class="text-ink-soft">Invalid email or password, or the account is inactive.</span>
                    </div>
                </div>
            @elseif (session('status'))
                <div role="status" class="mb-5 p-3.5 rounded-lg bg-brass-wash border border-[#E7DCC1] text-xs flex items-center gap-2.5 text-ink">
                    <svg class="w-4 h-4 text-brass-ink shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            <form action="{{ route('admin.login.submit') }}" method="POST" class="space-y-5" novalidate id="adminLoginForm">
                @csrf

                <!-- Email -->
                <div class="space-y-1.5">
                    <label for="email" class="block text-xs font-medium text-ink-soft">
                        Email
                    </label>
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
                            aria-required="true"
                            aria-invalid="{{ (isset($errors) && $errors->has('email')) ? 'true' : 'false' }}"
                            @if (isset($errors) && $errors->has('email')) aria-describedby="email-error" @endif
                            placeholder="admin@domain.com"
                            class="w-full pl-9 pr-3 py-2.5 bg-surface border rounded-lg text-sm text-ink placeholder-ink-mute transition-colors focus:outline-none {{ (isset($errors) && $errors->has('email') && $errors->first('email') !== 'Invalid email or password.') ? 'border-[#C98A7B] focus:border-[#B4553F] focus:ring-2 focus:ring-[#B4553F]/20' : 'border-line hover:border-line-strong focus:border-brass focus:ring-2 focus:ring-brass/20' }}"
                        />
                    </div>
                    @error('email')
                        @if ($message !== 'Invalid email or password.')
                            <p id="email-error" class="text-[11px] text-[#9C4A3E] mt-1 flex items-center gap-1.5" role="alert">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ $message }}
                            </p>
                        @endif
                    @enderror
                </div>

                <!-- Password -->
                <div class="space-y-1.5">
                    <label for="password" class="block text-xs font-medium text-ink-soft">
                        Password
                    </label>
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
                            aria-required="true"
                            aria-invalid="{{ (isset($errors) && $errors->has('password')) ? 'true' : 'false' }}"
                            @if (isset($errors) && $errors->has('password')) aria-describedby="password-error" @endif
                            placeholder="••••••••••••"
                            class="w-full pl-9 pr-10 py-2.5 bg-surface border rounded-lg text-sm text-ink placeholder-ink-mute transition-colors focus:outline-none {{ (isset($errors) && $errors->has('password')) ? 'border-[#C98A7B] focus:border-[#B4553F] focus:ring-2 focus:ring-[#B4553F]/20' : 'border-line hover:border-line-strong focus:border-brass focus:ring-2 focus:ring-brass/20' }}"
                        />
                        <button
                            type="button"
                            id="togglePasswordBtn"
                            onclick="togglePassword()"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-ink-mute hover:text-ink focus:outline-none focus-visible:text-ink transition-colors cursor-pointer"
                            aria-label="Show password"
                            aria-pressed="false"
                        >
                            <svg id="eyeIconOpen" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <svg id="eyeIconClosed" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p id="password-error" class="text-[11px] text-[#9C4A3E] mt-1 flex items-center gap-1.5" role="alert">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="w-full mt-1 py-2.5 px-5 rounded-lg bg-brass text-[#14130F] font-medium text-sm transition-colors flex items-center justify-center gap-2 cursor-pointer hover:bg-[#B89149] focus:outline-none focus-visible:ring-2 focus-visible:ring-brass focus-visible:ring-offset-2 focus-visible:ring-offset-surface"
                >
                    <span>Sign In to Dashboard</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </button>
            </form>

            <div class="mt-6 pt-5 border-t border-line">
                <a
                    href="{{ url('/') }}"
                    class="inline-flex items-center gap-1.5 text-xs text-ink-mute hover:text-ink transition-colors"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span>Return to site</span>
                </a>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const eyeOpen = document.getElementById('eyeIconOpen');
        const eyeClosed = document.getElementById('eyeIconClosed');
        const btn = document.getElementById('togglePasswordBtn');

        if (!passwordInput) return;

        const isPassword = passwordInput.getAttribute('type') === 'password';

        if (isPassword) {
            passwordInput.setAttribute('type', 'text');
            eyeOpen.classList.add('hidden');
            eyeClosed.classList.remove('hidden');
            btn.setAttribute('aria-label', 'Hide password');
            btn.setAttribute('aria-pressed', 'true');
        } else {
            passwordInput.setAttribute('type', 'password');
            eyeOpen.classList.remove('hidden');
            eyeClosed.classList.add('hidden');
            btn.setAttribute('aria-label', 'Show password');
            btn.setAttribute('aria-pressed', 'false');
        }
    }
</script>
@endpush
