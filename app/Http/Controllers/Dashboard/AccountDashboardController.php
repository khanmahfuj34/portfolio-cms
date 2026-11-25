<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\UpdateAccountPasswordRequest;
use App\Http\Requests\Dashboard\UpdateAccountProfileRequest;
use App\Http\Requests\StorePortfolioRequest;
use App\Http\Requests\UpdatePortfolioRequest;
use App\Models\Portfolio;
use App\Services\PortfolioPreviewService;
use App\Services\PortfolioService;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use InvalidArgumentException;

class AccountDashboardController extends Controller
{
    public function __construct(
        private readonly PortfolioService $portfolioService,
        private readonly PortfolioPreviewService $previewService,
        private readonly UserService $userService
    ) {}

    /**
     * Display the Individual Admin account management page.
     */
    public function editAccount(Request $request): View
    {
        return view('dashboard.account', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the authenticated Individual Admin's profile details.
     */
    public function updateAccount(UpdateAccountProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $originalEmail = $user->email;

        $updated = $this->userService->updateOwnProfile(
            $user,
            $request->safe()->only(['name', 'email'])
        );

        if ($updated->email !== $originalEmail) {
            $request->session()->regenerate();
        }

        return redirect()
            ->route('dashboard.account.edit')
            ->with('success', 'Account profile updated successfully.');
    }

    /**
     * Update the authenticated Individual Admin's password and rotate session security state.
     */
    public function updatePassword(UpdateAccountPasswordRequest $request): RedirectResponse
    {
        $this->userService->updateOwnPassword(
            $request->user(),
            $request->validated('password')
        );

        $request->session()->regenerate();
        $request->session()->regenerateToken();
        $this->userService->revokeOtherSessions($request->user(), $request->session()->getId());

        return redirect()
            ->route('dashboard.account.edit')
            ->with('success', 'Password updated successfully.');
    }

    /**
     * Display the Individual Admin dashboard scoped strictly to their own portfolios.
     */
    public function index(Request $request): View
    {
        $data = $this->loadOwnerPortfoliosData($request);

        return view('dashboard.index', $data);
    }

    /**
     * Display the Individual Admin's own portfolios listing.
     */
    public function portfolios(Request $request): View
    {
        $data = $this->loadOwnerPortfoliosData($request);

        return view('dashboard.portfolios.index', $data);
    }

    /**
     * Show the create portfolio form for the authenticated Individual Admin.
     */
    public function create(): View
    {
        Gate::authorize('create', Portfolio::class);

        return view('dashboard.portfolios.create');
    }

    /**
     * Create a new portfolio owned by the authenticated Individual Admin.
     */
    public function store(StorePortfolioRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $portfolioData = [
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
            'status' => 'draft',
        ];

        $profileData = [
            'full_name' => $validated['full_name'],
            'professional_title' => $validated['professional_title'] ?? null,
            'email' => $validated['email'] ?? null,
            'location' => $validated['location'] ?? null,
            'bio' => $validated['bio'] ?? null,
            'profile_photo_path' => null,
        ];

        $this->portfolioService->create(
            $request->user(),
            $portfolioData,
            $profileData,
            $validated['nav_items'],
            $validated['skills'] ?? [],
            $validated['projects'] ?? []
        );

        return redirect()
            ->route('dashboard.portfolios.index')
            ->with('success', 'Portfolio created successfully.');
    }

    /**
     * Show the edit form for an owned portfolio.
     */
    public function edit(Portfolio $portfolio): View
    {
        Gate::authorize('update', $portfolio);

        $portfolio->load(['profile', 'navItems', 'skills', 'projects', 'primaryDomain']);

        return view('dashboard.portfolios.edit', compact('portfolio'));
    }

    /**
     * Update core portfolio and profile information for an owned portfolio.
     */
    public function update(UpdatePortfolioRequest $request, Portfolio $portfolio): RedirectResponse
    {
        Gate::authorize('update', $portfolio);

        $validated = $request->validated();

        $portfolioData = [
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
        ];

        $profileData = [
            'full_name' => $validated['full_name'],
            'professional_title' => $validated['professional_title'] ?? null,
            'email' => $validated['email'] ?? null,
            'location' => $validated['location'] ?? null,
            'bio' => $validated['bio'] ?? null,
        ];

        try {
            $this->portfolioService->update($portfolio, $portfolioData, $profileData);

            return redirect()
                ->route('dashboard.portfolios.edit', $portfolio)
                ->with('success', 'Portfolio details updated successfully.');
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['slug' => $e->getMessage()]);
        }
    }

    /**
     * Delete an owned portfolio.
     */
    public function destroy(Portfolio $portfolio): RedirectResponse
    {
        Gate::authorize('delete', $portfolio);

        $portfolioName = $portfolio->name;
        $this->portfolioService->delete($portfolio);

        return redirect()
            ->route('dashboard.portfolios.index')
            ->with('success', "Portfolio '{$portfolioName}' was deleted successfully.");
    }

    /**
     * Redirect the owning Individual Admin to a short-lived signed draft preview URL.
     */
    public function preview(Request $request, Portfolio $portfolio): RedirectResponse
    {
        Gate::authorize('view', $portfolio);

        if ($portfolio->status === 'published') {
            $portfolio->loadMissing('primaryDomain');
            $publicUrl = $portfolio->primaryDomain
                ? app(\App\Services\PortfolioDomainService::class)->publicUrlFor($portfolio->primaryDomain, $request)
                : url('/' . $portfolio->slug);

            return redirect()->away($publicUrl);
        }

        $previewUrl = $this->previewService->generatePreviewUrl(
            $portfolio,
            $request->user(),
            $request
        );

        return redirect()->away($previewUrl);
    }

    /**
     * Revoke all active preview links for an owned portfolio.
     */
    public function revokePreview(Portfolio $portfolio): RedirectResponse
    {
        Gate::authorize('update', $portfolio);

        $this->previewService->revokeAllForPortfolio($portfolio);

        return back()->with('success', 'All previous draft preview links for this portfolio have been revoked.');
    }

    /**
     * Publish an owned portfolio.
     */
    public function publish(Portfolio $portfolio): RedirectResponse
    {
        Gate::authorize('publish', $portfolio);

        try {
            $this->portfolioService->publish($portfolio);

            return back()->with(
                'success',
                "Portfolio '{$portfolio->name}' was published successfully and is now live."
            );
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Unpublish an owned portfolio and move it back to draft.
     */
    public function unpublish(Portfolio $portfolio): RedirectResponse
    {
        Gate::authorize('unpublish', $portfolio);

        try {
            $this->portfolioService->unpublish($portfolio);

            return back()->with(
                'success',
                "Portfolio '{$portfolio->name}' was moved to Draft and is no longer public."
            );
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Retrieve paginated portfolios and summary metrics strictly scoped to the authenticated owner.
     *
     * @return array<string, mixed>
     */
    private function loadOwnerPortfoliosData(Request $request): array
    {
        $user = $request->user();
        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status');

        $baseQuery = $user->portfolios();

        $portfoliosQuery = (clone $baseQuery)
            ->with(['profile', 'primaryDomain'])
            ->withCount('navItems');

        if ($search !== '') {
            $portfoliosQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhereHas('profile', function ($pq) use ($search) {
                        $pq->where('full_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('professional_title', 'like', "%{$search}%");
                    });
            });
        }

        if ($status && in_array($status, ['published', 'draft'], true)) {
            $portfoliosQuery->where('status', $status);
        }

        $portfolios = $portfoliosQuery
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'published' => (clone $baseQuery)->where('status', 'published')->count(),
            'draft' => (clone $baseQuery)->where('status', 'draft')->count(),
            'domains' => (clone $baseQuery)->whereHas('primaryDomain')->count(),
        ];

        return compact('portfolios', 'stats', 'search', 'status');
    }
}
