<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePortfolioRequest;
use App\Models\Portfolio;
use App\Models\PortfolioProfile;
use App\Services\PortfolioService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    public function __construct(
        private readonly PortfolioService $portfolioService
    ) {}

    /**
     * Display a listing of client portfolio profiles.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status');

        $portfoliosQuery = Portfolio::with(['profile', 'user', 'primaryDomain'])
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
            'total' => Portfolio::count(),
            'published' => Portfolio::where('status', 'published')->count(),
            'draft' => Portfolio::where('status', 'draft')->count(),
            'profiles' => PortfolioProfile::count(),
        ];

        return view('admin.portfolios.index', compact('portfolios', 'stats', 'search', 'status'));
    }

    /**
     * Show the form for creating a new portfolio.
     */
    public function create(): View
    {
        return view('admin.portfolios.create');
    }

    /**
     * Show the form for editing an existing portfolio.
     */
    public function edit(Portfolio $portfolio): View
    {
        Gate::authorize('update', $portfolio);

        $portfolio->load(['profile', 'navItems', 'skills', 'projects', 'primaryDomain']);

        return view('admin.portfolios.edit', compact('portfolio'));
    }

    /**
     * Store a newly created portfolio in storage.
     */
    public function store(
        StorePortfolioRequest $request
    ): RedirectResponse {
        $validated = $request->validated();

        // Separate Portfolio Information
        $portfolioData = [
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'] ?? 'draft',
        ];

        // Separate Profile Information (profile photo deferred for Cloudinary integration)
        $profileData = [
            'full_name' => $validated['full_name'],
            'professional_title' => $validated['professional_title'] ?? null,
            'email' => $validated['email'] ?? null,
            'location' => $validated['location'] ?? null,
            'bio' => $validated['bio'] ?? null,
            'profile_photo_path' => null,
        ];

        // Normalized Navbar Items
        $navItemsData = $validated['nav_items'];

        // Skills & Projects Data
        $skillsData = $validated['skills'] ?? [];
        $projectsData = $validated['projects'] ?? [];

        $this->portfolioService->create(
            $request->user(),
            $portfolioData,
            $profileData,
            $navItemsData,
            $skillsData,
            $projectsData
        );

        return redirect()
            ->route('admin.profiles.index')
            ->with('success', 'Portfolio created successfully.');
    }

    /**
     * Publish a portfolio.
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
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Unpublish a portfolio and return to draft.
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
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Toggle the publication status of a portfolio.
     */
    public function togglePublish(Portfolio $portfolio): RedirectResponse
    {
        $ability = $portfolio->status === 'published' ? 'unpublish' : 'publish';
        Gate::authorize($ability, $portfolio);

        try {
            $updated = $this->portfolioService->togglePublish($portfolio);
            $statusLabel = ucfirst($updated->status);

            return back()->with(
                'success',
                "Portfolio '{$portfolio->name}' is now {$statusLabel}."
            );
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
