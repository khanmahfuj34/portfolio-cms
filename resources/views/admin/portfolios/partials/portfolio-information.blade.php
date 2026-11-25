<div class="rounded-xl bg-surface border border-line p-6 sm:p-7 space-y-6">

    <!-- Section header -->
    <div class="flex items-start gap-3 pb-5 border-b border-line">
        <div class="w-9 h-9 rounded-md bg-brass-wash text-brass-ink flex items-center justify-center flex-shrink-0">
            <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
        </div>
        <div class="min-w-0">
            <h3 class="text-sm font-medium text-ink">Portfolio Information</h3>
            <p class="text-xs text-ink-mute mt-0.5 leading-relaxed">
                Primary identity and web address for this portfolio.
            </p>
        </div>
    </div>

    <div class="space-y-5">
        <x-admin.form.input
            label="Portfolio name"
            name="name"
            id="portfolio-name-input"
            placeholder="Studio name or showcase title"
            :required="true"
            helper="The public title shown for this portfolio."
        />

        <div class="space-y-2">
            <x-admin.form.input
                label="Portfolio slug"
                name="slug"
                id="portfolio-slug-input"
                placeholder="minimalist-architecture"
                :required="true"
                helper="Lowercase letters, numbers, and hyphens only."
            />

            <!-- URL preview -->
            <div class="p-3 rounded-lg bg-paper-sunk border border-line text-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1.5">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="text-[11px] text-ink-mute flex-shrink-0">URL</span>
                    <span class="font-mono text-ink-soft truncate">
                        http://127.0.0.1:8000/<span id="slug-preview-display" class="text-brass-ink font-medium">{{ old('slug', 'your-slug') }}</span>
                    </span>
                </div>
                <span class="text-[11px] text-ink-mute self-start sm:self-auto flex-shrink-0">
                    Auto-generated from name
                </span>
            </div>
        </div>

        <x-admin.form.textarea
            label="Short description"
            name="description"
            id="portfolio-description-input"
            rows="4"
            placeholder="A brief summary of this portfolio's purpose and theme"
            maxlength="5000"
            helper="Used for meta descriptions and header summaries. 120–160 characters reads best."
        />
    </div>
</div>
