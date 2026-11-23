<?php

namespace Tests\Feature;

use App\Models\Portfolio;
use App\Models\PortfolioNavItem;
use App\Models\PortfolioProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PortfolioListingTest extends TestCase
{
    use DatabaseTransactions;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::firstOrCreate(
            ['email' => 'admin@portfoliocms.test'],
            [
                'name' => 'Super Admin Test',
                'password' => bcrypt('Admin@123456'),
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );
    }

    private function createPortfolio(array $portfolioAttrs = [], array $profileAttrs = [], int $navItemsCount = 1): Portfolio
    {
        $uniqueId = uniqid();

        $portfolio = Portfolio::create(array_merge([
            'user_id' => $this->superAdmin->id,
            'name' => 'Portfolio ' . $uniqueId,
            'slug' => 'portfolio-' . $uniqueId,
            'description' => 'A luxury portfolio description.',
            'status' => 'draft',
        ], $portfolioAttrs));

        PortfolioProfile::create(array_merge([
            'portfolio_id' => $portfolio->id,
            'full_name' => 'Client ' . $uniqueId,
            'professional_title' => 'Design Director',
            'email' => 'client.' . $uniqueId . '@example.test',
            'location' => 'Tokyo, Japan',
            'bio' => 'Sample bio text for portfolio owner.',
        ], $profileAttrs));

        for ($i = 1; $i <= $navItemsCount; $i++) {
            PortfolioNavItem::create([
                'portfolio_id' => $portfolio->id,
                'label' => $i === 1 ? 'Home' : 'Section ' . $i,
                'section_key' => $i === 1 ? 'home' : 'sec_' . $i,
                'url' => null,
                'sort_order' => $i,
                'is_visible' => true,
            ]);
        }

        return $portfolio;
    }

    public function test_guest_is_redirected_to_login_from_profiles_portfolios_and_dashboard(): void
    {
        $this->get('/admin/profiles')->assertStatus(302)->assertRedirect('/admin/login');
        $this->get('/admin/portfolios')->assertStatus(302)->assertRedirect('/admin/login');
        $this->get('/admin/dashboard')->assertStatus(302)->assertRedirect('/admin/login');
    }

    public function test_inactive_super_admin_is_forbidden_from_profiles_list(): void
    {
        $inactiveAdmin = User::create([
            'name' => 'Inactive Super Admin',
            'email' => 'inactive.' . uniqid() . '@portfoliocms.test',
            'password' => bcrypt('Admin@123456'),
            'role' => 'super_admin',
            'is_active' => false,
        ]);

        $response = $this->actingAs($inactiveAdmin)->get(route('admin.profiles.index'));
        $response->assertStatus(403);
    }

    public function test_active_super_admin_can_view_profiles_and_portfolios_listing(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.profiles.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.portfolios.index');
        $response->assertViewHas(['portfolios', 'stats']);

        // Portfolios alias route also renders the same listing
        $responseAlias = $this->actingAs($this->superAdmin)->get(route('admin.portfolios.index'));
        $responseAlias->assertStatus(200);
        $responseAlias->assertViewIs('admin.portfolios.index');
    }

    public function test_newly_created_portfolio_appears_immediately_at_top_of_profiles_list(): void
    {
        $uniqueSlug = 'instant-showcase-' . uniqid();

        $payload = [
            'name' => 'Instant Showcase Studio',
            'slug' => $uniqueSlug,
            'description' => 'Instantly created portfolio.',
            'status' => 'draft',
            'full_name' => 'Tadao Ando',
            'professional_title' => 'Minimalist Architect',
            'email' => 'tadao@ando.design',
            'location' => 'Osaka, Japan',
            'bio' => 'Concrete and natural light.',
            'nav_items' => [
                [
                    'label' => 'Home',
                    'type' => 'section',
                    'section_key' => 'home',
                    'sort_order' => 1,
                    'is_visible' => true,
                ],
            ],
        ];

        // 1. Submit creation
        $createResponse = $this->actingAs($this->superAdmin)->post('/admin/portfolios', $payload);
        $createResponse->assertStatus(302);
        $createResponse->assertRedirect(route('admin.profiles.index'));

        // 2. Follow redirect to Profiles List
        $listResponse = $this->actingAs($this->superAdmin)->get(route('admin.profiles.index'));
        $listResponse->assertStatus(200);
        $listResponse->assertSee('Instant Showcase Studio');
        $listResponse->assertSee('Tadao Ando');
        $listResponse->assertSee('Minimalist Architect');
        $listResponse->assertSee('/' . $uniqueSlug);

        // Verify it is the very first portfolio in the collection
        $firstPortfolio = $listResponse->viewData('portfolios')->first();
        $this->assertEquals($uniqueSlug, $firstPortfolio->slug);
    }

    public function test_listing_displays_portfolio_details_and_owner_profile_info(): void
    {
        $portfolio = $this->createPortfolio(
            ['name' => 'Komorebi Architecture Studio', 'slug' => 'komorebi-studio-' . uniqid(), 'status' => 'published'],
            ['full_name' => 'Kenzo Kuma', 'professional_title' => 'Master Architect'],
            3
        );

        $response = $this->actingAs($this->superAdmin)->get(route('admin.profiles.index'));

        $response->assertStatus(200);
        $response->assertSee('Komorebi Architecture Studio');
        $response->assertSee('Kenzo Kuma');
        $response->assertSee('Master Architect');
        $response->assertSee('/' . $portfolio->slug);
        $response->assertSee('3 Links');
    }

    public function test_listing_includes_manage_and_view_details_actions(): void
    {
        $portfolio = $this->createPortfolio(
            ['name' => 'Nordic Atelier', 'slug' => 'nordic-atelier-' . uniqid()],
            ['full_name' => 'Soren Kierkegaard', 'professional_title' => 'Design Philosopher']
        );

        $response = $this->actingAs($this->superAdmin)->get(route('admin.profiles.index', ['search' => 'Nordic Atelier']));

        $response->assertStatus(200);
        // Manage navigation link
        $response->assertSee(route('admin.portfolios.nav-items.index', $portfolio));
        $response->assertSee('Manage');
        // View Details link
        $response->assertSee(url('/' . $portfolio->slug));
        $response->assertSee('View Details');
    }

    public function test_listing_eager_loads_relationships_without_n_plus_one_queries(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->createPortfolio();
        }

        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->actingAs($this->superAdmin)->get(route('admin.profiles.index'));

        $response->assertStatus(200);

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Eager loading ensures exactly bounded queries:
        // 1 count for pagination, 1 for portfolios, 1 for profiles, 1 for users, plus stats counts
        $this->assertLessThanOrEqual(10, count($queries), 'Query count should not exceed baseline for eager loaded listing.');
    }

    public function test_search_filters_by_portfolio_name_or_slug(): void
    {
        $slugA = 'monolith-' . uniqid();
        $slugB = 'zenith-' . uniqid();

        $portfolioA = $this->createPortfolio(['name' => 'Monolith Studios', 'slug' => $slugA]);
        $portfolioB = $this->createPortfolio(['name' => 'Zenith Collective', 'slug' => $slugB]);

        // Search by name
        $responseName = $this->actingAs($this->superAdmin)->get(route('admin.profiles.index', ['search' => 'Monolith Studios']));
        $responseName->assertStatus(200);
        $responseName->assertSee('Monolith Studios');
        $responseName->assertDontSee('Zenith Collective');

        // Search by slug
        $responseSlug = $this->actingAs($this->superAdmin)->get(route('admin.profiles.index', ['search' => $slugB]));
        $responseSlug->assertStatus(200);
        $responseSlug->assertSee('Zenith Collective');
        $responseSlug->assertDontSee('Monolith Studios');
    }

    public function test_search_filters_by_owner_profile_name_email_and_title(): void
    {
        $uniqueA = uniqid();
        $uniqueB = uniqid();

        $portfolioA = $this->createPortfolio(
            ['name' => 'Alpha ' . $uniqueA],
            [
                'full_name' => 'Elena Rostova',
                'professional_title' => 'Principal UX Strategist',
                'email' => 'elena.' . $uniqueA . '@rostova.design',
            ]
        );

        $portfolioB = $this->createPortfolio(
            ['name' => 'Beta ' . $uniqueB],
            [
                'full_name' => 'Julian Alvarez',
                'professional_title' => 'Computational Sculptor',
                'email' => 'julian.' . $uniqueB . '@sculpt.art',
            ]
        );

        // Search by profile full name
        $resName = $this->actingAs($this->superAdmin)->get(route('admin.profiles.index', ['search' => 'Elena Rostova']));
        $resName->assertSee('Elena Rostova');
        $resName->assertDontSee('Julian Alvarez');

        // Search by profile professional title
        $resTitle = $this->actingAs($this->superAdmin)->get(route('admin.profiles.index', ['search' => 'Computational Sculptor']));
        $resTitle->assertSee('Julian Alvarez');
        $resTitle->assertDontSee('Elena Rostova');

        // Search by profile email
        $resEmail = $this->actingAs($this->superAdmin)->get(route('admin.profiles.index', ['search' => 'elena.' . $uniqueA]));
        $resEmail->assertSee('Elena Rostova');
        $resEmail->assertDontSee('Julian Alvarez');
    }

    public function test_filter_by_status_published_and_draft(): void
    {
        $unique = uniqid();
        $published = $this->createPortfolio(['name' => 'Pub Co ' . $unique, 'status' => 'published']);
        $draft = $this->createPortfolio(['name' => 'Draft Co ' . $unique, 'status' => 'draft']);

        // Filter Published
        $resPub = $this->actingAs($this->superAdmin)->get(route('admin.profiles.index', ['status' => 'published', 'search' => $unique]));
        $resPub->assertStatus(200);
        $resPub->assertSee('Pub Co ' . $unique);
        $resPub->assertDontSee('Draft Co ' . $unique);

        // Filter Draft
        $resDraft = $this->actingAs($this->superAdmin)->get(route('admin.profiles.index', ['status' => 'draft', 'search' => $unique]));
        $resDraft->assertStatus(200);
        $resDraft->assertSee('Draft Co ' . $unique);
        $resDraft->assertDontSee('Pub Co ' . $unique);
    }

    public function test_pagination_splits_records_at_ten_per_page(): void
    {
        $uniqueTag = 'pagebatch_' . uniqid();

        for ($i = 1; $i <= 15; $i++) {
            $this->createPortfolio(['name' => "Batch Item {$i} {$uniqueTag}"]);
        }

        // Page 1
        $resPage1 = $this->actingAs($this->superAdmin)->get(route('admin.profiles.index', ['search' => $uniqueTag]));
        $resPage1->assertStatus(200);
        $portfoliosPage1 = $resPage1->viewData('portfolios');
        $this->assertEquals(15, $portfoliosPage1->total());
        $this->assertEquals(10, $portfoliosPage1->count());
        $this->assertEquals(1, $portfoliosPage1->currentPage());

        // Page 2
        $resPage2 = $this->actingAs($this->superAdmin)->get(route('admin.profiles.index', ['search' => $uniqueTag, 'page' => 2]));
        $resPage2->assertStatus(200);
        $portfoliosPage2 = $resPage2->viewData('portfolios');
        $this->assertEquals(15, $portfoliosPage2->total());
        $this->assertEquals(5, $portfoliosPage2->count());
        $this->assertEquals(2, $portfoliosPage2->currentPage());
    }

    public function test_empty_state_rendered_when_no_portfolios_match(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.profiles.index', [
            'search' => 'definitely_does_not_exist_' . uniqid(),
        ]));

        $response->assertStatus(200);
        $response->assertSee('No portfolios found');
        $response->assertSee('Clear Filters');
        $response->assertSee(route('admin.portfolios.create'));
    }

    public function test_super_admin_can_view_portfolio_navigation_management_page(): void
    {
        $portfolio = $this->createPortfolio(
            ['name' => 'Architecture Navigation Showcase'],
            ['full_name' => 'Mies van der Rohe'],
            3
        );

        $response = $this->actingAs($this->superAdmin)
            ->get(route('admin.portfolios.nav-items.index', $portfolio));

        $response->assertStatus(200);
        $response->assertViewIs('admin.portfolios.nav-items.index');
        $response->assertSee('Navigation Management');
        $response->assertSee('Architecture Navigation Showcase');
        $response->assertSee('#home');
    }
}
