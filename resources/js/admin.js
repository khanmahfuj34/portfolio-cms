/**
 * Portfolio CMS — Super Admin Interactions
 * Handles slug generation, live URL preview, and dynamic Navbar Configuration.
 */

document.addEventListener('DOMContentLoaded', () => {
    initSlugGenerator();
    initNavbarManager();
    initFormActions();
});

/**
 * 1. Live Slug Auto-Generator and URL Preview
 */
function initSlugGenerator() {
    const nameInput = document.getElementById('portfolio-name-input');
    const slugInput = document.getElementById('portfolio-slug-input');
    const slugPreview = document.getElementById('slug-preview-display');

    if (!nameInput || !slugInput || !slugPreview) return;

    let isSlugManuallyEdited = slugInput.value.trim().length > 0;

    // Helper to sanitize slug
    function generateSlug(text) {
        return text
            .toString()
            .toLowerCase()
            .trim()
            .replace(/[\s\W-]+/g, '-') // replace spaces and non-word chars with hyphen
            .replace(/^-+|-+$/g, '');   // trim leading/trailing hyphens
    }

    // Auto-update slug on name input if not manually modified
    nameInput.addEventListener('input', (e) => {
        if (!isSlugManuallyEdited) {
            const slug = generateSlug(e.target.value);
            slugInput.value = slug;
            slugPreview.textContent = slug || 'your-slug';
        }
    });

    // Manual edits on slug input
    slugInput.addEventListener('input', (e) => {
        isSlugManuallyEdited = e.target.value.trim().length > 0;
        const cleanSlug = generateSlug(e.target.value);
        slugPreview.textContent = cleanSlug || 'your-slug';
    });
}

/**
 * 2. Dynamic Navbar Configuration Manager
 * Handles Add, Remove, Reorder, Visibility Toggle, Destination Type Switch, and 10-Item Limit.
 */
function initNavbarManager() {
    const container = document.getElementById('navbar-items-container');
    const btnAdd = document.getElementById('btn-add-navbar-item');
    const countDisplay = document.getElementById('navbar-count-display');
    const limitBanner = document.getElementById('navbar-limit-message');
    const template = document.getElementById('navbar-item-template');

    if (!container || !btnAdd) return;

    const MAX_ITEMS = 10;

    // Smart default presets for newly added links
    const PRESETS = [
        { label: 'About', section: 'about' },
        { label: 'Projects', section: 'projects' },
        { label: 'Skills', section: 'skills' },
        { label: 'Experience', section: 'experience' },
        { label: 'Services', section: 'services' },
        { label: 'Contact', section: 'contact' },
        { label: 'Blog', section: 'blog' },
        { label: 'Testimonials', section: 'testimonials' },
        { label: 'GitHub', url: 'https://github.com' }
    ];

    // Determine starting item counter from existing cards and input indices
    let maxIndex = 0;
    container.querySelectorAll('.navbar-item-card').forEach(card => {
        const inputWithName = card.querySelector('[name*="nav_items["]');
        if (inputWithName) {
            const match = inputWithName.name.match(/nav_items\[(\d+)\]/);
            if (match) {
                const num = parseInt(match[1], 10);
                if (num > maxIndex) maxIndex = num;
            }
        }
    });
    let itemCounter = Math.max(container.querySelectorAll('.navbar-item-card').length, maxIndex);

    /**
     * Update live counter and check maximum limit
     */
    function updateCounterAndLimits() {
        const items = container.querySelectorAll('.navbar-item-card');
        const count = items.length;

        if (countDisplay) {
            countDisplay.textContent = count;
        }

        const isLimitReached = count >= MAX_ITEMS;

        // Toggle Add button state
        btnAdd.disabled = isLimitReached;
        if (isLimitReached) {
            btnAdd.classList.add('opacity-40', 'cursor-not-allowed');
            if (limitBanner) limitBanner.classList.remove('hidden');
        } else {
            btnAdd.classList.remove('opacity-40', 'cursor-not-allowed');
            if (limitBanner) limitBanner.classList.add('hidden');
        }

        // Re-index all cards in DOM order
        items.forEach((card, idx) => {
            const orderNum = idx + 1;

            // Update badge #1, #2...
            const badge = card.querySelector('.sort-order-badge');
            if (badge) badge.textContent = `#${orderNum}`;

            // Update hidden sort order input
            const sortInput = card.querySelector('.input-sort-order');
            if (sortInput) sortInput.value = orderNum;

            // Update arrow button states
            const btnUp = card.querySelector('.btn-move-up');
            const btnDown = card.querySelector('.btn-move-down');

            if (btnUp) {
                btnUp.disabled = idx === 0;
                btnUp.classList.toggle('text-slate-300', idx === 0);
                btnUp.classList.toggle('cursor-not-allowed', idx === 0);
                btnUp.classList.toggle('text-slate-500', idx !== 0);
            }

            if (btnDown) {
                btnDown.disabled = idx === items.length - 1;
                btnDown.classList.toggle('text-slate-300', idx === items.length - 1);
                btnDown.classList.toggle('cursor-not-allowed', idx === items.length - 1);
                btnDown.classList.toggle('text-slate-500', idx !== items.length - 1);
            }
        });
    }

    /**
     * Attach interactive event listeners to a navbar item card
     */
    function bindCardEvents(card) {
        // Destination Type Change
        const typeSelect = card.querySelector('.destination-type-select');
        const sectionWrapper = card.querySelector('.section-key-container');
        const urlWrapper = card.querySelector('.external-url-container');
        const sectionInput = card.querySelector('input[name*="section_key"]');
        const urlInput = card.querySelector('input[name*="url"]');

        if (typeSelect && sectionWrapper && urlWrapper) {
            typeSelect.addEventListener('change', () => {
                const isSection = typeSelect.value === 'section';

                if (isSection) {
                    sectionWrapper.classList.remove('hidden');
                    urlWrapper.classList.add('hidden');
                    if (sectionInput) {
                        sectionInput.disabled = false;
                        sectionInput.required = true;
                    }
                    if (urlInput) {
                        urlInput.disabled = true;
                        urlInput.required = false;
                    }
                } else {
                    sectionWrapper.classList.add('hidden');
                    urlWrapper.classList.remove('hidden');
                    if (sectionInput) {
                        sectionInput.disabled = true;
                        sectionInput.required = false;
                    }
                    if (urlInput) {
                        urlInput.disabled = false;
                        urlInput.required = true;
                    }
                }
            });
        }

        // Label Auto-generate section_key
        const labelInput = card.querySelector('input[name*="[label]"]');
        if (labelInput && sectionInput && !card.dataset.isDefault) {
            let isSectionManuallyEdited = sectionInput.value.trim().length > 0;

            sectionInput.addEventListener('input', () => {
                isSectionManuallyEdited = sectionInput.value.trim().length > 0;
            });

            labelInput.addEventListener('input', (e) => {
                if (typeSelect && typeSelect.value === 'section' && !isSectionManuallyEdited) {
                    sectionInput.value = e.target.value
                        .toLowerCase()
                        .trim()
                        .replace(/[\s\W-]+/g, '_')
                        .replace(/^_+|_+$/g, '');
                }
            });
        }

        // Visibility Toggle
        const btnToggleVis = card.querySelector('.btn-toggle-visibility');
        const isVisibleInput = card.querySelector('.input-is-visible');
        const visLabel = card.querySelector('.visibility-label');
        const iconEye = card.querySelector('.icon-eye');

        if (btnToggleVis && isVisibleInput && visLabel) {
            btnToggleVis.addEventListener('click', () => {
                const isCurrentlyVisible = btnToggleVis.getAttribute('data-visible') === 'true';
                const newVisible = !isCurrentlyVisible;

                btnToggleVis.setAttribute('data-visible', String(newVisible));
                isVisibleInput.value = newVisible ? '1' : '0';

                if (newVisible) {
                    visLabel.textContent = 'Visible';
                    btnToggleVis.className = 'btn-toggle-visibility inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition-colors';
                    if (iconEye) {
                        iconEye.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
                    }
                    card.classList.remove('opacity-60');
                } else {
                    visLabel.textContent = 'Hidden';
                    btnToggleVis.className = 'btn-toggle-visibility inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-medium bg-slate-100 text-slate-500 border border-slate-200 hover:bg-slate-200/60 transition-colors';
                    if (iconEye) {
                        iconEye.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />';
                    }
                    card.classList.add('opacity-60');
                }
            });
        }

        // Move Up Button
        const btnUp = card.querySelector('.btn-move-up');
        if (btnUp) {
            btnUp.addEventListener('click', () => {
                const prev = card.previousElementSibling;
                if (prev) {
                    container.insertBefore(card, prev);
                    updateCounterAndLimits();
                }
            });
        }

        // Move Down Button
        const btnDown = card.querySelector('.btn-move-down');
        if (btnDown) {
            btnDown.addEventListener('click', () => {
                const next = card.nextElementSibling;
                if (next) {
                    container.insertBefore(next, card);
                    updateCounterAndLimits();
                }
            });
        }

        // Remove Item Button (Only on non-default cards)
        const btnRemove = card.querySelector('.btn-remove-item');
        if (btnRemove && !card.dataset.isDefault) {
            btnRemove.addEventListener('click', () => {
                card.style.transform = 'scale(0.96)';
                card.style.opacity = '0';
                card.style.transition = 'all 0.2s ease';
                setTimeout(() => {
                    card.remove();
                    updateCounterAndLimits();
                }, 200);
            });
        }
    }

    // Bind existing elements (Default Home item)
    container.querySelectorAll('.navbar-item-card').forEach(bindCardEvents);
    updateCounterAndLimits();

    // Add New Navbar Item Click
    btnAdd.addEventListener('click', () => {
        const currentCount = container.querySelectorAll('.navbar-item-card').length;
        if (currentCount >= MAX_ITEMS) {
            showToast('Maximum of 10 navbar items reached.', 'error');
            return;
        }

        itemCounter++;
        const newIndex = itemCounter;
        const newOrder = currentCount + 1;

        // Choose a preset based on current count
        const preset = PRESETS[(currentCount - 1) % PRESETS.length];
        const defaultLabel = preset.label;
        const defaultSection = preset.section || '';

        if (!template) return;

        let html = template.innerHTML
            .replace(/__ID__/g, `item-${newIndex}`)
            .replace(/__INDEX__/g, String(newIndex))
            .replace(/__ORDER__/g, String(newOrder))
            .replace(/__LABEL__/g, defaultLabel)
            .replace(/__SECTION_KEY__/g, defaultSection);

        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html.trim();
        const newCard = tempDiv.firstElementChild;

        container.appendChild(newCard);
        bindCardEvents(newCard);
        updateCounterAndLimits();

        // Focus the newly added link's label input
        const labelInput = newCard.querySelector('input[name*="[label]"]');
        if (labelInput) {
            labelInput.focus();
            labelInput.select();
        }
    });
}

/**
 * 3. Unified Create Profile Form Action
 */
function initFormActions() {
    const form = document.getElementById('portfolio-create-form');
    if (!form) return;

    // Additional client-side validation / submit polish if required
    form.addEventListener('submit', (e) => {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-75');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-[#0B0D12]" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span>Creating Profile...</span>
            `;
            // If submission takes longer, revert disabled after a delay
            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-75');
                submitBtn.innerHTML = originalText;
            }, 6000);
        }
    });
}

/**
 * Interactive Toast Notification
 */
function showToast(message, type = 'info') {
    let container = document.getElementById('admin-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'admin-toast-container';
        container.className = 'fixed bottom-5 right-5 z-50 flex flex-col gap-2 max-w-sm pointer-events-none';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    const bgColors = type === 'success' ? 'bg-[#0F1219] border-[#D4AF69]/40 text-slate-100' : 'bg-rose-900/90 border-rose-700 text-white';
    const icon = type === 'success' 
        ? '<span class="w-2 h-2 rounded-full bg-[#D4AF69] shadow-[0_0_6px_#D4AF69]"></span>' 
        : '<span class="w-2 h-2 rounded-full bg-rose-400"></span>';

    toast.className = `pointer-events-auto p-4 rounded-xl shadow-xl border text-xs flex items-center gap-3 backdrop-blur-md animate-toast ${bgColors}`;
    toast.innerHTML = `
        ${icon}
        <span class="flex-1 font-medium leading-relaxed">${message}</span>
    `;

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}
