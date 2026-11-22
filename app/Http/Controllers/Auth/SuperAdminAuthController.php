<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SuperAdminLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SuperAdminAuthController extends Controller
{
    /**
     * Display the Super Admin login page.
     */
    public function showLoginForm()
    {
        if (Auth::check() && Auth::user()->role === 'super_admin' && Auth::user()->is_active) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle Super Admin login.
     */
    public function login(
        SuperAdminLoginRequest $request
    ): RedirectResponse {

        $credentials = $request->validated();

        $credentials['role'] = 'super_admin';
        $credentials['is_active'] = true;

        if (! Auth::attempt($credentials)) {
            return back()
                ->withErrors([
                    'email' => 'Invalid email or password.',
                ])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(
            route('admin.dashboard')
        );
    }

    /**
     * Handle Super Admin logout.
     */
    public function logout(): RedirectResponse
    {
        Auth::logout();

        request()->session()->invalidate();

        request()->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
