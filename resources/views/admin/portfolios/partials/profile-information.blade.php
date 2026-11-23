<div class="rounded-2xl bg-white border border-slate-200/80 p-6 sm:p-7 shadow-xs space-y-6 relative overflow-hidden group">
    <!-- Subtle Ambient Top-Right Accent -->
    <div class="absolute -top-12 -right-12 w-32 h-32 bg-[#D4AF69]/5 rounded-full blur-2xl group-hover:bg-[#D4AF69]/10 transition-all duration-500 pointer-events-none" aria-hidden="true"></div>

    <!-- Section Header -->
    <div class="flex items-start gap-4 pb-5 border-b border-slate-100">
        <div class="w-11 h-11 rounded-xl bg-gradient-to-tr from-[#0F1219] to-[#252C3C] text-[#D4AF69] flex items-center justify-center flex-shrink-0 border border-[#D4AF69]/30 shadow-xs">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
        </div>
        <div class="min-w-0">
            <h3 class="text-base font-bold text-slate-900 tracking-tight flex items-center gap-2">
                <span>Owner Profile Information</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200/60">
                    Client Bio
                </span>
            </h3>
            <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">
                Biographical credentials and public contact information displayed within this portfolio.
            </p>
        </div>
    </div>

    <!-- Architecture Callout / Notice: Super Admin Exclusivity -->
    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs flex items-start gap-3">
        <div class="w-5 h-5 rounded-lg bg-[#D4AF69]/20 text-[#9A7326] flex items-center justify-center flex-shrink-0 mt-0.5">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div class="min-w-0 leading-relaxed text-slate-600">
            <span class="font-semibold text-slate-800">Admin Managed Profile:</span> 
            Portfolio owners do not have login credentials or self-service editing privileges. All showcase data is centrally controlled and updated by the Super Admin.
        </div>
    </div>

    <!-- Fields Grid -->
    <div class="space-y-4 pt-1">
        <!-- 1. Full Name -->
        <x-admin.form.input 
            label="Owner Full Name" 
            name="full_name" 
            id="owner-fullname-input"
            placeholder="e.g. Mahfujur Rahman Khan" 
            :required="true" 
            helper="The official individual or studio name displayed on the portfolio."
        />

        <!-- 2. Two-Column Row: Title & Email -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-admin.form.input 
                label="Professional Title" 
                name="professional_title" 
                id="owner-title-input"
                placeholder="e.g. Principal Architect & Creative Director" 
                helper="Headline position or specialization."
            />

            <x-admin.form.input 
                label="Public Contact Email" 
                name="email" 
                type="email"
                id="owner-email-input"
                placeholder="e.g. contact@mahfuj.design" 
                helper="Used for public contact inquiries."
            />
        </div>

        <!-- 3. Location -->
        <x-admin.form.input 
            label="Location / Base" 
            name="location" 
            id="owner-location-input"
            placeholder="e.g. Dhaka, Bangladesh / Available Worldwide" 
            helper="Primary city, country, or remote availability."
        />

        <!-- 4. Professional Bio -->
        <x-admin.form.textarea 
            label="Professional Biography" 
            name="bio" 
            id="owner-bio-input"
            rows="4" 
            placeholder="Write a compelling summary of background, design philosophy, notable clients, or accolades..."
            maxlength="5000"
            helper="Displayed in the portfolio's About and Bio section."
        />
    </div>
</div>
