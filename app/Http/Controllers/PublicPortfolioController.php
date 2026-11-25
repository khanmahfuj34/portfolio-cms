<?php

namespace App\Http\Controllers;

use App\Models\Portfolio;
use App\Services\PortfolioDomainService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PublicPortfolioController extends Controller
{
    public function __construct(
        private readonly PortfolioDomainService $domainService
    ) {}

    /**
     * Reserved slugs that must not be handled by the legacy slug fallback route.
     *
     * @var array<int, string>
     */
    private const RESERVED_SLUGS = [
        'admin',
        'api',
        'login',
        'logout',
        'dashboard',
        'profiles',
        'portfolios',
        'build',
        'assets',
        'storage',
    ];

    /**
     * Display the portfolio matching the incoming request hostname (subdomain or custom domain).
     */
    public function showByHost(Request $request): View
    {
        $domainRecord = $this->domainService->resolveVerifiedDomain($request->getHost());
        if (! $domainRecord) {
            abort(404);
        }

        $isSuperAdmin = $this->isActiveSuperAdmin();

        $portfolio = $this->basePublicQuery($isSuperAdmin)
            ->whereKey($domainRecord->portfolio_id)
            ->firstOrFail();

        return $this->renderPortfolioView($portfolio, $isSuperAdmin);
    }

    /**
     * Legacy slug-based portfolio route retained for backward compatibility on base domain.
     */
    public function show(string $slug): View
    {
        if (in_array(strtolower($slug), self::RESERVED_SLUGS, true)) {
            abort(404);
        }

        $isSuperAdmin = $this->isActiveSuperAdmin();

        $portfolio = $this->basePublicQuery($isSuperAdmin)
            ->where('slug', $slug)
            ->firstOrFail();

        return $this->renderPortfolioView($portfolio, $isSuperAdmin);
    }

    /**
     * Build the shared portfolio query with eager-loaded public sections and visibility rules.
     */
    private function basePublicQuery(bool $isSuperAdmin): Builder
    {
        $query = Portfolio::query()
            ->with([
                'profile',
                'navItems' => fn ($q) => $q->where('is_visible', true)->orderBy('sort_order', 'asc'),
                'skills'   => fn ($q) => $q->orderBy('id', 'asc'),
                'projects' => fn ($q) => $q
                    ->where('is_visible', true)
                    ->orderBy('sort_order', 'asc')
                    ->orderBy('id', 'asc'),
            ]);

        // Public visitors can only view published portfolios
        if (! $isSuperAdmin) {
            $query->where('status', 'published')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now());
        }

        return $query;
    }

    /**
     * Render the shared public portfolio Blade view.
     */
    private function renderPortfolioView(Portfolio $portfolio, bool $isSuperAdmin): View
    {
        $isPreview = $isSuperAdmin && (
            $portfolio->status !== 'published' ||
            $portfolio->published_at === null ||
            $portfolio->published_at > now()
        );

        return view('portfolio.show', [
            'portfolio' => $portfolio,
            'profile'   => $portfolio->profile,
            'navItems'  => $portfolio->navItems,
            'skills'    => $portfolio->skills,
            'projects'  => $portfolio->projects,
            'isPreview' => $isPreview,
        ]);
    }

    private function isActiveSuperAdmin(): bool
    {
        return Auth::check()
            && Auth::user()->role === 'super_admin'
            && (bool) Auth::user()->is_active;
    }
}
