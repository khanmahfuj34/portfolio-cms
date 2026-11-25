<?php

namespace Tests\Feature;

use App\Models\Portfolio;
use App\Models\PortfolioNavItem;
use App\Models\PortfolioProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MultiPortfolioIsolationTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'super_admin',
            'is_active' => true,
        ]);
    }

    public function test_multiple_portfolios_render_their_own_independent_data_without_leakage(): void
    {
        // 1. Create Portfolio A (Alice Alpha)
        $portfolioA = Portfolio::create([
            'user_id' => $this->admin->id,
            'name' => 'Portfolio Alpha',
            'slug' => 'alpha-architect',
            'description' => 'Alice Alpha System Design Portfolio',
            'status' => 'published',
            'published_at' => now()->subHour(),
        ]);

        PortfolioProfile::create([
            'portfolio_id' => $portfolioA->id,
            'full_name' => 'Alice Alpha',
            'professional_title' => 'Chief Systems Architect',
            'email' => 'alice@alpha-systems.io',
            'location' => 'Seattle, WA',
            'bio' => 'Alice specializes in distributed database partitioning and event streaming.',
        ]);

        PortfolioNavItem::create([
            'portfolio_id' => $portfolioA->id,
            'label' => 'Home',
            'section_key' => 'home',
            'sort_order' => 1,
            'is_visible' => true,
        ]);

        PortfolioNavItem::create([
            'portfolio_id' => $portfolioA->id,
            'label' => 'Alpha Architecture Cases',
            'section_key' => 'architecture',
            'sort_order' => 2,
            'is_visible' => true,
        ]);

        // 2. Create Portfolio B (Bob Beta)
        $portfolioB = Portfolio::create([
            'user_id' => $this->admin->id,
            'name' => 'Portfolio Beta',
            'slug' => 'beta-engineer',
            'description' => 'Bob Beta Full-Stack Engineering Showcase',
            'status' => 'published',
            'published_at' => now()->subHour(),
        ]);

        PortfolioProfile::create([
            'portfolio_id' => $portfolioB->id,
            'full_name' => 'Bob Beta',
            'professional_title' => 'Senior Frontend & UX Engineer',
            'email' => 'bob@beta-design.com',
            'location' => 'Austin, TX',
            'bio' => 'Bob builds accessible, high-performance web applications using modern tooling.',
        ]);

        PortfolioNavItem::create([
            'portfolio_id' => $portfolioB->id,
            'label' => 'Home',
            'section_key' => 'home',
            'sort_order' => 1,
            'is_visible' => true,
        ]);

        PortfolioNavItem::create([
            'portfolio_id' => $portfolioB->id,
            'label' => 'Beta UX Case Studies',
            'section_key' => 'ux-studies',
            'sort_order' => 2,
            'is_visible' => true,
        ]);

        PortfolioNavItem::create([
            'portfolio_id' => $portfolioB->id,
            'label' => 'Bob GitHub Repo',
            'section_key' => null,
            'url' => 'https://github.com/bobbeta',
            'sort_order' => 3,
            'is_visible' => true,
        ]);

        // 3. Create Portfolio C (Charlie Gamma - Draft)
        $portfolioC = Portfolio::create([
            'user_id' => $this->admin->id,
            'name' => 'Portfolio Gamma',
            'slug' => 'gamma-draft',
            'description' => 'Charlie Gamma Work in Progress',
            'status' => 'draft',
            'published_at' => null,
        ]);

        PortfolioProfile::create([
            'portfolio_id' => $portfolioC->id,
            'full_name' => 'Charlie Gamma',
            'professional_title' => 'Junior Cloud Developer',
            'email' => 'charlie@gamma.dev',
            'location' => 'New York, NY',
            'bio' => 'Charlie is currently building cloud infrastructure.',
        ]);

        PortfolioNavItem::create([
            'portfolio_id' => $portfolioC->id,
            'label' => 'Home',
            'section_key' => 'home',
            'sort_order' => 1,
            'is_visible' => true,
        ]);

        PortfolioNavItem::create([
            'portfolio_id' => $portfolioC->id,
            'label' => 'Gamma Cloud Labs',
            'section_key' => 'cloud-labs',
            'sort_order' => 2,
            'is_visible' => true,
        ]);

        // --- ASSERTION 1: Requesting Portfolio A delivers ONLY Portfolio A data ---
        $resA = $this->get('/alpha-architect');
        $resA->assertStatus(200);
        $resA->assertSee('Alice Alpha');
        $resA->assertSee('Chief Systems Architect');
        $resA->assertSee('alice@alpha-systems.io');
        $resA->assertSee('Seattle, WA');
        $resA->assertSee('Alpha Architecture Cases');
        $resA->assertSee('#architecture');

        // Verify zero cross-contamination from Portfolio B or Portfolio C
        $resA->assertDontSee('Bob Beta');
        $resA->assertDontSee('Senior Frontend & UX Engineer');
        $resA->assertDontSee('bob@beta-design.com');
        $resA->assertDontSee('Beta UX Case Studies');
        $resA->assertDontSee('https://github.com/bobbeta');
        $resA->assertDontSee('Charlie Gamma');
        $resA->assertDontSee('Gamma Cloud Labs');

        // --- ASSERTION 2: Requesting Portfolio B delivers ONLY Portfolio B data ---
        $resB = $this->get('/beta-engineer');
        $resB->assertStatus(200);
        $resB->assertSee('Bob Beta');
        $resB->assertSee('Senior Frontend & UX Engineer');
        $resB->assertSee('bob@beta-design.com');
        $resB->assertSee('Austin, TX');
        $resB->assertSee('Beta UX Case Studies');
        $resB->assertSee('#ux-studies');
        $resB->assertSee('https://github.com/bobbeta');

        // Verify zero cross-contamination from Portfolio A or Portfolio C
        $resB->assertDontSee('Alice Alpha');
        $resB->assertDontSee('Chief Systems Architect');
        $resB->assertDontSee('alice@alpha-systems.io');
        $resB->assertDontSee('Alpha Architecture Cases');
        $resB->assertDontSee('Charlie Gamma');
        $resB->assertDontSee('Gamma Cloud Labs');

        // --- ASSERTION 3: Requesting Draft Portfolio C as guest returns 404 ---
        $resC = $this->get('/gamma-draft');
        $resC->assertStatus(404);
    }

    public function test_hidden_navbar_items_are_never_rendered_for_any_portfolio(): void
    {
        $portfolio = Portfolio::create([
            'user_id' => $this->admin->id,
            'name' => 'Isolation Nav Test',
            'slug' => 'isolation-nav-slug',
            'status' => 'published',
            'published_at' => now()->subMinute(),
        ]);

        PortfolioNavItem::create([
            'portfolio_id' => $portfolio->id,
            'label' => 'Public Nav Link',
            'section_key' => 'home',
            'sort_order' => 1,
            'is_visible' => true,
        ]);

        PortfolioNavItem::create([
            'portfolio_id' => $portfolio->id,
            'label' => 'Super Secret Hidden Nav Link',
            'section_key' => 'secret',
            'sort_order' => 2,
            'is_visible' => false,
        ]);

        $response = $this->get('/isolation-nav-slug');
        $response->assertStatus(200);
        $response->assertSee('Public Nav Link');
        $response->assertDontSee('Super Secret Hidden Nav Link');
    }

    public function test_authenticated_super_admin_can_preview_draft_portfolio(): void
    {
        $portfolio = Portfolio::create([
            'user_id' => $this->admin->id,
            'name' => 'Draft Preview Test',
            'slug' => 'draft-preview-slug',
            'status' => 'draft',
            'published_at' => null,
        ]);

        PortfolioProfile::create([
            'portfolio_id' => $portfolio->id,
            'full_name' => 'Draft Preview Owner',
            'professional_title' => 'Quality Assurance Lead',
            'email' => 'qa@preview.test',
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
            'label' => 'Draft Nav Section',
            'section_key' => 'draft-sec',
            'sort_order' => 2,
            'is_visible' => true,
        ]);

        // Unauthenticated guest must get 404
        $guestResponse = $this->get('/draft-preview-slug');
        $guestResponse->assertStatus(404);

        // Authenticated Super Admin can preview the draft portfolio
        $adminResponse = $this->actingAs($this->admin, 'super_admin')->get('/draft-preview-slug');
        $adminResponse->assertStatus(200);
        $adminResponse->assertSee('Admin Preview Mode');
        $adminResponse->assertSee('Draft Preview Owner');
        $adminResponse->assertSee('Draft Nav Section');
    }

    public function test_super_admin_can_toggle_publish_status_of_portfolio(): void
    {
        $portfolio = Portfolio::create([
            'user_id' => $this->admin->id,
            'name' => 'Toggle Status Test',
            'slug' => 'toggle-status-slug',
            'status' => 'draft',
            'published_at' => null,
        ]);

        // 1. Initial State: Draft -> public gets 404
        $resGuest1 = $this->get('/toggle-status-slug');
        $resGuest1->assertStatus(404);

        // 2. Super Admin toggles to Published
        $toggleRes1 = $this->actingAs($this->admin, 'super_admin')->patch(
            route('admin.portfolios.toggle-publish', $portfolio)
        );
        $toggleRes1->assertSessionHas('success');

        $portfolio->refresh();
        $this->assertEquals('published', $portfolio->status);
        $this->assertNotNull($portfolio->published_at);

        // 3. Now public visitor gets 200 OK
        $resGuest2 = $this->get('/toggle-status-slug');
        $resGuest2->assertStatus(200);

        // 4. Super Admin toggles back to Draft
        $toggleRes2 = $this->actingAs($this->admin, 'super_admin')->patch(
            route('admin.portfolios.toggle-publish', $portfolio)
        );
        $toggleRes2->assertSessionHas('success');

        $portfolio->refresh();
        $this->assertEquals('draft', $portfolio->status);
        $this->assertNull($portfolio->published_at);

        // 5. Public visitor (unauthenticated) gets 404 again
        auth('super_admin')->logout();
        $resGuest3 = $this->get('/toggle-status-slug');
        $resGuest3->assertStatus(404);
    }

    public function test_unknown_slugs_return_404_for_both_guest_and_admin(): void
    {
        $this->get('/unknown-portfolio-xyz-987')->assertStatus(404);
        $this->actingAs($this->admin, 'super_admin')->get('/unknown-portfolio-xyz-987')->assertStatus(404);
    }
}
