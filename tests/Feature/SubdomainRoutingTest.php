<?php

namespace Tests\Feature;

use App\Models\Portfolio;
use App\Models\PortfolioDomain;
use App\Models\User;
use App\Services\PortfolioDomainService;
use App\Services\PortfolioService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use InvalidArgumentException;
use Tests\TestCase;

class SubdomainRoutingTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private PortfolioService $portfolioService;
    private PortfolioDomainService $domainService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $this->portfolioService = app(PortfolioService::class);
        $this->domainService = app(PortfolioDomainService::class);
    }

    private function createPublishedPortfolio(string $slug, string $fullName, array $skills = [], array $projects = []): Portfolio
    {
        $portfolio = $this->portfolioService->create(
            $this->admin,
            [
                'name' => "{$fullName} Studio",
                'slug' => $slug,
                'description' => "Portfolio for {$fullName}",
            ],
            [
                'full_name' => $fullName,
                'professional_title' => 'Software Engineer',
                'email' => "{$slug}@example.test",
                'bio' => "Engineering profile for {$fullName}.",
            ],
            [
                [
                    'label' => 'Home',
                    'section_key' => 'home',
                    'sort_order' => 1,
                    'is_visible' => true,
                ],
                [
                    'label' => 'Projects',
                    'section_key' => 'projects',
                    'sort_order' => 2,
                    'is_visible' => true,
                ],
                [
                    'label' => 'Skills',
                    'section_key' => 'skills',
                    'sort_order' => 3,
                    'is_visible' => true,
                ],
            ],
            $skills,
            $projects
        );

        return $this->portfolioService->publish($portfolio);
    }

    public function test_admin_dashboard_works_on_localhost_and_is_blocked_on_subdomains(): void
    {
        $this->actingAs($this->admin, 'super_admin')
            ->get('http://localhost/admin/dashboard')
            ->assertStatus(200)
            ->assertSee('Dashboard');

        $this->actingAs($this->admin, 'super_admin')
            ->get('http://mahfuj.localhost/admin/dashboard')
            ->assertStatus(404);
    }

    public function test_mahfuj_subdomain_resolves_to_correct_portfolio_with_all_sections(): void
    {
        $slug = 'mahfuj-sub-' . uniqid();
        $portfolio = $this->createPublishedPortfolio(
            $slug,
            'Mahfuj Al Hossain',
            [['name' => 'Laravel'], ['name' => 'MySQL']],
            [[
                'title' => 'Mahfuj Cloud Engine',
                'description' => 'Distributed deployment engine.',
                'technologies' => ['PHP', 'Docker'],
                'live_url' => 'https://mahfuj.example.com',
                'github_url' => 'https://github.com/example/mahfuj',
                'is_visible' => true,
                'sort_order' => 1,
            ]]
        );

        $domain = $portfolio->primaryDomain;
        $this->assertNotNull($domain);
        $this->assertSame("{$slug}.localhost", $domain->domain);

        $response = $this->get("http://{$domain->domain}/");

        $response->assertStatus(200);
        $response->assertSee('Mahfuj Al Hossain');
        $response->assertSee('Laravel');
        $response->assertSee('MySQL');
        $response->assertSee('Mahfuj Cloud Engine');
        $response->assertSee('https://mahfuj.example.com');
    }

    public function test_different_subdomains_resolve_to_different_portfolios_without_leakage(): void
    {
        $suffix = uniqid();
        $mahfujSlug = "mahfuj-{$suffix}";
        $rahimSlug = "rahim-{$suffix}";

        $this->createPublishedPortfolio($mahfujSlug, 'Mahfuj Owner', [['name' => 'RustLang']]);
        $this->createPublishedPortfolio($rahimSlug, 'Rahim Owner', [['name' => 'GoLang']]);

        $resMahfuj = $this->get("http://{$mahfujSlug}.localhost/");
        $resMahfuj->assertStatus(200);
        $resMahfuj->assertSee('Mahfuj Owner');
        $resMahfuj->assertSee('RustLang');
        $resMahfuj->assertDontSee('Rahim Owner');
        $resMahfuj->assertDontSee('GoLang');

        $resRahim = $this->get("http://{$rahimSlug}.localhost/");
        $resRahim->assertStatus(200);
        $resRahim->assertSee('Rahim Owner');
        $resRahim->assertSee('GoLang');
        $resRahim->assertDontSee('Mahfuj Owner');
        $resRahim->assertDontSee('RustLang');
    }

    public function test_unknown_and_reserved_subdomains_return_404(): void
    {
        $this->get('http://definitely-unknown-subdomain-999.localhost/')->assertStatus(404);

        foreach (['admin', 'api', 'www', 'login', 'dashboard', 'storage'] as $reserved) {
            $this->get("http://{$reserved}.localhost/")->assertStatus(404);
        }
    }

    public function test_draft_portfolio_is_not_publicly_accessible_via_subdomain(): void
    {
        $slug = 'draft-sub-' . uniqid();
        $this->portfolioService->create(
            $this->admin,
            ['name' => 'Draft Subdomain', 'slug' => $slug],
            ['full_name' => 'Draft Owner'],
            [['label' => 'Home', 'section_key' => 'home', 'sort_order' => 1, 'is_visible' => true]]
        );

        $this->get("http://{$slug}.localhost/")->assertStatus(404);
    }

    public function test_future_published_at_portfolio_is_not_publicly_accessible_via_subdomain(): void
    {
        $slug = 'future-sub-' . uniqid();
        $portfolio = $this->portfolioService->create(
            $this->admin,
            ['name' => 'Future Subdomain', 'slug' => $slug],
            ['full_name' => 'Future Owner'],
            [['label' => 'Home', 'section_key' => 'home', 'sort_order' => 1, 'is_visible' => true]]
        );

        $portfolio->update([
            'status' => 'published',
            'published_at' => now()->addDay(),
        ]);

        $this->get("http://{$slug}.localhost/")->assertStatus(404);
    }

    public function test_unpublished_portfolio_is_not_publicly_accessible_via_subdomain(): void
    {
        $slug = 'unpub-sub-' . uniqid();
        $portfolio = $this->createPublishedPortfolio($slug, 'Unpublish Owner');

        $this->get("http://{$slug}.localhost/")->assertStatus(200);

        $this->portfolioService->unpublish($portfolio);

        $this->get("http://{$slug}.localhost/")->assertStatus(404);
    }

    public function test_duplicate_domain_creation_is_rejected_and_primary_domain_is_exclusive(): void
    {
        $slugA = 'dup-a-' . uniqid();
        $slugB = 'dup-b-' . uniqid();

        $portfolioA = $this->createPublishedPortfolio($slugA, 'Owner A');
        $portfolioB = $this->createPublishedPortfolio($slugB, 'Owner B');

        // Duplicate domain rejected across portfolios
        $this->expectException(InvalidArgumentException::class);
        $this->domainService->attachDomain(
            $portfolioB,
            "{$slugA}.localhost",
            PortfolioDomain::TYPE_SUBDOMAIN,
            true,
            true
        );
    }

    public function test_setting_new_primary_domain_demotes_previous_primary_atomically(): void
    {
        $slug = 'primary-switch-' . uniqid();
        $portfolio = $this->createPublishedPortfolio($slug, 'Primary Switch Owner');

        $firstDomain = $portfolio->primaryDomain;
        $this->assertNotNull($firstDomain);
        $this->assertTrue($firstDomain->is_primary);

        $customDomain = "custom-{$slug}.example.com";
        $secondDomain = $this->domainService->attachDomain(
            $portfolio,
            $customDomain,
            PortfolioDomain::TYPE_CUSTOM,
            true,
            true
        );

        $firstDomain->refresh();
        $secondDomain->refresh();

        $this->assertFalse($firstDomain->is_primary);
        $this->assertTrue($secondDomain->is_primary);
        $this->assertEquals(1, $portfolio->domains()->where('is_primary', true)->count());

        // Custom domain also resolves cleanly
        $this->get("http://{$customDomain}/")
            ->assertStatus(200)
            ->assertSee('Primary Switch Owner');
    }

    public function test_reserved_slug_is_rejected_in_creation_request_and_service(): void
    {
        $response = $this->actingAs($this->admin, 'super_admin')
            ->post('/admin/portfolios', [
                'name' => 'Admin Spoof Portfolio',
                'slug' => 'admin',
                'full_name' => 'Spoof User',
                'nav_items' => [
                    [
                        'label' => 'Home',
                        'type' => 'section',
                        'section_key' => 'home',
                        'sort_order' => 1,
                        'is_visible' => true,
                    ],
                ],
            ]);

        $response->assertSessionHasErrors(['slug']);
        $this->assertDatabaseMissing('portfolios', ['slug' => 'admin']);
        $this->assertDatabaseMissing('portfolio_domains', ['domain' => 'admin.localhost']);
    }

    public function test_failed_domain_creation_rolls_back_entire_portfolio_creation(): void
    {
        $slug = 'rollback-sub-' . uniqid();
        // Pre-occupy the target subdomain via another portfolio
        $existing = $this->createPublishedPortfolio('holder-' . uniqid(), 'Holder Owner');
        $this->domainService->attachDomain($existing, "{$slug}.localhost", PortfolioDomain::TYPE_SUBDOMAIN, false, true);

        $initialPortfolioCount = Portfolio::count();

        try {
            $this->portfolioService->create(
                $this->admin,
                ['name' => 'Rollback Portfolio', 'slug' => $slug],
                ['full_name' => 'Rollback Owner'],
                [['label' => 'Home', 'section_key' => 'home', 'sort_order' => 1, 'is_visible' => true]],
                [['name' => 'ShouldRollbackSkill']],
                [['title' => 'ShouldRollbackProject']]
            );
            $this->fail('Expected InvalidArgumentException when domain already exists.');
        } catch (InvalidArgumentException) {
            $this->assertEquals($initialPortfolioCount, Portfolio::count());
            $this->assertDatabaseMissing('portfolios', ['slug' => $slug]);
            $this->assertDatabaseMissing('portfolio_skills', ['name' => 'ShouldRollbackSkill']);
            $this->assertDatabaseMissing('portfolio_projects', ['title' => 'ShouldRollbackProject']);
        }
    }

    public function test_super_admin_preview_remains_functional_on_subdomain(): void
    {
        $slug = 'preview-sub-' . uniqid();
        $this->portfolioService->create(
            $this->admin,
            ['name' => 'Preview Subdomain', 'slug' => $slug],
            ['full_name' => 'Preview Owner'],
            [['label' => 'Home', 'section_key' => 'home', 'sort_order' => 1, 'is_visible' => true]]
        );

        // Guest blocked with 404
        $this->get("http://{$slug}.localhost/")->assertStatus(404);

        // Authenticated active Super Admin sees Admin Preview Mode
        $this->actingAs($this->admin, 'super_admin')
            ->get("http://{$slug}.localhost/")
            ->assertStatus(200)
            ->assertSee('Admin Preview Mode')
            ->assertSee('Preview Owner');
    }

    public function test_unverified_custom_domain_returns_404(): void
    {
        $slug = 'unverified-' . uniqid();
        $portfolio = $this->createPublishedPortfolio($slug, 'Unverified Domain Owner');
        $customHost = "pending-{$slug}.example.com";

        $this->domainService->attachDomain(
            $portfolio,
            $customHost,
            PortfolioDomain::TYPE_CUSTOM,
            false,
            false
        );

        $this->get("http://{$customHost}/")->assertStatus(404);
    }
}
