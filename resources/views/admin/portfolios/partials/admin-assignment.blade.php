<div class="rounded-2xl bg-white border border-slate-200/80 p-5 sm:p-6 shadow-xs relative overflow-hidden group">
    <!-- Subtle Ambient Glow -->
    <div class="absolute -top-10 -right-10 w-32 h-32 bg-[#D4AF69]/5 rounded-full blur-2xl group-hover:bg-[#D4AF69]/10 transition-all duration-500 pointer-events-none" aria-hidden="true"></div>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        
        <!-- Left: Identity & Info -->
        <div class="flex items-center gap-4">
            <!-- Shield / Admin Icon Avatar -->
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-[#0F1219] via-[#1A1F2C] to-[#252C3C] text-[#D4AF69] flex items-center justify-center flex-shrink-0 border border-[#D4AF69]/30 shadow-xs relative">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <span class="absolute -bottom-1 -right-1 w-3.5 h-3.5 rounded-full bg-emerald-500 border-2 border-white ring-1 ring-emerald-400/40" title="Active Administrator"></span>
            </div>

            <!-- Admin Meta -->
            <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h3 class="text-sm font-bold text-slate-900 tracking-tight">
                        {{ auth()->user()->name ?? 'Super Administrator' }}
                    </h3>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#D4AF69]/15 text-[#9A7326] border border-[#D4AF69]/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#D4AF69]"></span>
                        Super Admin
                    </span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                        Assigned Administrator
                    </span>
                </div>
                <div class="flex items-center gap-2 mt-1 text-xs text-slate-500">
                    <span class="font-mono text-slate-600">{{ auth()->user()->email ?? 'admin@portfoliocms.test' }}</span>
                    <span>&bull;</span>
                    <span class="text-slate-400">Sole System Administrator</span>
                </div>
            </div>
        </div>

        <!-- Right: Read-Only Badge -->
        <div class="flex items-center gap-2 sm:self-center bg-slate-50 border border-slate-200/80 rounded-xl px-3.5 py-2 text-xs text-slate-600 shadow-2xs">
            <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            <div class="leading-tight">
                <span class="font-semibold text-slate-700 block">Read-Only Assignment</span>
                <span class="text-[11px] text-slate-400">Centralized Super Admin control</span>
            </div>
        </div>

    </div>
</div>
