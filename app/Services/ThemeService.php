<?php

namespace App\Services;

use App\Enums\ThemeStatus;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class ThemeService
{
    /**
     * Metadata columns permitted for create/update operations.
     *
     * @var array<int, string>
     */
    private const EDITABLE_FIELDS = [
        'name',
        'slug',
        'theme_key',
        'description',
        'category',
        'thumbnail',
        'version',
        'is_featured',
    ];

    /**
     * Create a new draft Theme explicitly owned by the authorized creator.
     *
     * @throws AuthorizationException|InvalidArgumentException|Throwable
     */
    public function create(array $data, User $creator): Theme
    {
        Gate::forUser($creator)->authorize('create', Theme::class);

        $attributes = $this->prepareMetadata($data, requireSlug: true);

        return DB::transaction(function () use ($attributes, $creator): Theme {
            $theme = new Theme($attributes);
            $theme->created_by = $creator->id;
            $theme->status = ThemeStatus::Draft;
            $theme->published_at = null;
            $theme->save();

            return $theme->refresh();
        });
    }

    /**
     * Update editable metadata for a Theme without altering creator or lifecycle state.
     *
     * @throws AuthorizationException|InvalidArgumentException|Throwable
     */
    public function update(Theme $theme, array $data, ?User $actor = null): Theme
    {
        if ($actor instanceof User) {
            Gate::forUser($actor)->authorize('update', $theme);
        }

        $attributes = $this->prepareMetadata($data, requireSlug: array_key_exists('slug', $data));

        return DB::transaction(function () use ($theme, $attributes): Theme {
            $theme->fill($attributes);
            $theme->save();

            return $theme->refresh();
        });
    }

    /**
     * Safely delete an unused Theme; refuses deletion if portfolios reference it.
     *
     * @throws AuthorizationException|InvalidArgumentException|Throwable
     */
    public function delete(Theme $theme, ?User $actor = null): bool
    {
        if ($actor instanceof User) {
            Gate::forUser($actor)->authorize('delete', $theme);
        }

        return DB::transaction(function () use ($theme): bool {
            $lockedTheme = Theme::whereKey($theme->id)->lockForUpdate()->firstOrFail();

            if ($lockedTheme->portfolios()->exists()) {
                throw new InvalidArgumentException(
                    'Cannot delete a theme while one or more portfolios reference it.'
                );
            }

            return (bool) $lockedTheme->delete();
        });
    }

    /**
     * Retrieve the full paginated theme catalog for Super Admin management.
     */
    public function getAllThemes(array $filters = []): LengthAwarePaginator
    {
        $query = Theme::query()
            ->with('creator')
            ->withCount('portfolios');

        $this->applyCatalogFilters($query, $filters, allowStatusFilter: true);

        return $query
            ->latest('id')
            ->paginate($this->resolvePerPage($filters))
            ->withQueryString();
    }

    /**
     * Retrieve the paginated catalog of Published themes available for application.
     */
    public function getPublishedThemes(array $filters = []): LengthAwarePaginator
    {
        $query = Theme::query()
            ->available()
            ->with('creator');

        $this->applyCatalogFilters($query, $filters, allowStatusFilter: false);

        return $query
            ->latest('id')
            ->paginate($this->resolvePerPage($filters))
            ->withQueryString();
    }

    /**
     * Extract and normalize only editable metadata keys.
     *
     * @return array<string, mixed>
     * @throws InvalidArgumentException
     */
    private function prepareMetadata(array $data, bool $requireSlug): array
    {
        $attributes = array_intersect_key($data, array_flip(self::EDITABLE_FIELDS));

        if (array_key_exists('name', $attributes)) {
            $attributes['name'] = trim((string) $attributes['name']);
        }

        if (array_key_exists('slug', $attributes) || $requireSlug) {
            $rawSlug = (string) ($attributes['slug'] ?? $attributes['name'] ?? '');
            $normalizedSlug = Str::slug(trim($rawSlug));

            if ($normalizedSlug === '') {
                throw new InvalidArgumentException('A valid non-empty theme slug is required.');
            }

            $attributes['slug'] = $normalizedSlug;
        }

        if (array_key_exists('theme_key', $attributes)) {
            $attributes['theme_key'] = trim((string) $attributes['theme_key']);
        }

        if (array_key_exists('category', $attributes)) {
            $category = trim((string) $attributes['category']);
            $attributes['category'] = $category !== '' ? $category : null;
        }

        if (array_key_exists('thumbnail', $attributes)) {
            $thumbnail = trim((string) $attributes['thumbnail']);
            $attributes['thumbnail'] = $thumbnail !== '' ? $thumbnail : null;
        }

        if (array_key_exists('version', $attributes)) {
            $attributes['version'] = trim((string) $attributes['version']);
        }

        if (array_key_exists('is_featured', $attributes)) {
            $attributes['is_featured'] = (bool) $attributes['is_featured'];
        }

        return $attributes;
    }

    /**
     * Apply safe search, category, featured, and optional status filters.
     */
    private function applyCatalogFilters(Builder $query, array $filters, bool $allowStatusFilter): void
    {
        if ($allowStatusFilter && ! empty($filters['status'])) {
            $status = $filters['status'] instanceof ThemeStatus
                ? $filters['status']
                : ThemeStatus::tryFrom(strtolower(trim((string) $filters['status'])));

            if ($status !== null) {
                $query->where('status', $status->value);
            }
        }

        $category = trim((string) ($filters['category'] ?? ''));
        if ($category !== '') {
            $query->where('category', $category);
        }

        $featuredFilter = $filters['is_featured'] ?? $filters['featured'] ?? null;
        if ($featuredFilter !== null && $featuredFilter !== '') {
            $featured = filter_var($featuredFilter, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($featured !== null) {
                $query->where('is_featured', $featured);
            }
        }

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $escaped = $this->escapeLike($search);
            $query->where(function (Builder $q) use ($escaped): void {
                $q->where('name', 'like', "%{$escaped}%")
                    ->orWhere('slug', 'like', "%{$escaped}%");
            });
        }
    }

    private function resolvePerPage(array $filters): int
    {
        return max(1, min((int) ($filters['per_page'] ?? 10), 100));
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
