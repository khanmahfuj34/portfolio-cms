<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (RejectDualAuthSessions::hasPersistedDualGuards($request)) {
            return RejectDualAuthSessions::failClosed($request);
        }

        $user = $request->user('super_admin');

        if ($user instanceof User && ! $user->isActive()) {
            Auth::guard('super_admin')->logout();
            Auth::guard('admin')->logout();
            Auth::guard('web')->logout();

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            abort(403, 'Unauthorized access.');
        }

        if (! $user instanceof User || ! $user->isSuperAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        $response = $next($request);
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, private');
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }
}
