<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\SuperAdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PortfolioNavItemController;
use App\Http\Controllers\Admin\PortfolioController;

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
        // Portfolio Management
        Route::post('/portfolios', [PortfolioController::class, 'store'])
            ->name('portfolios.store');


        // Portfolio Navbar Management

        Route::prefix('portfolios/{portfolio}/nav-items')
            ->name('portfolios.nav-items.')
            ->whereNumber('portfolio')
            ->controller(PortfolioNavItemController::class)
            ->group(function () {

                // Display all navigation items
                Route::get('/', 'index')
                    ->name('index');

                // Create navigation item
                Route::post('/', 'store')
                    ->name('store');

                // Reorder navigation items
                Route::patch('/reorder', 'reorder')
                    ->name('reorder');

                // Update navigation item
                Route::put('/{navItem}', 'update')
                    ->whereNumber('navItem')
                    ->name('update');

                // Delete navigation item
                Route::delete('/{navItem}', 'destroy')
                    ->whereNumber('navItem')
                    ->name('destroy');

                // Toggle navigation visibility
                Route::patch('/{navItem}/visibility', 'toggleVisibility')
                    ->whereNumber('navItem')
                    ->name('visibility');
                

            });
    });