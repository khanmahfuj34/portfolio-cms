<div class="rounded-2xl bg-white border border-slate-200/80 p-6 sm:p-7 shadow-xs space-y-6 relative overflow-hidden group">
    <!-- Subtle Ambient Top-Right Accent -->
    <div class="absolute -top-12 -right-12 w-32 h-32 bg-[#D4AF69]/5 rounded-full blur-2xl group-hover:bg-[#D4AF69]/10 transition-all duration-500 pointer-events-none" aria-hidden="true"></div>

    <!-- Section Header -->
    <div class="flex items-start gap-4 pb-5 border-b border-slate-100">
        <div class="w-11 h-11 rounded-xl bg-gradient-to-tr from-[#0F1219] to-[#252C3C] text-[#D4AF69] flex items-center justify-center flex-shrink-0 border border-[#D4AF69]/30 shadow-xs">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
        </div>
        <div class="min-w-0">
            <h3 class="text-base font-bold text-slate-900 tracking-tight flex items-center gap-2">
                <span>Portfolio Information</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#D4AF69]/15 text-[#9A7326] border border-[#D4AF69]/25">
                    Core Site
                </span>
            </h3>
            <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">
                Configure primary identity and web address routing for this portfolio profile.
            </p>
        </div>
    </div>

    <!-- Fields Grid -->
    <div class="space-y-5">
        <!-- 1. Portfolio Name -->
        <x-admin.form.input 
            label="Portfolio Name" 
            name="name" 
            id="portfolio-name-input"
            placeholder="e.g. Minimalist Architecture & Design Studio" 
            :required="true" 
            helper="Enter a descriptive public title for this portfolio showcase."
        />

        <!-- 2. Portfolio Slug & Live URL Preview -->
        <div class="space-y-2">
            <x-admin.form.input 
                label="Portfolio Slug" 
                name="slug" 
                id="portfolio-slug-input"
                placeholder="e.g. minimalist-architecture" 
                :required="true" 
                helper="The unique URL identifier used to access this portfolio. Only lowercase letters, numbers, and hyphens."
            />

            <!-- Live URL Preview Box -->
            <div class="p-3 rounded-xl bg-slate-900 text-slate-300 border border-slate-800 text-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 shadow-xs">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-[#D4AF69]/15 text-[#E8C98E] text-[10px] font-semibold uppercase tracking-wider flex-shrink-0">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        Live URL Preview
                    </span>
                    <span class="font-mono text-slate-400 truncate">
                        http://127.0.0.1:8000/<span id="slug-preview-display" class="text-[#D4AF69] font-bold">{{ old('slug', 'your-slug') }}</span>
                    </span>
                </div>
                <span class="text-[10px] text-slate-500 self-end sm:self-auto flex-shrink-0">
                    Auto-generated from name
                </span>
            </div>
        </div>

        <!-- 3. Short Description -->
        <x-admin.form.textarea 
            label="Short Description" 
            name="description" 
            id="portfolio-description-input"
            rows="4" 
            placeholder="A brief summary describing the purpose and creative theme of this portfolio..."
            maxlength="5000"
            helper="Recommended 120-160 characters. Used for meta descriptions and header summaries."
        />
    </div>
</div>
