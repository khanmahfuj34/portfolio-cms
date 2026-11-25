const REDUCED = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const FINE_POINTER = () => window.matchMedia('(pointer: fine)').matches;

export function initPortfolioShowcase() {
    const root = document.querySelector('[data-showcase]');

    if (!root) {
        return;
    }

    const viewport = root.querySelector('[data-showcase-viewport]');
    const track = root.querySelector('[data-showcase-track]');

    if (!viewport || !track) {
        return;
    }

    const originals = Array.from(track.children).filter((node) => !node.hasAttribute('data-clone'));

    if (!originals.length) {
        return;
    }

    originals.forEach((node) => {
        const clone = node.cloneNode(true);
        clone.setAttribute('aria-hidden', 'true');
        clone.setAttribute('data-clone', 'true');
        clone.querySelectorAll('[id]').forEach((el) => el.removeAttribute('id'));
        clone.querySelectorAll('a, button').forEach((el) => {
            el.setAttribute('tabindex', '-1');
        });
        track.appendChild(clone);
    });

    const slides = Array.from(track.children);
    const prev = root.querySelector('[data-showcase-prev]');
    const next = root.querySelector('[data-showcase-next]');
    const progress = root.querySelector('[data-showcase-progress]');

    const state = {
        x: 0,
        target: 0,
        half: 0,
        speed: 26,
        hoverPause: false,
        focusPause: false,
        dragging: false,
        dragStartX: 0,
        dragStartTarget: 0,
        moved: false,
        visible: true,
        lastTime: 0,
    };

    const measure = () => {
        const styles = window.getComputedStyle(track);
        const gap = parseFloat(styles.columnGap || styles.gap) || 0;

        state.half = originals.reduce((total, slide) => {
            return total + slide.getBoundingClientRect().width + gap;
        }, 0);
    };

    const wrap = () => {
        if (state.half <= 0) {
            return;
        }

        while (state.target <= -state.half) {
            state.target += state.half;
            state.x += state.half;
        }

        while (state.target > 0) {
            state.target -= state.half;
            state.x -= state.half;
        }
    };

    const updateProgress = () => {
        if (!progress || state.half <= 0) {
            return;
        }

        let position = (-state.x) % state.half;

        if (position < 0) {
            position += state.half;
        }

        const ratio = Math.min(Math.max(position / state.half, 0), 1);
        progress.style.width = `${Math.max(ratio * 100, 4)}%`;
    };

    const render = (time) => {
        const delta = state.lastTime ? Math.min((time - state.lastTime) / 1000, 0.05) : 0;
        state.lastTime = time;

        const paused =
            state.hoverPause || state.focusPause || state.dragging || document.hidden || !state.visible || REDUCED();

        if (!paused) {
            state.target -= state.speed * delta;
        }

        wrap();

        state.x += (state.target - state.x) * 0.14;
        track.style.transform = `translate3d(${state.x.toFixed(2)}px, 0, 0)`;
        updateProgress();

        window.requestAnimationFrame(render);
    };

    const step = (direction) => {
        const first = slides[0];
        const styles = window.getComputedStyle(track);
        const gap = parseFloat(styles.columnGap || styles.gap) || 0;
        const width = first ? first.getBoundingClientRect().width + gap : 0;

        state.target += direction * width;
        wrap();
    };

    if (prev) {
        prev.addEventListener('click', () => step(1));
    }

    if (next) {
        next.addEventListener('click', () => step(-1));
    }

    viewport.addEventListener('mouseenter', () => {
        state.hoverPause = true;
    });

    viewport.addEventListener('mouseleave', () => {
        state.hoverPause = false;
    });

    viewport.addEventListener('focusin', () => {
        state.focusPause = true;
    });

    viewport.addEventListener('focusout', () => {
        state.focusPause = false;
    });

    viewport.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowRight') {
            event.preventDefault();
            step(-1);
        } else if (event.key === 'ArrowLeft') {
            event.preventDefault();
            step(1);
        }
    });

    viewport.addEventListener('pointerdown', (event) => {
        if (event.button !== 0 && event.pointerType === 'mouse') {
            return;
        }

        state.dragging = true;
        state.moved = false;
        state.dragStartX = event.clientX;
        state.dragStartTarget = state.target;
        viewport.classList.add('is-dragging');
        viewport.setPointerCapture?.(event.pointerId);
    });

    viewport.addEventListener('pointermove', (event) => {
        if (!state.dragging) {
            return;
        }

        const delta = event.clientX - state.dragStartX;

        if (Math.abs(delta) > 5) {
            state.moved = true;
        }

        state.target = state.dragStartTarget + delta;
        wrap();
    });

    const endDrag = (event) => {
        if (!state.dragging) {
            return;
        }

        state.dragging = false;
        viewport.classList.remove('is-dragging');

        if (viewport.hasPointerCapture?.(event.pointerId)) {
            viewport.releasePointerCapture(event.pointerId);
        }
    };

    viewport.addEventListener('pointerup', endDrag);
    viewport.addEventListener('pointercancel', endDrag);
    viewport.addEventListener('lostpointercapture', () => {
        state.dragging = false;
        viewport.classList.remove('is-dragging');
    });

    viewport.addEventListener(
        'click',
        (event) => {
            if (state.moved) {
                event.preventDefault();
                event.stopPropagation();
                state.moved = false;
            }
        },
        true
    );

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    state.visible = entry.isIntersecting;
                });
            },
            { rootMargin: '120px 0px' }
        );

        observer.observe(root);
    }

    let resizeTimer = null;

    window.addEventListener(
        'resize',
        () => {
            window.clearTimeout(resizeTimer);
            resizeTimer = window.setTimeout(() => {
                measure();
                wrap();
            }, 140);
        },
        { passive: true }
    );

    if (FINE_POINTER() && !REDUCED()) {
        root.querySelectorAll('.showcase-card').forEach((card) => {
            let frame = null;
            let pendingX = 0;
            let pendingY = 0;

            const apply = () => {
                card.style.setProperty('--rx', `${pendingX.toFixed(2)}deg`);
                card.style.setProperty('--ry', `${pendingY.toFixed(2)}deg`);
                frame = null;
            };

            card.addEventListener(
                'pointermove',
                (event) => {
                    if (state.dragging) {
                        return;
                    }

                    const rect = card.getBoundingClientRect();
                    const px = (event.clientX - rect.left) / rect.width - 0.5;
                    const py = (event.clientY - rect.top) / rect.height - 0.5;

                    pendingY = px * 9;
                    pendingX = py * -9;

                    if (frame === null) {
                        frame = window.requestAnimationFrame(apply);
                    }
                },
                { passive: true }
            );

            card.addEventListener(
                'pointerleave',
                () => {
                    if (frame !== null) {
                        window.cancelAnimationFrame(frame);
                        frame = null;
                    }

                    card.style.setProperty('--rx', '0deg');
                    card.style.setProperty('--ry', '0deg');
                },
                { passive: true }
            );
        });
    }

    measure();
    state.lastTime = performance.now();
    window.requestAnimationFrame(render);
}
