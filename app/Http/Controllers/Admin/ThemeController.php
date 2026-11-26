<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ApplyThemeRequest;
use App\Models\Portfolio;
use App\Models\PortfolioNavItem;
use App\Models\PortfolioProfile;
use App\Models\PortfolioProject;
use App\Models\PortfolioSkill;
use App\Models\Theme;
use App\Models\User;
use App\Services\PortfolioThemeResolver;
use App\Services\ThemeApplicationService;
use App\Services\ThemeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View as ViewFacade;
use InvalidArgumentException;

class ThemeController extends Controller
{
    public function __construct(
        private readonly ThemeService $themeService,
        private readonly ThemeApplicationService $themeApplicationService,
        private readonly PortfolioThemeResolver $themeResolver
    ) {}

    /**
     * Browse paginated published themes available to Individual Admins.
     */
    public function index(Request $request): View|JsonResponse
    {
        $actor = $this->individualAdmin($request);
        Gate::forUser($actor)->authorize('browsePublished', Theme::class);

        $filters = $request->only(['search', 'category', 'featured', 'per_page']);
        $themes = $this->themeService->getPublishedThemes($filters);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => collect($themes->items())
                    ->map(fn (Theme $theme): array => $this->formatPublicTheme($theme))
                    ->values(),
                'meta' => [
                    'current_page' => $themes->currentPage(),
                    'last_page' => $themes->lastPage(),
                    'per_page' => $themes->perPage(),
                    'total' => $themes->total(),
                ],
                'filters' => $filters,
            ]);
        }

        $portfolios = $actor->portfolios()
            ->with('theme:id,name,theme_key,status')
            ->latest('id')
            ->get(['id', 'user_id', 'theme_id', 'name', 'slug', 'status']);

        $requestedPortfolioId = $request->query('portfolio');
        $selectedPortfolio = is_numeric($requestedPortfolioId)
            ? $portfolios->firstWhere('id', (int) $requestedPortfolioId)
            : null;
        $selectedPortfolio ??= $portfolios->first();

        $categories = Theme::query()
            ->published()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('dashboard.themes.index', [
            'themes' => $themes,
            'filters' => $filters,
            'categories' => $categories,
            'portfolios' => $portfolios,
            'selectedPortfolio' => $selectedPortfolio,
        ]);
    }

    /**
     * Render a safe read-only preview of a published theme using either the actor's
     * own selected portfolio content (without altering persistent data) or a neutral fixture.
     */
    public function preview(Request $request, Theme $theme): Response
    {
        $actor = $this->individualAdmin($request);
        Gate::forUser($actor)->authorize('viewPublished', $theme);

        if (! $this->themeResolver->isRegisteredKey((string) $theme->theme_key)) {
            return $this->previewUnavailableResponse($theme);
        }

        $portfolioId = $request->query('portfolio');
        $previewPortfolio = null;

        if ($portfolioId !== null && $portfolioId !== '') {
            if (! ctype_digit((string) $portfolioId)) {
                abort(404);
            }

            $ownedPortfolio = Portfolio::query()
                ->with([
                    'profile',
                    'navItems' => fn ($q) => $q->where('is_visible', true)->orderBy('sort_order', 'asc'),
                    'skills' => fn ($q) => $q->orderBy('id', 'asc'),
                    'projects' => fn ($q) => $q->where('is_visible', true)->orderBy('sort_order', 'asc')->orderBy('id', 'asc'),
                ])
                ->findOrFail((int) $portfolioId);

            Gate::forUser($actor)->authorize('view', $ownedPortfolio);

            $previewPortfolio = $ownedPortfolio->replicate();
            $previewPortfolio->id = $ownedPortfolio->id;
            $previewPortfolio->setRelation('theme', $theme);
            $previewPortfolio->setRelation('profile', $ownedPortfolio->profile);
            $previewPortfolio->setRelation('navItems', $ownedPortfolio->navItems);
            $previewPortfolio->setRelation('skills', $ownedPortfolio->skills);
            $previewPortfolio->setRelation('projects', $ownedPortfolio->projects);
        } else {
            $previewPortfolio = $this->buildNeutralPreviewPortfolio($theme);
        }

        $view = $this->themeResolver->resolveView($previewPortfolio);

        if (! ViewFacade::exists($view)) {
            return $this->previewUnavailableResponse($theme);
        }

        return response()
            ->view($view, [
                'portfolio' => $previewPortfolio,
                'profile' => $previewPortfolio->profile,
                'navItems' => $previewPortfolio->navItems,
                'skills' => $previewPortfolio->skills,
                'projects' => $previewPortfolio->projects,
                'isPreview' => true,
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, private')
            ->header('Referrer-Policy', 'no-referrer')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    /**
     * View details of a single published theme.
     */
    public function show(Request $request, Theme $theme): JsonResponse
    {
        Gate::forUser($this->individualAdmin($request))->authorize('viewPublished', $theme);

        return response()->json([
            'success' => true,
            'data' => $this->formatPublicTheme($theme),
        ]);
    }

    /**
     * Apply a published theme to an Individual Admin's own portfolio.
     */
    public function apply(ApplyThemeRequest $request, Portfolio $portfolio, Theme $theme): JsonResponse
    {
        $actor = $this->individualAdmin($request);

        Gate::forUser($actor)->authorize('apply', [$theme, $portfolio]);

        try {
            $updated = $this->themeApplicationService->apply($theme, $portfolio, $actor);
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Theme applied to portfolio successfully.',
            'data' => [
                'portfolio' => [
                    'id' => $updated->id,
                    'name' => $updated->name,
                    'slug' => $updated->slug,
                    'status' => $updated->status,
                    'theme_id' => $updated->theme_id,
                ],
                'theme' => $this->formatPublicTheme($theme->fresh() ?? $theme),
            ],
        ]);
    }

    private function individualAdmin(Request $request): User
    {
        $user = $request->user('admin');

        if (! $user instanceof User) {
            abort(403, 'Unauthorized access.');
        }

        return $user;
    }

    /**
     * Serialize only safe public metadata without creator/private user details or internal paths.
     *
     * @return array<string, mixed>
     */
    private function formatPublicTheme(Theme $theme): array
    {
        return [
            'id' => $theme->id,
            'name' => $theme->name,
            'slug' => $theme->slug,
            'theme_key' => $theme->theme_key,
            'description' => $theme->description,
            'category' => $theme->category,
            'thumbnail' => $theme->thumbnail,
            'version' => $theme->version,
            'is_featured' => (bool) $theme->is_featured,
            'published_at' => $theme->published_at?->toIso8601String(),
        ];
    }

    private function previewUnavailableResponse(Theme $theme): Response
    {
        return response()
            ->view('dashboard.themes.preview-unavailable', [
                'theme' => $theme,
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, private')
            ->header('Referrer-Policy', 'no-referrer')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    /**
     * Build a non-persisted neutral sample fixture when previewing without an owned portfolio.
     */
    private function buildNeutralPreviewPortfolio(Theme $theme): Portfolio
    {
        $portfolio = new Portfolio([
            'name' => $theme->name . ' Preview',
            'slug' => 'demo',
            'description' => 'Neutral structural preview showing typography, color hierarchy, and section layout for this theme.',
            'status' => 'draft',
        ]);
        $portfolio->setRelation('theme', $theme);

        $profile = new PortfolioProfile([
            'full_name' => 'Portfolio Owner',
            'professional_title' => $theme->category ? $theme->category . ' Showcase' : 'Theme Layout Preview',
            'bio' => 'This sample layout demonstrates how headings, biography copy, skills, and project cards look when this theme is applied.',
            'email' => null,
            'location' => null,
            'profile_photo_path' => null,
        ]);

        $navItems = new Collection([
            new PortfolioNavItem(['label' => 'Home', 'section_key' => 'home', 'sort_order' => 1, 'is_visible' => true]),
            new PortfolioNavItem(['label' => 'About', 'section_key' => 'about', 'sort_order' => 2, 'is_visible' => true]),
            new PortfolioNavItem(['label' => 'Skills', 'section_key' => 'skills', 'sort_order' => 3, 'is_visible' => true]),
            new PortfolioNavItem(['label' => 'Projects', 'section_key' => 'projects', 'sort_order' => 4, 'is_visible' => true]),
        ]);

        $skills = new Collection([
            new PortfolioSkill(['name' => 'Layout Architecture']),
            new PortfolioSkill(['name' => 'Responsive Grid']),
            new PortfolioSkill(['name' => 'Design Systems']),
            new PortfolioSkill(['name' => 'Typography']),
        ]);

        $projects = new Collection([
            new PortfolioProject([
                'title' => 'Sample Showcase Module A',
                'description' => 'Representative featured card demonstrating tag pills, typography contrast, and spacing.',
                'technologies' => ['Responsive', 'Grid', 'Accessible'],
                'is_featured' => true,
                'is_visible' => true,
                'sort_order' => 1,
            ]),
            new PortfolioProject([
                'title' => 'Sample Showcase Module B',
                'description' => 'Standard card layout preview with neutral sample copy and technology labels.',
                'technologies' => ['Templates', 'UI Layout'],
                'is_featured' => false,
                'is_visible' => true,
                'sort_order' => 2,
            ]),
        ]);

        $portfolio->setRelation('profile', $profile);
        $portfolio->setRelation('navItems', $navItems);
        $portfolio->setRelation('skills', $skills);
        $portfolio->setRelation('projects', $projects);

        return $portfolio;
    }
}
