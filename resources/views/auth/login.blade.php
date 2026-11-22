@extends('layouts.admin-auth')

@section('title', 'Super Admin Login — Portfolio CMS')

@section('content')
<div class="w-full max-w-[440px] mx-auto">
    <!-- Card Frame with Subtle Gold Border Gradient -->
    <div class="relative p-[1px] rounded-3xl bg-gradient-to-b from-[#D4AF69]/40 via-[#D4AF69]/10 to-white/5 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.9),0_0_35px_-5px_rgba(212,175,105,0.08)]">
        
        <!-- Card Inner Surface -->
        <div class="relative rounded-[23px] bg-[#12151D]/90 backdrop-blur-2xl px-6 py-8 sm:px-9 sm:py-10">
            
            <!-- Branding Header -->
            <div class="text-center mb-8">
                <!-- Luxury Monogram Emblem -->
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-b from-[#1E232E] to-[#12151D] border border-[#D4AF69]/30 shadow-[0_0_20px_rgba(212,175,105,0.15)] mb-4 transition-transform duration-300 hover:scale-105" aria-hidden="true">
                    <svg class="w-7 h-7 text-[#D4AF69]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                        <path d="M2 17l10 5 10-5"></path>
                        <path d="M2 12l10 5 10-5"></path>
                    </svg>
                </div>

                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-semibold tracking-widest uppercase bg-[#D4AF69]/10 text-[#D4AF69] border border-[#D4AF69]/25 mb-2.5">
                    <svg class="w-3 h-3 text-[#D4AF69]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    Super Admin Console
                </div>

                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight">
                    <span class="text-white">Portfolio</span> <span class="gold-gradient-text">CMS</span>
                </h1>
                <p class="mt-2 text-xs sm:text-sm text-slate-400">
                    Authenticate to manage content and infrastructure
                </p>
            </div>

            <!-- Global Error / Authentication Failed Banner -->
            @if (isset($errors) && $errors->has('email') && $errors->first('email') === 'Invalid email or password.')
                <div role="alert" class="mb-6 p-3.5 rounded-xl bg-red-950/40 border border-red-500/30 text-red-200 text-xs sm:text-sm flex items-start gap-3 shadow-lg shadow-red-950/20 animate-shake">
                    <svg class="w-5 h-5 text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <div class="flex-1">
                        <span class="font-semibold block text-red-300">Authentication Failed</span>
                        <span>Invalid email or password, or inactive admin privileges.</span>
                    </div>
                </div>
            @elseif (session('status'))
                <div role="status" class="mb-6 p-3.5 rounded-xl bg-[#D4AF69]/10 border border-[#D4AF69]/30 text-[#F2DAA4] text-xs sm:text-sm flex items-center gap-3">
                    <svg class="w-5 h-5 text-[#D4AF69] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            <!-- Login Form -->
            <form action="{{ route('admin.login.submit') }}" method="POST" class="space-y-5" novalidate id="adminLoginForm">
                @csrf

                <!-- Email Input -->
                <div class="space-y-1.5">
                    <label for="email" class="block text-xs font-medium text-slate-300 uppercase tracking-wider">
                        Administrator Email <span class="text-[#D4AF69]" aria-hidden="true">*</span>
                    </label>
                    <div class="relative rounded-xl transition duration-200">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400" aria-hidden="true">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
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
                            class="w-full pl-10 pr-4 py-3 bg-[#181C26]/90 border rounded-xl text-sm text-white placeholder-slate-500 transition duration-200 focus:outline-none {{ (isset($errors) && $errors->has('email') && $errors->first('email') !== 'Invalid email or password.') ? 'border-red-500/70 focus:border-red-500 focus:ring-2 focus:ring-red-500/30' : 'border-white/10 hover:border-white/20 focus:border-[#D4AF69] focus:ring-2 focus:ring-[#D4AF69]/25' }}"
                        />
                    </div>
                    @error('email')
                        @if ($message !== 'Invalid email or password.')
                            <p id="email-error" class="text-xs text-red-400 mt-1 flex items-center gap-1.5" role="alert">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ $message }}
                            </p>
                        @endif
                    @enderror
                </div>

                <!-- Password Input -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label for="password" class="block text-xs font-medium text-slate-300 uppercase tracking-wider">
                            Master Password <span class="text-[#D4AF69]" aria-hidden="true">*</span>
                        </label>
                        <span class="text-[11px] text-slate-500 flex items-center gap-1" title="Protected with bcrypt hashing">
                            <svg class="w-3 h-3 text-[#D4AF69]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            Secured
                        </span>
                    </div>
                    <div class="relative rounded-xl transition duration-200">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400" aria-hidden="true">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
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
                            class="w-full pl-10 pr-11 py-3 bg-[#181C26]/90 border rounded-xl text-sm text-white placeholder-slate-500 transition duration-200 focus:outline-none {{ (isset($errors) && $errors->has('password')) ? 'border-red-500/70 focus:border-red-500 focus:ring-2 focus:ring-red-500/30' : 'border-white/10 hover:border-white/20 focus:border-[#D4AF69] focus:ring-2 focus:ring-[#D4AF69]/25' }}"
                        />
                        <!-- Show / Hide Password Button -->
                        <button
                            type="button"
                            id="togglePasswordBtn"
                            onclick="togglePassword()"
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-[#D4AF69] focus:outline-none focus:text-[#D4AF69] transition-colors duration-200 cursor-pointer"
                            aria-label="Show password"
                            aria-pressed="false"
                        >
                            <!-- Eye Open Icon -->
                            <svg id="eyeIconOpen" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <!-- Eye Closed Icon -->
                            <svg id="eyeIconClosed" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p id="password-error" class="text-xs text-red-400 mt-1 flex items-center gap-1.5" role="alert">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    class="w-full mt-2 py-3.5 px-5 rounded-xl gold-btn-gradient text-[#0B0D12] font-bold text-sm tracking-wide transition-all duration-200 flex items-center justify-center gap-2 group cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#D4AF69] focus:ring-offset-2 focus:ring-offset-[#12151D] active:scale-[0.99] motion-reduce:transform-none motion-reduce:transition-none"
                >
                    <span>Sign In to Dashboard</span>
                    <svg class="w-4 h-4 transition-transform duration-200 group-hover:translate-x-1 motion-reduce:transform-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </button>
            </form>

            <!-- Navigation Back to Site -->
            <div class="mt-8 pt-6 border-t border-white/5 text-center">
                <a
                    href="{{ url('/') }}"
                    class="inline-flex items-center gap-1.5 text-xs text-slate-400 hover:text-[#D4AF69] transition-colors duration-200"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span>Return to Website</span>
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
