<?php

namespace Tests\Feature;

use App\Models\Portfolio;
use App\Models\PortfolioNavItem;
use App\Models\PortfolioProfile;
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
}
