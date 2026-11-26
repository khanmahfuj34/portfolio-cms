<?php

namespace App\Services;

use App\Enums\ThemeStatus;
use App\Models\Portfolio;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;
use Throwable;

class ThemeApplicationService
{
    /**
     * Apply a published theme exclusively to a portfolio owned by the active Individual Admin.
     *
     * @throws AuthorizationException|InvalidArgumentException|Throwable
     */
    public function apply(Theme $theme, Portfolio $portfolio, User $actor): Portfolio
    {
        if (! $actor->isAdmin()) {
            throw new AuthorizationException(
                'Only active Individual Admin accounts can apply a theme.'
            );
        }

        Gate::forUser($actor)->authorize('apply', [$theme, $portfolio]);

        return DB::transaction(function () use ($theme, $portfolio, $actor): Portfolio {
            $lockedActor = User::whereKey($actor->id)->lockForUpdate()->firstOrFail();
            $lockedTheme = Theme::whereKey($theme->id)->lockForUpdate()->firstOrFail();
            $lockedPortfolio = Portfolio::whereKey($portfolio->id)->lockForUpdate()->firstOrFail();

            if (! $lockedActor->isAdmin() || (int) $lockedPortfolio->user_id !== (int) $lockedActor->id) {
                throw new AuthorizationException(
                    'Only the active portfolio owner may apply a theme.'
                );
            }

            if (! ($lockedTheme->status instanceof ThemeStatus && $lockedTheme->status->canBeApplied())) {
                throw new InvalidArgumentException(
                    "Theme '{$lockedTheme->name}' cannot be applied because it is not published."
                );
            }

            Gate::forUser($lockedActor)->authorize('apply', [$lockedTheme, $lockedPortfolio]);

            if ((int) $lockedPortfolio->theme_id === (int) $lockedTheme->id) {
                return $lockedPortfolio->refresh();
            }

            $lockedPortfolio->theme_id = $lockedTheme->id;
            $lockedPortfolio->save();

            return $lockedPortfolio->refresh();
        });
    }
}
