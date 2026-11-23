<?php

namespace App\Services;

use App\Models\Portfolio;
use App\Models\PortfolioNavItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PortfolioNavItemService
{
        
    /**
     * Get all navigation items for Admin site.
     */
    public function getAll(Portfolio $portfolio): Collection
    {
        return $portfolio->navItems;
    }
    
    /**
     * Get visible navigation items for public portfolio.
     */
    public function getPublicNavItems(Portfolio $portfolio): Collection
    {
        // Draft portfolios must not expose public navigation.
        abort_unless(
            $portfolio->status === 'published',
            404
        );

        return $portfolio->navItems()
            ->where('is_visible', true)
            ->orderBy('sort_order')
            ->get();
    }



    /**
     * Create a new navigation item.
     */
    public function create(
        Portfolio $portfolio,
        array $data
    ): PortfolioNavItem {

        if (! isset($data['sort_order'])) {
            $data['sort_order'] = (
                $portfolio->navItems()->max('sort_order') ?? 0
            ) + 1;
        }

        return $portfolio->navItems()->create($data);
    }

    /**
     * Update an existing navigation item.
     */
    public function update(
        Portfolio $portfolio,
        int $navItemId,
        array $data
    ): PortfolioNavItem {

        $navItem = $portfolio->navItems()
            ->findOrFail($navItemId);

        // Switch destination to internal section.
        if (array_key_exists('section_key', $data)
            && ! array_key_exists('url', $data)) {

            $data['url'] = null;
        }

        // Switch destination to external URL.
        if (array_key_exists('url', $data)
            && ! array_key_exists('section_key', $data)) {

            $data['section_key'] = null;
        }

        $navItem->update($data);

        return $navItem->refresh();
    }

    /**
     * Delete a navigation item.
     */
    public function delete(
        Portfolio $portfolio,
        int $navItemId
    ): bool {

        $navItem = $portfolio->navItems()
            ->findOrFail($navItemId);

        return (bool) $navItem->delete();
    }

    /**
     * Reorder all navigation items of a portfolio.
     */
    public function reorder(
        Portfolio $portfolio,
        array $itemIds
    ): void {

        DB::transaction(function () use ($portfolio, $itemIds) {

            // Lock the portfolio row to serialize reorder operations.
            $lockedPortfolio = Portfolio::query()
                ->whereKey($portfolio->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            // Retrieve and lock all existing navigation items.
            $existingIds = $lockedPortfolio->navItems()
                ->lockForUpdate()
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->sort()
                ->values()
                ->all();

            // Normalize submitted IDs.
            $submittedIds = collect($itemIds)
                ->map(fn ($id) => (int) $id)
                ->sort()
                ->values()
                ->all();

            // Ensure every existing item is submitted exactly once.
            if ($existingIds !== $submittedIds) {
                throw ValidationException::withMessages([
                    'items' => 'Invalid navigation items. Please refresh and try again.',
                ]);
            }

            // Assign consecutive sort positions.
            foreach ($itemIds as $position => $id) {
                $lockedPortfolio->navItems()
                    ->whereKey($id)
                    ->update([
                        'sort_order' => $position + 1,
                    ]);
            }
        });
    }

    /**
     * Toggle navigation item visibility.
     */
    public function toggleVisibility(
        Portfolio $portfolio,
        int $navItemId
    ): PortfolioNavItem {

        $navItem = $portfolio->navItems()
            ->findOrFail($navItemId);

        $navItem->update([
            'is_visible' => ! $navItem->is_visible,
        ]);

        return $navItem->refresh();
    }
}