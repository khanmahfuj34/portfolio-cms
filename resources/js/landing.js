import { initAnimations } from './landing/animations';
import { initPortfolioShowcase } from './landing/portfolio-showcase';
import { initCursorEffects } from './landing/cursor-effects';
import { initInteractions } from './landing/interactions';

function boot() {
    initInteractions();
    initAnimations();
    initPortfolioShowcase();
    initCursorEffects();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot, { once: true });
} else {
    boot();
}
