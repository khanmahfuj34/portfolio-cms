/**
 * Portfolio CMS — Portfolio Form & Slug Generator
 */

export function initSlugGenerator() {
    const nameInput = document.getElementById('portfolio-name-input');
    const slugInput = document.getElementById('portfolio-slug-input');
    const slugPreview = document.getElementById('slug-preview-display');

    if (!nameInput || !slugInput || !slugPreview) return;

    let isSlugManuallyEdited = slugInput.value.trim().length > 0;

    function generateSlug(text) {
        return text
            .toString()
            .toLowerCase()
            .trim()
            .replace(/[\s\W-]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    nameInput.addEventListener('input', (e) => {
        if (!isSlugManuallyEdited) {
            const slug = generateSlug(e.target.value);
            slugInput.value = slug;
            slugPreview.textContent = slug || 'your-slug';
        }
    });

    slugInput.addEventListener('input', (e) => {
        isSlugManuallyEdited = e.target.value.trim().length > 0;
        const cleanSlug = generateSlug(e.target.value);
        slugPreview.textContent = cleanSlug || 'your-slug';
    });
}

export function initFormActions() {
    const form = document.getElementById('portfolio-create-form');
    const submitBtn = document.getElementById('btn-submit-profile');

    if (!form || !submitBtn) return;

    form.addEventListener('submit', () => {
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
    });
}
