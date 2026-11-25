<div class="rounded-xl bg-surface border border-line p-6 sm:p-7 space-y-6">

    <!-- Section header -->
    <div class="flex items-start gap-3 pb-5 border-b border-line">
        <div class="w-9 h-9 rounded-md bg-brass-wash text-brass-ink flex items-center justify-center flex-shrink-0">
            <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
        </div>
        <div class="min-w-0">
            <h3 class="text-sm font-medium text-ink">Owner Profile Information</h3>
            <p class="text-xs text-ink-mute mt-0.5 leading-relaxed">
                Bio and contact details shown within this portfolio.
            </p>
        </div>
    </div>

    <!-- Notice -->
    <div class="p-3.5 rounded-lg bg-brass-wash/60 border border-[#E7DCC1] text-xs flex items-start gap-2.5">
        <svg class="w-4 h-4 text-brass-ink flex-shrink-0 mt-px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="min-w-0 leading-relaxed text-ink-soft">
            <span class="font-medium text-ink">Admin managed.</span>
            Portfolio owners do not have login access. Profile data is edited here by the Super Admin.
        </p>
    </div>

    <div class="space-y-4">
        <x-admin.form.input
            label="Owner full name"
            name="full_name"
            id="owner-fullname-input"
            placeholder="Individual or studio name"
            :required="true"
            helper="Displayed as the portfolio owner."
        />

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-admin.form.input
                label="Professional title"
                name="professional_title"
                id="owner-title-input"
                placeholder="Principal architect"
                helper="Headline or specialization."
            />

            <x-admin.form.input
                label="Public contact email"
                name="email"
                type="email"
                id="owner-email-input"
                placeholder="contact@studio.com"
                helper="Used for public inquiries."
            />
        </div>

        <x-admin.form.input
            label="Location"
            name="location"
            id="owner-location-input"
            placeholder="City, country"
            helper="Base city or remote availability."
        />

        <x-admin.form.textarea
            label="Professional biography"
            name="bio"
            id="owner-bio-input"
            rows="4"
            placeholder="Background, design philosophy, notable clients, accolades"
            maxlength="5000"
            helper="Shown in the About section of the portfolio."
        />
    </div>
</div>
