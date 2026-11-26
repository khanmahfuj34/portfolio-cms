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
use Tests\TestCase;

class RifahModernThemeTest extends TestCase
{
    use DatabaseTransactions;

    private ThemeService $themeService;
    private ThemePublishingService $publishingService;
    private ThemeApplicationService $applicationService;
    private PortfolioService $portfolioService;
    private PortfolioPreviewService $previewService;
    private PortfolioThemeResolver $resolver;

    private User $superAdmin;
    private User $ownerA;
    private User $ownerB;
    private Theme $publishedRifahTheme;

    protected function setUp(): void
    {
        parent::setUp();

        $this->themeService = app(ThemeService::class);
        $this->publishingService = app(ThemePublishingService::class);
        $this->applicationService = app(ThemeApplicationService::class);
        $this->portfolioService = app(PortfolioService::class);
        $this->previewService = app(PortfolioPreviewService::class);
        $this->resolver = app(PortfolioThemeResolver::class);

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

        $existing = Theme::where('theme_key', PortfolioThemeResolver::RIFAH_MODERN_KEY)->first();
        if ($existing) {
            $this->publishedRifahTheme = $existing->status === ThemeStatus::Published
                ? $existing
                : $this->publishingService->publish($existing, $this->superAdmin);
        } else {
            $draft = $this->themeService->create([
                'name' => 'Rifah Modern',
                'slug' => 'rifah-modern',
                'theme_key' => 'rifah-modern',
                'description' => 'Modern animated developer portfolio theme',
                'category' => 'Developer Portfolio',
                'version' => '1.0.0',
            ], $this->superAdmin);
            $this->publishedRifahTheme = $this->publishingService->publish($draft, $this->superAdmin);
        }
    }

    public function test_resolver_registers_rifah_modern_view(): void
    {
        $this->assertTrue($this->resolver->isRegisteredKey('rifah-modern'));

        $portfolio = new Portfolio(['name' => 'Test']);
        $portfolio->setRelation('theme', $this->publishedRifahTheme);

        $this->assertSame('portfolio-themes.rifah-modern.index', $this->resolver->resolveView($portfolio));
    }

    public function test_complete_portfolio_and_multiple_projects_render_with_subdomain_and_dark_mode_toggle(): void
    {
        $slug = 'full-dev-' . uniqid();
        $portfolio = $this->portfolioService->create(
            $this->ownerA,
            [
                'name' => 'Dev Workbench',
                'slug' => $slug,
                'description' => 'Production systems engineering showcase.',
            ],
            [
                'full_name' => 'Nabil Hasan',
                'professional_title' => 'Systems Engineer',
                'email' => 'nabil@example.test',
                'location' => 'Dhaka',
                'bio' => 'Building distributed Laravel and Rust services.',
            ],
            [
                ['label' => 'Home', 'section_key' => 'home', 'sort_order' => 1, 'is_visible' => true],
                ['label' => 'About', 'section_key' => 'about', 'sort_order' => 2, 'is_visible' => true],
                ['label' => 'Skills', 'section_key' => 'skills', 'sort_order' => 3, 'is_visible' => true],
                ['label' => 'Experience', 'section_key' => 'experience', 'sort_order' => 4, 'is_visible' => true],
                ['label' => 'Education', 'section_key' => 'education', 'sort_order' => 5, 'is_visible' => true],
                ['label' => 'Projects', 'section_key' => 'projects', 'sort_order' => 6, 'is_visible' => true],
                ['label' => 'Contact', 'section_key' => 'contact', 'sort_order' => 7, 'is_visible' => true],
            ],
            [['name' => 'Laravel'], ['name' => 'PostgreSQL'], ['name' => 'TypeScript']],
            [
                [
                    'title' => 'Queue Mesh',
                    'description' => 'High-throughput job orchestrator.',
                    'technologies' => ['Laravel', 'Redis'],
                    'live_url' => 'https://queuemesh.example.test',
                    'github_url' => 'https://github.com/example/queuemesh',
                    'is_featured' => true,
                    'is_visible' => true,
                    'sort_order' => 1,
                ],
                [
                    'title' => 'Schema Guard',
                    'description' => 'Automated migration linter.',
                    'technologies' => ['PHP', 'MySQL'],
                    'is_featured' => false,
                    'is_visible' => true,
                    'sort_order' => 2,
                ],
            ]
        );
        $portfolio->profile->update(['profile_photo_path' => '/images/avatars/nabil.png']);
        $published = $this->portfolioService->publish($portfolio);
        $this->applicationService->apply($this->publishedRifahTheme, $published, $this->ownerA);

        $domain = $published->primaryDomain->domain;
        $response = $this->get("http://{$domain}/");

        $response->assertOk()
            ->assertViewIs('portfolio-themes.rifah-modern.index')
            ->assertSee('Nabil Hasan')
            ->assertSee('Systems Engineer')
            ->assertSee('Building distributed Laravel and Rust services.')
            ->assertSee('/images/avatars/nabil.png', false)
            ->assertSee('Laravel')
            ->assertSee('Queue Mesh')
            ->assertSee('Schema Guard')
            ->assertSee('https://queuemesh.example.test', false)
            ->assertSee('nabil@example.test')
            ->assertSee('No experience entries have been added')
            ->assertSee('No education records have been added')
            ->assertSee('data-rm-theme-toggle', false);
    }

    public function test_graceful_fallbacks_for_missing_avatar_projects_skills_contact_and_isolation_across_owners(): void
    {
        $slugA = 'minimal-a-' . uniqid();
        $portfolioA = $this->portfolioService->create(
            $this->ownerA,
            ['name' => 'Minimal Alpha', 'slug' => $slugA],
            ['full_name' => 'Tariq Rahman', 'bio' => ''],
            [
                ['label' => 'Home', 'section_key' => 'home', 'sort_order' => 1, 'is_visible' => true],
                ['label' => 'Contact', 'section_key' => 'contact', 'sort_order' => 2, 'is_visible' => true],
            ],
            [],
            []
        );
        $publishedA = $this->portfolioService->publish($portfolioA);
        $this->applicationService->apply($this->publishedRifahTheme, $publishedA, $this->ownerA);

        $resA = $this->get("http://{$publishedA->primaryDomain->domain}/");
        $resA->assertOk()
            ->assertViewIs('portfolio-themes.rifah-modern.index')
            ->assertSee('Tariq Rahman')
            ->assertSee('>TR<', false)
            ->assertSee('No contact details have been added yet.')
            ->assertDontSee('<img src=', false);

        // Owner B using same theme remains completely isolated
        $slugB = 'minimal-b-' . uniqid();
        $portfolioB = $this->portfolioService->create(
            $this->ownerB,
            ['name' => 'Studio Beta', 'slug' => $slugB],
            ['full_name' => 'Sadia Karim', 'email' => 'sadia@example.test'],
            [['label' => 'Home', 'section_key' => 'home', 'sort_order' => 1, 'is_visible' => true]],
            [['name' => 'Figma']],
            []
        );
        $publishedB = $this->portfolioService->publish($portfolioB);
        $this->applicationService->apply($this->publishedRifahTheme, $publishedB, $this->ownerB);

        $resB = $this->get("http://{$publishedB->primaryDomain->domain}/");
        $resB->assertOk()
            ->assertSee('Sadia Karim')
            ->assertSee('Figma')
            ->assertDontSee('Tariq Rahman');
    }

    public function test_special_characters_long_text_xss_safety_draft_preview_and_default_fallback(): void
    {
        $slug = 'xss-test-' . uniqid();
        $longBio = str_repeat('Scalable microservice architecture and developer tooling. ', 12);

        $draftPortfolio = $this->portfolioService->create(
            $this->ownerA,
            ['name' => 'Security "Test" <Site>', 'slug' => $slug],
            [
                'full_name' => 'Amina <script>alert(1)</script>',
                'professional_title' => 'Security & Cloud Architect',
                'bio' => $longBio,
            ],
            [['label' => 'Home', 'section_key' => 'home', 'sort_order' => 1, 'is_visible' => true]],
            [['name' => 'C++ & Rust']],
            [[
                'title' => 'Safe <img src=x onerror=alert(1)>',
                'description' => str_repeat('Long project details. ', 20),
                'live_url' => null,
                'is_visible' => true,
                'sort_order' => 1,
            ]]
        );
        $this->applicationService->apply($this->publishedRifahTheme, $draftPortfolio, $this->ownerA);

        $signedUrl = $this->previewService->generatePreviewUrl($draftPortfolio, $this->ownerA);
        $previewRes = $this->get($signedUrl);

        $previewRes->assertOk()
            ->assertViewIs('portfolio-themes.rifah-modern.index')
            ->assertSee('Admin Preview Mode')
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<img src=x onerror=alert(1)>', false)
            ->assertHeader('Referrer-Policy', 'no-referrer')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');

        // Default unthemed portfolio continues using portfolio.show
        $unthemed = $this->portfolioService->publish($this->portfolioService->create(
            $this->ownerB,
            ['name' => 'Default Theme Site', 'slug' => 'default-site-' . uniqid()],
            ['full_name' => 'Kamal Uddin'],
            [['label' => 'Home', 'section_key' => 'home', 'sort_order' => 1, 'is_visible' => true]]
        ));
        $this->get("http://{$unthemed->primaryDomain->domain}/")
            ->assertOk()
            ->assertViewIs('portfolio.show');
    }

    public function test_theme_source_files_contain_no_reference_personal_identifying_content(): void
    {
        $themeFiles = array_merge(
            glob(resource_path('views/portfolio-themes/rifah-modern/*.blade.php')) ?: [],
            glob(resource_path('views/portfolio-themes/rifah-modern/partials/*.blade.php')) ?: [],
            [
                resource_path('css/portfolio-themes/rifah-modern.css'),
                resource_path('js/portfolio-themes/rifah-modern.js'),
            ]
        );

        $forbiddenStrings = [
            'Rifah Jahan',
            'Tamanna',
            'ISFJ',
            'rifah1119',
            '1880683098',
            'VocabNex',
            'DevPulse',
            'resume.pdf',
            'photo.jpg',
        ];

        $this->assertNotEmpty($themeFiles);

        foreach ($themeFiles as $file) {
            $content = (string) file_get_contents($file);
            foreach ($forbiddenStrings as $forbidden) {
                $this->assertStringNotContainsStringIgnoringCase(
                    $forbidden,
                    $content,
                    "Forbidden reference personal string '{$forbidden}' found in {$file}"
                );
            }
        }
    }
}
