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
     * Get all navigation items of a portfolio.
     */
    public function getAll(Portfolio $portfolio): Collection
    {
        return $portfolio->navItems()->get();
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
     * Reorder all navigation items.
     */
    public function reorder(
        Portfolio $portfolio,
        array $itemIds
    ): void {

        $existingIds = $portfolio->navItems()
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->sort()
            ->values()
            ->all();

        $submittedIds = collect($itemIds)
            ->map(fn ($id) => (int) $id)
            ->sort()
            ->values()
            ->all();

        // Every existing item must be included exactly once.
        if ($existingIds !== $submittedIds) {
            throw ValidationException::withMessages([
                'items' => 'Invalid navigation items submitted.',
            ]);
        }

        DB::transaction(function () use ($portfolio, $itemIds) {
            foreach ($itemIds as $position => $id) {
                $portfolio->navItems()
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
