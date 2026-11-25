<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Base Domain for Public Portfolio Subdomains
    |--------------------------------------------------------------------------
    |
    | Locally this defaults to "localhost" (so portfolios resolve under
    | http://{slug}.localhost:8000). In production, set PORTFOLIO_BASE_DOMAIN
    | to your primary root domain (e.g., "portfoliocms.com").
    |
    */
    'base_domain' => strtolower((string) env('PORTFOLIO_BASE_DOMAIN', 'localhost')),

    /*
    |--------------------------------------------------------------------------
    | Reserved Subdomain Labels
    |--------------------------------------------------------------------------
    |
    | Platform-reserved labels that cannot be registered as portfolio subdomains.
    |
    */
    'reserved_subdomains' => [
        'admin',
        'api',
        'www',
        'login',
        'logout',
        'register',
        'dashboard',
        'profiles',
        'portfolios',
        'storage',
        'build',
        'assets',
        'mail',
        'app',
        'static',
        'cdn',
    ],

    /*
    |--------------------------------------------------------------------------
    | Legacy Slug Route Fallback
    |--------------------------------------------------------------------------
    |
    | Keeps /{slug} on the base domain temporarily active for backward
    | compatibility and existing slug-based tests while transitioning to
    | subdomain URLs. Can be disabled via PORTFOLIO_LEGACY_SLUG_FALLBACK=false.
    |
    */
    'legacy_slug_fallback' => (bool) env('PORTFOLIO_LEGACY_SLUG_FALLBACK', true),
];
