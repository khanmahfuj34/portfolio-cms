<?php

namespace App\Services;
use App\Models\Portfolio;
use App\Models\PortfolioSkill;
use Illuminate\Database\Eloquent\Collection;
class PortfolioSkillService
{
    /**
     * Get all skills belonging to a portfolio.
     */
    public function getAll(Portfolio $portfolio): Collection
    {
        return $portfolio->skills()
            ->orderBy('name')
            ->orderBy('id')
            ->get();
    }
    /**
     * Create a skill under a portfolio.
     */
    public function create(
        Portfolio $portfolio,
        string $name
    ): PortfolioSkill {
        return $portfolio->skills()->create([
            'name' => trim($name),
        ]);
    }
    /**
     * Update a skill after verifying portfolio ownership.
     */
    public function update(
        Portfolio $portfolio,
        PortfolioSkill $skill,
        string $name
    ): PortfolioSkill {
        $skill = $portfolio->skills()
            ->findOrFail($skill->id);

        $skill->update([
            'name' => trim($name),
        ]);

        return $skill->refresh();
    }
    /**
     * Delete a skill after verifying portfolio ownership.
     */
    public function delete(
        Portfolio $portfolio,
        PortfolioSkill $skill
    ): void {
        $skill = $portfolio->skills()
            ->findOrFail($skill->id);

        $skill->delete();
    }


}
