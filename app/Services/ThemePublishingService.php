<?php

namespace App\Services;

use App\Enums\ThemeStatus;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;
use Throwable;

class ThemePublishingService
{
    /**
     * Publish a draft theme (including previously unpublished themes).
     *
     * @throws AuthorizationException|InvalidArgumentException|Throwable
     */
    public function publish(Theme $theme, User $actor): Theme
    {
        return $this->transition(
            $theme,
            $actor,
            'publish',
            function (Theme $locked): void {
                if ($locked->status === ThemeStatus::Archived) {
                    throw new InvalidArgumentException(
                        "Archived theme '{$locked->name}' must be restored to draft before publishing."
                    );
                }
            },
            function (Theme $locked): void {
                $locked->status = ThemeStatus::Published;
                $locked->published_at = now();
            }
        );
    }

    /**
     * Unpublish a theme back to draft while preserving its historical published_at timestamp.
     *
     * @throws AuthorizationException|InvalidArgumentException|Throwable
     */
    public function unpublish(Theme $theme, User $actor): Theme
    {
        return $this->transition(
            $theme,
            $actor,
            'unpublish',
            function (Theme $locked): void {
                if ($locked->status === ThemeStatus::Archived) {
                    throw new InvalidArgumentException(
                        "Archived theme '{$locked->name}' cannot be unpublished; restore it to draft instead."
                    );
                }

                if ($locked->status === ThemeStatus::Draft) {
                    throw new InvalidArgumentException(
                        "Theme '{$locked->name}' is already a draft."
                    );
                }
            },
            function (Theme $locked): void {
                $locked->status = ThemeStatus::Draft;
            }
        );
    }

    /**
     * Archive a theme so it can no longer be newly applied while keeping existing portfolios intact.
     *
     * @throws AuthorizationException|InvalidArgumentException|Throwable
     */
    public function archive(Theme $theme, User $actor): Theme
    {
        return $this->transition(
            $theme,
            $actor,
            'archive',
            function (Theme $locked): void {
                if ($locked->status === ThemeStatus::Archived) {
                    throw new InvalidArgumentException(
                        "Theme '{$locked->name}' is already archived."
                    );
                }
            },
            function (Theme $locked): void {
                $locked->status = ThemeStatus::Archived;
            }
        );
    }

    /**
     * Restore an archived theme back to draft (requiring explicit republishing afterward).
     *
     * @throws AuthorizationException|InvalidArgumentException|Throwable
     */
    public function restore(Theme $theme, User $actor): Theme
    {
        return $this->transition(
            $theme,
            $actor,
            'archive',
            function (Theme $locked): void {
                if ($locked->status !== ThemeStatus::Archived) {
                    throw new InvalidArgumentException(
                        "Only archived themes can be restored."
                    );
                }
            },
            function (Theme $locked): void {
                $locked->status = ThemeStatus::Draft;
            }
        );
    }

    /**
     * Authorize actor via ThemePolicy and execute a row-locked lifecycle state transition.
     *
     * @throws AuthorizationException|InvalidArgumentException|Throwable
     */
    private function transition(
        Theme $theme,
        User $actor,
        string $ability,
        callable $validateState,
        callable $applyChanges
    ): Theme {
        Gate::forUser($actor)->authorize($ability, $theme);

        return DB::transaction(function () use ($theme, $validateState, $applyChanges): Theme {
            $locked = Theme::whereKey($theme->id)->lockForUpdate()->firstOrFail();

            $validateState($locked);
            $applyChanges($locked);
            $locked->save();

            return $locked->refresh();
        });
    }
}
