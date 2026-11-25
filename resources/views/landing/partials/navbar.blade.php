<header class="site-nav" data-nav>
    <div class="l-container nav-inner">
        <a href="{{ url('/') }}" class="brand" aria-label="Portfolio CMS home">
            <span class="brand-mark" aria-hidden="true">P</span>
            <span class="brand-name">Portfolio <span>CMS</span></span>
        </a>

        <nav class="nav-links" aria-label="Primary navigation">
            <a href="#features" class="nav-link" data-nav-link>Features</a>
            <a href="#showcase" class="nav-link" data-nav-link>Templates</a>
            <a href="#how-it-works" class="nav-link" data-nav-link>How It Works</a>
        </nav>

        <div class="nav-actions">
            <a href="{{ route('login') }}" class="nav-login">Login</a>
            <a href="{{ route('register') }}" class="btn btn-primary btn-sm" data-magnetic data-magnetic-strength="0.18">
                Get Started
                <svg class="arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M5 12h14M13 6l6 6-6 6" />
                </svg>
            </a>
        </div>

        <button type="button" class="nav-toggle" data-nav-toggle aria-label="Open navigation menu" aria-expanded="false" aria-controls="mobile-drawer">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</header>

<div class="mobile-drawer" id="mobile-drawer" data-drawer aria-hidden="true">
    <button type="button" class="drawer-scrim" data-drawer-close aria-label="Close navigation menu"></button>

    <div class="drawer-panel">
        <a href="#features" class="drawer-link" data-drawer-close>Features <span>01</span></a>
        <a href="#showcase" class="drawer-link" data-drawer-close>Templates <span>02</span></a>
        <a href="#how-it-works" class="drawer-link" data-drawer-close>How It Works <span>03</span></a>
        <a href="#customization" class="drawer-link" data-drawer-close>Customization <span>04</span></a>
        <a href="#faq" class="drawer-link" data-drawer-close>FAQ <span>05</span></a>

        <div class="drawer-actions">
            <a href="{{ route('register') }}" class="btn btn-primary">
                Get Started
                <svg class="arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M5 12h14M13 6l6 6-6 6" />
                </svg>
            </a>
            <a href="{{ route('login') }}" class="btn btn-ghost">Login</a>
        </div>

        <p class="drawer-foot">
            One platform for your personal portfolio, projects, skills and professional identity.
        </p>
    </div>
</div>
