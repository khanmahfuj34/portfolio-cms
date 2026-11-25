<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PortfolioController;
use App\Http\Controllers\Admin\PortfolioNavItemController;
use App\Http\Controllers\Admin\PortfolioProjectController;
use App\Http\Controllers\Admin\PortfolioSkillController;
use App\Http\Controllers\Auth\SuperAdminAuthController;
use App\Http\Controllers\PublicPortfolioController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

$baseDomain = config('portfolio.base_domain', 'localhost');

/*
|--------------------------------------------------------------------------
| 1. Local / Platform Subdomain Public Portfolio Routes
|--------------------------------------------------------------------------
| Resolves {subdomain}.{base_domain} (e.g., http://mahfuj.localhost:8000)
| via PortfolioDomainService and PublicPortfolioController::showByHost().
*/
Route::domain('{subdomain}.' . $baseDomain)
    ->where(['subdomain' => '[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?'])
    ->group(function () {
        Route::get('/', [PublicPortfolioController::class, 'showByHost'])
            ->name('portfolio.subdomain.show');
    });

/*
|--------------------------------------------------------------------------
| 2. Base Domain Admin Routes & Optional Legacy Slug Fallback
|--------------------------------------------------------------------------
| Scoped strictly to config('portfolio.base_domain') (localhost) so admin
| routes and legacy slug fallbacks cannot leak across portfolio subdomains.
*/
Route::domain($baseDomain)->group(function () {

    // Application Root Redirect: if logged in as super admin, go to dashboard; else login
    Route::get('/', function () {
        if (Auth::check() && Auth::user()->role === 'super_admin' && Auth::user()->is_active) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('admin.login');
    });

    // Admin Root Redirect
    Route::get('/admin', function () {
        if (Auth::check() && Auth::user()->role === 'super_admin' && Auth::user()->is_active) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('admin.login');
    });

    // Guest Admin Routes
    Route::middleware('guest')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/login', [SuperAdminAuthController::class, 'showLoginForm'])
            ->name('login');

        Route::post('/login', [SuperAdminAuthController::class, 'login'])
            ->name('login.submit');
    });

    // Protected Admin Routes
    Route::middleware(['auth', 'super.admin'])
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {

            Route::post('/logout', [SuperAdminAuthController::class, 'logout'])
                ->name('logout');

            Route::get('/dashboard', [DashboardController::class, 'index'])
                ->name('dashboard');

            // Profiles & Portfolios Management
            Route::get('/profiles', [PortfolioController::class, 'index'])
                ->name('profiles.index');

            Route::get('/portfolios', [PortfolioController::class, 'index'])
                ->name('portfolios.index');

            Route::get('/portfolios/create', [PortfolioController::class, 'create'])
                ->name('portfolios.create');

            Route::get('/portfolios/{portfolio}/edit', [PortfolioController::class, 'edit'])
                ->name('portfolios.edit')
                ->whereNumber('portfolio');

            Route::post('/portfolios', [PortfolioController::class, 'store'])
                ->name('portfolios.store');

            Route::patch('/portfolios/{portfolio}/publish', [PortfolioController::class, 'publish'])
                ->name('portfolios.publish')
                ->whereNumber('portfolio');

            Route::patch('/portfolios/{portfolio}/unpublish', [PortfolioController::class, 'unpublish'])
                ->name('portfolios.unpublish')
                ->whereNumber('portfolio');

            Route::patch('/portfolios/{portfolio}/toggle-publish', [PortfolioController::class, 'togglePublish'])
                ->name('portfolios.toggle-publish')
                ->whereNumber('portfolio');

            // Portfolio Projects & Skills
            Route::scopeBindings()->group(function () {
                Route::get(
                    'portfolios/{portfolio}/projects',
                    [PortfolioProjectController::class, 'index']
                )->name('portfolios.projects.index');

                Route::post(
                    'portfolios/{portfolio}/projects',
                    [PortfolioProjectController::class, 'store']
                )->name('portfolios.projects.store');

                Route::put(
                    'portfolios/{portfolio}/projects/{project}',
                    [PortfolioProjectController::class, 'update']
                )
                    ->whereNumber('portfolio')
                    ->whereNumber('project')
                    ->name('portfolios.projects.update');

                Route::delete(
                    'portfolios/{portfolio}/projects/{project}',
                    [PortfolioProjectController::class, 'destroy']
                )->name('portfolios.projects.destroy');

                Route::get(
                    'portfolios/{portfolio}/skills',
                    [PortfolioSkillController::class, 'index']
                )->name('portfolios.skills.index');

                Route::post(
                    'portfolios/{portfolio}/skills',
                    [PortfolioSkillController::class, 'store']
                )->name('portfolios.skills.store');

                Route::put(
                    'portfolios/{portfolio}/skills/{skill}',
                    [PortfolioSkillController::class, 'update']
                )->name('portfolios.skills.update');

                Route::delete(
                    'portfolios/{portfolio}/skills/{skill}',
                    [PortfolioSkillController::class, 'destroy']
                )->name('portfolios.skills.destroy');
            });

            // Portfolio Navbar Management
            Route::prefix('portfolios/{portfolio}/nav-items')
                ->name('portfolios.nav-items.')
                ->whereNumber('portfolio')
                ->controller(PortfolioNavItemController::class)
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::post('/', 'store')->name('store');
                    Route::patch('/reorder', 'reorder')->name('reorder');
                    Route::put('/{navItem}', 'update')->whereNumber('navItem')->name('update');
                    Route::delete('/{navItem}', 'destroy')->whereNumber('navItem')->name('destroy');
                    Route::patch('/{navItem}/visibility', 'toggleVisibility')->whereNumber('navItem')->name('visibility');
                });
        });

    /*
    |--------------------------------------------------------------------------
    | Legacy Slug Fallback (Backward Compatibility)
    |--------------------------------------------------------------------------
    | Retained temporarily on the base domain when PORTFOLIO_LEGACY_SLUG_FALLBACK=true
    | to keep legacy slug URLs and same-origin draft previews operational.
    */
    if (config('portfolio.legacy_slug_fallback', true)) {
        Route::get('/{slug}', [PublicPortfolioController::class, 'show'])
            ->name('portfolio.show')
            ->where('slug', '^[a-z0-9]+(?:-[a-z0-9]+)*$');
    }
});

/*
|--------------------------------------------------------------------------
| 3. Future Custom Domain Catch-All Public Portfolio Route
|--------------------------------------------------------------------------
| Reached only when the incoming host does not match base_domain or
| {subdomain}.{base_domain}, enabling verified custom domains seamlessly.
*/
Route::get('/', [PublicPortfolioController::class, 'showByHost'])
    ->name('portfolio.custom-domain.show');
