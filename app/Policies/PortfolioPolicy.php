<?php

namespace App\Policies;

use App\Models\Portfolio;
use App\Models\User;

class PortfolioPolicy
{
    /**
     * Super Admins retain full system-level permissions across all portfolios.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view their own portfolios listing.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view a specific portfolio.
     */
    public function view(User $user, Portfolio $portfolio): bool
    {
        return $this->ownsPortfolio($user, $portfolio);
    }

    /**
     * Determine whether the user can create portfolios.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update a portfolio or its nested resources.
     */
    public function update(User $user, Portfolio $portfolio): bool
    {
        return $this->ownsPortfolio($user, $portfolio);
    }

    /**
     * Determine whether the user can delete a portfolio.
     */
    public function delete(User $user, Portfolio $portfolio): bool
    {
        return $this->ownsPortfolio($user, $portfolio);
    }

    /**
     * Determine whether the user can publish a portfolio.
     */
    public function publish(User $user, Portfolio $portfolio): bool
    {
        return $this->ownsPortfolio($user, $portfolio);
    }

    /**
     * Determine whether the user can unpublish a portfolio.
     */
    public function unpublish(User $user, Portfolio $portfolio): bool
    {
        return $this->ownsPortfolio($user, $portfolio);
    }

    private function ownsPortfolio(User $user, Portfolio $portfolio): bool
    {
        return $user->isAdmin() && (int) $portfolio->user_id === (int) $user->id;
    }
}
