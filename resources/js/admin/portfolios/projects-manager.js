/**
 * Portfolio CMS — Projects Configuration Manager
 * Handles both Form Mode (for main portfolio form submissions) and AJAX Mode (for standalone editing).
 */

export function initProjectsManager() {
    const section = document.getElementById('projects-configuration-section');
    if (!section) return;

    const formEl = document.getElementById('portfolio-create-form');
    const portfolioId = section.getAttribute('data-portfolio-id');
    const projectsBaseUrl = section.getAttribute('data-projects-url');

    // Form Mode: Active if on the main create/edit form or if no portfolioId yet
    const isFormMode = Boolean(formEl) || !portfolioId;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    const btnOpenAdd = document.getElementById('btn-open-add-project');
    const container = document.getElementById('projects-items-container');
    const countDisplay = document.getElementById('projects-count-display');
    const counterBadge = document.getElementById('projects-counter-badge');
    const alertContainer = document.getElementById('projects-alert-container');
    const hiddenContainer = document.getElementById('projects-hidden-inputs');

    // Project Form Modal
    const modalProject = document.getElementById('modal-project');
    const modalTitle = document.getElementById('modal-project-title');
    const formIdInput = document.getElementById('project-form-id');
    const titleInput = document.getElementById('project-title-input');
    const sortOrderInput = document.getElementById('project-sort-order-input');
    const descInput = document.getElementById('project-description-input');
    const thumbInput = document.getElementById('project-thumbnail-input');
    const liveUrlInput = document.getElementById('project-live-url-input');
    const githubUrlInput = document.getElementById('project-github-url-input');
    const techInput = document.getElementById('project-technologies-input');
    const featuredInput = document.getElementById('project-featured-input');
    const visibleInput = document.getElementById('project-visible-input');
    const modalError = document.getElementById('project-modal-error');
    const btnSaveProject = document.getElementById('btn-save-project');
    const btnSaveProjectText = document.getElementById('btn-save-project-text');
    const btnSaveProjectSpinner = document.getElementById('btn-save-project-spinner');

    // Delete Confirmation Modal
    const modalDelete = document.getElementById('modal-delete-project');
    const deleteMessage = document.getElementById('modal-delete-project-message');
    const btnConfirmDelete = document.getElementById('modal-delete-project-confirm-btn');
    let projectToDeleteId = null;

    // In-memory state for Form Mode
    let projectsState = [];

    function safeHttpUrl(value) {
        if (typeof value !== 'string') return null;
        const trimmed = value.trim();
        return /^https?:\/\//i.test(trimmed) ? trimmed : null;
    }

    function safeThumbnailUrl(value) {
        if (typeof value !== 'string') return null;
        const trimmed = value.trim();
        return /^(?:https?:\/\/|\/(?!\/))/i.test(trimmed) ? trimmed : null;
    }

    // Helper: Show banner alert safely via DOM APIs
    function showAlert(type, message) {
        if (!alertContainer) return;
        const isSuccess = type === 'success';
        alertContainer.className = `p-3 rounded-lg text-xs flex items-center justify-between gap-3 ${
            isSuccess
                ? 'bg-emerald-50 border border-emerald-200 text-emerald-800'
                : 'bg-[#FBF1EF] border border-[#E9D2CC] text-[#9C4A3E]'
        }`;

        const wrapper = document.createElement('div');
        wrapper.className = 'flex items-center gap-2';
        wrapper.innerHTML = `
            <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                ${isSuccess
                    ? '<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />'
                    : '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'}
            </svg>
        `;
        const msgSpan = document.createElement('span');
        msgSpan.textContent = message;
        wrapper.appendChild(msgSpan);

        const closeBtn = document.createElement('button');
        closeBtn.type = 'button';
        closeBtn.className = 'text-xs opacity-60 hover:opacity-100 p-0.5';
        closeBtn.textContent = '\u00D7';
        closeBtn.addEventListener('click', () => alertContainer.classList.add('hidden'));

        alertContainer.replaceChildren(wrapper, closeBtn);
        alertContainer.classList.remove('hidden');
        setTimeout(() => {
            alertContainer.classList.add('hidden');
        }, 5000);
    }

    // Helper: Sync hidden inputs via DOM APIs (no HTML string interpolation)
    function syncHiddenInputs() {
        if (!isFormMode || !hiddenContainer) return;
        const fragment = document.createDocumentFragment();
        const appendHidden = (name, value) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = String(value ?? '');
            fragment.appendChild(input);
        };

        projectsState.forEach((proj, idx) => {
            appendHidden(`projects[${idx}][title]`, proj.title);
            appendHidden(`projects[${idx}][description]`, proj.description || '');
            appendHidden(`projects[${idx}][thumbnail]`, proj.thumbnail || '');
            appendHidden(`projects[${idx}][live_url]`, proj.live_url || '');
            appendHidden(`projects[${idx}][github_url]`, proj.github_url || '');
            appendHidden(`projects[${idx}][sort_order]`, proj.sort_order ?? idx);
            appendHidden(`projects[${idx}][is_featured]`, proj.is_featured ? '1' : '0');
            appendHidden(`projects[${idx}][is_visible]`, proj.is_visible ? '1' : '0');

            const techs = Array.isArray(proj.technologies) ? proj.technologies : [];
            techs.forEach((t) => appendHidden(`projects[${idx}][technologies][]`, t));
        });

        hiddenContainer.replaceChildren(fragment);
    }

    // Helper: Update count & empty state
    function updateCount() {
        const count = isFormMode
            ? projectsState.length
            : (container ? container.querySelectorAll('.project-item-card').length : 0);

        if (countDisplay) countDisplay.textContent = String(count);
        if (counterBadge) {
            const countSpan = document.createElement('span');
            countSpan.id = 'projects-count-display';
            countSpan.textContent = String(count);
            counterBadge.replaceChildren(countSpan, document.createTextNode(` ${count === 1 ? 'Project' : 'Projects'}`));
        }

        const emptyState = document.getElementById('projects-empty-state');
        if (count === 0 && !emptyState && container) {
            const emptyBox = document.createElement('div');
            emptyBox.id = 'projects-empty-state';
            emptyBox.className = 'w-full text-center py-8 px-4 rounded-lg border border-dashed border-line text-ink-mute';
            const titleP = document.createElement('p');
            titleP.className = 'text-xs';
            titleP.textContent = 'No projects configured yet.';
            const hintP = document.createElement('p');
            hintP.className = 'text-[11px] text-ink-mute/70 mt-1';
            hintP.textContent = 'Click "Add Project" above to create your first portfolio project.';
            emptyBox.replaceChildren(titleP, hintP);
            container.replaceChildren(emptyBox);
        } else if (count > 0 && emptyState) {
            emptyState.remove();
        }
    }

    function openModal(modalEl) {
        if (!modalEl) return;
        modalEl.classList.remove('hidden');
    }

    function closeModal(modalEl) {
        if (!modalEl) return;
        modalEl.classList.add('hidden');
    }

    // Helper: Render project card safely using DOM APIs
    function renderProjectCard(project) {
        const card = document.createElement('div');
        card.className = 'project-item-card rounded-lg border border-line bg-paper-sunk/40 hover:bg-paper-sunk/60 p-4 transition-colors space-y-3';
        card.setAttribute('data-project-id', String(project.id));
        card.setAttribute('data-project-data', JSON.stringify(project));

        const topRow = document.createElement('div');
        topRow.className = 'flex flex-col sm:flex-row sm:items-start justify-between gap-3 pb-3 border-b border-line';

        const leftInfo = document.createElement('div');
        leftInfo.className = 'flex items-start gap-3 min-w-0';

        const validThumb = safeThumbnailUrl(project.thumbnail);
        if (validThumb) {
            const img = document.createElement('img');
            img.src = validThumb;
            img.alt = String(project.title ?? '');
            img.className = 'w-12 h-12 rounded-md object-cover border border-line flex-shrink-0 bg-surface';
            leftInfo.appendChild(img);
        } else {
            const placeholder = document.createElement('div');
            placeholder.className = 'w-10 h-10 rounded-md bg-surface border border-line flex items-center justify-center flex-shrink-0 text-ink-mute';
            placeholder.innerHTML = `
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            `;
            leftInfo.appendChild(placeholder);
        }

        const textWrap = document.createElement('div');
        textWrap.className = 'min-w-0 space-y-1';

        const headingRow = document.createElement('div');
        headingRow.className = 'flex items-center gap-2 flex-wrap';

        const titleEl = document.createElement('h4');
        titleEl.className = 'project-title-display text-xs sm:text-sm font-semibold text-ink truncate';
        titleEl.textContent = String(project.title ?? '');
        headingRow.appendChild(titleEl);

        if (project.is_featured) {
            const featuredBadge = document.createElement('span');
            featuredBadge.className = 'inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-brass-wash text-brass-ink border border-brass/30';
            featuredBadge.textContent = 'Featured';
            headingRow.appendChild(featuredBadge);
        }

        const visibilityBadge = document.createElement('span');
        visibilityBadge.className = `inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium ${project.is_visible ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500 border border-slate-200'}`;
        visibilityBadge.textContent = project.is_visible ? 'Visible' : 'Hidden';
        headingRow.appendChild(visibilityBadge);

        const orderBadge = document.createElement('span');
        orderBadge.className = 'text-[10px] text-ink-mute font-mono';
        orderBadge.textContent = `Order: ${Number.isFinite(Number(project.sort_order)) ? Number(project.sort_order) : 0}`;
        headingRow.appendChild(orderBadge);

        textWrap.appendChild(headingRow);

        if (project.description) {
            const descP = document.createElement('p');
            descP.className = 'text-xs text-ink-soft line-clamp-2 leading-relaxed';
            descP.textContent = String(project.description);
            textWrap.appendChild(descP);
        }

        leftInfo.appendChild(textWrap);

        const actionsDiv = document.createElement('div');
        actionsDiv.className = 'flex items-center gap-1.5 self-end sm:self-start flex-shrink-0';
        actionsDiv.innerHTML = `
            <button type="button"
                    class="btn-edit-project inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-ink-soft hover:text-ink bg-surface border border-line hover:bg-paper-sunk rounded-md transition-colors"
                    title="Edit project">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                </svg>
                <span>Edit</span>
            </button>
            <button type="button"
                    class="btn-delete-project inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-[#9C4A3E] hover:bg-[#FBF1EF] border border-[#E9D2CC] rounded-md transition-colors"
                    title="Delete project">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                <span>Delete</span>
            </button>
        `;

        topRow.replaceChildren(leftInfo, actionsDiv);

        const bottomRow = document.createElement('div');
        bottomRow.className = 'flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 text-[11px] text-ink-mute';

        const techContainer = document.createElement('div');
        techContainer.className = 'flex flex-wrap gap-1.5 items-center';
        const technologies = Array.isArray(project.technologies) ? project.technologies : [];
        if (technologies.length > 0) {
            technologies.forEach((tech) => {
                const techSpan = document.createElement('span');
                techSpan.className = 'px-2 py-0.5 rounded bg-surface border border-line text-ink-soft text-[11px] font-mono';
                techSpan.textContent = String(tech);
                techContainer.appendChild(techSpan);
            });
        } else {
            const noneSpan = document.createElement('span');
            noneSpan.className = 'text-ink-mute/70 italic';
            noneSpan.textContent = 'No technologies listed';
            techContainer.appendChild(noneSpan);
        }

        const linksContainer = document.createElement('div');
        linksContainer.className = 'flex items-center gap-3';

        const validLiveUrl = safeHttpUrl(project.live_url);
        if (validLiveUrl) {
            const liveLink = document.createElement('a');
            liveLink.href = validLiveUrl;
            liveLink.target = '_blank';
            liveLink.rel = 'noopener noreferrer';
            liveLink.className = 'inline-flex items-center gap-1 text-brass-ink hover:underline';
            liveLink.innerHTML = `
                <span>Live preview</span>
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
            `;
            linksContainer.appendChild(liveLink);
        }

        const validGithubUrl = safeHttpUrl(project.github_url);
        if (validGithubUrl) {
            const githubLink = document.createElement('a');
            githubLink.href = validGithubUrl;
            githubLink.target = '_blank';
            githubLink.rel = 'noopener noreferrer';
            githubLink.className = 'inline-flex items-center gap-1 text-ink-soft hover:text-ink hover:underline';
            githubLink.innerHTML = `
                <span>Repository</span>
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
            `;
            linksContainer.appendChild(githubLink);
        }

        bottomRow.replaceChildren(techContainer, linksContainer);
        card.replaceChildren(topRow, bottomRow);

        return card;
    }

    // Initialize State from DOM (on page load or validation error rehydration)
    if (container) {
        const existingCards = container.querySelectorAll('.project-item-card');
        existingCards.forEach((card, idx) => {
            const rawData = card.getAttribute('data-project-data');
            if (rawData) {
                try {
                    const parsed = JSON.parse(rawData);
                    if (parsed && parsed.title) {
                        if (!parsed.id) parsed.id = `proj-init-${idx}`;
                        projectsState.push(parsed);
                    }
                } catch (e) {}
            }
        });
        if (isFormMode) {
            syncHiddenInputs();
        }
    }

    // 1. Open Add Project Modal
    if (btnOpenAdd) {
        btnOpenAdd.addEventListener('click', () => {
            if (formIdInput) formIdInput.value = '';
            if (titleInput) titleInput.value = '';
            if (descInput) descInput.value = '';
            if (thumbInput) thumbInput.value = '';
            if (liveUrlInput) liveUrlInput.value = '';
            if (githubUrlInput) githubUrlInput.value = '';
            if (techInput) techInput.value = '';

            const currentCount = isFormMode
                ? projectsState.length
                : (container ? container.querySelectorAll('.project-item-card').length : 0);

            if (sortOrderInput) sortOrderInput.value = currentCount;
            if (visibleInput) visibleInput.checked = true;
            if (featuredInput) featuredInput.checked = false;

            if (modalTitle) modalTitle.textContent = 'Add Project';
            if (btnSaveProjectText) btnSaveProjectText.textContent = 'Save Project';
            if (modalError) modalError.classList.add('hidden');

            openModal(modalProject);
            titleInput?.focus();
        });
    }

    // 2. Delegate Edit & Delete on projects container
    if (container) {
        container.addEventListener('click', (e) => {
            const btnEdit = e.target.closest('.btn-edit-project');
            const btnDelete = e.target.closest('.btn-delete-project');
            const card = e.target.closest('.project-item-card');

            if (!card) return;
            const projectDataRaw = card.getAttribute('data-project-data');
            if (!projectDataRaw) return;

            let project;
            try {
                project = JSON.parse(projectDataRaw);
            } catch (err) {
                return;
            }

            if (btnEdit) {
                if (formIdInput) formIdInput.value = project.id;
                if (titleInput) titleInput.value = project.title || '';
                if (sortOrderInput) sortOrderInput.value = project.sort_order ?? 0;
                if (descInput) descInput.value = project.description || '';
                if (thumbInput) thumbInput.value = project.thumbnail || '';
                if (liveUrlInput) liveUrlInput.value = project.live_url || '';
                if (githubUrlInput) githubUrlInput.value = project.github_url || '';

                const techs = Array.isArray(project.technologies) ? project.technologies.join(', ') : '';
                if (techInput) techInput.value = techs;

                if (featuredInput) featuredInput.checked = Boolean(project.is_featured);
                if (visibleInput) visibleInput.checked = Boolean(project.is_visible);

                if (modalTitle) modalTitle.textContent = 'Edit Project';
                if (btnSaveProjectText) btnSaveProjectText.textContent = 'Update Project';
                if (modalError) modalError.classList.add('hidden');

                openModal(modalProject);
                titleInput?.focus();
            }

            if (btnDelete) {
                projectToDeleteId = project.id;
                if (deleteMessage) {
                    deleteMessage.textContent = `Are you sure you want to delete project "${project.title}"? All associated details will be permanently removed.`;
                }
                openModal(modalDelete);
            }
        });
    }

    // 3. Save Project (Create or Update)
    if (btnSaveProject) {
        btnSaveProject.addEventListener('click', async (e) => {
            e.preventDefault();

            const isEdit = Boolean(formIdInput && formIdInput.value);
            const projectId = formIdInput ? formIdInput.value : '';

            const title = titleInput ? titleInput.value.trim() : '';
            if (!title) {
                if (modalError) {
                    modalError.textContent = 'Project title is required.';
                    modalError.classList.remove('hidden');
                }
                titleInput?.focus();
                return;
            }

            if (title.length > 150) {
                if (modalError) {
                    modalError.textContent = 'Project title must not exceed 150 characters.';
                    modalError.classList.remove('hidden');
                }
                titleInput?.focus();
                return;
            }

            // Parse technologies
            const techRaw = techInput ? techInput.value : '';
            const techArray = techRaw
                .split(',')
                .map(t => t.trim())
                .filter(t => t.length > 0);

            const payload = {
                id: isEdit ? projectId : ('proj-' + Date.now() + '-' + Math.random().toString(36).substring(2, 6)),
                title,
                sort_order: parseInt(sortOrderInput ? sortOrderInput.value : '0', 10) || 0,
                description: descInput ? (descInput.value.trim() || null) : null,
                thumbnail: thumbInput ? (thumbInput.value.trim() || null) : null,
                live_url: liveUrlInput ? (liveUrlInput.value.trim() || null) : null,
                github_url: githubUrlInput ? (githubUrlInput.value.trim() || null) : null,
                technologies: techArray,
                is_featured: featuredInput ? featuredInput.checked : false,
                is_visible: visibleInput ? visibleInput.checked : true
            };

            if (modalError) modalError.classList.add('hidden');

            // Form Mode (Client state)
            if (isFormMode) {
                if (isEdit) {
                    const idx = projectsState.findIndex(p => String(p.id) === String(projectId));
                    if (idx !== -1) {
                        projectsState[idx] = payload;
                    }
                    const oldCard = container?.querySelector(`.project-item-card[data-project-id="${projectId}"]`);
                    const newCard = renderProjectCard(payload);
                    if (oldCard) {
                        oldCard.replaceWith(newCard);
                    } else if (container) {
                        container.appendChild(newCard);
                    }
                } else {
                    projectsState.push(payload);
                    const emptyState = document.getElementById('projects-empty-state');
                    if (emptyState) emptyState.remove();

                    if (container) {
                        container.appendChild(renderProjectCard(payload));
                    }
                }

                syncHiddenInputs();
                updateCount();
                closeModal(modalProject);
                return;
            }

            // AJAX Mode (Persisted Portfolio)
            if (!projectsBaseUrl) return;

            btnSaveProject.disabled = true;
            if (btnSaveProjectText) btnSaveProjectText.classList.add('hidden');
            if (btnSaveProjectSpinner) btnSaveProjectSpinner.classList.remove('hidden');

            const url = isEdit ? `${projectsBaseUrl}/${projectId}` : projectsBaseUrl;
            const method = isEdit ? 'PUT' : 'POST';

            try {
                const response = await fetch(url, {
                    method,
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();

                if (!response.ok) {
                    if (modalError) {
                        if (data.errors) {
                            const ul = document.createElement('ul');
                            ul.className = 'list-disc list-inside space-y-0.5';
                            Object.values(data.errors).flat().forEach((errMsg) => {
                                const li = document.createElement('li');
                                li.textContent = String(errMsg);
                                ul.appendChild(li);
                            });
                            modalError.replaceChildren(ul);
                        } else {
                            const p = document.createElement('p');
                            p.textContent = String(data.message || 'Failed to save project.');
                            modalError.replaceChildren(p);
                        }
                        modalError.classList.remove('hidden');
                    }
                    return;
                }

                const savedProject = data.data || data.project || { ...payload, id: isEdit ? projectId : Date.now() };

                const emptyState = document.getElementById('projects-empty-state');
                if (emptyState) emptyState.remove();

                const newCard = renderProjectCard(savedProject);

                if (isEdit) {
                    const oldCard = container?.querySelector(`.project-item-card[data-project-id="${projectId}"]`);
                    if (oldCard) {
                        oldCard.replaceWith(newCard);
                    } else if (container) {
                        container.appendChild(newCard);
                    }
                } else if (container) {
                    container.appendChild(newCard);
                }

                updateCount();
                closeModal(modalProject);
                showAlert('success', data.message || `Project "${savedProject.title}" saved successfully.`);
            } catch (err) {
                if (modalError) {
                    modalError.innerHTML = '<p>A network error occurred. Please try again.</p>';
                    modalError.classList.remove('hidden');
                }
            } finally {
                btnSaveProject.disabled = false;
                if (btnSaveProjectText) btnSaveProjectText.classList.remove('hidden');
                if (btnSaveProjectSpinner) btnSaveProjectSpinner.classList.add('hidden');
            }
        });
    }

    // 4. Delete Project Confirm
    if (btnConfirmDelete) {
        btnConfirmDelete.addEventListener('click', async () => {
            if (!projectToDeleteId) return;

            // Form Mode (Client state deletion)
            if (isFormMode) {
                projectsState = projectsState.filter(p => String(p.id) !== String(projectToDeleteId));
                const card = container?.querySelector(`.project-item-card[data-project-id="${projectToDeleteId}"]`);
                if (card) card.remove();

                syncHiddenInputs();
                updateCount();
                closeModal(modalDelete);
                projectToDeleteId = null;
                return;
            }

            // AJAX Mode
            if (!projectsBaseUrl) return;

            btnConfirmDelete.disabled = true;

            try {
                const response = await fetch(`${projectsBaseUrl}/${projectToDeleteId}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    }
                });

                const data = await response.json();

                if (!response.ok) {
                    showAlert('error', data.message || 'Failed to delete project.');
                    return;
                }

                const card = container?.querySelector(`.project-item-card[data-project-id="${projectToDeleteId}"]`);
                if (card) card.remove();

                updateCount();
                closeModal(modalDelete);
                showAlert('success', data.message || 'Project deleted successfully.');
            } catch (err) {
                showAlert('error', 'A network error occurred while deleting project.');
            } finally {
                btnConfirmDelete.disabled = false;
                projectToDeleteId = null;
            }
        });
    }

    function escapeHtml(text) {
        if (text === null || text === undefined) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
}
