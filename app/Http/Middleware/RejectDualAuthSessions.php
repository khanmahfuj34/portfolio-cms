<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\SessionGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RejectDualAuthSessions
{
    public const RECOVERY_MESSAGE = 'Your session was reset for security reasons. Please sign in again.';

    /**
     * Centrally fail closed whenever a browser session holds both privileged guards.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->hasPersistedDualGuards($request)) {
            return self::failClosed($request);
        }

        return $next($request);
    }

    public static function hasPersistedDualGuards(Request $request): bool
    {
        return self::hasPersistedGuard($request, 'super_admin')
            && self::hasPersistedGuard($request, 'admin');
    }

    public static function failClosed(Request $request): Response
    {
        Auth::guard('super_admin')->logout();
        Auth::guard('admin')->logout();
        Auth::guard('web')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => self::RECOVERY_MESSAGE], 403);
        }

        return redirect('/')
            ->with('status', self::RECOVERY_MESSAGE)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, private')
            ->header('Pragma', 'no-cache');
    }

    private static function hasPersistedGuard(Request $request, string $guardName): bool
    {
        $guard = Auth::guard($guardName);

        if (! $guard instanceof SessionGuard) {
            return false;
        }

        if ($request->hasSession() && $request->session()->has($guard->getName())) {
            return true;
        }

        return $request->cookies->has($guard->getRecallerName());
    }
}
