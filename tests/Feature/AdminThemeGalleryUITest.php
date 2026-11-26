<?php

namespace Tests\Feature;

use App\Enums\ThemeStatus;
use App\Models\Portfolio;
use App\Models\PortfolioNavItem;
use App\Models\PortfolioProfile;
use App\Models\PortfolioProject;
use App\Models\PortfolioSkill;
use App\Models\Theme;
use App\Models\User;
use App\Services\PortfolioService;
use App\Services\ThemePublishingService;
use App\Services\ThemeService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminThemeGalleryUITest extends TestCase
{
    use DatabaseTransactions;

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

    private function createTheme(ThemeStatus $status, array $overrides = []): Theme
    {
        $suffix = uniqid();
        $theme = new Theme(array_merge([
            'name' => 'Gallery Theme ' . $suffix,
            'slug' => 'gallery-theme-' . $suffix,
            'theme_key' => 'gallery_theme_' . $suffix,
            'description' => 'Published portfolio theme description.',
            'category' => 'Developer',
            'thumbnail' => null,
            'version' => '1.0.0',
            'status' => $status,
            'is_featured' => false,
            'published_at' => $status === ThemeStatus::Published ? now() : null,
        ], $overrides));
        $theme->created_by = $this->superAdmin->id;
        $theme->save();

        return $theme;
    }

    private function createOwnedPortfolio(User $owner, string $label, ?int $themeId = null): Portfolio
    {
        $suffix = uniqid();

        $portfolio = $this->portfolioService->create(
            $owner,
            [
                'name' => "{$label} {$suffix}",
                'slug' => "site-{$suffix}",
                'description' => "Preserved summary {$suffix}",
            ],
            [
                'full_name' => "Owner {$label} {$suffix}",
                'professional_title' => 'Systems Engineer',
                'email' => "contact-{$suffix}@example.test",
                'location' => 'Remote',
                'bio' => "Detailed bio {$suffix}",
            ],
            [
                ['label' => 'Home', 'section_key' => 'home', 'sort_order' => 1, 'is_visible' => true],
                ['label' => 'Projects', 'section_key' => 'projects', 'sort_order' => 2, 'is_visible' => true],
            ],
            [['name' => 'PHP'], ['name' => 'Laravel']],
            [[
                'title' => "Showcase App {$suffix}",
                'description' => 'Preserved project details.',
                'technologies' => ['Laravel', 'MySQL'],
                'is_visible' => true,
                'sort_order' => 1,
            ]]
        );

        if ($themeId !== null) {
            $portfolio->theme_id = $themeId;
            $portfolio->save();
        }

        return $portfolio->fresh();
    }

    public function test_active_individual_admin_sees_theme_gallery_and_sidebar_link_with_published_only_themes(): void
    {
        $marker = uniqid();
        $published = $this->createTheme(ThemeStatus::Published, [
            'name' => "VisiblePub {$marker}",
            'slug' => "visible-pub-{$marker}",
            'category' => 'Minimal',
            'version' => '2.1.0',
            'is_featured' => true,
        ]);
        $draft = $this->createTheme(ThemeStatus::Draft, ['name' => "HiddenDraft {$marker}"]);
        $archived = $this->createTheme(ThemeStatus::Archived, ['name' => "HiddenArch {$marker}"]);

        // Initially published, then unpublished -> must not appear
        $unpublished = $this->createTheme(ThemeStatus::Published, ['name' => "HiddenUnpub {$marker}"]);
        $this->publishingService->unpublish($unpublished, $this->superAdmin);

        $portfolio = $this->createOwnedPortfolio($this->ownerA, 'Primary');

        $response = $this->actingAs($this->ownerA, 'admin')
            ->get("http://localhost/dashboard/themes?search={$marker}");

        $response->assertOk()
            ->assertViewIs('dashboard.themes.index')
            ->assertSee('Theme Gallery')
            ->assertSee('http://localhost/dashboard/themes')
            ->assertSee($published->name)
            ->assertSee('Minimal')
            ->assertSee('v2.1.0')
            ->assertSee('Featured')
            ->assertSee($portfolio->name)
            ->assertSee('Default Theme')
            ->assertDontSee($draft->name)
            ->assertDontSee($archived->name)
            ->assertDontSee($unpublished->name);

        // Zero published state works cleanly without errors
        $emptySearch = $this->actingAs($this->ownerA, 'admin')
            ->get('http://localhost/dashboard/themes?search=no_such_theme_xyz_' . uniqid());
        $emptySearch->assertOk()->assertSee('No published themes available');
    }

    public function test_guest_super_admin_and_inactive_individual_admin_are_denied_theme_gallery_and_preview(): void
    {
        $published = $this->createTheme(ThemeStatus::Published);

        // Guest
        $this->get('http://localhost/dashboard/themes')->assertRedirect('http://localhost/login');
        $this->get("http://localhost/dashboard/themes/{$published->id}/preview")->assertRedirect('http://localhost/login');

        // Super Admin session alone
        $this->actingAs($this->superAdmin, 'super_admin')
            ->get('http://localhost/dashboard/themes')
            ->assertRedirect('http://localhost/login');

        $this->actingAs($this->superAdmin, 'super_admin')
            ->get("http://localhost/dashboard/themes/{$published->id}/preview")
            ->assertRedirect('http://localhost/login');

        // Inactive Individual Admin
        $this->actingAs($this->inactiveAdmin, 'admin')
            ->get('http://localhost/dashboard/themes')
            ->assertRedirect('http://localhost/login');

        $this->app['auth']->forgetGuards();

        $this->actingAs($this->inactiveAdmin, 'admin')
            ->get("http://localhost/dashboard/themes/{$published->id}/preview")
            ->assertRedirect('http://localhost/login');
    }

    public function test_portfolio_selector_is_strictly_owner_scoped_and_supports_one_or_multiple_portfolios(): void
    {
        $publishedOne = $this->createTheme(ThemeStatus::Published, ['name' => 'Selector One ' . uniqid()]);
        $publishedTwo = $this->createTheme(ThemeStatus::Published, ['name' => 'Selector Two ' . uniqid()]);

        $ownerPortfolioA = $this->createOwnedPortfolio($this->ownerA, 'OwnerA-Alpha', $publishedOne->id);
        $otherUserPortfolio = $this->createOwnedPortfolio($this->ownerB, 'SecretOwnerB');

        // Single owned portfolio -> auto-selected cleanly without leaking owner B's portfolio
        $singleResponse = $this->actingAs($this->ownerA, 'admin')
            ->get('http://localhost/dashboard/themes');

        $singleResponse->assertOk()
            ->assertSee($ownerPortfolioA->name)
            ->assertSee($publishedOne->name)
            ->assertDontSee($otherUserPortfolio->name);

        // Second owned portfolio -> multi-portfolio <select> lists A & B, never owner B's portfolio
        $ownerPortfolioB = $this->createOwnedPortfolio($this->ownerA, 'OwnerA-Beta', $publishedTwo->id);

        $multiResponse = $this->actingAs($this->ownerA, 'admin')
            ->get("http://localhost/dashboard/themes?portfolio={$ownerPortfolioB->id}");

        $multiResponse->assertOk()
            ->assertSee('id="target-portfolio-select"', false)
            ->assertSee($ownerPortfolioA->name)
            ->assertSee($ownerPortfolioB->name)
            ->assertDontSee($otherUserPortfolio->name);
    }

    public function test_multiple_portfolios_can_switch_published_themes_independently_preserving_content(): void
    {
        $themeOne = $this->createTheme(ThemeStatus::Published, ['name' => 'Horizon One ' . uniqid()]);
        $themeTwo = $this->createTheme(ThemeStatus::Published, ['name' => 'Horizon Two ' . uniqid()]);
        $draftTheme = $this->createTheme(ThemeStatus::Draft, ['name' => 'Unpublished Draft']);

        $portfolioA = $this->createOwnedPortfolio($this->ownerA, 'PortfolioA');
        $portfolioB = $this->createOwnedPortfolio($this->ownerA, 'PortfolioB');
        $otherPortfolio = $this->createOwnedPortfolio($this->ownerB, 'ForeignSite');

        $snapshotA = [
            'name' => $portfolioA->name,
            'slug' => $portfolioA->slug,
            'description' => $portfolioA->description,
            'bio' => $portfolioA->profile->bio,
            'projects_count' => PortfolioProject::where('portfolio_id', $portfolioA->id)->count(),
            'skills_count' => PortfolioSkill::where('portfolio_id', $portfolioA->id)->count(),
            'nav_count' => PortfolioNavItem::where('portfolio_id', $portfolioA->id)->count(),
        ];

        // 1. Apply themeOne to Portfolio A
        $this->actingAs($this->ownerA, 'admin')
            ->patchJson("http://localhost/dashboard/portfolios/{$portfolioA->id}/themes/{$themeOne->id}/apply")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.portfolio.theme_id', $themeOne->id);

        $this->assertSame($themeOne->id, $portfolioA->fresh()->theme_id);
        $this->assertNull($portfolioB->fresh()->theme_id);

        // 2. Apply themeTwo to Portfolio B without affecting Portfolio A
        $this->actingAs($this->ownerA, 'admin')
            ->patchJson("http://localhost/dashboard/portfolios/{$portfolioB->id}/themes/{$themeTwo->id}/apply")
            ->assertOk()
            ->assertJsonPath('data.portfolio.theme_id', $themeTwo->id);

        $this->assertSame($themeOne->id, $portfolioA->fresh()->theme_id);
        $this->assertSame($themeTwo->id, $portfolioB->fresh()->theme_id);

        // 3. Switch Portfolio A to themeTwo without affecting content
        $this->actingAs($this->ownerA, 'admin')
            ->patchJson("http://localhost/dashboard/portfolios/{$portfolioA->id}/themes/{$themeTwo->id}/apply")
            ->assertOk()
            ->assertJsonPath('data.portfolio.theme_id', $themeTwo->id);

        $reloadedA = $portfolioA->fresh(['profile', 'projects', 'skills', 'navItems']);
        $this->assertSame($snapshotA['name'], $reloadedA->name);
        $this->assertSame($snapshotA['slug'], $reloadedA->slug);
        $this->assertSame($snapshotA['description'], $reloadedA->description);
        $this->assertSame($snapshotA['bio'], $reloadedA->profile->bio);
        $this->assertSame($snapshotA['projects_count'], $reloadedA->projects->count());
        $this->assertSame($snapshotA['skills_count'], $reloadedA->skills->count());
        $this->assertSame($snapshotA['nav_count'], $reloadedA->navItems->count());

        // 4. Draft/unpublished theme & cross-owner portfolio are rejected
        $this->actingAs($this->ownerA, 'admin')
            ->patchJson("http://localhost/dashboard/portfolios/{$portfolioA->id}/themes/{$draftTheme->id}/apply")
            ->assertForbidden();

        $this->actingAs($this->ownerA, 'admin')
            ->patchJson("http://localhost/dashboard/portfolios/{$otherPortfolio->id}/themes/{$themeOne->id}/apply")
            ->assertForbidden();

        $this->assertNull($otherPortfolio->fresh()->theme_id);
    }

    public function test_theme_preview_renders_owned_portfolio_safely_without_mutating_theme_id_and_blocks_draft_or_foreign_portfolios(): void
    {
        $rifahTheme = Theme::query()->where('theme_key', 'rifah-modern')->first()
            ?? $this->createTheme(ThemeStatus::Draft, ['theme_key' => 'rifah-modern']);

        // Remember DB state inside transaction; publish rifah-modern temporarily in transaction
        $wasDraft = $rifahTheme->status !== ThemeStatus::Published;
        if ($wasDraft) {
            $rifahTheme = $this->publishingService->publish($rifahTheme, $this->superAdmin);
        }

        $draftTheme = $this->createTheme(ThemeStatus::Draft, ['theme_key' => 'draft_blocked_' . uniqid()]);
        $unregisteredPub = $this->createTheme(ThemeStatus::Published, ['theme_key' => 'unreg_pub_' . uniqid()]);

        $ownerAPortfolio = $this->createOwnedPortfolio($this->ownerA, 'MyPreviewPortfolio');
        $ownerBPortfolio = $this->createOwnedPortfolio($this->ownerB, 'ForeignPrivate');

        // 1. Preview published theme with owner's own portfolio renders real content without changing theme_id
        $previewOwn = $this->actingAs($this->ownerA, 'admin')
            ->get("http://localhost/dashboard/themes/{$rifahTheme->id}/preview?portfolio={$ownerAPortfolio->id}");

        $previewOwn->assertOk()
            ->assertSee($ownerAPortfolio->profile->full_name)
            ->assertDontSee($ownerBPortfolio->profile->full_name);

        $this->assertNull($ownerAPortfolio->fresh()->theme_id);

        // 2. Preview without portfolio parameter uses neutral non-personal fixture
        $previewNeutral = $this->actingAs($this->ownerA, 'admin')
            ->get("http://localhost/dashboard/themes/{$rifahTheme->id}/preview");

        $previewNeutral->assertOk()
            ->assertSee('Portfolio Owner')
            ->assertDontSee($ownerBPortfolio->profile->full_name);

        // 3. Preview with cross-owner portfolio ID is denied
        $this->actingAs($this->ownerA, 'admin')
            ->get("http://localhost/dashboard/themes/{$rifahTheme->id}/preview?portfolio={$ownerBPortfolio->id}")
            ->assertForbidden();

        // 4. Draft theme preview is denied for Individual Admin
        $this->actingAs($this->ownerA, 'admin')
            ->get("http://localhost/dashboard/themes/{$draftTheme->id}/preview")
            ->assertForbidden();

        // 5. Unregistered published theme key shows clean preview-unavailable page
        $this->actingAs($this->ownerA, 'admin')
            ->get("http://localhost/dashboard/themes/{$unregisteredPub->id}/preview")
            ->assertOk()
            ->assertSee('Theme Preview Unavailable');
    }
}
