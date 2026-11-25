<div class="rounded-xl bg-surface border border-line p-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

        <div class="flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-md bg-paper text-brass-ink flex items-center justify-center flex-shrink-0 border border-line">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>

            <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h3 class="text-sm font-medium text-ink">
                        {{ auth()->user()->name ?? 'Super Administrator' }}
                    </h3>
                    <span class="text-[11px] text-brass-ink bg-brass-wash px-1.5 py-0.5 rounded">Super admin</span>
                </div>
                <p class="text-[11px] text-ink-mute mt-0.5">
                    <span class="font-mono">{{ auth()->user()->email ?? 'admin@portfoliocms.test' }}</span>
                    <span class="mx-1.5 text-line-strong">·</span>
                    Assigned Administrator
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 text-[11px] text-ink-mute">
            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            <span class="leading-tight">
                <span class="font-medium text-ink-soft block">Read-Only Assignment</span>
                <span>Managed by you</span>
            </span>
        </div>

    </div>
</div>
