const REDUCED = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function initNavbar() {
    const nav = document.querySelector('[data-nav]');
    const progress = document.querySelector('[data-scroll-progress]');

    if (!nav) {
        return;
    }

    let ticking = false;

    const update = () => {
        const scrollY = window.scrollY || window.pageYOffset;

        nav.classList.toggle('is-scrolled', scrollY > 12);
        document.body.classList.toggle('is-scrolled', scrollY > 12);

        if (progress) {
            const height = document.documentElement.scrollHeight - window.innerHeight;
            const ratio = height > 0 ? Math.min(scrollY / height, 1) : 0;
            progress.style.transform = `scaleX(${ratio})`;
        }

        ticking = false;
    };

    const onScroll = () => {
        if (!ticking) {
            ticking = true;
            window.requestAnimationFrame(update);
        }
    };

    window.addEventListener('scroll', onScroll, { passive: true });
    update();
}

function initDrawer() {
    const toggle = document.querySelector('[data-nav-toggle]');
    const drawer = document.querySelector('[data-drawer]');

    if (!toggle || !drawer) {
        return;
    }

    const panel = drawer.querySelector('.drawer-panel');
    const closeEls = drawer.querySelectorAll('[data-drawer-close]');
    const links = drawer.querySelectorAll('a');
    let lastFocused = null;

    const focusables = () => Array.from(panel.querySelectorAll('a[href], button:not([disabled])'));

    const open = () => {
        lastFocused = document.activeElement;
        document.body.classList.add('drawer-open');
        toggle.setAttribute('aria-expanded', 'true');
        drawer.setAttribute('aria-hidden', 'false');
        window.setTimeout(() => {
            const items = focusables();
            items[0]?.focus();
        }, 120);
    };

    const close = () => {
        document.body.classList.remove('drawer-open');
        toggle.setAttribute('aria-expanded', 'false');
        drawer.setAttribute('aria-hidden', 'true');

        if (lastFocused instanceof HTMLElement) {
            lastFocused.focus();
        }
    };

    const isOpen = () => document.body.classList.contains('drawer-open');

    toggle.addEventListener('click', () => {
        if (isOpen()) {
            close();
        } else {
            open();
        }
    });

    closeEls.forEach((el) => el.addEventListener('click', close));
    links.forEach((link) => link.addEventListener('click', close));

    document.addEventListener('keydown', (event) => {
        if (!isOpen()) {
            return;
        }

        if (event.key === 'Escape') {
            close();
            return;
        }

        if (event.key !== 'Tab') {
            return;
        }

        const items = focusables();

        if (!items.length) {
            return;
        }

        const first = items[0];
        const last = items[items.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });

    window.addEventListener(
        'resize',
        () => {
            if (window.innerWidth >= 1024 && isOpen()) {
                close();
            }
        },
        { passive: true }
    );
}

function initScrollSpy() {
    const links = Array.from(document.querySelectorAll('[data-nav-link]'));
    const sections = links
        .map((link) => {
            const hash = link.getAttribute('href');
            return hash && hash.startsWith('#') ? document.querySelector(hash) : null;
        })
        .filter(Boolean);

    if (!sections.length || !('IntersectionObserver' in window)) {
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                links.forEach((link) => {
                    link.classList.toggle('is-active', link.getAttribute('href') === `#${entry.target.id}`);
                });
            });
        },
        { rootMargin: '-42% 0px -52% 0px', threshold: 0 }
    );

    sections.forEach((section) => observer.observe(section));
}

function initFaq() {
    const items = Array.from(document.querySelectorAll('[data-faq-item]'));

    if (!items.length) {
        return;
    }

    items.forEach((item) => {
        const button = item.querySelector('[data-faq-trigger]');

        if (!button) {
            return;
        }

        button.addEventListener('click', () => {
            const willOpen = !item.classList.contains('is-open');

            items.forEach((other) => {
                const otherButton = other.querySelector('[data-faq-trigger]');
                other.classList.remove('is-open');
                otherButton?.setAttribute('aria-expanded', 'false');
            });

            if (willOpen) {
                item.classList.add('is-open');
                button.setAttribute('aria-expanded', 'true');
            }
        });
    });
}

function initCustomization() {
    const root = document.querySelector('[data-custom]');

    if (!root) {
        return;
    }

    const tabs = Array.from(root.querySelectorAll('[data-custom-tab]'));
    const panels = Array.from(root.querySelectorAll('[data-custom-panel]'));
    const swatches = Array.from(root.querySelectorAll('.custom-palette i'));

    if (!tabs.length || !panels.length) {
        return;
    }

    let index = 0;
    let timer = null;
    let userInteracted = false;

    const activate = (nextIndex) => {
        index = (nextIndex + tabs.length) % tabs.length;

        tabs.forEach((tab, i) => {
            const active = i === index;
            tab.classList.toggle('is-active', active);
            tab.setAttribute('aria-selected', active ? 'true' : 'false');
            tab.setAttribute('tabindex', active ? '0' : '-1');
        });

        panels.forEach((panel, i) => {
            panel.classList.toggle('is-active', i === index);
            panel.setAttribute('aria-hidden', i === index ? 'false' : 'true');
        });

        const activeTab = tabs[index];
        const palette = (activeTab?.dataset.swatches || '').split(',').map((value) => value.trim()).filter(Boolean);

        if (palette.length) {
            swatches.forEach((swatch, i) => {
                swatch.style.setProperty('--sw', palette[i % palette.length]);
            });
        }
    };

    const stop = () => {
        if (timer) {
            window.clearInterval(timer);
            timer = null;
        }
    };

    const start = () => {
        if (REDUCED() || userInteracted) {
            return;
        }

        stop();
        timer = window.setInterval(() => activate(index + 1), 5600);
    };

    tabs.forEach((tab, i) => {
        tab.addEventListener('click', () => {
            userInteracted = true;
            stop();
            activate(i);
        });

        tab.addEventListener('keydown', (event) => {
            if (event.key !== 'ArrowRight' && event.key !== 'ArrowLeft') {
                return;
            }

            event.preventDefault();
            const direction = event.key === 'ArrowRight' ? 1 : -1;
            const nextIndex = (i + direction + tabs.length) % tabs.length;
            userInteracted = true;
            stop();
            activate(nextIndex);
            tabs[nextIndex]?.focus();
        });
    });

    root.addEventListener('mouseenter', stop);
    root.addEventListener('mouseleave', start);
    root.addEventListener('focusin', stop);
    root.addEventListener('focusout', (event) => {
        if (!root.contains(event.relatedTarget)) {
            start();
        }
    });

    activate(0);
    start();
}

function initBackToTop() {
    document.querySelectorAll('[data-to-top]').forEach((button) => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: REDUCED() ? 'auto' : 'smooth',
            });
        });
    });
}

function initAnchorOffset() {
    document.querySelectorAll('a[href^="#"]').forEach((link) => {
        const hash = link.getAttribute('href');

        if (!hash || hash === '#') {
            return;
        }

        const target = document.querySelector(hash);

        if (!target) {
            return;
        }

        link.addEventListener('click', (event) => {
            event.preventDefault();

            const top = target.getBoundingClientRect().top + window.scrollY - 88;

            window.scrollTo({
                top,
                behavior: REDUCED() ? 'auto' : 'smooth',
            });

            if (history.replaceState) {
                history.replaceState(null, '', hash);
            }
        });
    });
}

export function initInteractions() {
    initNavbar();
    initDrawer();
    initScrollSpy();
    initFaq();
    initCustomization();
    initBackToTop();
    initAnchorOffset();
}
