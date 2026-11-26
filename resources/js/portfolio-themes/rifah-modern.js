const REDUCED_MOTION = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function initThemeToggle() {
    const root = document.documentElement;
    const toggleButtons = Array.from(document.querySelectorAll('[data-rm-theme-toggle]'));
    if (!toggleButtons.length) {
        return;
    }

    const syncIcons = (isDark) => {
        toggleButtons.forEach((btn) => {
            btn.setAttribute('aria-pressed', isDark ? 'true' : 'false');
            const sun = btn.querySelector('[data-rm-icon-sun]');
            const moon = btn.querySelector('[data-rm-icon-moon]');
            if (sun && moon) {
                sun.classList.toggle('hidden', !isDark);
                moon.classList.toggle('hidden', isDark);
            }
        });
    };

    syncIcons(root.classList.contains('dark'));

    toggleButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            const isDark = root.classList.toggle('dark');
            try {
                window.localStorage.setItem('rm-theme', isDark ? 'dark' : 'light');
            } catch {
                // Ignore storage errors in restricted environments
            }
            syncIcons(isDark);
        });
    });
}

function initMobileMenu() {
    const toggleBtn = document.querySelector('[data-rm-mobile-toggle]');
    const menu = document.querySelector('[data-rm-mobile-menu]');
    const iconOpen = document.querySelector('[data-rm-mobile-open]');
    const iconClose = document.querySelector('[data-rm-mobile-close]');

    if (!toggleBtn || !menu) {
        return;
    }

    const setOpen = (open) => {
        menu.classList.toggle('hidden', !open);
        toggleBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (iconOpen && iconClose) {
            iconOpen.classList.toggle('hidden', open);
            iconClose.classList.toggle('hidden', !open);
        }
    };

    toggleBtn.addEventListener('click', () => {
        setOpen(menu.classList.contains('hidden'));
    });

    menu.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => setOpen(false));
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setOpen(false);
        }
    });
}

function initScrollProgressAndActiveNav() {
    const bar = document.querySelector('[data-rm-progress]');
    const navLinks = Array.from(document.querySelectorAll('[data-rm-nav-link]'));
    const sections = Array.from(document.querySelectorAll('main section[id]'));

    const update = () => {
        if (bar) {
            const docHeight = document.documentElement.scrollHeight - window.innerHeight;
            const ratio = docHeight > 0 ? Math.min(1, Math.max(0, window.scrollY / docHeight)) : 0;
            bar.style.transform = `scaleX(${ratio.toFixed(4)})`;
        }

        if (navLinks.length && sections.length) {
            const scrollPos = window.scrollY + 140;
            let activeId = sections[0]?.id || '';

            for (const section of sections) {
                if (section.offsetTop <= scrollPos) {
                    activeId = section.id;
                }
            }

            navLinks.forEach((link) => {
                const target = (link.getAttribute('href') || '').replace(/^#/, '');
                link.classList.toggle('is-active', target !== '' && target === activeId);
            });
        }
    };

    window.addEventListener('scroll', update, { passive: true });
    update();
}

function initReveals() {
    const items = Array.from(document.querySelectorAll('.rm-reveal'));
    if (!items.length) {
        return;
    }

    if (REDUCED_MOTION() || !('IntersectionObserver' in window)) {
        items.forEach((el) => el.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        },
        { threshold: 0.14, rootMargin: '0px 0px -6% 0px' }
    );

    items.forEach((el) => observer.observe(el));
}

function initHeroConnections() {
    const canvas = document.querySelector('[data-rm-connections]');
    if (!canvas || REDUCED_MOTION()) {
        return;
    }

    const ctx = canvas.getContext('2d');
    if (!ctx) {
        return;
    }

    const PARTICLE_COUNT = 75;
    const MAX_DISTANCE = 130;
    const MOUSE_RADIUS = 170;
    const SPEED = 0.18;

    let particles = [];
    let rafId = null;
    let isVisible = true;
    const mouse = { x: null, y: null };

    const resize = () => {
        const rect = canvas.getBoundingClientRect();
        if (!rect.width || !rect.height) {
            return;
        }
        const dpr = Math.min(window.devicePixelRatio || 1, 2);
        canvas.width = rect.width * dpr;
        canvas.height = rect.height * dpr;
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

        particles = Array.from({ length: PARTICLE_COUNT }, () => ({
            x: Math.random() * rect.width,
            y: Math.random() * rect.height,
            vx: (Math.random() - 0.5) * SPEED,
            vy: (Math.random() - 0.5) * SPEED,
            radius: Math.random() * 1.8 + 1.3,
        }));
    };

    const draw = () => {
        rafId = null;
        if (!isVisible || document.hidden || REDUCED_MOTION()) {
            return;
        }

        const { width, height } = canvas.getBoundingClientRect();
        ctx.clearRect(0, 0, width, height);

        for (let i = 0; i < particles.length; i++) {
            const p = particles[i];
            p.x += p.vx;
            p.y += p.vy;

            if (p.x <= 0 || p.x >= width) p.vx *= -1;
            if (p.y <= 0 || p.y >= height) p.vy *= -1;

            if (mouse.x !== null && mouse.y !== null) {
                const dx = p.x - mouse.x;
                const dy = p.y - mouse.y;
                const dist = Math.hypot(dx, dy) || 0.0001;
                if (dist < MOUSE_RADIUS) {
                    const force = (MOUSE_RADIUS - dist) / MOUSE_RADIUS;
                    p.vx += (dx / dist) * force * 0.08;
                    p.vy += (dy / dist) * force * 0.08;
                }
            }

            p.vx = Math.max(-SPEED * 3, Math.min(SPEED * 3, p.vx * 0.985));
            p.vy = Math.max(-SPEED * 3, Math.min(SPEED * 3, p.vy * 0.985));

            ctx.beginPath();
            ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
            ctx.fillStyle = 'rgba(194, 84, 45, 0.72)';
            ctx.fill();

            for (let j = i + 1; j < particles.length; j++) {
                const other = particles[j];
                const dx = p.x - other.x;
                const dy = p.y - other.y;
                const dist = Math.hypot(dx, dy);
                if (dist < MAX_DISTANCE) {
                    const alpha = (1 - dist / MAX_DISTANCE) * 0.18;
                    ctx.beginPath();
                    ctx.moveTo(p.x, p.y);
                    ctx.lineTo(other.x, other.y);
                    ctx.strokeStyle = `rgba(194, 84, 45, ${alpha.toFixed(3)})`;
                    ctx.lineWidth = 1;
                    ctx.stroke();
                }
            }
        }

        rafId = window.requestAnimationFrame(draw);
    };

    const schedule = () => {
        if (rafId === null && isVisible && !document.hidden) {
            rafId = window.requestAnimationFrame(draw);
        }
    };

    window.addEventListener('resize', resize, { passive: true });
    window.addEventListener('pointermove', (event) => {
        const rect = canvas.getBoundingClientRect();
        const x = event.clientX - rect.left;
        const y = event.clientY - rect.top;
        if (x >= 0 && x <= rect.width && y >= 0 && y <= rect.height) {
            mouse.x = x;
            mouse.y = y;
        } else {
            mouse.x = null;
            mouse.y = null;
        }
    }, { passive: true });

    document.addEventListener('visibilitychange', () => {
        if (document.hidden && rafId !== null) {
            window.cancelAnimationFrame(rafId);
            rafId = null;
        } else {
            schedule();
        }
    });

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            isVisible = Boolean(entries[0]?.isIntersecting);
            if (isVisible) {
                schedule();
            } else if (rafId !== null) {
                window.cancelAnimationFrame(rafId);
                rafId = null;
            }
        }, { threshold: 0.05 });
        observer.observe(canvas);
    }

    resize();
    schedule();
}

function initProjectInteractions() {
    const filterButtons = Array.from(document.querySelectorAll('[data-rm-filter]'));
    const cards = Array.from(document.querySelectorAll('[data-rm-project-card]'));

    if (filterButtons.length && cards.length) {
        filterButtons.forEach((btn) => {
            btn.addEventListener('click', () => {
                const filter = btn.dataset.rmFilter || 'all';
                filterButtons.forEach((b) => b.classList.toggle('is-active', b === btn));

                cards.forEach((card) => {
                    if (filter === 'all') {
                        card.classList.remove('hidden');
                        return;
                    }
                    if (filter === 'featured') {
                        card.classList.toggle('hidden', card.dataset.rmFeatured !== '1');
                        return;
                    }
                    const techs = (card.dataset.rmTechs || '').split('|');
                    card.classList.toggle('hidden', !techs.includes(filter.toLowerCase()));
                });
            });
        });
    }

    const modal = document.querySelector('[data-rm-project-modal]');
    const openButtons = Array.from(document.querySelectorAll('[data-rm-open-project]'));
    if (!modal || !openButtons.length) {
        return;
    }

    const titleEl = modal.querySelector('[data-rm-modal-title]');
    const descEl = modal.querySelector('[data-rm-modal-desc]');
    const techEl = modal.querySelector('[data-rm-modal-techs]');
    const liveEl = modal.querySelector('[data-rm-modal-live]');
    const githubEl = modal.querySelector('[data-rm-modal-github]');
    const closeBtns = Array.from(modal.querySelectorAll('[data-rm-modal-close]'));

    const closeModal = () => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    };

    openButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            const title = btn.dataset.title || '';
            const description = btn.dataset.description || '';
            const techs = (btn.dataset.technologies || '').split('|').filter(Boolean);
            const liveUrl = btn.dataset.liveUrl || '';
            const githubUrl = btn.dataset.githubUrl || '';

            if (titleEl) titleEl.textContent = title;
            if (descEl) descEl.textContent = description || 'No project description provided.';

            if (techEl) {
                techEl.textContent = '';
                techs.forEach((t) => {
                    const badge = document.createElement('span');
                    badge.className = 'px-2.5 py-1 rounded-md bg-ink/5 text-ink/80 text-xs font-medium border border-ink/10';
                    badge.textContent = t;
                    techEl.appendChild(badge);
                });
            }

            if (liveEl) {
                liveEl.classList.toggle('hidden', !liveUrl);
                if (liveUrl) liveEl.setAttribute('href', liveUrl);
            }
            if (githubEl) {
                githubEl.classList.toggle('hidden', !githubUrl);
                if (githubUrl) githubEl.setAttribute('href', githubUrl);
            }

            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        });
    });

    closeBtns.forEach((b) => b.addEventListener('click', closeModal));
    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });
}

function boot() {
    initThemeToggle();
    initMobileMenu();
    initScrollProgressAndActiveNav();
    initReveals();
    initHeroConnections();
    initProjectInteractions();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot, { once: true });
} else {
    boot();
}
