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
        $request->session()->regenerate();

        return redirect()->intended(
            route('admin.dashboard')
        );
    }

    /**
     * Handle Super Admin logout without disturbing an independent Individual Admin session.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('super_admin')->logout();

        if (Auth::guard('admin')->check()) {
            $request->session()->regenerate(true);
            $request->session()->regenerateToken();
        } else {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect()->route('admin.login');
    }
}
