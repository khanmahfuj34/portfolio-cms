<?php

namespace Tests\Feature;

use App\Enums\ThemeStatus;
use App\Models\Portfolio;
use App\Models\Theme;
use App\Models\User;
use App\Services\PortfolioPreviewService;
use App\Services\PortfolioService;
use App\Services\PortfolioThemeResolver;
use App\Services\ThemeApplicationService;
use App\Services\ThemePublishingService;
use App\Services\ThemeService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class PortfolioThemeResolverTest extends TestCase
{
    use DatabaseTransactions;

    private PortfolioThemeResolver $resolver;
    private ThemeService $themeService;
    private ThemePublishingService $publishingService;
    private ThemeApplicationService $applicationService;
    private PortfolioService $portfolioService;
    private PortfolioPreviewService $previewService;

    private User $superAdmin;
    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = app(PortfolioThemeResolver::class);
        $this->themeService = app(ThemeService::class);
        $this->publishingService = app(ThemePublishingService::class);
        $this->applicationService = app(ThemeApplicationService::class);
        $this->portfolioService = app(PortfolioService::class);
        $this->previewService = app(PortfolioPreviewService::class);

        $this->superAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        $this->owner = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);
    }

    private function createPublishedPortfolio(string $slug): Portfolio
    {
        $portfolio = $this->portfolioService->create(
            $this->owner,
            [
                'name' => "Portfolio {$slug}",
                'slug' => $slug,
                'description' => 'Themed public portfolio.',
            ],
            [
                'full_name' => "Author {$slug}",
                'professional_title' => 'Engineer',
                'email' => "{$slug}@example.com",
                'bio' => 'Public bio content.',
            ],
            [
                ['label' => 'Home', 'section_key' => 'home', 'sort_order' => 1, 'is_visible' => true],
            ],
            [['name' => 'Laravel']],
            [[
                'title' => "Project {$slug}",
                'description' => 'Visible project.',
                'technologies' => ['PHP'],
                'is_visible' => true,
                'sort_order' => 1,
            ]]
        );

        return $this->portfolioService->publish($portfolio);
    }

    public function test_unthemed_and_registered_themes_resolve_to_trusted_views(): void
    {
        $portfolio = $this->createPublishedPortfolio('unthemed-' . uniqid());
        $this->assertNull($portfolio->theme_id);
        $this->assertSame(PortfolioThemeResolver::DEFAULT_VIEW, $this->resolver->resolveView($portfolio));
        $this->assertSame(PortfolioThemeResolver::DEFAULT_KEY, $this->resolver->resolveKey($portfolio));

        // Registered 'default' theme key resolves via fixed allowlist
        $defaultTheme = $this->themeService->create([
            'name' => 'Default Theme',
            'slug' => 'default-theme-' . uniqid(),
            'theme_key' => 'default',
            'version' => '1.0.0',
        ], $this->superAdmin);
        $publishedDefault = $this->publishingService->publish($defaultTheme, $this->superAdmin);
        $this->applicationService->apply($publishedDefault, $portfolio, $this->owner);

        $fresh = $portfolio->fresh('theme');
        $this->assertSame(PortfolioThemeResolver::DEFAULT_VIEW, $this->resolver->resolveView($fresh));
        $this->assertTrue($this->resolver->isRegisteredKey('default'));

        // Custom static registry maps an approved key strictly to a known view (never dynamic DB paths)
        $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'portfolio_theme_test_' . uniqid();
        mkdir($tempDir, 0777, true);
        file_put_contents($tempDir . DIRECTORY_SEPARATOR . 'registered-preview.blade.php', '<div>Custom Trusted Theme: {{ $portfolio->name }}</div>');
        View::addNamespace('testthemes', $tempDir);

        try {
            $customResolver = new PortfolioThemeResolver([
                'default' => PortfolioThemeResolver::DEFAULT_VIEW,
                'editorial_pro' => 'testthemes::registered-preview',
            ]);

            $customTheme = new Theme(['theme_key' => 'editorial_pro', 'status' => ThemeStatus::Published]);
            $portfolio->setRelation('theme', $customTheme);

            $this->assertSame('testthemes::registered-preview', $customResolver->resolveView($portfolio));
            $this->assertSame('editorial_pro', $customResolver->resolveKey($portfolio));
        } finally {
            @unlink($tempDir . DIRECTORY_SEPARATOR . 'registered-preview.blade.php');
            @rmdir($tempDir);
        }
    }

    public function test_unknown_or_arbitrary_database_view_paths_safely_fall_back_to_default(): void
    {
        $portfolio = $this->createPublishedPortfolio('safe-fallback-' . uniqid());

        foreach ([
            'unknown_theme_key',
            'admin.users.index',
            'auth.login',
            '../../etc/passwd',
            'landing.index',
        ] as $arbitraryKey) {
            $theme = new Theme([
                'name' => 'Untrusted Theme',
                'slug' => 'untrusted-' . uniqid(),
                'theme_key' => $arbitraryKey,
                'status' => ThemeStatus::Published,
            ]);
            $portfolio->setRelation('theme', $theme);

            $this->assertFalse($this->resolver->isRegisteredKey($arbitraryKey));
            $this->assertSame(PortfolioThemeResolver::DEFAULT_VIEW, $this->resolver->resolveView($portfolio));
            $this->assertSame(PortfolioThemeResolver::DEFAULT_KEY, $this->resolver->resolveKey($portfolio));
        }

        // If a registry entry points to a missing view file, resolver safely falls back to default
        $missingViewResolver = new PortfolioThemeResolver(['missing_key' => 'portfolio-themes.nonexistent']);
        $portfolio->setRelation('theme', new Theme(['theme_key' => 'missing_key']));
        $this->assertSame(PortfolioThemeResolver::DEFAULT_VIEW, $missingViewResolver->resolveView($portfolio));
    }

    public function test_assigned_published_unpublished_and_archived_themes_render_via_subdomain_and_legacy_slug(): void
    {
        $slug = 'themed-render-' . uniqid();
        $portfolio = $this->createPublishedPortfolio($slug);
        $domain = $portfolio->primaryDomain->domain;

        $theme = $this->themeService->create([
            'name' => 'Active Theme',
            'slug' => 'active-theme-' . uniqid(),
            'theme_key' => 'default',
            'version' => '1.0.0',
        ], $this->superAdmin);
        $publishedTheme = $this->publishingService->publish($theme, $this->superAdmin);
        $this->applicationService->apply($publishedTheme, $portfolio, $this->owner);

        // Published assigned theme renders via both subdomain and legacy slug
        $this->get("http://{$domain}/")
            ->assertOk()
            ->assertViewIs('portfolio.show')
            ->assertSee("Author {$slug}")
            ->assertSee("Project {$slug}");

        $this->get("http://localhost/{$slug}")
            ->assertOk()
            ->assertViewIs('portfolio.show')
            ->assertSee("Author {$slug}");

        // Unpublished theme already assigned still renders cleanly
        $this->publishingService->unpublish($publishedTheme, $this->superAdmin);
        $this->get("http://{$domain}/")->assertOk()->assertSee("Author {$slug}");

        // Archived theme already assigned still renders cleanly
        $this->publishingService->archive($publishedTheme->fresh(), $this->superAdmin);
        $this->get("http://{$domain}/")->assertOk()->assertSee("Author {$slug}");
        $this->get("http://localhost/{$slug}")->assertOk()->assertSee("Author {$slug}");

        // Rendering must not alter portfolio or theme records
        $this->assertSame($publishedTheme->id, $portfolio->fresh()->theme_id);
        $this->assertSame(ThemeStatus::Archived, $publishedTheme->fresh()->status);
    }

    public function test_draft_preview_and_signed_preview_token_preserve_theme_resolution_and_security_headers(): void
    {
        $slug = 'draft-themed-' . uniqid();
        $draftPortfolio = $this->portfolioService->create(
            $this->owner,
            ['name' => "Draft {$slug}", 'slug' => $slug],
            ['full_name' => "Draft Author {$slug}"],
            [['label' => 'Home', 'section_key' => 'home', 'sort_order' => 1, 'is_visible' => true]]
        );

        $theme = $this->themeService->create([
            'name' => 'Preview Theme',
            'slug' => 'preview-theme-' . uniqid(),
            'theme_key' => 'unknown_future_key',
            'version' => '1.0.0',
        ], $this->superAdmin);
        $publishedTheme = $this->publishingService->publish($theme, $this->superAdmin);
        $this->applicationService->apply($publishedTheme, $draftPortfolio, $this->owner);

        $domain = $draftPortfolio->primaryDomain->domain;

        // Public guest cannot view draft
        $this->get("http://{$domain}/")->assertNotFound();

        // Signed preview URL resolves safely with preview banner and no-referrer/no-store headers
        $signedUrl = $this->previewService->generatePreviewUrl($draftPortfolio, $this->owner);
        $previewResponse = $this->get($signedUrl);

        $previewResponse->assertOk()
            ->assertViewIs('portfolio.show')
            ->assertSee('Admin Preview Mode')
            ->assertSee("Draft Author {$slug}")
            ->assertHeader('Referrer-Policy', 'no-referrer')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }
}
