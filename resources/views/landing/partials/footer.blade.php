<footer class="site-footer">
    <div class="l-container">
        <div class="footer-grid">
            <div class="footer-brand">
                <a href="{{ url('/') }}" class="brand" aria-label="Portfolio CMS home">
                    <span class="brand-mark" aria-hidden="true">P</span>
                    <span class="brand-name">Portfolio <span>CMS</span></span>
                </a>
                <p>
                    A focused portfolio platform for developers, designers, photographers and creative
                    professionals. Build your work, shape your identity and publish when you are ready.
                </p>
            </div>

            <div class="footer-col">
                <h4>Product</h4>
                <ul>
                    <li><a href="#features">Features</a></li>
                    <li><a href="#showcase">Templates</a></li>
                    <li><a href="#how-it-works">How It Works</a></li>
                    <li><a href="#benefits">Benefits</a></li>
                    <li><a href="#faq">FAQ</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Portfolio styles</h4>
                <ul>
                    <li><a href="#showcase">Developer</a></li>
                    <li><a href="#showcase">Designer</a></li>
                    <li><a href="#showcase">Photographer</a></li>
                    <li><a href="#customization">Creative Professional</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Account</h4>
                <ul>
                    <li><a href="{{ route('login') }}">Login</a></li>
                    <li><a href="{{ route('register') }}">Create account</a></li>
                    <li><a href="{{ route('admin.login') }}">Super Admin login</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} Portfolio CMS. All rights reserved.</p>

            <button type="button" class="to-top" data-to-top>
                Back to top
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 19V5M6 11l6-6 6 6" />
                </svg>
            </button>
        </div>
    </div>
</footer>
