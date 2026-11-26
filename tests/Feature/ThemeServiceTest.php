<?php

namespace Tests\Feature;

use App\Enums\ThemeStatus;
use App\Models\Portfolio;
use App\Models\Theme;
use App\Models\User;
use App\Services\ThemeService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Tests\TestCase;

class ThemeServiceTest extends TestCase
{
    use DatabaseTransactions;

    private ThemeService $service;
    private User $superAdmin;
    private User $individualAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(ThemeService::class);
        $this->superAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);
        $this->individualAdmin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);
    }

    private function makeThemeData(string $suffix = '', array $overrides = []): array
    {
        $suffix = $suffix !== '' ? $suffix : uniqid();

        return array_merge([
            'name' => 'Studio Minimal ' . $suffix,
            'slug' => 'Studio Minimal ' . $suffix,
            'theme_key' => 'studio_minimal_' . $suffix,
            'description' => 'Clean studio layout.',
            'category' => 'designer',
            'thumbnail' => '/images/themes/studio.png',
            'version' => '1.0.0',
            'is_featured' => false,
        ], $overrides);
    }

    public function test_create_initializes_draft_theme_normalizes_slug_and_assigns_creator_explicitly(): void
    {
        $payload = $this->makeThemeData('alpha', [
            'created_by' => $this->individualAdmin->id,
            'status' => ThemeStatus::Published->value,
            'published_at' => '2026-11-26 09:00:00',
        ]);

        $theme = $this->service->create($payload, $this->superAdmin);

        $this->assertInstanceOf(Theme::class, $theme);
        $this->assertSame('studio-minimal-alpha', $theme->slug);
        $this->assertSame($this->superAdmin->id, $theme->created_by);
        $this->assertSame(ThemeStatus::Draft, $theme->status);
        $this->assertNull($theme->published_at);
        $this->assertDatabaseHas('themes', [
            'id' => $theme->id,
            'created_by' => $this->superAdmin->id,
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    public function test_create_rejects_unauthorized_actor_via_theme_policy(): void
    {
        $this->expectException(AuthorizationException::class);

        $this->service->create($this->makeThemeData(), $this->individualAdmin);
    }

    public function test_update_modifies_editable_metadata_and_preserves_lifecycle_and_creator(): void
    {
        $publishedAt = now()->subDay()->startOfSecond();

        $theme = $this->service->create($this->makeThemeData('before'), $this->superAdmin);
        $theme->status = ThemeStatus::Published;
        $theme->published_at = $publishedAt;
        $theme->save();

        $updated = $this->service->update($theme, [
            'name' => 'Updated Theme Name',
            'slug' => 'updated-theme-slug-' . $theme->id,
            'theme_key' => 'updated_key_' . $theme->id,
            'description' => 'Updated description.',
            'category' => 'developer',
            'thumbnail' => '/images/themes/updated.png',
            'version' => '2.1.0',
            'is_featured' => true,
            // Protected fields must be ignored even if passed to service
            'created_by' => $this->individualAdmin->id,
            'status' => ThemeStatus::Archived->value,
            'published_at' => null,
        ], $this->superAdmin);

        $this->assertSame('Updated Theme Name', $updated->name);
        $this->assertSame('updated-theme-slug-' . $theme->id, $updated->slug);
        $this->assertSame('updated_key_' . $theme->id, $updated->theme_key);
        $this->assertSame('developer', $updated->category);
        $this->assertSame('2.1.0', $updated->version);
        $this->assertTrue($updated->is_featured);
        $this->assertSame($this->superAdmin->id, $updated->created_by);
        $this->assertSame(ThemeStatus::Published, $updated->status);
        $this->assertTrue($publishedAt->equalTo($updated->published_at));
    }

    public function test_delete_removes_unused_theme_and_refuses_in_use_theme(): void
    {
        $unusedTheme = $this->service->create($this->makeThemeData('unused'), $this->superAdmin);
        $this->assertTrue($this->service->delete($unusedTheme, $this->superAdmin));
        $this->assertDatabaseMissing('themes', ['id' => $unusedTheme->id]);

        $inUseTheme = $this->service->create($this->makeThemeData('inuse'), $this->superAdmin);
        $portfolio = new Portfolio([
            'user_id' => $this->individualAdmin->id,
            'name' => 'Attached Portfolio',
            'slug' => 'attached-pf-' . uniqid(),
            'status' => 'draft',
        ]);
        $portfolio->theme_id = $inUseTheme->id;
        $portfolio->save();

        // Direct call without actor still enforces safe deletion invariant
        try {
            $this->service->delete($inUseTheme);
            $this->fail('Expected InvalidArgumentException when deleting a theme in use.');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('Cannot delete', $e->getMessage());
        }

        // Call with explicit actor enforces ThemePolicy deletion rule
        try {
            $this->service->delete($inUseTheme, $this->superAdmin);
            $this->fail('Expected AuthorizationException when deleting a theme in use via actor.');
        } catch (AuthorizationException) {
            // Expected
        }

        // Theme and portfolio relation remain intact
        $this->assertDatabaseHas('themes', ['id' => $inUseTheme->id]);
        $this->assertDatabaseHas('portfolios', [
            'id' => $portfolio->id,
            'theme_id' => $inUseTheme->id,
        ]);
    }

    public function test_catalog_filtering_searching_published_isolation_and_pagination(): void
    {
        $suffix = uniqid();

        $draft = $this->service->create($this->makeThemeData("dr{$suffix}", [
            'name' => "Editorial Draft {$suffix}",
            'slug' => "editorial-draft-{$suffix}",
            'category' => 'editorial',
            'is_featured' => true,
        ]), $this->superAdmin);

        $pubDev = $this->service->create($this->makeThemeData("pd{$suffix}", [
            'name' => "Monolith Dev {$suffix}",
            'slug' => "monolith-dev-{$suffix}",
            'category' => 'developer',
            'is_featured' => true,
        ]), $this->superAdmin);
        $pubDev->status = ThemeStatus::Published;
        $pubDev->published_at = now();
        $pubDev->save();

        $pubDesign = $this->service->create($this->makeThemeData("pds{$suffix}", [
            'name' => "Atelier Light {$suffix}",
            'slug' => "atelier-light-{$suffix}",
            'category' => 'designer',
            'is_featured' => false,
        ]), $this->superAdmin);
        $pubDesign->status = ThemeStatus::Published;
        $pubDesign->published_at = now();
        $pubDesign->save();

        $archived = $this->service->create($this->makeThemeData("ar{$suffix}", [
            'name' => "Legacy Archived {$suffix}",
            'slug' => "legacy-archived-{$suffix}",
            'category' => 'developer',
            'is_featured' => false,
        ]), $this->superAdmin);
        $archived->status = ThemeStatus::Archived;
        $archived->save();

        // Super Admin catalog filters by status, category, featured, and search (name & slug)
        $draftResults = $this->service->getAllThemes(['status' => 'draft', 'search' => $suffix]);
        $this->assertSame([$draft->id], $draftResults->pluck('id')->all());

        $featuredResults = $this->service->getAllThemes(['is_featured' => true, 'search' => $suffix]);
        $this->assertEqualsCanonicalizing([$draft->id, $pubDev->id], $featuredResults->pluck('id')->all());

        $slugSearch = $this->service->getAllThemes(['search' => "atelier-light-{$suffix}"]);
        $this->assertSame([$pubDesign->id], $slugSearch->pluck('id')->all());

        // Wildcards in search do not accidentally match everything
        $wildcardEmpty = $this->service->getAllThemes(['search' => '100%_' . $suffix]);
        $this->assertSame(0, $wildcardEmpty->total());

        // Published catalog excludes draft & archived even if a status filter is injected
        $publishedCatalog = $this->service->getPublishedThemes([
            'status' => 'draft',
            'search' => $suffix,
        ]);
        $this->assertEqualsCanonicalizing([$pubDev->id, $pubDesign->id], $publishedCatalog->pluck('id')->all());

        $publishedFeaturedDev = $this->service->getPublishedThemes([
            'category' => 'developer',
            'is_featured' => true,
            'search' => $suffix,
        ]);
        $this->assertSame([$pubDev->id], $publishedFeaturedDev->pluck('id')->all());

        // Pagination check
        $pageOne = $this->service->getPublishedThemes(['search' => $suffix, 'per_page' => 1, 'page' => 1]);
        $this->assertSame(2, $pageOne->total());
        $this->assertCount(1, $pageOne->items());

        // N+1 check on getAllThemes eager loading creator & portfolios_count
        DB::enableQueryLog();
        DB::flushQueryLog();
        $loaded = $this->service->getAllThemes(['search' => $suffix]);
        foreach ($loaded as $item) {
            $this->assertNotNull($item->creator);
            $this->assertNotNull($item->portfolios_count);
        }
        $queries = DB::getQueryLog();
        DB::disableQueryLog();
        // Should be <= 3 queries (count, themes, creators eager load) regardless of row count
        $this->assertLessThanOrEqual(3, count($queries));

        // Empty catalog behavior
        $emptyResult = $this->service->getAllThemes(['search' => 'no-such-theme-xyz-' . uniqid()]);
        $this->assertSame(0, $emptyResult->total());
        $this->assertEmpty($emptyResult->items());
    }
}
