<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\ThemeStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreThemeRequest;
use App\Http\Requests\SuperAdmin\UpdateThemeRequest;
use App\Models\Portfolio;
use App\Models\PortfolioNavItem;
use App\Models\PortfolioProfile;
use App\Models\PortfolioProject;
use App\Models\PortfolioSkill;
use App\Models\Theme;
use App\Models\User;
use App\Services\PortfolioThemeResolver;
use App\Services\ThemePublishingService;
use App\Services\ThemeService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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
        private readonly ThemePublishingService $publishingService,
        private readonly PortfolioThemeResolver $themeResolver
    ) {}

    /**
     * Display paginated theme catalog page or JSON list with optional filters.
     */
    public function index(Request $request): View|JsonResponse
    {
        Gate::forUser($this->superAdmin($request))->authorize('viewAny', Theme::class);

        $filters = $request->only(['status', 'category', 'is_featured', 'featured', 'search', 'per_page']);
        $themes = $this->themeService->getAllThemes($filters);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => collect($themes->items())->map(fn (Theme $theme): array => $this->formatTheme($theme))->values(),
                'meta' => [
                    'current_page' => $themes->currentPage(),
                    'last_page' => $themes->lastPage(),
                    'per_page' => $themes->perPage(),
                    'total' => $themes->total(),
                ],
                'filters' => $filters,
            ]);
        }

        $statusCounts = Theme::query()
            ->toBase()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $stats = [
            'total' => (int) $statusCounts->sum(),
            'published' => (int) ($statusCounts[ThemeStatus::Published->value] ?? 0),
            'draft' => (int) ($statusCounts[ThemeStatus::Draft->value] ?? 0),
            'archived' => (int) ($statusCounts[ThemeStatus::Archived->value] ?? 0),
        ];

        $categories = Theme::query()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('admin.themes.index', [
            'themes' => $themes,
            'filters' => $filters,
            'stats' => $stats,
            'categories' => $categories,
        ]);
    }

    /**
     * Render a safe theme preview using a neutral in-memory fixture portfolio.
     */
    public function preview(Request $request, Theme $theme): Response
    {
        Gate::forUser($this->superAdmin($request))->authorize('view', $theme);

        if (! $this->themeResolver->isRegisteredKey((string) $theme->theme_key)) {
            return $this->previewUnavailableResponse($theme);
        }

        $previewPortfolio = $this->buildNeutralPreviewPortfolio($theme);
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
     * Store a newly created draft theme.
     */
    public function store(StoreThemeRequest $request): RedirectResponse|JsonResponse
    {
        $creator = $this->superAdmin($request);
        $theme = $this->themeService->create($request->validated(), $creator);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Theme created successfully.',
                'data' => $this->formatTheme($theme->loadMissing('creator')),
            ], 201);
        }

        return redirect()
            ->route('admin.themes.index')
            ->with('success', 'Theme created successfully.');
    }

    /**
     * Display details of a specific theme without exposing sensitive data.
     */
    public function show(Request $request, Theme $theme): JsonResponse
    {
        Gate::forUser($this->superAdmin($request))->authorize('view', $theme);

        $theme->loadMissing('creator')->loadCount('portfolios');

        return response()->json([
            'success' => true,
            'data' => $this->formatTheme($theme),
        ]);
    }

    /**
     * Update editable metadata of an existing theme.
     */
    public function update(UpdateThemeRequest $request, Theme $theme): RedirectResponse|JsonResponse
    {
        $actor = $this->superAdmin($request);
        $updated = $this->themeService->update($theme, $request->validated(), $actor);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Theme updated successfully.',
                'data' => $this->formatTheme($updated->loadMissing('creator')),
            ]);
        }

        return redirect()
            ->route('admin.themes.index')
            ->with('success', 'Theme updated successfully.');
    }

    /**
     * Delete an unused theme while gracefully handling in-use restrictions.
     */
    public function destroy(Request $request, Theme $theme): RedirectResponse|JsonResponse
    {
        $actor = $this->superAdmin($request);

        try {
            Gate::forUser($actor)->authorize('delete', $theme);
            $this->themeService->delete($theme, $actor);
        } catch (AuthorizationException|InvalidArgumentException $exception) {
            if (! $actor->isSuperAdmin()) {
                throw $exception;
            }

            $message = 'Cannot delete a theme while one or more portfolios reference it.';

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            return redirect()
                ->route('admin.themes.index')
                ->with('error', $message);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Theme deleted successfully.',
            ]);
        }

        return redirect()
            ->route('admin.themes.index')
            ->with('success', 'Theme deleted successfully.');
    }

    /**
     * Publish a draft or previously unpublished theme.
     */
    public function publish(Request $request, Theme $theme): RedirectResponse|JsonResponse
    {
        return $this->runLifecycleTransition(
            $request,
            $theme,
            fn (Theme $target, User $actor): Theme => $this->publishingService->publish($target, $actor),
            'Theme published successfully.'
        );
    }

    /**
     * Unpublish a published theme back to draft while preserving published_at history.
     */
    public function unpublish(Request $request, Theme $theme): RedirectResponse|JsonResponse
    {
        return $this->runLifecycleTransition(
            $request,
            $theme,
            fn (Theme $target, User $actor): Theme => $this->publishingService->unpublish($target, $actor),
            'Theme unpublished and moved to draft.'
        );
    }

    /**
     * Archive a theme so it can no longer be newly applied while keeping existing portfolios intact.
     */
    public function archive(Request $request, Theme $theme): RedirectResponse|JsonResponse
    {
        return $this->runLifecycleTransition(
            $request,
            $theme,
            fn (Theme $target, User $actor): Theme => $this->publishingService->archive($target, $actor),
            'Theme archived successfully.'
        );
    }

    /**
     * Restore an archived theme back to draft (requiring explicit republishing afterward).
     */
    public function restore(Request $request, Theme $theme): RedirectResponse|JsonResponse
    {
        return $this->runLifecycleTransition(
            $request,
            $theme,
            fn (Theme $target, User $actor): Theme => $this->publishingService->restore($target, $actor),
            'Theme restored to draft.'
        );
    }

    private function runLifecycleTransition(
        Request $request,
        Theme $theme,
        callable $transition,
        string $successMessage
    ): RedirectResponse|JsonResponse {
        $actor = $this->superAdmin($request);

        try {
            $updated = $transition($theme, $actor);
        } catch (InvalidArgumentException $exception) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $exception->getMessage(),
                ], 422);
            }

            return redirect()
                ->route('admin.themes.index')
                ->with('error', $exception->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $successMessage,
                'data' => $this->formatTheme($updated->loadMissing('creator')),
            ]);
        }

        return redirect()
            ->route('admin.themes.index')
            ->with('success', $successMessage);
    }

    private function superAdmin(Request $request): User
    {
        $user = $request->user('super_admin');

        if (! $user instanceof User) {
            abort(403, 'Unauthorized access.');
        }

        return $user;
    }

    /**
     * Format safe public/management metadata for a Theme resource.
     *
     * @return array<string, mixed>
     */
    private function formatTheme(Theme $theme): array
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
            'status' => $theme->status?->value,
            'status_label' => $theme->status?->label(),
            'is_featured' => (bool) $theme->is_featured,
            'published_at' => $theme->published_at?->toIso8601String(),
            'portfolios_count' => $theme->portfolios_count ?? null,
            'creator' => $theme->relationLoaded('creator') && $theme->creator
                ? [
                    'id' => $theme->creator->id,
                    'name' => $theme->creator->name,
                ]
                : null,
            'created_at' => $theme->created_at?->toIso8601String(),
            'updated_at' => $theme->updated_at?->toIso8601String(),
        ];
    }

    private function previewUnavailableResponse(Theme $theme): Response
    {
        return response()
            ->view('admin.themes.preview-unavailable', [
                'theme' => $theme,
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, private')
            ->header('Referrer-Policy', 'no-referrer')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    /**
     * Build a non-persisted neutral sample portfolio fixture so theme previews
     * never read or expose real users' private portfolio records.
     */
    private function buildNeutralPreviewPortfolio(Theme $theme): Portfolio
    {
        $portfolio = new Portfolio([
            'name' => $theme->name . ' Preview',
            'slug' => 'demo',
            'description' => 'Neutral structural preview demonstrating typography scales, color contrast, and section spacing for this theme.',
            'status' => 'draft',
        ]);
        $portfolio->setRelation('theme', $theme);

        $profile = new PortfolioProfile([
            'full_name' => 'Portfolio Owner',
            'professional_title' => $theme->category ? $theme->category . ' Showcase' : 'Theme Layout Preview',
            'bio' => 'This non-personal sample layout demonstrates how headings, bio copy, skill chips, and project cards render within this theme.',
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
                'description' => 'Representative featured project card illustrating title hierarchy, tag pills, and balanced card spacing.',
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
