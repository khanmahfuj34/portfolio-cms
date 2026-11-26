<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SuperAdminLoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SuperAdminAuthController extends Controller
{
    /**
     * Display the Super Admin login page.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        $superAdmin = Auth::guard('super_admin')->user();
        if ($superAdmin instanceof User && $superAdmin->isSuperAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle Super Admin login (isolated to 'super_admin' guard).
     */
    public function login(
        SuperAdminLoginRequest $request
    ): RedirectResponse {
        $throttleKey = Str::transliterate(Str::lower((string) $request->input('email')) . '|super-admin|' . $request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ])->redirectTo(route('admin.login'));
        }

        $credentials = $request->validated();

        $credentials['role'] = User::ROLE_SUPER_ADMIN;
        $credentials['is_active'] = true;

        if (! Auth::guard('super_admin')->validate($credentials)) {
            RateLimiter::hit($throttleKey, 60);

            return redirect()
                ->route('admin.login')
                ->withErrors([
                    'email' => 'Invalid email or password.',
                ])
                ->onlyInput('email');
        }

        // Enforce single active authentication guard per browser session.
        Auth::guard('admin')->logout();
        Auth::guard('web')->logout();

        $intended = (string) $request->session()->get('url.intended', '');
        $intendedHost = parse_url($intended, PHP_URL_HOST);
        $intendedPath = parse_url($intended, PHP_URL_PATH) ?: '';
        $isSafeIntendedHost = ! is_string($intendedHost) || $intendedHost === '' || app(\App\Services\PortfolioDomainService::class)->isBaseHost($intendedHost);
        if (! $isSafeIntendedHost || ! preg_match('#^/admin/(?:dashboard|users|themes|portfolios|profiles)(?:/.*)?$#', $intendedPath)) {
            $request->session()->forget('url.intended');
        }

        if (! Auth::guard('super_admin')->attempt($credentials)) {
            RateLimiter::hit($throttleKey, 60);

            return redirect()
                ->route('admin.login')
                ->withErrors([
                    'email' => 'Invalid email or password.',
                ])
                ->onlyInput('email');
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate(true);
        $request->session()->regenerateToken();

        return redirect()->intended(
            route('admin.dashboard')
        );
    }

    /**
     * Handle Super Admin logout and invalidate the current browser session.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('super_admin')->logout();
        Auth::guard('admin')->logout();
        Auth::guard('web')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect()->route('admin.login');
    }
}
