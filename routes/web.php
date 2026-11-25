<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PortfolioController;
use App\Http\Controllers\Admin\PortfolioNavItemController;
use App\Http\Controllers\Admin\PortfolioProjectController;
use App\Http\Controllers\Admin\PortfolioSkillController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\SuperAdminAuthController;
use App\Http\Controllers\Dashboard\AccountDashboardController;
use App\Http\Controllers\PublicPortfolioController;
use App\Http\Controllers\RootController;
use App\Models\User;
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
| 2. Unified Root Entry Point & Base-Domain Admin / Legacy Fallback Routes
|--------------------------------------------------------------------------
| "/" resolves custom domains via PublicPortfolioController when the host is
| external, and dispatches base-host requests (localhost / local 127.0.0.1)
| according to authenticated guards or renders the public landing page.
*/
Route::get('/', RootController::class)
    ->name('portfolio.custom-domain.show');

Route::middleware('base.domain')->group(function () {

    // Individual Admin Authentication Routes (isolated to 'admin' guard)
    Route::middleware('guest:admin')->group(function () {
        Route::get('/register', [AdminAuthController::class, 'showRegisterForm'])
            ->name('register');

        Route::post('/register', [AdminAuthController::class, 'register'])
            ->middleware('throttle:10,1')
            ->name('register.submit');

        Route::get('/login', [AdminAuthController::class, 'showLoginForm'])
            ->name('login');

        Route::post('/login', [AdminAuthController::class, 'login'])
            ->middleware('throttle:10,1')
            ->name('login.submit');
    });

    Route::post('/logout', [AdminAuthController::class, 'logout'])
        ->middleware('auth:admin')
        ->name('logout');

    // Individual Admin Workspace Routes (Strictly owner-scoped on 'admin' guard)
    Route::middleware(['auth:admin', 'individual.admin'])
        ->prefix('dashboard')
        ->name('dashboard.')
        ->group(function () {
            Route::get('/', [AccountDashboardController::class, 'index'])
                ->name('index');

            Route::get('/account', [AccountDashboardController::class, 'editAccount'])
                ->name('account.edit');

            Route::patch('/account', [AccountDashboardController::class, 'updateAccount'])
                ->name('account.update');

            Route::put('/account/password', [AccountDashboardController::class, 'updatePassword'])
                ->name('account.password');

            Route::get('/portfolios', [AccountDashboardController::class, 'portfolios'])
                ->name('portfolios.index');

            Route::get('/portfolios/create', [AccountDashboardController::class, 'create'])
                ->name('portfolios.create');

            Route::post('/portfolios', [AccountDashboardController::class, 'store'])
                ->name('portfolios.store');

            Route::get('/portfolios/{portfolio}/edit', [AccountDashboardController::class, 'edit'])
                ->name('portfolios.edit')
                ->whereNumber('portfolio');

            Route::put('/portfolios/{portfolio}', [AccountDashboardController::class, 'update'])
                ->name('portfolios.update')
                ->whereNumber('portfolio');

            Route::delete('/portfolios/{portfolio}', [AccountDashboardController::class, 'destroy'])
                ->name('portfolios.destroy')
                ->whereNumber('portfolio');

            Route::get('/portfolios/{portfolio}/preview', [AccountDashboardController::class, 'preview'])
                ->name('portfolios.preview')
                ->whereNumber('portfolio');

            Route::post('/portfolios/{portfolio}/preview/revoke', [AccountDashboardController::class, 'revokePreview'])
                ->name('portfolios.preview.revoke')
                ->whereNumber('portfolio');

            Route::patch('/portfolios/{portfolio}/publish', [AccountDashboardController::class, 'publish'])
                ->name('portfolios.publish')
                ->whereNumber('portfolio');

            Route::patch('/portfolios/{portfolio}/unpublish', [AccountDashboardController::class, 'unpublish'])
                ->name('portfolios.unpublish')
                ->whereNumber('portfolio');

            // Reused Nested Portfolio Resources (PortfolioPolicy + scopeBindings enforced)
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

    // Admin Root Redirect
    Route::get('/admin', function () {
        $superAdmin = Auth::guard('super_admin')->user();
        if ($superAdmin instanceof User && $superAdmin->isSuperAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('admin.login');
    });

    // Guest Admin Routes (isolated to 'super_admin' guard)
    Route::middleware('guest:super_admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/login', [SuperAdminAuthController::class, 'showLoginForm'])
            ->name('login');

        Route::post('/login', [SuperAdminAuthController::class, 'login'])
            ->middleware('throttle:10,1')
            ->name('login.submit');
    });

    // Protected Admin Routes
    Route::middleware(['auth:super_admin', 'super.admin'])
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {

            Route::post('/logout', [SuperAdminAuthController::class, 'logout'])
                ->name('logout');

            Route::get('/dashboard', [DashboardController::class, 'index'])
                ->name('dashboard');

            // Individual Admin Users Management
            Route::get('/users', [UserManagementController::class, 'index'])
                ->name('users.index');

            Route::patch('/users/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])
                ->name('users.toggle-status')
                ->whereNumber('user');

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
