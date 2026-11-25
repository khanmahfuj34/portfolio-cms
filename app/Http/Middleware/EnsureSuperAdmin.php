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
        $user = $request->user('super_admin');

        if ($user instanceof User && ! $user->isActive()) {
            Auth::guard('super_admin')->logout();
            if ($request->hasSession()) {
                if (Auth::guard('admin')->check()) {
                    $request->session()->regenerate(true);
                    $request->session()->regenerateToken();
                } else {
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                }
            }

            abort(403, 'Unauthorized access.');
        }

        if (! $user instanceof User || ! $user->isSuperAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}
