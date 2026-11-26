<?php

namespace App\Http\Controllers;

use App\Http\Middleware\RejectDualAuthSessions;
use App\Models\User;
use App\Services\PortfolioDomainService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class RootController extends Controller
{
    public function __construct(
        private readonly PortfolioDomainService $domainService,
        private readonly PublicPortfolioController $publicPortfolioController
    ) {}

    /**
     * Resolve "/" dynamically:
     * - Non-base custom domains delegate to public portfolio host resolution.
     * - Base hosts dispatch by single authenticated guard/role or render the landing page.
     */
    public function __invoke(Request $request): RedirectResponse|View|HttpResponse|SymfonyResponse
    {
        if (! $this->domainService->isBaseHost($request->getHost())) {
            return $this->publicPortfolioController->showByHost($request);
        }

        $hasSuperGuard = Auth::guard('super_admin')->check();
        $hasAdminGuard = Auth::guard('admin')->check();

        // Fail closed if both privileged guards exist simultaneously in the same session.
        if (($hasSuperGuard && $hasAdminGuard) || RejectDualAuthSessions::hasPersistedDualGuards($request)) {
            return RejectDualAuthSessions::failClosed($request);
        }

        // Never treat default web guard authentication as a privileged session.
        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
            if (! $hasSuperGuard && ! $hasAdminGuard && $request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }
        }

        $superAdmin = $hasSuperGuard ? Auth::guard('super_admin')->user() : null;
        if ($hasSuperGuard) {
            if ($superAdmin instanceof User && $superAdmin->isSuperAdmin()) {
                return redirect()->route('admin.dashboard')
                    ->header('Cache-Control', 'no-store, no-cache, must-revalidate, private')
                    ->header('Pragma', 'no-cache');
            }

            Auth::guard('super_admin')->logout();
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return response()
                ->view('landing.index')
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, private');
        }

        $admin = $hasAdminGuard ? Auth::guard('admin')->user() : null;
        if ($hasAdminGuard) {
            if ($admin instanceof User && $admin->isAdmin()) {
                return redirect(Route::has('dashboard.index') ? route('dashboard.index') : '/dashboard')
                    ->header('Cache-Control', 'no-store, no-cache, must-revalidate, private')
                    ->header('Pragma', 'no-cache');
            }

            Auth::guard('admin')->logout();
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return response()
                ->view('landing.index')
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, private');
        }

        return response()
            ->view('landing.index')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, private');
    }
}
