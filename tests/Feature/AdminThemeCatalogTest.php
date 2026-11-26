<?php

namespace Tests\Feature;

use App\Enums\ThemeStatus;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminThemeCatalogTest extends TestCase
{
    use DatabaseTransactions;

    private User $superAdmin;
    private User $individualAdmin;
    private User $inactiveIndividualAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        $this->individualAdmin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $this->inactiveIndividualAdmin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => false,
        ]);
    }

    private function createTheme(ThemeStatus $status, array $overrides = []): Theme
    {
        $suffix = uniqid();

        $theme = new Theme(array_merge([
            'name' => 'Catalog Theme ' . $suffix,
            'slug' => 'catalog-theme-' . $suffix,
            'theme_key' => 'catalog_theme_' . $suffix,
            'description' => 'Published portfolio theme.',
            'category' => 'developer',
            'thumbnail' => '/images/themes/catalog.png',
            'version' => '1.0.0',
            'status' => $status,
            'is_featured' => false,
            'published_at' => $status === ThemeStatus::Published ? now() : null,
        ], $overrides));
        $theme->created_by = $this->superAdmin->id;
        $theme->save();

        return $theme;
    }

    public function test_guest_and_super_admin_guard_alone_cannot_access_individual_admin_theme_gallery(): void
    {
        $published = $this->createTheme(ThemeStatus::Published);

        // Guest redirects to login on web and 401 on JSON
        $this->get('http://localhost/dashboard/themes')->assertRedirect('http://localhost/login');
        $this->get("http://localhost/dashboard/themes/{$published->id}")->assertRedirect('http://localhost/login');
        $this->getJson('http://localhost/dashboard/themes')->assertUnauthorized();

        // Super Admin guard alone cannot access Individual Admin routes
        $this->actingAs($this->superAdmin, 'super_admin')
            ->get('http://localhost/dashboard/themes')
            ->assertRedirect('http://localhost/login');

        $this->actingAs($this->superAdmin, 'super_admin')
            ->getJson("http://localhost/dashboard/themes/{$published->id}")
            ->assertUnauthorized();
    }

    public function test_inactive_individual_admin_is_denied_theme_gallery_access(): void
    {
        $published = $this->createTheme(ThemeStatus::Published);

        $this->actingAs($this->inactiveIndividualAdmin, 'admin')
            ->getJson('http://localhost/dashboard/themes')
            ->assertForbidden();

        $this->actingAs($this->inactiveIndividualAdmin, 'admin')
            ->get("http://localhost/dashboard/themes/{$published->id}")
            ->assertRedirect('http://localhost/login');
    }

    public function test_individual_admin_can_browse_only_published_themes_with_filters_and_pagination(): void
    {
        $marker = uniqid();

        $draft = $this->createTheme(ThemeStatus::Draft, [
            'name' => "Hidden Draft {$marker}",
            'slug' => "hidden-draft-{$marker}",
            'category' => 'developer',
            'is_featured' => true,
        ]);

        $archived = $this->createTheme(ThemeStatus::Archived, [
            'name' => "Hidden Archived {$marker}",
            'slug' => "hidden-archived-{$marker}",
            'category' => 'developer',
            'is_featured' => true,
        ]);

        $pubDev = $this->createTheme(ThemeStatus::Published, [
            'name' => "Monolith Dev {$marker}",
            'slug' => "monolith-dev-{$marker}",
            'category' => 'developer',
            'is_featured' => true,
        ]);

        $pubDesign = $this->createTheme(ThemeStatus::Published, [
            'name' => "Atelier Light {$marker}",
            'slug' => "atelier-light-{$marker}",
            'category' => 'designer',
            'is_featured' => false,
        ]);

        $gallery = $this->actingAs($this->individualAdmin, 'admin')
            ->getJson("http://localhost/dashboard/themes?search={$marker}");

        $gallery->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.total', 2);

        $ids = collect($gallery->json('data'))->pluck('id')->all();
        $this->assertEqualsCanonicalizing([$pubDev->id, $pubDesign->id], $ids);
        $this->assertNotContains($draft->id, $ids);
        $this->assertNotContains($archived->id, $ids);

        // Category filter
        $catResponse = $this->actingAs($this->individualAdmin, 'admin')
            ->getJson("http://localhost/dashboard/themes?search={$marker}&category=designer");
        $catResponse->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $pubDesign->id);

        // Featured filter
        $featuredResponse = $this->actingAs($this->individualAdmin, 'admin')
            ->getJson("http://localhost/dashboard/themes?search={$marker}&featured=1");
        $featuredResponse->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $pubDev->id);

        // Pagination
        $pageOne = $this->actingAs($this->individualAdmin, 'admin')
            ->getJson("http://localhost/dashboard/themes?search={$marker}&per_page=1&page=1");
        $pageOne->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonCount(1, 'data');
    }

    public function test_individual_admin_can_view_published_theme_without_private_data_and_is_blocked_from_draft_or_archived(): void
    {
        $published = $this->createTheme(ThemeStatus::Published);
        $draft = $this->createTheme(ThemeStatus::Draft);
        $archived = $this->createTheme(ThemeStatus::Archived);

        $showResponse = $this->actingAs($this->individualAdmin, 'admin')
            ->getJson("http://localhost/dashboard/themes/{$published->id}");

        $showResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $published->id)
            ->assertJsonPath('data.name', $published->name)
            ->assertJsonPath('data.slug', $published->slug)
            ->assertJsonPath('data.theme_key', $published->theme_key)
            ->assertJsonPath('data.description', $published->description)
            ->assertJsonPath('data.category', $published->category)
            ->assertJsonPath('data.thumbnail', $published->thumbnail)
            ->assertJsonPath('data.version', $published->version)
            ->assertJsonPath('data.is_featured', false)
            ->assertJsonMissingPath('data.created_by')
            ->assertJsonMissingPath('data.creator')
            ->assertJsonMissing(['email' => $this->superAdmin->email]);

        $this->actingAs($this->individualAdmin, 'admin')
            ->getJson("http://localhost/dashboard/themes/{$draft->id}")
            ->assertForbidden();

        $this->actingAs($this->individualAdmin, 'admin')
            ->getJson("http://localhost/dashboard/themes/{$archived->id}")
            ->assertForbidden();
    }

    public function test_existing_dashboard_and_portfolio_routes_remain_functional(): void
    {
        $this->actingAs($this->individualAdmin, 'admin')
            ->get('http://localhost/dashboard')
            ->assertOk();

        $this->actingAs($this->individualAdmin, 'admin')
            ->get('http://localhost/dashboard/portfolios')
            ->assertOk();

        $this->actingAs($this->individualAdmin, 'admin')
            ->get('http://mahfuj.localhost/dashboard/themes')
            ->assertNotFound();
    }
}
