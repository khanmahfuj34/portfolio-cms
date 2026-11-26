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
        if (RejectDualAuthSessions::hasPersistedDualGuards($request)) {
            return RejectDualAuthSessions::failClosed($request);
        }

        $user = $request->user('admin');

        if (! $user instanceof User) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->guest(Route::has('login') ? route('login') : '/login');
        }

        if (! $user->isActive()) {
            Auth::guard('admin')->logout();
            Auth::guard('super_admin')->logout();
            Auth::guard('web')->logout();

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
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

        $response = $next($request);
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, private');
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }
}
