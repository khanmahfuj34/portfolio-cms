<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class EnsureIndividualAdmin
{
    /**
     * Ensure the authenticated user is an active Individual Admin.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('admin');

        if (! $user instanceof User) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->guest(Route::has('login') ? route('login') : '/login');
        }

        if (! $user->isActive()) {
            Auth::guard('admin')->logout();
            if ($request->hasSession()) {
                if (Auth::guard('super_admin')->check()) {
                    $request->session()->regenerate(true);
                    $request->session()->regenerateToken();
                } else {
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                }
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Your account is inactive.'], 403);
            }

            $loginUrl = Route::has('login') ? route('login') : '/login';

            return redirect($loginUrl)->withErrors([
                'email' => 'Your account has been deactivated.',
            ]);
        }

        if (! $user->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}
