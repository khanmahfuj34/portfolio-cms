<?php

namespace Tests\Feature;

use App\Models\Portfolio;
use App\Models\PortfolioNavItem;
use App\Models\PortfolioProfile;
use App\Models\PortfolioProject;
use App\Models\PortfolioSkill;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PublicPortfolioTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role' => 'super_admin',
            'is_active' => true,
        ]);
    }

    public function test_guest_can_view_published_portfolio_with_profile_and_navbar(): void
    {
        $portfolio = Portfolio::create([
            'user_id' => $this->user->id,
            'name' => 'Alex Rivera Portfolio',
            'slug' => 'alex-rivera',
            'description' => 'A showcase of senior engineering and architecture work.',
            'status' => 'published',
            'published_at' => now()->subHour(),
        ]);

        PortfolioProfile::create([
            'portfolio_id' => $portfolio->id,
            'full_name' => 'Alex Rivera',
            'professional_title' => 'Principal Software Architect',
            'email' => 'alex@example.com',
            'location' => 'San Francisco, CA',
            'bio' => 'Building scalable systems with Laravel, distributed architecture and cloud.',
        ]);

        PortfolioNavItem::create([
            'portfolio_id' => $portfolio->id,
            'label' => 'Home',
            'section_key' => 'home',
            'url' => null,
            'sort_order' => 1,
            'is_visible' => true,
        ]);

        PortfolioNavItem::create([
            'portfolio_id' => $portfolio->id,
            'label' => 'About',
            'section_key' => 'about',
            'url' => null,
            'sort_order' => 2,
            'is_visible' => true,
        ]);

        PortfolioNavItem::create([
            'portfolio_id' => $portfolio->id,
            'label' => 'GitHub Profile',
            'section_key' => null,
            'url' => 'https://github.com/alexrivera',
            'sort_order' => 3,
            'is_visible' => true,
        ]);

        $response = $this->get('/alex-rivera');

        $response->assertStatus(200);
        $response->assertSee('Alex Rivera');
        $response->assertSee('Principal Software Architect');
        $response->assertSee('San Francisco, CA');
        $response->assertSee('alex@example.com');
        $response->assertSee('Building scalable systems');
        $response->assertSee('Home');
        $response->assertSee('About');
        $response->assertSee('GitHub Profile');
        $response->assertSee('#home');
        $response->assertSee('#about');
        $response->assertSee('https://github.com/alexrivera');
    }

    public function test_draft_portfolio_returns_404(): void
    {
        $portfolio = Portfolio::create([
            'user_id' => $this->user->id,
            'name' => 'Draft Portfolio',
            'slug' => 'draft-portfolio',
            'description' => 'Unpublished work in progress.',
            'status' => 'draft',
            'published_at' => null,
        ]);

        $response = $this->get('/draft-portfolio');

        $response->assertStatus(404);
    }

    public function test_published_portfolio_with_null_published_at_returns_404(): void
    {
        $portfolio = Portfolio::create([
            'user_id' => $this->user->id,
            'name' => 'Incomplete Published Portfolio',
            'slug' => 'incomplete-published',
            'description' => 'Invalid publication state.',
            'status' => 'published',
            'published_at' => null,
        ]);

        $response = $this->get('/incomplete-published');

        $response->assertStatus(404);
    }

    public function test_future_published_at_returns_404(): void
    {
        $portfolio = Portfolio::create([
            'user_id' => $this->user->id,
            'name' => 'Scheduled Portfolio',
            'slug' => 'scheduled-future',
            'description' => 'Scheduled for next week.',
            'status' => 'published',
            'published_at' => now()->addDays(7),
        ]);

        $response = $this->get('/scheduled-future');

        $response->assertStatus(404);
    }

    public function test_nonexistent_slug_returns_404(): void
    {
        $response = $this->get('/definitely-nonexistent-portfolio-slug-12345');

        $response->assertStatus(404);
    }

    public function test_reserved_slugs_return_404_from_public_handler(): void
    {
        $response = $this->get('/storage');

        $response->assertStatus(404);
    }

    public function test_hidden_navbar_items_are_not_rendered(): void
    {
        $portfolio = Portfolio::create([
            'user_id' => $this->user->id,
            'name' => 'Visibility Test Portfolio',
            'slug' => 'visibility-test',
            'status' => 'published',
            'published_at' => now()->subMinute(),
        ]);

        PortfolioNavItem::create([
            'portfolio_id' => $portfolio->id,
            'label' => 'Visible Home Link',
            'section_key' => 'home',
            'sort_order' => 1,
            'is_visible' => true,
        ]);

        PortfolioNavItem::create([
            'portfolio_id' => $portfolio->id,
            'label' => 'Secret Confidential Draft Section',
            'section_key' => 'secret',
            'sort_order' => 2,
            'is_visible' => false,
        ]);

        $response = $this->get('/visibility-test');

        $response->assertStatus(200);
        $response->assertSee('Visible Home Link');
        $response->assertDontSee('Secret Confidential Draft Section');
    }

    public function test_navbar_sort_order_is_respected(): void
    {
        $portfolio = Portfolio::create([
            'user_id' => $this->user->id,
            'name' => 'Ordered Portfolio',
            'slug' => 'ordered-portfolio',
            'status' => 'published',
            'published_at' => now()->subMinute(),
        ]);

        PortfolioNavItem::create([
            'portfolio_id' => $portfolio->id,
            'label' => 'Second Navigation Item',
            'section_key' => 'second',
            'sort_order' => 2,
            'is_visible' => true,
        ]);

        PortfolioNavItem::create([
            'portfolio_id' => $portfolio->id,
            'label' => 'First Navigation Item',
            'section_key' => 'first',
            'sort_order' => 1,
            'is_visible' => true,
        ]);

        PortfolioNavItem::create([
            'portfolio_id' => $portfolio->id,
            'label' => 'Third Navigation Item',
            'section_key' => 'third',
            'sort_order' => 3,
            'is_visible' => true,
        ]);

        $response = $this->get('/ordered-portfolio');

        $response->assertStatus(200);
        $content = $response->getContent();

        $posFirst = strpos($content, 'First Navigation Item');
        $posSecond = strpos($content, 'Second Navigation Item');
        $posThird = strpos($content, 'Third Navigation Item');

        $this->assertNotFalse($posFirst);
        $this->assertNotFalse($posSecond);
        $this->assertNotFalse($posThird);

        $this->assertTrue($posFirst < $posSecond);
        $this->assertTrue($posSecond < $posThird);
    }

    public function test_external_links_have_secure_rel_and_target_attributes(): void
    {
        $portfolio = Portfolio::create([
            'user_id' => $this->user->id,
            'name' => 'Security Test Portfolio',
            'slug' => 'security-test',
            'status' => 'published',
            'published_at' => now()->subMinute(),
        ]);

        PortfolioNavItem::create([
            'portfolio_id' => $portfolio->id,
            'label' => 'External Portfolio Link',
            'section_key' => null,
            'url' => 'https://external-security.org',
            'sort_order' => 1,
            'is_visible' => true,
        ]);

        $response = $this->get('/security-test');

        $response->assertStatus(200);
        $response->assertSee('target="_blank"', false);
        $response->assertSee('rel="noopener noreferrer"', false);
    }

    public function test_admin_and_auth_routes_are_not_shadowed_by_slug_route(): void
    {
        // Admin login page must resolve cleanly to login
        $loginResponse = $this->get('/admin/login');
        $loginResponse->assertStatus(200);
        $loginResponse->assertSee('Sign In to Dashboard');

        // Admin dashboard must redirect guest to admin.login
        $dashboardResponse = $this->get('/admin/dashboard');
        $dashboardResponse->assertRedirect(route('admin.login'));

        // Admin profiles listing must redirect guest to admin.login
        $profilesResponse = $this->get('/admin/profiles');
        $profilesResponse->assertRedirect(route('admin.login'));
    }

    public function test_public_page_does_not_contain_admin_or_edit_controls(): void
    {
        $portfolio = Portfolio::create([
            'user_id' => $this->user->id,
            'name' => 'Public Only Portfolio',
            'slug' => 'public-only',
            'status' => 'published',
            'published_at' => now()->subMinute(),
        ]);

        $response = $this->get('/public-only');

        $response->assertStatus(200);
        $response->assertDontSee('admin/portfolios');
        $response->assertDontSee('admin/profiles');
        $response->assertDontSee('Delete');
        $response->assertDontSee('Edit Profile');
        $response->assertDontSee('csrf');
    }

    public function test_published_portfolio_renders_skills_and_visible_ordered_projects(): void
    {
        $portfolio = Portfolio::create([
            'user_id' => $this->user->id,
            'name' => 'Full Showcase Portfolio',
            'slug' => 'full-showcase',
            'status' => 'published',
            'published_at' => now()->subMinute(),
        ]);

        PortfolioNavItem::create([
            'portfolio_id' => $portfolio->id,
            'label' => 'Home',
            'section_key' => 'home',
            'sort_order' => 1,
            'is_visible' => true,
        ]);

        PortfolioNavItem::create([
            'portfolio_id' => $portfolio->id,
            'label' => 'Projects',
            'section_key' => 'projects',
            'sort_order' => 2,
            'is_visible' => true,
        ]);

        PortfolioNavItem::create([
            'portfolio_id' => $portfolio->id,
            'label' => 'Skills',
            'section_key' => 'skills',
            'sort_order' => 3,
            'is_visible' => true,
        ]);

        PortfolioSkill::create(['portfolio_id' => $portfolio->id, 'name' => 'Laravel']);
        PortfolioSkill::create(['portfolio_id' => $portfolio->id, 'name' => 'TypeScript']);

        // Project 2 (higher sort_order) created first to confirm ordering by sort_order then id
        $secondProject = PortfolioProject::create([
            'portfolio_id' => $portfolio->id,
            'title' => 'Second Ordered Project',
            'description' => 'Appears second based on sort order.',
            'thumbnail' => 'https://example.com/second.jpg',
            'live_url' => 'https://second.example.com',
            'github_url' => 'https://github.com/example/second',
            'technologies' => ['Go', 'Docker'],
            'is_featured' => false,
            'is_visible' => true,
            'sort_order' => 2,
        ]);

        // Same sort_order tie-breaker by id
        $firstProjectA = PortfolioProject::create([
            'portfolio_id' => $portfolio->id,
            'title' => 'First Ordered Project Alpha',
            'description' => 'First project summary.',
            'thumbnail' => 'https://example.com/first.jpg',
            'live_url' => 'https://first.example.com',
            'github_url' => 'https://github.com/example/first',
            'technologies' => ['PHP', 'MySQL'],
            'is_featured' => true,
            'is_visible' => true,
            'sort_order' => 1,
        ]);

        $firstProjectB = PortfolioProject::create([
            'portfolio_id' => $portfolio->id,
            'title' => 'First Ordered Project Beta',
            'description' => 'Tie-break by id.',
            'is_featured' => false,
            'is_visible' => true,
            'sort_order' => 1,
        ]);

        PortfolioProject::create([
            'portfolio_id' => $portfolio->id,
            'title' => 'Secret Hidden Project',
            'description' => 'Must never be shown publicly.',
            'is_visible' => false,
            'sort_order' => 0,
        ]);

        $response = $this->get('/full-showcase');

        $response->assertStatus(200);
        $response->assertDontSee('Content for this section is configured in the portfolio workspace.');

        // Skills rendered
        $response->assertSee('Laravel');
        $response->assertSee('TypeScript');

        // Visible projects & fields rendered
        $response->assertSee('First Ordered Project Alpha');
        $response->assertSee('First project summary.');
        $response->assertSee('https://example.com/first.jpg');
        $response->assertSee('https://first.example.com');
        $response->assertSee('https://github.com/example/first');
        $response->assertSee('PHP');
        $response->assertSee('MySQL');
        $response->assertSee('Featured');

        $response->assertSee('Second Ordered Project');
        $response->assertSee('Go');
        $response->assertSee('Docker');

        // Hidden project never rendered
        $response->assertDontSee('Secret Hidden Project');
        $response->assertDontSee('Must never be shown publicly.');

        // Ordering check: sort_order 1 (Alpha then Beta) before sort_order 2 (Second)
        $content = $response->getContent();
        $posAlpha = strpos($content, $firstProjectA->title);
        $posBeta = strpos($content, $firstProjectB->title);
        $posSecond = strpos($content, $secondProject->title);

        $this->assertNotFalse($posAlpha);
        $this->assertNotFalse($posBeta);
        $this->assertNotFalse($posSecond);
        $this->assertTrue($posAlpha < $posBeta);
        $this->assertTrue($posBeta < $posSecond);
    }

    public function test_public_portfolio_handles_empty_skills_and_projects_gracefully(): void
    {
        $portfolio = Portfolio::create([
            'user_id' => $this->user->id,
            'name' => 'Empty Collections Portfolio',
            'slug' => 'empty-collections',
            'status' => 'published',
            'published_at' => now()->subMinute(),
        ]);

        PortfolioNavItem::create([
            'portfolio_id' => $portfolio->id,
            'label' => 'Home',
            'section_key' => 'home',
            'sort_order' => 1,
            'is_visible' => true,
        ]);

        PortfolioNavItem::create([
            'portfolio_id' => $portfolio->id,
            'label' => 'Projects',
            'section_key' => 'projects',
            'sort_order' => 2,
            'is_visible' => true,
        ]);

        PortfolioNavItem::create([
            'portfolio_id' => $portfolio->id,
            'label' => 'Skills',
            'section_key' => 'skills',
            'sort_order' => 3,
            'is_visible' => true,
        ]);

        $response = $this->get('/empty-collections');

        $response->assertStatus(200);
        $response->assertDontSee('Content for this section is configured in the portfolio workspace.');
        $response->assertSee('No projects have been published yet.');
        $response->assertSee('No skills have been added yet.');
    }

    public function test_public_portfolio_projects_and_skills_are_isolated_per_portfolio(): void
    {
        $portfolioA = Portfolio::create([
            'user_id' => $this->user->id,
            'name' => 'Portfolio Alpha',
            'slug' => 'iso-alpha',
            'status' => 'published',
            'published_at' => now()->subMinute(),
        ]);
        PortfolioSkill::create(['portfolio_id' => $portfolioA->id, 'name' => 'AlphaSkillOnly']);
        PortfolioProject::create([
            'portfolio_id' => $portfolioA->id,
            'title' => 'AlphaProjectOnly',
            'is_visible' => true,
        ]);

        $portfolioB = Portfolio::create([
            'user_id' => $this->user->id,
            'name' => 'Portfolio Beta',
            'slug' => 'iso-beta',
            'status' => 'published',
            'published_at' => now()->subMinute(),
        ]);
        PortfolioSkill::create(['portfolio_id' => $portfolioB->id, 'name' => 'BetaSkillOnly']);
        PortfolioProject::create([
            'portfolio_id' => $portfolioB->id,
            'title' => 'BetaProjectOnly',
            'is_visible' => true,
        ]);

        $resA = $this->get('/iso-alpha');
        $resA->assertStatus(200);
        $resA->assertSee('AlphaSkillOnly');
        $resA->assertSee('AlphaProjectOnly');
        $resA->assertDontSee('BetaSkillOnly');
        $resA->assertDontSee('BetaProjectOnly');
    }
}
