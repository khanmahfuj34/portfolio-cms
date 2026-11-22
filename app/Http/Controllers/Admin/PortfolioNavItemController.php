<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePortfolioNavItemRequest;
use App\Http\Requests\UpdatePortfolioNavItemRequest;
use App\Models\Portfolio;
use App\Services\PortfolioNavItemService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortfolioNavItemController extends Controller
{
    public function __construct(
        private readonly PortfolioNavItemService $navItemService
    ) {}

    /**
     * Display navigation items.
     */
    public function index(Portfolio $portfolio): View
    {
        $navItems = $this->navItemService->getAll($portfolio);

        return view('admin.portfolios.nav-items.index', compact(
            'portfolio',
            'navItems'
        ));
    }

    /**
     * Store a navigation item.
     */
    public function store(
        StorePortfolioNavItemRequest $request,
        Portfolio $portfolio
    ): RedirectResponse {
        $this->navItemService->create(
            $portfolio,
            $request->validated()
        );

        return back()->with(
            'success',
            'Navigation link created successfully.'
        );
    }

    /**
     * Update a navigation item.
     */
    public function update(
        UpdatePortfolioNavItemRequest $request,
        Portfolio $portfolio,
        int $navItem
    ): RedirectResponse {
        $this->navItemService->update(
            $portfolio,
            $navItem,
            $request->validated()
        );

        return back()->with(
            'success',
            'Navigation link updated successfully.'
        );
    }

    /**
     * Delete a navigation item.
     */
    public function destroy(
        Portfolio $portfolio,
        int $navItem
    ): RedirectResponse {
        $this->navItemService->delete($portfolio, $navItem);

        return back()->with(
            'success',
            'Navigation link deleted successfully.'
        );
    }

    /**
     * Reorder navigation items.
     */
    public function reorder(
        Request $request,
        Portfolio $portfolio
    ): RedirectResponse {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'integer', 'distinct'],
        ]);

        $this->navItemService->reorder(
            $portfolio,
            $validated['items']
        );

        return back()->with(
            'success',
            'Navigation order updated successfully.'
        );
    }

    /**
     * Toggle navigation visibility.
     */
    public function toggleVisibility(
        Portfolio $portfolio,
        int $navItem
    ): RedirectResponse {
        $this->navItemService->toggleVisibility(
            $portfolio,
            $navItem
        );

        return back()->with(
            'success',
            'Navigation visibility updated successfully.'
        );
    }
}
