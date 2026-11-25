const REDUCED = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const COARSE = () => window.matchMedia('(pointer: coarse)').matches;

export function initCursorEffects() {
    const aura = document.querySelector('.cursor-aura');

    if (!aura || REDUCED() || COARSE()) {
        return;
    }

    const state = {
        targetX: window.innerWidth / 2,
        targetY: window.innerHeight / 2,
        x: window.innerWidth / 2,
        y: window.innerHeight / 2,
        raf: null,
        active: false,
    };

    const render = () => {
        state.x += (state.targetX - state.x) * 0.11;
        state.y += (state.targetY - state.y) * 0.11;
        aura.style.transform = `translate3d(${state.x.toFixed(2)}px, ${state.y.toFixed(2)}px, 0)`;

        if (Math.abs(state.targetX - state.x) > 0.4 || Math.abs(state.targetY - state.y) > 0.4) {
            state.raf = window.requestAnimationFrame(render);
        } else {
            state.raf = null;
        }
    };

    const move = (event) => {
        state.targetX = event.clientX;
        state.targetY = event.clientY;

        if (!state.active) {
            state.active = true;
            state.x = event.clientX;
            state.y = event.clientY;
            aura.classList.add('is-active');
        }

        if (state.raf === null) {
            state.raf = window.requestAnimationFrame(render);
        }
    };

    window.addEventListener('pointermove', move, { passive: true });

    document.addEventListener('mouseleave', () => {
        aura.classList.remove('is-active');
    });

    window.addEventListener(
        'blur',
        () => {
            aura.classList.remove('is-active');
        },
        { passive: true }
    );
}
