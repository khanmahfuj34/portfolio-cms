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
     * Handle Individual Admin self-registration under strict single active session policy.
     */
    public function register(AdminRegisterRequest $request): RedirectResponse
    {
        $user = $this->userService->registerIndividualAdmin(
            $request->safe()->only(['name', 'email', 'password'])
        );

        Auth::guard('super_admin')->logout();
        Auth::guard('web')->logout();
        $request->session()->forget('url.intended');

        Auth::guard('admin')->login($user);
        $request->session()->regenerate(true);
        $request->session()->regenerateToken();

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
        $credentials = [
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ];

        if (! Auth::guard('admin')->validate($credentials)) {
            RateLimiter::hit($throttleKey, 60);

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Invalid email or password.',
                ])
                ->onlyInput('email');
        }

        // Enforce single active authentication guard per browser session.
        Auth::guard('super_admin')->logout();
        Auth::guard('web')->logout();

        $intended = (string) $request->session()->get('url.intended', '');
        $intendedHost = parse_url($intended, PHP_URL_HOST);
        $intendedPath = parse_url($intended, PHP_URL_PATH) ?: '';
        $isSafeIntendedHost = ! is_string($intendedHost) || $intendedHost === '' || app(\App\Services\PortfolioDomainService::class)->isBaseHost($intendedHost);
        if (! $isSafeIntendedHost || ! preg_match('#^/dashboard(?:/.*)?$#', $intendedPath)) {
            $request->session()->forget('url.intended');
        }

        if (! Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, 60);

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Invalid email or password.',
                ])
                ->onlyInput('email');
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate(true);
        $request->session()->regenerateToken();

        return redirect()->intended($this->dashboardPath());
    }

    /**
     * Handle Individual Admin logout and invalidate the current browser session.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();
        Auth::guard('super_admin')->logout();
        Auth::guard('web')->logout();

        if ($request->hasSession()) {
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
