<?php

namespace App\Services;

use App\Models\Portfolio;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

class PortfolioService
{
    /**
     * Atomically create a Portfolio, its associated Profile, and all submitted Navbar items.
     *
     * @param User $admin Authenticated Super Admin creator
     * @param array $portfolioData Core portfolio attributes
     * @param array $profileData Owner profile attributes
     * @param array $navItemsData Submitted navigation links
     * @return Portfolio
     * @throws InvalidArgumentException|Throwable
     */
    public function create(
        User $admin,
        array $portfolioData,
        array $profileData,
        array $navItemsData,
        array $skillsData = [],
        array $projectsData = []
    ): Portfolio {
        // 1. Defensive Invariant Validation for Navbar Items
        $preparedNavItems = $this->validateAndPrepareNavItems($navItemsData);

        // 2. Enforce Portfolio Business Constraints
        // Always derive creator from authenticated Super Admin
        $portfolioData['user_id'] = $admin->id;
        // Business rule: Initial creation always forces draft status and null published_at
        $portfolioData['status'] = 'draft';
        $portfolioData['published_at'] = null;

        // 3. Atomic Multi-Table Transaction
        return DB::transaction(function () use (
            $admin,
            $portfolioData,
            $profileData,
            $preparedNavItems,
            $skillsData,
            $projectsData
        ) {
            // A. Create Portfolio belonging to authenticated Super Admin
            $portfolio = $admin->portfolios()->create($portfolioData);

            // B. Create Owner Profile linked via portfolio_id
            $portfolio->profile()->create($profileData);

            // C. Batch-create all validated Navbar items
            $portfolio->navItems()->createMany($preparedNavItems);

            // D. Batch-create validated Skills
            if (!empty($skillsData)) {
                $preparedSkills = $this->prepareSkills($skillsData);
                if (!empty($preparedSkills)) {
                    $portfolio->skills()->createMany($preparedSkills);
                }
            }

            // E. Create validated Projects
            if (!empty($projectsData)) {
                foreach ($projectsData as $projectItem) {
                    if (is_array($projectItem) && !empty($projectItem['title'])) {
                        $technologies = $this->normalizeTechnologies($projectItem['technologies'] ?? []);
                        $portfolio->projects()->create([
                            'title' => trim((string) $projectItem['title']),
                            'description' => $projectItem['description'] ?? null,
                            'thumbnail' => $projectItem['thumbnail'] ?? null,
                            'live_url' => $projectItem['live_url'] ?? null,
                            'github_url' => $projectItem['github_url'] ?? null,
                            'technologies' => $technologies,
                            'is_featured' => !empty($projectItem['is_featured']),
                            'is_visible' => isset($projectItem['is_visible']) ? (bool) $projectItem['is_visible'] : true,
                            'sort_order' => (int) ($projectItem['sort_order'] ?? 0),
                        ]);
                    }
                }
            }

            return $portfolio;
        });
    }

    /**
     * Defensively validate and prepare navigation items for persistence.
     *
     * @param array $navItemsData
     * @return array
     * @throws InvalidArgumentException
     */
    protected function validateAndPrepareNavItems(array $navItemsData): array
    {
        if (empty($navItemsData)) {
            throw new InvalidArgumentException('Navbar items array cannot be empty.');
        }

        $items = array_values($navItemsData);
        $totalItems = count($items);

        if ($totalItems > 10) {
            throw new InvalidArgumentException('Navbar items cannot exceed 10 items.');
        }

        $homeCount = 0;
        $sortOrders = [];
        $prepared = [];

        foreach ($items as $index => $item) {
            if (! is_array($item)) {
                throw new InvalidArgumentException("Navbar item at index {$index} must be an array.");
            }

            $label = trim((string) ($item['label'] ?? ''));
            if ($label === '') {
                throw new InvalidArgumentException("Navbar item at index {$index} must have a label.");
            }

            $sortOrder = (int) ($item['sort_order'] ?? 0);
            if ($sortOrder < 1 || $sortOrder > 10) {
                throw new InvalidArgumentException("Navbar item '{$label}' has invalid sort order {$sortOrder}.");
            }
            $sortOrders[] = $sortOrder;

            // Destination extraction and normalization
            $sectionKey = filled($item['section_key'] ?? null) ? trim((string) $item['section_key']) : null;
            $url = filled($item['url'] ?? null) ? trim((string) $item['url']) : null;

            // Enforce mutual exclusivity: exactly one destination must be present
            if (($sectionKey === null && $url === null) || ($sectionKey !== null && $url !== null)) {
                throw new InvalidArgumentException("Navbar item '{$label}' must provide exactly one destination.");
            }

            // Home Item Integrity
            $isHomeLabel = strcasecmp($label, 'Home') === 0;
            $isHomeSection = ($sectionKey === 'home');

            if ($isHomeLabel || $isHomeSection) {
                $homeCount++;

                if (! $isHomeLabel) {
                    throw new InvalidArgumentException("The Home navbar item must have label 'Home'.");
                }

                if ($url !== null) {
                    throw new InvalidArgumentException('The Home navbar item cannot use an external URL destination.');
                }

                if ($sectionKey !== 'home') {
                    throw new InvalidArgumentException("The Home navbar item must use section key 'home'.");
                }

                if ($sortOrder !== 1) {
                    throw new InvalidArgumentException('The Home navbar item must have sort order 1.');
                }
            }

            // Prepare record (UI-only 'type' field is discarded)
            $prepared[] = [
                'label' => $label,
                'section_key' => $sectionKey,
                'url' => $url,
                'sort_order' => $sortOrder,
                'is_visible' => filter_var($item['is_visible'] ?? true, FILTER_VALIDATE_BOOLEAN),
            ];
        }

        // Exactly one Home item invariant
        if ($homeCount !== 1) {
            throw new InvalidArgumentException("Exactly one Home navbar item is required. Found: {$homeCount}.");
        }

        // Sort orders must be unique
        if (count(array_unique($sortOrders)) !== $totalItems) {
            throw new InvalidArgumentException('Navbar item sort orders must be unique.');
        }

        return $prepared;
    }

    /**
     * Publish the given portfolio.
     *
     * @param Portfolio $portfolio
     * @return Portfolio
     * @throws InvalidArgumentException|Throwable
     */
    public function publish(Portfolio $portfolio): Portfolio
    {
        if ($portfolio->status === 'published') {
            throw new InvalidArgumentException("Portfolio '{$portfolio->name}' is already published.");
        }

        return DB::transaction(function () use ($portfolio) {
            $portfolio->update([
                'status' => 'published',
                'published_at' => now(),
            ]);

            return $portfolio;
        });
    }

    /**
     * Unpublish the given portfolio and move it back to draft.
     *
     * @param Portfolio $portfolio
     * @return Portfolio
     * @throws InvalidArgumentException|Throwable
     */
    public function unpublish(Portfolio $portfolio): Portfolio
    {
        if ($portfolio->status === 'draft') {
            throw new InvalidArgumentException("Portfolio '{$portfolio->name}' is already a draft.");
        }

        return DB::transaction(function () use ($portfolio) {
            $portfolio->update([
                'status' => 'draft',
                'published_at' => null,
            ]);

            return $portfolio;
        });
    }

    /**
     * Toggle publication status between published and draft.
     *
     * @param Portfolio $portfolio
     * @return Portfolio
     * @throws InvalidArgumentException|Throwable
     */
    public function togglePublish(Portfolio $portfolio): Portfolio
    {
        return $portfolio->status === 'published'
            ? $this->unpublish($portfolio)
            : $this->publish($portfolio);
    }

    /**
     * Prepare skills list for mass creation, normalizing and deduplicating names.
     */
    protected function prepareSkills(array $skillsData): array
    {
        $prepared = [];
        $seen = [];
        foreach ($skillsData as $skill) {
            $name = is_array($skill) ? trim((string) ($skill['name'] ?? '')) : trim((string) $skill);
            $lower = strtolower($name);
            if ($name !== '' && !in_array($lower, $seen, true)) {
                $seen[] = $lower;
                $prepared[] = ['name' => $name];
            }
        }
        return $prepared;
    }

    /**
     * Normalize project technologies array.
     */
    protected function normalizeTechnologies(array $technologies): array
    {
        $normalized = array_map(
            fn ($technology) => trim((string) $technology),
            $technologies
        );

        $normalized = array_filter(
            $normalized,
            fn ($technology) => $technology !== ''
        );

        return array_values(array_unique($normalized));
    }
}
