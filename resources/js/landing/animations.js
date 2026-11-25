const REDUCED = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const FINE_POINTER = () => window.matchMedia('(pointer: fine)').matches;

function markVisible(el) {
    el.classList.add('is-visible');
}

function initReveal() {
    const items = Array.from(document.querySelectorAll('[data-reveal], [data-split]'));

    if (!items.length) {
        return;
    }

    if (REDUCED() || !('IntersectionObserver' in window)) {
        items.forEach(markVisible);
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                markVisible(entry.target);
                observer.unobserve(entry.target);
            });
        },
        { rootMargin: '0px 0px -8% 0px', threshold: 0.14 }
    );

    items.forEach((item) => observer.observe(item));
}

function initStagger() {
    document.querySelectorAll('[data-reveal-group]').forEach((group) => {
        const children = Array.from(group.querySelectorAll('[data-reveal]'));
        children.forEach((child, index) => {
            if (!child.style.getPropertyValue('--reveal-delay')) {
                child.style.setProperty('--reveal-delay', `${Math.min(index * 70, 560)}ms`);
            }
        });
    });
}

function initSplitHeadings() {
    if (REDUCED()) {
        return;
    }

    document.querySelectorAll('[data-split]').forEach((heading) => {
        const words = heading.textContent.trim().split(/\s+/);

        if (words.length < 2) {
            return;
        }

        heading.textContent = '';

        words.forEach((word, index) => {
            const span = document.createElement('span');
            span.className = 'split-word';
            span.textContent = word;
            span.style.transitionDelay = `${Math.min(index * 42, 520)}ms`;
            heading.appendChild(span);

            if (index < words.length - 1) {
                heading.appendChild(document.createTextNode(' '));
            }
        });
    });
}

function initTimeline() {
    const timelines = document.querySelectorAll('[data-timeline]');

    if (!timelines.length) {
        return;
    }

    if (REDUCED() || !('IntersectionObserver' in window)) {
        timelines.forEach(markVisible);
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    markVisible(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.25 }
    );

    timelines.forEach((timeline) => observer.observe(timeline));
}

function initParallax() {
    const scene = document.querySelector('[data-parallax-scene]');

    if (!scene || REDUCED() || !FINE_POINTER()) {
        return;
    }

    const layers = Array.from(scene.querySelectorAll('[data-parallax-depth]'));

    if (!layers.length) {
        return;
    }

    const state = {
        targetX: 0,
        targetY: 0,
        currentX: 0,
        currentY: 0,
        raf: null,
        active: false,
    };

    const render = () => {
        state.currentX += (state.targetX - state.currentX) * 0.075;
        state.currentY += (state.targetY - state.currentY) * 0.075;

        layers.forEach((layer) => {
            const depth = Number(layer.dataset.parallaxDepth) || 10;
            const x = state.currentX * depth;
            const y = state.currentY * depth;
            layer.style.translate = `${x.toFixed(2)}px ${y.toFixed(2)}px`;
        });

        const settled =
            Math.abs(state.targetX - state.currentX) < 0.0008 &&
            Math.abs(state.targetY - state.currentY) < 0.0008 &&
            !state.active;

        if (settled) {
            state.raf = null;
            return;
        }

        state.raf = window.requestAnimationFrame(render);
    };

    const schedule = () => {
        if (state.raf === null) {
            state.raf = window.requestAnimationFrame(render);
        }
    };

    scene.addEventListener(
        'pointermove',
        (event) => {
            const rect = scene.getBoundingClientRect();
            state.targetX = (event.clientX - rect.left) / rect.width - 0.5;
            state.targetY = (event.clientY - rect.top) / rect.height - 0.5;
            state.active = true;
            schedule();
        },
        { passive: true }
    );

    scene.addEventListener(
        'pointerleave',
        () => {
            state.targetX = 0;
            state.targetY = 0;
            state.active = false;
            schedule();
        },
        { passive: true }
    );
}

function initMagnetic() {
    if (REDUCED() || !FINE_POINTER()) {
        return;
    }

    document.querySelectorAll('[data-magnetic]').forEach((el) => {
        const strength = Number(el.dataset.magneticStrength) || 0.24;

        el.addEventListener(
            'pointermove',
            (event) => {
                const rect = el.getBoundingClientRect();
                const x = (event.clientX - rect.left - rect.width / 2) * strength;
                const y = (event.clientY - rect.top - rect.height / 2) * strength;
                el.style.translate = `${x.toFixed(1)}px ${y.toFixed(1)}px`;
            },
            { passive: true }
        );

        el.addEventListener(
            'pointerleave',
            () => {
                el.style.translate = '0px 0px';
            },
            { passive: true }
        );
    });
}

function initCounters() {
    const counters = document.querySelectorAll('[data-count]');

    if (!counters.length) {
        return;
    }

    const animate = (el) => {
        const target = Number(el.dataset.count) || 0;
        const suffix = el.dataset.countSuffix || '';
        const duration = 1500;
        const start = performance.now();

        const tick = (now) => {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = `${Math.round(target * eased)}${suffix}`;

            if (progress < 1) {
                window.requestAnimationFrame(tick);
            }
        };

        window.requestAnimationFrame(tick);
    };

    if (REDUCED() || !('IntersectionObserver' in window)) {
        counters.forEach((el) => {
            el.textContent = `${el.dataset.count || 0}${el.dataset.countSuffix || ''}`;
        });
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    animate(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.5 }
    );

    counters.forEach((counter) => observer.observe(counter));
}

export function initAnimations() {
    initStagger();
    initSplitHeadings();
    initReveal();
    initTimeline();
    initParallax();
    initMagnetic();
    initCounters();
}
