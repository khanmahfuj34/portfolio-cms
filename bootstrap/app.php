<?php

use App\Http\Middleware\EnsureBaseDomain;
use App\Http\Middleware\EnsureIndividualAdmin;
use App\Http\Middleware\EnsureSuperAdmin;
use App\Models\User;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectTo(
            guests: function (Request $request): string {
                if ($request->is('admin', 'admin/*')) {
                    return '/admin/login';
                }

                return Route::has('login') ? route('login') : '/login';
            },
            users: function (Request $request): string {
                if ($request->is('admin', 'admin/*')) {
                    $superAdmin = Auth::guard('super_admin')->user();
                    if ($superAdmin instanceof User && $superAdmin->isSuperAdmin()) {
                        return '/admin/dashboard';
                    }
                }

                $admin = Auth::guard('admin')->user();
                if ($admin instanceof User && $admin->isAdmin()) {
                    return Route::has('dashboard.index') ? route('dashboard.index') : '/dashboard';
                }

                return '/';
            },
        );

        $middleware->alias([
            'base.domain' => EnsureBaseDomain::class,
            'super.admin' => EnsureSuperAdmin::class,
            'individual.admin' => EnsureIndividualAdmin::class,
        ]);

        $middleware->prependToPriorityList(AuthenticatesRequests::class, EnsureBaseDomain::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
