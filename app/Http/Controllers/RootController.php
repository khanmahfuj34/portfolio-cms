<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\PortfolioDomainService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class RootController extends Controller
{
    public function __construct(
        private readonly PortfolioDomainService $domainService,
        private readonly PublicPortfolioController $publicPortfolioController
    ) {}

    /**
     * Resolve "/" dynamically:
     * - Non-base custom domains delegate to public portfolio host resolution.
     * - Base hosts dispatch by authenticated guard/role or render the landing page.
     */
    public function __invoke(Request $request): RedirectResponse|View|HttpResponse
    {
        if (! $this->domainService->isBaseHost($request->getHost())) {
            return $this->publicPortfolioController->showByHost($request);
        }

        $superAdmin = Auth::guard('super_admin')->user();
        $admin = Auth::guard('admin')->user();

        $sanitizedGuard = false;

        if ($superAdmin instanceof User && ! $superAdmin->isSuperAdmin()) {
            Auth::guard('super_admin')->logout();
            $superAdmin = null;
            $sanitizedGuard = true;
        }

        if ($admin instanceof User && ! $admin->isAdmin()) {
            Auth::guard('admin')->logout();
            $admin = null;
            $sanitizedGuard = true;
        }

        $hasValidSuperAdmin = $superAdmin instanceof User && $superAdmin->isSuperAdmin();
        $hasValidAdmin = $admin instanceof User && $admin->isAdmin();

        if ($sanitizedGuard && $request->hasSession()) {
            if ($hasValidSuperAdmin || $hasValidAdmin) {
                $request->session()->regenerate(true);
            } else {
                $request->session()->invalidate();
            }
            $request->session()->regenerateToken();
        }

        if ($hasValidSuperAdmin && $hasValidAdmin) {
            return view('auth.portal-choice', [
                'superAdmin' => $superAdmin,
                'individualAdmin' => $admin,
            ]);
        }

        if ($hasValidSuperAdmin) {
            return redirect()->route('admin.dashboard');
        }

        if ($hasValidAdmin) {
            return redirect(Route::has('dashboard.index') ? route('dashboard.index') : '/dashboard');
        }

        return view('landing.index');
    }
}
