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
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use InvalidArgumentException;
use RuntimeException;
use Tests\TestCase;

class ThemeApplicationServiceTest extends TestCase
{
    use DatabaseTransactions;

    private ThemeApplicationService $applicationService;
    private ThemeService $themeService;
    private ThemePublishingService $publishingService;
    private PortfolioService $portfolioService;

    private User $superAdmin;
    private User $ownerA;
    private User $ownerB;
    private User $inactiveAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->applicationService = app(ThemeApplicationService::class);
        $this->themeService = app(ThemeService::class);
        $this->publishingService = app(ThemePublishingService::class);
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
            'name' => 'App Theme ' . $suffix,
            'slug' => 'app-theme-' . $suffix,
            'theme_key' => 'app_theme_' . $suffix,
            'description' => 'Theme for portfolio application tests.',
            'category' => 'developer',
            'thumbnail' => '/images/themes/app.png',
            'version' => '1.0.0',
            'is_featured' => false,
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
                'name' => "Rich Portfolio {$suffix}",
                'slug' => "rich-pf-{$suffix}",
                'description' => "Preserved description {$suffix}.",
            ],
            [
                'full_name' => "Creator {$suffix}",
                'professional_title' => 'Backend Architect',
                'email' => "creator-{$suffix}@example.com",
                'location' => 'Dhaka',
                'bio' => "Original bio {$suffix}.",
            ],
            [
                ['label' => 'Home', 'section_key' => 'home', 'sort_order' => 1, 'is_visible' => true],
                ['label' => 'Work', 'section_key' => 'projects', 'sort_order' => 2, 'is_visible' => true],
            ],
            [['name' => 'Laravel'], ['name' => 'PHP']],
            [[
                'title' => "Project {$suffix}",
                'description' => 'Kept intact.',
                'technologies' => ['Laravel'],
                'is_visible' => true,
                'sort_order' => 1,
            ]]
        );
    }

    public function test_individual_admin_applies_published_theme_updating_only_theme_id_and_isolating_other_portfolios(): void
    {
        $themeOne = $this->createTheme(ThemeStatus::Published);
        $themeTwo = $this->createTheme(ThemeStatus::Published);

        $portfolioOne = $this->createRichPortfolio($this->ownerA, 'one-' . uniqid());
        $portfolioTwo = $this->createRichPortfolio($this->ownerA, 'two-' . uniqid());
        $originalDomain = $portfolioOne->primaryDomain->domain;

        $updated = $this->applicationService->apply($themeOne, $portfolioOne, $this->ownerA);

        $this->assertSame($themeOne->id, $updated->theme_id);
        $this->assertTrue($updated->theme->is($themeOne));

        // All unrelated portfolio fields & nested content remain intact
        $updated->load(['profile', 'navItems', 'skills', 'projects', 'primaryDomain']);
        $this->assertSame($this->ownerA->id, $updated->user_id);
        $this->assertSame($portfolioOne->name, $updated->name);
        $this->assertSame($portfolioOne->slug, $updated->slug);
        $this->assertSame($portfolioOne->description, $updated->description);
        $this->assertSame('draft', $updated->status);
        $this->assertNull($updated->published_at);
        $this->assertSame($originalDomain, $updated->primaryDomain->domain);
        $this->assertSame($portfolioOne->profile->full_name, $updated->profile->full_name);
        $this->assertCount(2, $updated->navItems);
        $this->assertCount(2, $updated->skills);
        $this->assertCount(1, $updated->projects);

        // Other portfolio owned by same Individual Admin remains untouched
        $this->assertNull($portfolioTwo->fresh()->theme_id);

        // Switching to another published theme & reapplying the same theme is safe & idempotent
        $switched = $this->applicationService->apply($themeTwo, $updated, $this->ownerA);
        $this->assertSame($themeTwo->id, $switched->theme_id);

        $reapplied = $this->applicationService->apply($themeTwo, $switched, $this->ownerA);
        $this->assertSame($themeTwo->id, $reapplied->theme_id);
    }

    public function test_cross_owner_inactive_admin_and_super_admin_actors_are_rejected(): void
    {
        $publishedTheme = $this->createTheme(ThemeStatus::Published);
        $portfolioA = $this->createRichPortfolio($this->ownerA, 'auth-' . uniqid());
        $portfolioInactive = $this->createRichPortfolio($this->inactiveAdmin, 'inact-' . uniqid());

        // Another Individual Admin cannot apply theme to Owner A's portfolio
        try {
            $this->applicationService->apply($publishedTheme, $portfolioA, $this->ownerB);
            $this->fail('Expected AuthorizationException for cross-owner application.');
        } catch (AuthorizationException $e) {
            $this->assertInstanceOf(AuthorizationException::class, $e);
        }

        // Super Admin actor is rejected for this Individual Admin application workflow
        try {
            $this->applicationService->apply($publishedTheme, $portfolioA, $this->superAdmin);
            $this->fail('Expected AuthorizationException for Super Admin actor.');
        } catch (AuthorizationException $e) {
            $this->assertInstanceOf(AuthorizationException::class, $e);
        }

        // Inactive Individual Admin owning a portfolio is rejected
        try {
            $this->applicationService->apply($publishedTheme, $portfolioInactive, $this->inactiveAdmin);
            $this->fail('Expected AuthorizationException for inactive Individual Admin.');
        } catch (AuthorizationException $e) {
            $this->assertInstanceOf(AuthorizationException::class, $e);
        }

        $this->assertNull($portfolioA->fresh()->theme_id);
        $this->assertNull($portfolioInactive->fresh()->theme_id);
    }

    public function test_draft_archived_and_stale_concurrent_theme_applications_are_rejected(): void
    {
        $draftTheme = $this->createTheme(ThemeStatus::Draft);
        $archivedTheme = $this->createTheme(ThemeStatus::Archived);
        $publishedTheme = $this->createTheme(ThemeStatus::Published);

        $portfolio = $this->createRichPortfolio($this->ownerA, 'status-' . uniqid());

        foreach ([$draftTheme, $archivedTheme] as $nonPublished) {
            try {
                $this->applicationService->apply($nonPublished, $portfolio, $this->ownerA);
                $this->fail('Expected AuthorizationException for non-published theme.');
            } catch (AuthorizationException $e) {
                $this->assertInstanceOf(AuthorizationException::class, $e);
            }
        }

        // Simulate stale in-memory Published Theme instance when DB was concurrently archived
        $staleTheme = clone $publishedTheme;
        $this->publishingService->archive($publishedTheme, $this->superAdmin);

        try {
            $this->applicationService->apply($staleTheme, $portfolio, $this->ownerA);
            $this->fail('Expected InvalidArgumentException when locked DB theme is no longer published.');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('not published', $e->getMessage());
        }

        $this->assertNull($portfolio->fresh()->theme_id);
    }

    public function test_failed_persistence_rolls_back_and_existing_assignment_survives_theme_unpublish_or_archive(): void
    {
        $theme = $this->createTheme(ThemeStatus::Published);
        $portfolio = $this->createRichPortfolio($this->ownerA, 'persist-' . uniqid());

        // Simulated persistence failure leaves DB unchanged
        Event::listen('eloquent.saving: ' . Portfolio::class, function (): void {
            throw new RuntimeException('Simulated portfolio save error');
        });

        try {
            $this->applicationService->apply($theme, $portfolio, $this->ownerA);
            $this->fail('Expected RuntimeException during simulated failure.');
        } catch (RuntimeException $e) {
            $this->assertSame('Simulated portfolio save error', $e->getMessage());
        } finally {
            Event::forget('eloquent.saving: ' . Portfolio::class);
        }

        $this->assertNull($portfolio->fresh()->theme_id);

        // Successfully apply, then unpublish and archive theme -> assignment stays intact
        $applied = $this->applicationService->apply($theme, $portfolio, $this->ownerA);
        $this->assertSame($theme->id, $applied->theme_id);

        $this->publishingService->unpublish($theme, $this->superAdmin);
        $this->assertSame($theme->id, $portfolio->fresh()->theme_id);

        $this->publishingService->archive($theme->fresh(), $this->superAdmin);
        $this->assertSame($theme->id, $portfolio->fresh()->theme_id);
    }
}
