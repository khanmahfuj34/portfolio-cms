<?php

namespace App\Policies;

use App\Enums\ThemeStatus;
use App\Models\Portfolio;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class ThemePolicy
{
    /**
     * Determine whether an active Super Admin can view the full theme catalog.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether an active Super Admin can view any theme record.
     */
    public function view(User $user, Theme $theme): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether an active Super Admin can create themes.
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether an active Super Admin can update theme metadata.
     */
    public function update(User $user, Theme $theme): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether an active Super Admin can delete an unused theme.
     */
    public function delete(User $user, Theme $theme): bool
    {
        return $user->isSuperAdmin() && ! $theme->portfolios()->exists();
    }

    /**
     * Determine whether an active Super Admin can publish or republish a theme.
     */
    public function publish(User $user, Theme $theme): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether an active Super Admin can unpublish a theme from new applications.
     */
    public function unpublish(User $user, Theme $theme): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether an active Super Admin can archive a theme.
     */
    public function archive(User $user, Theme $theme): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can browse published themes.
     */
    public function browsePublished(User $user): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can inspect details of a published theme.
     */
    public function viewPublished(User $user, Theme $theme): bool
    {
        return ($user->isAdmin() || $user->isSuperAdmin())
            && $theme->status instanceof ThemeStatus
            && $theme->status->isPublished();
    }

    /**
     * Determine whether the user can apply a published theme to a portfolio they may update.
     */
    public function apply(User $user, Theme $theme, ?Portfolio $portfolio = null): bool
    {
        if (! ($theme->status instanceof ThemeStatus && $theme->status->canBeApplied())) {
            return false;
        }

        if (! $portfolio instanceof Portfolio) {
            return false;
        }

        if (! ($user->isAdmin() || $user->isSuperAdmin())) {
            return false;
        }

        return Gate::forUser($user)->allows('update', $portfolio);
    }
}
