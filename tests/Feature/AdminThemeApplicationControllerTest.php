<?php

namespace Tests\Feature;

use App\Enums\ThemeStatus;
use App\Models\Portfolio;
use App\Models\Theme;
use App\Models\User;
use App\Services\PortfolioService;
use App\Services\ThemeApplicationService;
use App\Services\ThemePublishingService;
use App\Services\ThemeService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminThemeApplicationControllerTest extends TestCase
{
    use DatabaseTransactions;

    private ThemeService $themeService;
    private ThemePublishingService $publishingService;
    private ThemeApplicationService $applicationService;
    private PortfolioService $portfolioService;

    private User $superAdmin;
    private User $ownerA;
    private User $ownerB;
    private User $inactiveAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->themeService = app(ThemeService::class);
        $this->publishingService = app(ThemePublishingService::class);
        $this->applicationService = app(ThemeApplicationService::class);
        $this->portfolioService = app(PortfolioService::class);

        $this->superAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);
        $this->ownerA = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);
        $this->ownerB = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);
        $this->inactiveAdmin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => false,
        ]);
    }

    private function createTheme(ThemeStatus $status = ThemeStatus::Published): Theme
    {
        $suffix = uniqid();
        $theme = $this->themeService->create([
            'name' => 'Endpoint Theme ' . $suffix,
            'slug' => 'endpoint-theme-' . $suffix,
            'theme_key' => 'endpoint_theme_' . $suffix,
            'description' => 'Safe public theme description.',
            'category' => 'developer',
            'thumbnail' => '/images/themes/endpoint.png',
            'version' => '1.0.0',
            'is_featured' => true,
        ], $this->superAdmin);

        if ($status === ThemeStatus::Published) {
            return $this->publishingService->publish($theme, $this->superAdmin);
        }

        if ($status === ThemeStatus::Archived) {
            return $this->publishingService->archive($theme, $this->superAdmin);
        }

        return $theme;
    }

    private function createRichPortfolio(User $owner, string $suffix = ''): Portfolio
    {
        $suffix = $suffix !== '' ? $suffix : uniqid();

        return $this->portfolioService->create(
            $owner,
            [
                'name' => "Rich Site {$suffix}",
                'slug' => "rich-site-{$suffix}",
                'description' => "Preserved site description {$suffix}.",
            ],
            [
                'full_name' => "Owner {$suffix}",
                'professional_title' => 'Product Engineer',
                'email' => "owner-{$suffix}@example.com",
                'location' => 'Dhaka',
                'bio' => "Original bio {$suffix}.",
            ],
            [
                ['label' => 'Home', 'section_key' => 'home', 'sort_order' => 1, 'is_visible' => true],
                ['label' => 'Projects', 'section_key' => 'projects', 'sort_order' => 2, 'is_visible' => true],
            ],
            [['name' => 'PHP'], ['name' => 'Laravel']],
            [[
                'title' => "App {$suffix}",
                'description' => 'Intact project record.',
                'technologies' => ['Laravel'],
                'is_visible' => true,
                'sort_order' => 1,
            ]]
        );
    }

    public function test_route_definition_uses_patch_numeric_constraints_and_expected_middleware(): void
    {
        $route = Route::getRoutes()->getByName('dashboard.portfolios.themes.apply');

        $this->assertNotNull($route);
        $this->assertContains('PATCH', $route->methods());
        $this->assertSame('dashboard/portfolios/{portfolio}/themes/{theme}/apply', $route->uri());
        $this->assertSame('[0-9]+', $route->wheres['portfolio'] ?? null);
        $this->assertSame('[0-9]+', $route->wheres['theme'] ?? null);

        $middleware = $route->gatherMiddleware();
        $this->assertContains('base.domain', $middleware);
        $this->assertContains('auth:admin', $middleware);
        $this->assertContains('individual.admin', $middleware);
    }

    public function test_individual_admin_can_apply_published_theme_updating_only_theme_id_without_leaking_private_data(): void
    {
        $theme = $this->createTheme(ThemeStatus::Published);
        $portfolioA = $this->createRichPortfolio($this->ownerA, 'a-' . uniqid());
        $portfolioB = $this->createRichPortfolio($this->ownerA, 'b-' . uniqid());
        $domainA = $portfolioA->primaryDomain->domain;

        $response = $this->actingAs($this->ownerA, 'admin')
            ->patchJson("http://localhost/dashboard/portfolios/{$portfolioA->id}/themes/{$theme->id}/apply");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.portfolio.id', $portfolioA->id)
            ->assertJsonPath('data.portfolio.theme_id', $theme->id)
            ->assertJsonPath('data.theme.id', $theme->id)
            ->assertJsonPath('data.theme.slug', $theme->slug)
            ->assertJsonMissingPath('data.theme.created_by')
            ->assertJsonMissingPath('data.theme.creator')
            ->assertJsonMissing(['email' => $this->superAdmin->email])
            ->assertJsonMissing(['email' => $this->ownerA->email]);

        $freshA = $portfolioA->fresh(['profile', 'navItems', 'skills', 'projects', 'primaryDomain']);
        $this->assertSame($theme->id, $freshA->theme_id);
        $this->assertSame($portfolioA->name, $freshA->name);
        $this->assertSame($portfolioA->slug, $freshA->slug);
        $this->assertSame($portfolioA->description, $freshA->description);
        $this->assertSame($this->ownerA->id, $freshA->user_id);
        $this->assertSame('draft', $freshA->status);
        $this->assertNull($freshA->published_at);
        $this->assertSame($domainA, $freshA->primaryDomain->domain);
        $this->assertSame($portfolioA->profile->full_name, $freshA->profile->full_name);
        $this->assertCount(2, $freshA->navItems);
        $this->assertCount(2, $freshA->skills);
        $this->assertCount(1, $freshA->projects);

        // Other portfolio owned by same user stays untouched
        $this->assertNull($portfolioB->fresh()->theme_id);
    }

    public function test_unauthenticated_super_admin_inactive_and_cross_owner_attempts_are_rejected(): void
    {
        $theme = $this->createTheme(ThemeStatus::Published);
        $portfolioA = $this->createRichPortfolio($this->ownerA, 'sec-' . uniqid());
        $portfolioInactive = $this->createRichPortfolio($this->inactiveAdmin, 'inact-' . uniqid());

        $urlA = "http://localhost/dashboard/portfolios/{$portfolioA->id}/themes/{$theme->id}/apply";

        // Guest
        $this->patchJson($urlA)->assertUnauthorized();
        $this->patch($urlA)->assertRedirect('http://localhost/login');

        // Super Admin session without admin guard session
        $this->actingAs($this->superAdmin, 'super_admin')
            ->patchJson($urlA)
            ->assertUnauthorized();
        $this->actingAs($this->superAdmin, 'super_admin')
            ->patch($urlA)
            ->assertRedirect('http://localhost/login');

        // Inactive Individual Admin
        $this->app['auth']->forgetGuards();
        $this->actingAs($this->inactiveAdmin, 'admin')
            ->patchJson("http://localhost/dashboard/portfolios/{$portfolioInactive->id}/themes/{$theme->id}/apply")
            ->assertForbidden();

        // Cross-owner Individual Admin (IDOR protection)
        $this->app['auth']->forgetGuards();
        $this->actingAs($this->ownerB, 'admin')
            ->patchJson($urlA)
            ->assertForbidden();

        $this->assertNull($portfolioA->fresh()->theme_id);
        $this->assertNull($portfolioInactive->fresh()->theme_id);
    }

    public function test_draft_archived_nonexistent_and_protected_payload_requests_keep_existing_assignment_intact(): void
    {
        $existingTheme = $this->createTheme(ThemeStatus::Published);
        $draftTheme = $this->createTheme(ThemeStatus::Draft);
        $archivedTheme = $this->createTheme(ThemeStatus::Archived);

        $portfolio = $this->createRichPortfolio($this->ownerA, 'keep-' . uniqid());
        $this->applicationService->apply($existingTheme, $portfolio, $this->ownerA);

        // Draft and Archived themes must fail and keep existing assignment
        $this->actingAs($this->ownerA, 'admin')
            ->patchJson("http://localhost/dashboard/portfolios/{$portfolio->id}/themes/{$draftTheme->id}/apply")
            ->assertForbidden();

        $this->actingAs($this->ownerA, 'admin')
            ->patchJson("http://localhost/dashboard/portfolios/{$portfolio->id}/themes/{$archivedTheme->id}/apply")
            ->assertForbidden();

        // Non-numeric and nonexistent IDs return 404
        $this->actingAs($this->ownerA, 'admin')
            ->patchJson("http://localhost/dashboard/portfolios/abc/themes/{$existingTheme->id}/apply")
            ->assertNotFound();

        $this->actingAs($this->ownerA, 'admin')
            ->patchJson("http://localhost/dashboard/portfolios/999999/themes/{$existingTheme->id}/apply")
            ->assertNotFound();

        $this->actingAs($this->ownerA, 'admin')
            ->patchJson("http://localhost/dashboard/portfolios/{$portfolio->id}/themes/999999/apply")
            ->assertNotFound();

        // Protected override fields in request body rejected
        $this->actingAs($this->ownerA, 'admin')
            ->patchJson("http://localhost/dashboard/portfolios/{$portfolio->id}/themes/{$existingTheme->id}/apply", [
                'user_id' => $this->ownerB->id,
                'status' => 'published',
                'theme_id' => 999999,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['user_id', 'status', 'theme_id']);

        $this->assertSame($existingTheme->id, $portfolio->fresh()->theme_id);
    }

    public function test_existing_dashboard_and_theme_catalog_routes_remain_functional_and_blocked_on_subdomains(): void
    {
        $theme = $this->createTheme(ThemeStatus::Published);
        $portfolio = $this->createRichPortfolio($this->ownerA, 'routes-' . uniqid());

        $this->actingAs($this->ownerA, 'admin')
            ->get('http://localhost/dashboard')
            ->assertOk();

        $this->actingAs($this->ownerA, 'admin')
            ->getJson('http://localhost/dashboard/themes')
            ->assertOk();

        $this->actingAs($this->ownerA, 'admin')
            ->patchJson("http://mahfuj.localhost/dashboard/portfolios/{$portfolio->id}/themes/{$theme->id}/apply")
            ->assertNotFound();
    }
}
