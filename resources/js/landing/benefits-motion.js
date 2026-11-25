const REDUCED = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const FINE_POINTER = () => window.matchMedia('(pointer: fine)').matches;

const SCENES = [
    {
        nav: 'overview',
        url: 'localhost/dashboard',
        badge: 'Live',
        badgeTone: 'live',
        title: 'Your workspace',
        stats: { portfolios: '3', published: '2', drafts: '1', subdomains: '3' },
        focusStat: 'portfolios',
        bars: [38, 56, 48, 72, 62, 86, 78],
        rowStatus: 'draft',
        rowStatusLabel: 'Draft',
        rowAction: 'Preview',
        highlightSubdomain: false,
        floatActive: 'live',
    },
    {
        nav: 'portfolios',
        url: 'localhost/dashboard/portfolios',
        badge: '3 Portfolios',
        badgeTone: 'cyan',
        title: 'My portfolios',
        stats: { portfolios: '3', published: '2', drafts: '1', subdomains: '3' },
        focusStat: 'portfolios',
        bars: [54, 68, 42, 78, 66, 90, 74],
        rowStatus: 'draft',
        rowStatusLabel: 'Draft',
        rowAction: 'Edit',
        highlightSubdomain: false,
        floatActive: 'preview',
    },
    {
        nav: 'subdomains',
        url: 'developer.localhost',
        badge: 'Active Hosts',
        badgeTone: 'cyan',
        title: 'Subdomain routing',
        stats: { portfolios: '3', published: '2', drafts: '1', subdomains: '3' },
        focusStat: 'subdomains',
        bars: [46, 74, 84, 62, 70, 92, 80],
        rowStatus: 'published',
        rowStatusLabel: 'Published',
        rowAction: 'Open',
        highlightSubdomain: true,
        floatActive: 'live',
    },
    {
        nav: 'projects',
        url: 'localhost/dashboard/portfolios/1/edit',
        badge: 'Synced',
        badgeTone: 'violet',
        title: 'Projects & skills',
        stats: { portfolios: '3', published: '2', drafts: '1', subdomains: '3' },
        focusStat: null,
        bars: [64, 52, 76, 90, 82, 70, 94],
        rowStatus: 'draft',
        rowStatusLabel: 'Draft',
        rowAction: 'Save',
        highlightSubdomain: false,
        floatActive: 'preview',
    },
    {
        nav: 'preview',
        url: 'designer.localhost/?preview_token=…',
        badge: 'Signed Preview',
        badgeTone: 'amber',
        title: 'Draft preview',
        stats: { portfolios: '3', published: '2', drafts: '1', subdomains: '3' },
        focusStat: 'drafts',
        bars: [44, 60, 70, 58, 76, 84, 68],
        rowStatus: 'preview',
        rowStatusLabel: 'Previewing',
        rowAction: 'Revoke',
        highlightSubdomain: true,
        floatActive: 'preview',
    },
    {
        nav: 'overview',
        url: 'designer.localhost',
        badge: 'Published',
        badgeTone: 'live',
        title: 'Your workspace',
        stats: { portfolios: '3', published: '3', drafts: '0', subdomains: '3' },
        focusStat: 'published',
        bars: [58, 76, 68, 86, 80, 95, 90],
        rowStatus: 'published',
        rowStatusLabel: 'Published',
        rowAction: 'Live',
        highlightSubdomain: true,
        floatActive: 'live',
    },
];

const CYCLE_MS = 3400;

export function initBenefitsMotion() {
    const section = document.querySelector('#benefits');
    const dashWindow = section?.querySelector('[data-dash-window]');

    if (!section || !dashWindow) {
        return;
    }

    const urlEl = dashWindow.querySelector('[data-dash-url]');
    const badgeEl = dashWindow.querySelector('[data-dash-badge]');
    const titleEl = dashWindow.querySelector('[data-dash-title]');
    const navItems = Array.from(dashWindow.querySelectorAll('[data-dash-nav]'));
    const statCards = Array.from(dashWindow.querySelectorAll('[data-dash-stat]'));
    const bars = Array.from(dashWindow.querySelectorAll('[data-dash-bar]'));
    const toggleStatusEl = dashWindow.querySelector('[data-dash-toggle-status]');
    const toggleActionEl = dashWindow.querySelector('[data-dash-toggle-action]');
    const domainPills = Array.from(dashWindow.querySelectorAll('[data-dash-domain]'));
    const floatPreview = dashWindow.querySelector('.dash-float--preview');
    const floatLive = dashWindow.querySelector('.dash-float--live');
    const benefitItems = Array.from(section.querySelectorAll('[data-benefit-scene]'));

    let currentIndex = 0;
    let isVisible = false;
    let isPaused = false;
    let timer = null;

    const applyScene = (index) => {
        const scene = SCENES[index] || SCENES[0];
        currentIndex = index;

        if (urlEl && urlEl.textContent.trim() !== scene.url) {
            urlEl.classList.add('is-updating');
            urlEl.textContent = scene.url;
            window.setTimeout(() => urlEl.classList.remove('is-updating'), 280);
        }

        if (titleEl) {
            titleEl.textContent = scene.title;
        }

        if (badgeEl) {
            badgeEl.dataset.tone = scene.badgeTone;
            const textNode = badgeEl.querySelector('[data-dash-badge-label]');
            if (textNode) {
                textNode.textContent = scene.badge;
            }
        }

        navItems.forEach((item) => {
            item.classList.toggle('is-active', item.dataset.dashNav === scene.nav);
        });

        statCards.forEach((card) => {
            const key = card.dataset.dashStat;
            const numEl = card.querySelector('[data-dash-stat-val]');
            if (numEl && scene.stats[key] !== undefined && numEl.textContent !== scene.stats[key]) {
                numEl.textContent = scene.stats[key];
                numEl.classList.remove('is-bumped');
                void numEl.offsetWidth;
                numEl.classList.add('is-bumped');
            }
            card.classList.toggle('is-focused', scene.focusStat === key);
        });

        bars.forEach((bar, i) => {
            if (scene.bars[i] !== undefined) {
                bar.style.height = `${scene.bars[i]}%`;
            }
        });

        if (toggleStatusEl) {
            toggleStatusEl.dataset.state = scene.rowStatus;
            toggleStatusEl.textContent = scene.rowStatusLabel;
        }

        if (toggleActionEl) {
            toggleActionEl.textContent = scene.rowAction;
            toggleActionEl.dataset.state = scene.rowStatus;
        }

        domainPills.forEach((pill) => {
            pill.classList.toggle('is-highlighted', scene.highlightSubdomain);
        });

        if (floatPreview) {
            floatPreview.classList.toggle('is-emphasized', scene.floatActive === 'preview');
        }
        if (floatLive) {
            floatLive.classList.toggle('is-emphasized', scene.floatActive === 'live');
        }

        dashWindow.dataset.scene = String(index);

        benefitItems.forEach((item) => {
            const active = Number(item.dataset.benefitScene) === index;
            item.classList.toggle('is-active', active);
        });
    };

    const stopTimer = () => {
        if (timer !== null) {
            window.clearInterval(timer);
            timer = null;
        }
    };

    const startTimer = () => {
        stopTimer();
        if (REDUCED() || !isVisible || isPaused || document.hidden) {
            return;
        }
        timer = window.setInterval(() => {
            applyScene((currentIndex + 1) % SCENES.length);
        }, CYCLE_MS);
    };

    // Coordinate benefit items -> dashboard preview scenes
    benefitItems.forEach((item) => {
        const idx = Number(item.dataset.benefitScene);
        item.addEventListener('pointerenter', () => {
            isPaused = true;
            stopTimer();
            applyScene(idx);
        });
        item.addEventListener('click', () => {
            isPaused = true;
            stopTimer();
            applyScene(idx);
        });
    });

    const benefitList = section.querySelector('.benefit-list');
    if (benefitList) {
        benefitList.addEventListener('pointerleave', () => {
            isPaused = false;
            startTimer();
        });
    }

    // Pause cycling on direct hover over the dashboard window so users can inspect cleanly
    dashWindow.addEventListener('pointerenter', () => {
        isPaused = true;
        stopTimer();
    });
    dashWindow.addEventListener('pointerleave', () => {
        isPaused = false;
        dashWindow.style.setProperty('--tilt-x', '0deg');
        dashWindow.style.setProperty('--tilt-y', '0deg');
        startTimer();
    });

    // Subtle pointer spotlight + 3D perspective depth on fine pointers
    if (!REDUCED() && FINE_POINTER()) {
        let rafId = null;
        let targetRx = 0;
        let targetRy = 0;
        let spotX = 50;
        let spotY = 50;

        const updateTransform = () => {
            rafId = null;
            dashWindow.style.setProperty('--tilt-x', `${targetRx.toFixed(2)}deg`);
            dashWindow.style.setProperty('--tilt-y', `${targetRy.toFixed(2)}deg`);
            dashWindow.style.setProperty('--spot-x', `${spotX.toFixed(1)}%`);
            dashWindow.style.setProperty('--spot-y', `${spotY.toFixed(1)}%`);
        };

        dashWindow.addEventListener(
            'pointermove',
            (event) => {
                const rect = dashWindow.getBoundingClientRect();
                const nx = (event.clientX - rect.left) / rect.width;
                const ny = (event.clientY - rect.top) / rect.height;
                spotX = nx * 100;
                spotY = ny * 100;
                targetRy = (nx - 0.5) * 4.4;
                targetRx = (0.5 - ny) * 4.0;
                if (rafId === null) {
                    rafId = window.requestAnimationFrame(updateTransform);
                }
            },
            { passive: true }
        );
    }

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopTimer();
        } else {
            startTimer();
        }
    });

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    isVisible = entry.isIntersecting;
                    if (isVisible) {
                        dashWindow.classList.add('is-live');
                        startTimer();
                    } else {
                        stopTimer();
                    }
                });
            },
            { threshold: 0.2 }
        );
        observer.observe(section);
    } else {
        isVisible = true;
        dashWindow.classList.add('is-live');
        startTimer();
    }

    applyScene(0);
}
