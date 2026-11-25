/**
 * Portfolio CMS — Skills Configuration Manager
 * Handles both Form Mode (for main portfolio form submissions) and AJAX Mode (for standalone editing).
 */

export function initSkillsManager() {
    const section = document.getElementById('skills-configuration-section');
    if (!section) return;

    const formEl = document.getElementById('portfolio-create-form');
    const portfolioId = section.getAttribute('data-portfolio-id');
    const skillsBaseUrl = section.getAttribute('data-skills-url');

    // Form Mode: Active if on the main create/edit form or if no portfolioId yet
    const isFormMode = Boolean(formEl) || !portfolioId;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    const btnOpenAdd = document.getElementById('btn-open-add-skill');
    const formCard = document.getElementById('add-skill-form-card');
    const btnCancelAdd = document.getElementById('btn-cancel-add-skill');
    const inputCreateName = document.getElementById('skill-name-input');
    const createError = document.getElementById('skill-create-error');
    const btnSubmitSkill = document.getElementById('btn-submit-skill');
    const btnSubmitSkillText = document.getElementById('btn-submit-skill-text');
    const btnSubmitSkillSpinner = document.getElementById('btn-submit-skill-spinner');

    const container = document.getElementById('skills-items-container');
    const countDisplay = document.getElementById('skills-count-display');
    const counterBadge = document.getElementById('skills-counter-badge');
    const alertContainer = document.getElementById('skills-alert-container');
    const hiddenContainer = document.getElementById('skills-hidden-inputs');

    // Modals
    const modalEdit = document.getElementById('modal-edit-skill');
    const editIdInput = document.getElementById('edit-skill-id');
    const editNameInput = document.getElementById('edit-skill-name-input');
    const editError = document.getElementById('skill-edit-error');
    const btnConfirmEdit = document.getElementById('btn-confirm-edit-skill');
    const btnConfirmEditText = document.getElementById('btn-confirm-edit-skill-text');
    const btnConfirmEditSpinner = document.getElementById('btn-confirm-edit-skill-spinner');

    const modalDelete = document.getElementById('modal-delete-skill');
    const deleteMessage = document.getElementById('modal-delete-skill-message');
    const btnConfirmDelete = document.getElementById('modal-delete-skill-confirm-btn');
    let skillToDeleteId = null;

    // In-memory state for Form Mode
    let skillsState = [];

    // Helper: Show notification banner
    function showAlert(type, message) {
        if (!alertContainer) return;
        const isSuccess = type === 'success';
        alertContainer.className = `p-3 rounded-lg text-xs flex items-center justify-between gap-3 ${
            isSuccess
                ? 'bg-emerald-50 border border-emerald-200 text-emerald-800'
                : 'bg-[#FBF1EF] border border-[#E9D2CC] text-[#9C4A3E]'
        }`;
        alertContainer.innerHTML = `
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    ${isSuccess
                        ? '<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />'
                        : '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'}
                </svg>
                <span>${escapeHtml(message)}</span>
            </div>
            <button type="button" class="text-xs opacity-60 hover:opacity-100 p-0.5" onclick="this.parentElement.classList.add('hidden')">&times;</button>
        `;
        alertContainer.classList.remove('hidden');
        setTimeout(() => {
            alertContainer.classList.add('hidden');
        }, 5000);
    }

    // Helper: Sync hidden inputs for form submission
    function syncHiddenInputs() {
        if (!isFormMode || !hiddenContainer) return;
        hiddenContainer.innerHTML = skillsState.map((skill, idx) =>
            `<input type="hidden" class="skill-hidden-name" name="skills[${idx}][name]" value="${escapeHtml(skill.name)}">`
        ).join('');
    }

    // Helper: Update count display & empty state
    function updateCount() {
        const count = isFormMode
            ? skillsState.length
            : (container ? container.querySelectorAll('.skill-item-chip').length : 0);

        if (countDisplay) countDisplay.textContent = count;
        if (counterBadge) {
            counterBadge.innerHTML = `<span id="skills-count-display">${count}</span> ${count === 1 ? 'Skill' : 'Skills'}`;
        }

        const emptyState = document.getElementById('skills-empty-state');
        if (count === 0 && !emptyState && container) {
            container.innerHTML = `
                <div id="skills-empty-state" class="w-full text-center py-8 px-4 rounded-lg border border-dashed border-line text-ink-mute">
                    <p class="text-xs">No skills configured yet.</p>
                    <p class="text-[11px] text-ink-mute/70 mt-1">Click "Add Skill" above to add your first skill.</p>
                </div>
            `;
        } else if (count > 0 && emptyState) {
            emptyState.remove();
        }
    }

    // Helper: Create skill chip element
    function createSkillChipElement(skill) {
        const chip = document.createElement('div');
        chip.className = 'skill-item-chip inline-flex items-center gap-2 pl-3 pr-2 py-1.5 rounded-lg border border-line bg-paper-sunk/40 hover:bg-paper-sunk text-ink text-xs transition-colors group';
        chip.setAttribute('data-skill-id', skill.id);
        chip.setAttribute('data-skill-name', skill.name);
        chip.innerHTML = `
            <span class="skill-name-text font-medium text-ink">${escapeHtml(skill.name)}</span>
            <div class="flex items-center gap-1 border-l border-line/80 pl-1.5">
                <button type="button" class="btn-edit-skill p-1 text-ink-mute hover:text-ink rounded transition-colors" title="Edit skill">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                </button>
                <button type="button" class="btn-delete-skill p-1 text-ink-mute hover:text-[#9C4A3E] rounded transition-colors" title="Delete skill">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            </div>
        `;
        return chip;
    }

    // Modal helpers
    function openModal(modalEl) {
        if (!modalEl) return;
        modalEl.classList.remove('hidden');
    }
    function closeModal(modalEl) {
        if (!modalEl) return;
        modalEl.classList.add('hidden');
    }

    // Initialize State from DOM (useful on reload or validation error rehydration)
    if (container) {
        const existingChips = container.querySelectorAll('.skill-item-chip');
        existingChips.forEach((chip, idx) => {
            const id = chip.getAttribute('data-skill-id') || `skill-init-${idx}`;
            const name = chip.getAttribute('data-skill-name') || chip.querySelector('.skill-name-text')?.textContent.trim() || '';
            if (name) {
                skillsState.push({ id, name });
            }
        });
        if (isFormMode) {
            syncHiddenInputs();
        }
    }

    // 1. Toggle Add Skill Form
    if (btnOpenAdd && formCard) {
        btnOpenAdd.addEventListener('click', () => {
            formCard.classList.toggle('hidden');
            if (!formCard.classList.contains('hidden') && inputCreateName) {
                inputCreateName.value = '';
                if (createError) createError.classList.add('hidden');
                inputCreateName.focus();
            }
        });
    }

    if (btnCancelAdd && formCard) {
        btnCancelAdd.addEventListener('click', () => {
            formCard.classList.add('hidden');
            if (createError) createError.classList.add('hidden');
            if (inputCreateName) inputCreateName.value = '';
        });
    }

    // 2. Add Skill Action
    async function handleAddSkill() {
        if (!inputCreateName) return;
        const name = inputCreateName.value.trim();

        if (!name) {
            if (createError) {
                createError.textContent = 'Skill name is required.';
                createError.classList.remove('hidden');
            }
            inputCreateName.focus();
            return;
        }

        if (name.length > 100) {
            if (createError) {
                createError.textContent = 'Skill name must not exceed 100 characters.';
                createError.classList.remove('hidden');
            }
            inputCreateName.focus();
            return;
        }

        if (createError) createError.classList.add('hidden');

        // Form Mode (Unsaved / Client State)
        if (isFormMode) {
            const isDuplicate = skillsState.some(s => s.name.toLowerCase() === name.toLowerCase());
            if (isDuplicate) {
                if (createError) {
                    createError.textContent = 'This skill has already been added.';
                    createError.classList.remove('hidden');
                }
                inputCreateName.focus();
                return;
            }

            const newSkill = {
                id: 'skill-' + Date.now() + '-' + Math.random().toString(36).substring(2, 6),
                name
            };

            skillsState.push(newSkill);

            const emptyState = document.getElementById('skills-empty-state');
            if (emptyState) emptyState.remove();

            if (container) {
                const chip = createSkillChipElement(newSkill);
                container.appendChild(chip);
            }

            syncHiddenInputs();
            updateCount();

            // Clear input and keep focus for quick repeated additions
            inputCreateName.value = '';
            inputCreateName.focus();
            return;
        }

        // AJAX Mode (Persisted Portfolio)
        if (!skillsBaseUrl) return;

        if (btnSubmitSkill) btnSubmitSkill.disabled = true;
        if (btnSubmitSkillText) btnSubmitSkillText.classList.add('hidden');
        if (btnSubmitSkillSpinner) btnSubmitSkillSpinner.classList.remove('hidden');

        try {
            const response = await fetch(skillsBaseUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ name })
            });

            const data = await response.json();

            if (!response.ok) {
                const errorMsg = data.errors?.name?.[0] || data.message || 'Failed to create skill.';
                if (createError) {
                    createError.textContent = errorMsg;
                    createError.classList.remove('hidden');
                }
                return;
            }

            const skill = data.data || data.skill || { id: Date.now(), name };
            const chip = createSkillChipElement(skill);

            const emptyState = document.getElementById('skills-empty-state');
            if (emptyState) emptyState.remove();

            if (container) container.appendChild(chip);
            updateCount();

            inputCreateName.value = '';
            if (formCard) formCard.classList.add('hidden');
            showAlert('success', data.message || `Skill "${skill.name}" created successfully.`);
        } catch (err) {
            if (createError) {
                createError.textContent = 'A network error occurred. Please try again.';
                createError.classList.remove('hidden');
            }
        } finally {
            if (btnSubmitSkill) btnSubmitSkill.disabled = false;
            if (btnSubmitSkillText) btnSubmitSkillText.classList.remove('hidden');
            if (btnSubmitSkillSpinner) btnSubmitSkillSpinner.classList.add('hidden');
        }
    }

    if (btnSubmitSkill) {
        btnSubmitSkill.addEventListener('click', handleAddSkill);
    }

    if (inputCreateName) {
        inputCreateName.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                handleAddSkill();
            }
        });
    }

    // 3. Delegate Edit & Delete clicks on chips container
    if (container) {
        container.addEventListener('click', (e) => {
            const btnEdit = e.target.closest('.btn-edit-skill');
            const btnDelete = e.target.closest('.btn-delete-skill');
            const chip = e.target.closest('.skill-item-chip');

            if (!chip) return;
            const skillId = chip.getAttribute('data-skill-id');
            const skillName = chip.getAttribute('data-skill-name') || chip.querySelector('.skill-name-text')?.textContent.trim();

            if (btnEdit) {
                if (editIdInput) editIdInput.value = skillId;
                if (editNameInput) editNameInput.value = skillName;
                if (editError) editError.classList.add('hidden');
                openModal(modalEdit);
                editNameInput?.focus();
            }

            if (btnDelete) {
                skillToDeleteId = skillId;
                if (deleteMessage) {
                    deleteMessage.textContent = `Are you sure you want to delete skill "${skillName}"? It will be removed from this portfolio.`;
                }
                openModal(modalDelete);
            }
        });
    }

    // 4. Update Skill Confirm
    async function handleUpdateSkill() {
        if (!editIdInput || !editNameInput) return;
        const skillId = editIdInput.value;
        const newName = editNameInput.value.trim();

        if (!skillId || !newName) {
            if (editError) {
                editError.textContent = 'Skill name is required.';
                editError.classList.remove('hidden');
            }
            editNameInput?.focus();
            return;
        }

        if (newName.length > 100) {
            if (editError) {
                editError.textContent = 'Skill name must not exceed 100 characters.';
                editError.classList.remove('hidden');
            }
            editNameInput?.focus();
            return;
        }

        if (editError) editError.classList.add('hidden');

        // Form Mode (Client state update)
        if (isFormMode) {
            const isDuplicate = skillsState.some(
                s => String(s.id) !== String(skillId) && s.name.toLowerCase() === newName.toLowerCase()
            );
            if (isDuplicate) {
                if (editError) {
                    editError.textContent = 'A skill with this name already exists.';
                    editError.classList.remove('hidden');
                }
                editNameInput?.focus();
                return;
            }

            const item = skillsState.find(s => String(s.id) === String(skillId));
            if (item) {
                item.name = newName;
            }

            const chip = container?.querySelector(`.skill-item-chip[data-skill-id="${skillId}"]`);
            if (chip) {
                chip.setAttribute('data-skill-name', newName);
                const label = chip.querySelector('.skill-name-text');
                if (label) label.textContent = newName;
            }

            syncHiddenInputs();
            closeModal(modalEdit);
            return;
        }

        // AJAX Mode (Persisted Portfolio)
        if (!skillsBaseUrl) return;

        if (btnConfirmEdit) btnConfirmEdit.disabled = true;
        if (btnConfirmEditText) btnConfirmEditText.classList.add('hidden');
        if (btnConfirmEditSpinner) btnConfirmEditSpinner.classList.remove('hidden');

        try {
            const response = await fetch(`${skillsBaseUrl}/${skillId}`, {
                method: 'PUT',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ name: newName })
            });

            const data = await response.json();

            if (!response.ok) {
                const errorMsg = data.errors?.name?.[0] || data.message || 'Failed to update skill.';
                if (editError) {
                    editError.textContent = errorMsg;
                    editError.classList.remove('hidden');
                }
                return;
            }

            const chip = container?.querySelector(`.skill-item-chip[data-skill-id="${skillId}"]`);
            if (chip) {
                chip.setAttribute('data-skill-name', newName);
                const label = chip.querySelector('.skill-name-text');
                if (label) label.textContent = newName;
            }

            closeModal(modalEdit);
            showAlert('success', data.message || `Skill "${newName}" updated successfully.`);
        } catch (err) {
            if (editError) {
                editError.textContent = 'A network error occurred. Please try again.';
                editError.classList.remove('hidden');
            }
        } finally {
            if (btnConfirmEdit) btnConfirmEdit.disabled = false;
            if (btnConfirmEditText) btnConfirmEditText.classList.remove('hidden');
            if (btnConfirmEditSpinner) btnConfirmEditSpinner.classList.add('hidden');
        }
    }

    if (btnConfirmEdit) {
        btnConfirmEdit.addEventListener('click', handleUpdateSkill);
    }

    if (editNameInput) {
        editNameInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                handleUpdateSkill();
            }
        });
    }

    // 5. Delete Skill Confirm
    if (btnConfirmDelete) {
        btnConfirmDelete.addEventListener('click', async () => {
            if (!skillToDeleteId) return;

            // Form Mode (Client state deletion)
            if (isFormMode) {
                skillsState = skillsState.filter(s => String(s.id) !== String(skillToDeleteId));
                const chip = container?.querySelector(`.skill-item-chip[data-skill-id="${skillToDeleteId}"]`);
                if (chip) chip.remove();

                syncHiddenInputs();
                updateCount();
                closeModal(modalDelete);
                skillToDeleteId = null;
                return;
            }

            // AJAX Mode
            if (!skillsBaseUrl) return;

            btnConfirmDelete.disabled = true;

            try {
                const response = await fetch(`${skillsBaseUrl}/${skillToDeleteId}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    }
                });

                const data = await response.json();

                if (!response.ok) {
                    showAlert('error', data.message || 'Failed to delete skill.');
                    return;
                }

                const chip = container?.querySelector(`.skill-item-chip[data-skill-id="${skillToDeleteId}"]`);
                if (chip) chip.remove();

                updateCount();
                closeModal(modalDelete);
                showAlert('success', data.message || 'Skill deleted successfully.');
            } catch (err) {
                showAlert('error', 'A network error occurred while deleting skill.');
            } finally {
                btnConfirmDelete.disabled = false;
                skillToDeleteId = null;
            }
        });
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}
