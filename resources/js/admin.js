/**
 * Portfolio CMS — Super Admin Interactions
 * Integrates Portfolio Form, Navbar Configuration, Skills Manager, and Projects Manager.
 */

import { initSlugGenerator, initFormActions } from './admin/portfolios/portfolio-form';
import { initNavbarManager } from './admin/portfolios/navbar-manager';
import { initSkillsManager } from './admin/portfolios/skills-manager';
import { initProjectsManager } from './admin/portfolios/projects-manager';

export function initModalManager() {
    document.addEventListener('click', (e) => {
        const closeBtn = e.target.closest('[data-modal-close]');
        if (closeBtn) {
            e.preventDefault();
            const modalId = closeBtn.getAttribute('data-modal-close');
            const modal = (modalId ? document.getElementById(modalId) : null) || closeBtn.closest('[role="dialog"]');
            if (modal) {
                modal.classList.add('hidden');
            }
            return;
        }

        const backdrop = e.target.closest('[data-modal-backdrop]');
        if (backdrop) {
            const modalId = backdrop.getAttribute('data-modal-backdrop');
            const modal = (modalId ? document.getElementById(modalId) : null) || backdrop.closest('[role="dialog"]');
            if (modal) {
                modal.classList.add('hidden');
            }
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const openModals = document.querySelectorAll('[role="dialog"]:not(.hidden)');
            openModals.forEach(m => m.classList.add('hidden'));
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initModalManager();
    initSlugGenerator();
    initNavbarManager();
    initSkillsManager();
    initProjectsManager();
    initFormActions();
});

