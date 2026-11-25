<?php

namespace App\Services;
use App\Models\Portfolio;
use App\Models\PortfolioProject;
use Illuminate\Database\Eloquent\Collection;

class PortfolioProjectService
{
     /**
     * Get all projects belonging to a portfolio.
     */
    public function getAll(Portfolio $portfolio): Collection
    {
        return $portfolio->projects()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }
     /**
     * Create a project under a portfolio.
     */
    public function create(
        Portfolio $portfolio,
        array $data
    ): PortfolioProject {
        $data['technologies'] = $this->normalizeTechnologies(
            $data['technologies'] ?? []
        );

        return $portfolio->projects()->create($data);
    }
     /**
     * Update a project after verifying portfolio ownership.
     */
    public function update(
        Portfolio $portfolio,
        PortfolioProject $project,
        array $data
    ): PortfolioProject {
        $project = $portfolio->projects()
            ->findOrFail($project->id);

        if (array_key_exists('technologies', $data)) {
            $data['technologies'] = $this->normalizeTechnologies(
                $data['technologies'] ?? []
            );
        }

        $project->update($data);

        return $project->refresh();
    }
     /**
     * Delete a project after verifying portfolio ownership.
     */
    public function delete(
        Portfolio $portfolio,
        PortfolioProject $project
    ): void {
        $project = $portfolio->projects()
            ->findOrFail($project->id);

        $project->delete();
    }
    /**
     * Normalize project technologies.
     */
    private function normalizeTechnologies(array $technologies): array
    {
        $normalized = array_map(
            fn ($technology) => trim($technology),
            $technologies
        );

        $normalized = array_filter(
            $normalized,
            fn ($technology) => $technology !== ''
        );

        return array_values(array_unique($normalized));
    }
}
