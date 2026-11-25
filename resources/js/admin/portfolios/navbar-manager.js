/**
 * Portfolio CMS — Dynamic Navbar Configuration Manager
 * Handles Add, Remove, Reorder, Visibility Toggle, Destination Type Switch, and 10-Item Limit.
 */

export function initNavbarManager() {
    const container = document.getElementById('navbar-items-container');
    const btnAdd = document.getElementById('btn-add-navbar-item');
    const countDisplay = document.getElementById('navbar-count-display');
    const limitBanner = document.getElementById('navbar-limit-message');
    const template = document.getElementById('navbar-item-template');

    if (!container || !btnAdd) return;

    const MAX_ITEMS = 10;

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

    function updateCounterAndLimits() {
        const items = container.querySelectorAll('.navbar-item-card');
        const count = items.length;

        if (countDisplay) {
            countDisplay.textContent = count;
        }

        const isLimitReached = count >= MAX_ITEMS;

        btnAdd.disabled = isLimitReached;
        if (isLimitReached) {
            btnAdd.classList.add('opacity-40', 'cursor-not-allowed');
            if (limitBanner) limitBanner.classList.remove('hidden');
        } else {
            btnAdd.classList.remove('opacity-40', 'cursor-not-allowed');
            if (limitBanner) limitBanner.classList.add('hidden');
        }

        items.forEach((card, idx) => {
            const orderNum = idx + 1;

            const badge = card.querySelector('.sort-order-badge');
            if (badge) badge.textContent = `#${orderNum}`;

            const sortInput = card.querySelector('.input-sort-order');
            if (sortInput) sortInput.value = orderNum;

            const btnUp = card.querySelector('.btn-move-up');
            const btnDown = card.querySelector('.btn-move-down');

            if (btnUp) {
                btnUp.disabled = idx === 0;
                btnUp.classList.toggle('text-line-strong', idx === 0);
                btnUp.classList.toggle('cursor-not-allowed', idx === 0);
                btnUp.classList.toggle('text-ink-mute', idx !== 0);
            }

            if (btnDown) {
                btnDown.disabled = idx === items.length - 1;
                btnDown.classList.toggle('text-line-strong', idx === items.length - 1);
                btnDown.classList.toggle('cursor-not-allowed', idx === items.length - 1);
                btnDown.classList.toggle('text-ink-mute', idx !== items.length - 1);
            }
        });
    }

    function bindCardEvents(card) {
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
                    btnToggleVis.className = 'btn-toggle-visibility inline-flex items-center gap-1.5 px-2 py-1 rounded-md text-[11px] font-medium bg-[#EEF3EC] text-[#3F5A38] border border-[#DAE4D5] transition-colors';
                    if (iconEye) {
                        iconEye.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
                    }
                    card.classList.remove('opacity-60');
                } else {
                    visLabel.textContent = 'Hidden';
                    btnToggleVis.className = 'btn-toggle-visibility inline-flex items-center gap-1.5 px-2 py-1 rounded-md text-[11px] font-medium bg-paper text-ink-mute border border-line transition-colors';
                    if (iconEye) {
                        iconEye.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />';
                    }
                    card.classList.add('opacity-60');
                }
            });
        }

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

        const btnRemove = card.querySelector('.btn-remove-item');
        if (btnRemove && !card.dataset.isDefault) {
            btnRemove.addEventListener('click', () => {
                card.style.opacity = '0';
                card.style.transition = 'opacity 0.18s ease';
                setTimeout(() => {
                    card.remove();
                    updateCounterAndLimits();
                }, 180);
            });
        }
    }

    container.querySelectorAll('.navbar-item-card').forEach(bindCardEvents);
    updateCounterAndLimits();

    btnAdd.addEventListener('click', () => {
        const currentCount = container.querySelectorAll('.navbar-item-card').length;
        if (currentCount >= MAX_ITEMS) return;

        itemCounter++;
        const newIndex = itemCounter;
        const newOrder = currentCount + 1;

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

        const labelInput = newCard.querySelector('input[name*="[label]"]');
        if (labelInput) {
            labelInput.focus();
            labelInput.select();
        }
    });
}
