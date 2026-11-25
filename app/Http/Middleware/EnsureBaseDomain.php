<?php

namespace App\Http\Middleware;

use App\Services\PortfolioDomainService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBaseDomain
{
    public function __construct(
        private readonly PortfolioDomainService $domainService
    ) {}

    /**
     * Allow access only on the application base domain (or local loopback aliases).
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->domainService->isBaseHost($request->getHost())) {
            abort(404);
        }

        return $next($request);
    }
}
