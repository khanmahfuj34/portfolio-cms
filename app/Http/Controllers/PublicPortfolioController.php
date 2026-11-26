<?php

namespace App\Http\Controllers;

use App\Models\Portfolio;
use App\Models\User;
use App\Services\PortfolioDomainService;
use App\Services\PortfolioPreviewService;
use App\Services\PortfolioThemeResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class PublicPortfolioController extends Controller
{
    public function __construct(
        private readonly PortfolioDomainService $domainService,
        private readonly PortfolioPreviewService $previewService,
        private readonly PortfolioThemeResolver $themeResolver
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
        'register',
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
    public function showByHost(Request $request): Response
    {
        $domainRecord = $this->domainService->resolveVerifiedDomain($request->getHost());
        if (! $domainRecord) {
            abort(404);
        }

        $portfolio = $this->basePublicQuery()
            ->whereKey($domainRecord->portfolio_id)
            ->firstOrFail();

        return $this->authorizeAndRender($request, $portfolio);
    }

    /**
     * Legacy slug-based portfolio route retained for backward compatibility on base domain.
     */
    public function show(Request $request, string $slug): Response
    {
        if (in_array(strtolower($slug), self::RESERVED_SLUGS, true)) {
            abort(404);
        }

        $portfolio = $this->basePublicQuery()
            ->where('slug', $slug)
            ->firstOrFail();

        return $this->authorizeAndRender($request, $portfolio);
    }

    /**
     * Build the shared portfolio query with eager-loaded public sections.
     */
    private function basePublicQuery(): Builder
    {
        return Portfolio::query()
            ->with([
                'theme',
                'profile',
                'navItems' => fn ($q) => $q->where('is_visible', true)->orderBy('sort_order', 'asc'),
                'skills'   => fn ($q) => $q->orderBy('id', 'asc'),
                'projects' => fn ($q) => $q
                    ->where('is_visible', true)
                    ->orderBy('sort_order', 'asc')
                    ->orderBy('id', 'asc'),
            ]);
    }

    /**
     * Enforce public visibility or authorized owner/super-admin draft preview, then render view.
     */
    private function authorizeAndRender(Request $request, Portfolio $portfolio): Response
    {
        $isPublished = $portfolio->status === 'published'
            && $portfolio->published_at !== null
            && $portfolio->published_at <= now();

        if ($isPublished) {
            return $this->renderPortfolio($portfolio, isPreview: false);
        }

        if (! $this->canPreviewDraft($request, $portfolio)) {
            abort(404);
        }

        return $this->renderPortfolio($portfolio, isPreview: true)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, private')
            ->header('Referrer-Policy', 'no-referrer')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    private function renderPortfolio(Portfolio $portfolio, bool $isPreview): Response
    {
        $view = $this->themeResolver->resolveView($portfolio);

        return response()->view($view, [
            'portfolio' => $portfolio,
            'profile'   => $portfolio->profile,
            'navItems'  => $portfolio->navItems,
            'skills'    => $portfolio->skills,
            'projects'  => $portfolio->projects,
            'isPreview' => $isPreview,
        ]);
    }

    private function canPreviewDraft(Request $request, Portfolio $portfolio): bool
    {
        $superAdmin = Auth::guard('super_admin')->user();
        if ($superAdmin instanceof User && $superAdmin->isSuperAdmin()) {
            return true;
        }

        $admin = Auth::guard('admin')->user();
        if ($admin instanceof User && $admin->isAdmin() && (int) $portfolio->user_id === (int) $admin->id) {
            return true;
        }

        $previewToken = $request->query('preview_token');
        if (is_string($previewToken) && $previewToken !== '') {
            return $this->previewService->validateToken($previewToken, $portfolio);
        }

        return false;
    }
}
