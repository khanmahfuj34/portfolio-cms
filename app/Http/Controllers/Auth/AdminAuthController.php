<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AdminLoginRequest;
use App\Http\Requests\Auth\AdminRegisterRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    /**
     * Display the Individual Admin registration page.
     */
    public function showRegisterForm(): View|RedirectResponse
    {
        if ($redirect = $this->redirectIfAuthenticated()) {
            return $redirect;
        }

        return view('auth.admin-register');
    }

    /**
     * Handle Individual Admin self-registration.
     */
    public function register(AdminRegisterRequest $request): RedirectResponse
    {
        $user = $this->userService->registerIndividualAdmin(
            $request->safe()->only(['name', 'email', 'password'])
        );

        Auth::guard('admin')->login($user);
        $request->session()->regenerate();

        return redirect($this->dashboardPath());
    }

    /**
     * Display the Individual Admin login page.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if ($redirect = $this->redirectIfAuthenticated()) {
            return $redirect;
        }

        return view('auth.admin-login');
    }

    /**
     * Handle Individual Admin login (strictly active ROLE_ADMIN only on 'admin' guard).
     */
    public function login(AdminLoginRequest $request): RedirectResponse
    {
        $throttleKey = $this->loginThrottleKey($request);

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ])->redirectTo(route('login'));
        }

        $validated = $request->validated();

        $authenticated = Auth::guard('admin')->attempt([
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ], $request->boolean('remember'));

        if (! $authenticated) {
            RateLimiter::hit($throttleKey, 60);

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Invalid email or password.',
                ])
                ->onlyInput('email');
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        return redirect()->intended($this->dashboardPath());
    }

    /**
     * Handle Individual Admin logout without disturbing an independent Super Admin session.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();

        if (Auth::guard('super_admin')->check()) {
            $request->session()->regenerate(true);
            $request->session()->regenerateToken();
        } else {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect()->route('login');
    }

    private function redirectIfAuthenticated(): ?RedirectResponse
    {
        $user = Auth::guard('admin')->user();
        if ($user instanceof User && $user->isAdmin()) {
            return redirect($this->dashboardPath());
        }

        return null;
    }

    private function dashboardPath(): string
    {
        return Route::has('dashboard.index') ? route('dashboard.index') : '/dashboard';
    }

    private function loginThrottleKey(Request $request): string
    {
        return 'admin-login:' . strtolower((string) $request->input('email')) . '|' . $request->ip();
    }
}
