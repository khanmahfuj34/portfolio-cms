<?php

namespace Tests\Feature;

use App\Models\Portfolio;
use App\Models\PortfolioProject;
use App\Models\PortfolioSkill;
use App\Models\User;
use App\Services\PortfolioService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class IndividualAdminPortfolioOwnershipTest extends TestCase
{
    use DatabaseTransactions;

    private User $superAdmin;
    private User $adminA;
    private User $adminB;
    private PortfolioService $portfolioService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        $this->adminA = User::factory()->create([
            'name' => 'Creator Alpha',
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $this->adminB = User::factory()->create([
            'name' => 'Creator Beta',
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $this->portfolioService = app(PortfolioService::class);
    }

    private function validCreationPayload(string $name, string $slug, array $overrides = []): array
    {
        return array_merge([
            'name' => $name,
            'slug' => $slug,
            'description' => "Showcase for {$name}",
            'status' => 'published', // Must still force draft on initial creation
            'user_id' => $this->adminB->id, // Attempted spoofing must be ignored
            'full_name' => 'Owner ' . $name,
            'professional_title' => 'Architect',
            'email' => "{$slug}@example.test",
            'location' => 'Dhaka',
            'bio' => 'Bio text.',
            'nav_items' => [
                [
                    'label' => 'Home',
                    'type' => 'section',
                    'section_key' => 'home',
                    'sort_order' => 1,
                    'is_visible' => true,
                ],
            ],
            'skills' => [
                ['name' => 'Laravel'],
            ],
            'projects' => [
                [
                    'title' => "{$name} Flagship",
                    'technologies' => ['PHP', 'MySQL'],
                    'is_visible' => true,
                    'sort_order' => 1,
                ],
            ],
        ], $overrides);
    }

    public function test_dashboard_requires_individual_admin_and_scopes_to_owner(): void
    {
        // Guest redirects to /login
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/dashboard/portfolios')->assertRedirect('/login');

        // Super Admin is isolated from /dashboard portal
        $this->actingAs($this->superAdmin, 'super_admin')->get('/dashboard')->assertRedirect('/login');
        $this->actingAs($this->superAdmin, 'admin')->get('/dashboard')->assertStatus(403);

        // Inactive Individual Admin is logged out and redirected to /login
        $inactive = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_active' => false]);
        $this->actingAs($inactive, 'admin')->get('/dashboard')->assertRedirect('/login');
        $this->assertGuest('admin');

        // Create portfolios for both Admin A and Admin B
        $this->portfolioService->create(
            $this->adminA,
            ['name' => 'Alpha Portfolio Only', 'slug' => 'alpha-only-' . uniqid()],
            ['full_name' => 'Alpha Owner'],
            [['label' => 'Home', 'section_key' => 'home', 'sort_order' => 1, 'is_visible' => true]]
        );
        $this->portfolioService->create(
            $this->adminB,
            ['name' => 'Beta Portfolio Secret', 'slug' => 'beta-secret-' . uniqid()],
            ['full_name' => 'Beta Owner'],
            [['label' => 'Home', 'section_key' => 'home', 'sort_order' => 1, 'is_visible' => true]]
        );

        $dashRes = $this->actingAs($this->adminA, 'admin')->get('/dashboard');
        $dashRes->assertStatus(200);
        $dashRes->assertSee('Alpha Portfolio Only');
        $dashRes->assertDontSee('Beta Portfolio Secret');

        $listRes = $this->actingAs($this->adminA, 'admin')->get('/dashboard/portfolios');
        $listRes->assertStatus(200);
        $listRes->assertSee('Alpha Portfolio Only');
        $listRes->assertDontSee('Beta Portfolio Secret');
        $this->assertSame(1, $listRes->viewData('stats')['total']);
    }

    public function test_individual_admin_can_create_multiple_portfolios_with_unique_subdomains(): void
    {
        $suffix = uniqid();
        $slugs = [
            'Developer' => "developer-{$suffix}",
            'Designer' => "designer-{$suffix}",
            'Photographer' => "photographer-{$suffix}",
        ];

        $this->actingAs($this->adminA, 'admin')->get('/dashboard/portfolios/create')->assertStatus(200);

        foreach ($slugs as $label => $slug) {
            $res = $this->actingAs($this->adminA, 'admin')->post(
                '/dashboard/portfolios',
                $this->validCreationPayload($label, $slug)
            );
            $res->assertRedirect('/dashboard/portfolios');

            $portfolio = Portfolio::where('slug', $slug)->firstOrFail();
            // Client-supplied user_id spoofing & status=published ignored
            $this->assertSame($this->adminA->id, $portfolio->user_id);
            $this->assertSame('draft', $portfolio->status);
            $this->assertSame("{$slug}.localhost", $portfolio->primaryDomain->domain);
        }

        $this->assertSame(3, $this->adminA->portfolios()->count());
    }

    public function test_individual_admin_can_update_publish_unpublish_and_delete_own_portfolio(): void
    {
        $slug = 'dev-own-' . uniqid();
        $newSlug = 'dev-renamed-' . uniqid();

        $portfolio = $this->portfolioService->create(
            $this->adminA,
            ['name' => 'Original Title', 'slug' => $slug],
            ['full_name' => 'Alpha Owner'],
            [['label' => 'Home', 'section_key' => 'home', 'sort_order' => 1, 'is_visible' => true]]
        );

        // Edit page
        $this->actingAs($this->adminA, 'admin')
            ->get("/dashboard/portfolios/{$portfolio->id}/edit")
            ->assertStatus(200)
            ->assertSee('Original Title');

        // Update metadata + slug -> subdomain updates cleanly
        $this->actingAs($this->adminA, 'admin')
            ->put("/dashboard/portfolios/{$portfolio->id}", [
                'name' => 'Updated Title',
                'slug' => $newSlug,
                'description' => 'Refined summary.',
                'full_name' => 'Alpha Updated',
                'professional_title' => 'Principal Dev',
                'email' => 'alpha@updated.test',
                'location' => 'Dhaka',
                'bio' => 'Updated bio.',
            ])
            ->assertRedirect("/dashboard/portfolios/{$portfolio->id}/edit")
            ->assertSessionHas('success');

        $portfolio->refresh();
        $this->assertSame('Updated Title', $portfolio->name);
        $this->assertSame($newSlug, $portfolio->slug);
        $this->assertSame('Alpha Updated', $portfolio->profile->full_name);
        $this->assertSame("{$newSlug}.localhost", $portfolio->primaryDomain->domain);

        // Publish -> accessible on new subdomain
        $this->actingAs($this->adminA, 'admin')
            ->patch("/dashboard/portfolios/{$portfolio->id}/publish")
            ->assertRedirect();

        $portfolio->refresh();
        $this->assertSame('published', $portfolio->status);
        $this->assertNotNull($portfolio->published_at);

        Auth::guard('admin')->logout();
        $this->get("http://{$newSlug}.localhost/")->assertStatus(200)->assertSee('Alpha Updated');

        // Unpublish -> hidden from subdomain
        $this->actingAs($this->adminA, 'admin')
            ->patch("http://localhost/dashboard/portfolios/{$portfolio->id}/unpublish")
            ->assertRedirect();

        $portfolio->refresh();
        $this->assertSame('draft', $portfolio->status);
        $this->assertNull($portfolio->published_at);

        Auth::guard('admin')->logout();
        $this->get("http://{$newSlug}.localhost/")->assertStatus(404);

        // Delete -> cascades cleanly
        $this->actingAs($this->adminA, 'admin')
            ->delete("http://localhost/dashboard/portfolios/{$portfolio->id}")
            ->assertRedirect('/dashboard/portfolios');

        $this->assertDatabaseMissing('portfolios', ['id' => $portfolio->id]);
        $this->assertDatabaseMissing('portfolio_domains', ['portfolio_id' => $portfolio->id]);
    }

    public function test_individual_admin_cannot_access_or_mutate_another_admins_portfolio_or_nested_items(): void
    {
        $portfolioB = $this->portfolioService->create(
            $this->adminB,
            ['name' => 'Beta Owned', 'slug' => 'beta-idor-' . uniqid()],
            ['full_name' => 'Beta Owner'],
            [['label' => 'Home', 'section_key' => 'home', 'sort_order' => 1, 'is_visible' => true]],
            [['name' => 'SecretSkill']],
            [['title' => 'SecretProject']]
        );
        $skillB = PortfolioSkill::where('portfolio_id', $portfolioB->id)->firstOrFail();
        $projectB = PortfolioProject::where('portfolio_id', $portfolioB->id)->firstOrFail();
        $navB = $portfolioB->navItems()->firstOrFail();

        // Admin A attempts all direct & nested operations on Admin B's portfolio
        $this->actingAs($this->adminA, 'admin')->get("/dashboard/portfolios/{$portfolioB->id}/edit")->assertStatus(403);
        $this->actingAs($this->adminA, 'admin')->put("/dashboard/portfolios/{$portfolioB->id}", [
            'name' => 'Hijacked',
            'slug' => $portfolioB->slug,
            'full_name' => 'Hijacked',
        ])->assertStatus(403);
        $this->actingAs($this->adminA, 'admin')->delete("/dashboard/portfolios/{$portfolioB->id}")->assertStatus(403);
        $this->actingAs($this->adminA, 'admin')->patch("/dashboard/portfolios/{$portfolioB->id}/publish")->assertStatus(403);
        $this->actingAs($this->adminA, 'admin')->patch("/dashboard/portfolios/{$portfolioB->id}/unpublish")->assertStatus(403);

        // Nested Skills, Projects, NavItems IDOR protection
        $this->actingAs($this->adminA, 'admin')->getJson("/dashboard/portfolios/{$portfolioB->id}/skills")->assertStatus(403);
        $this->actingAs($this->adminA, 'admin')->postJson("/dashboard/portfolios/{$portfolioB->id}/skills", ['name' => 'Injected'])->assertStatus(403);
        $this->actingAs($this->adminA, 'admin')->putJson("/dashboard/portfolios/{$portfolioB->id}/skills/{$skillB->id}", ['name' => 'Hijacked'])->assertStatus(403);
        $this->actingAs($this->adminA, 'admin')->deleteJson("/dashboard/portfolios/{$portfolioB->id}/skills/{$skillB->id}")->assertStatus(403);

        $this->actingAs($this->adminA, 'admin')->getJson("/dashboard/portfolios/{$portfolioB->id}/projects")->assertStatus(403);
        $this->actingAs($this->adminA, 'admin')->postJson("/dashboard/portfolios/{$portfolioB->id}/projects", ['title' => 'Injected'])->assertStatus(403);
        $this->actingAs($this->adminA, 'admin')->putJson("/dashboard/portfolios/{$portfolioB->id}/projects/{$projectB->id}", ['title' => 'Hijacked'])->assertStatus(403);
        $this->actingAs($this->adminA, 'admin')->deleteJson("/dashboard/portfolios/{$portfolioB->id}/projects/{$projectB->id}")->assertStatus(403);

        $this->actingAs($this->adminA, 'admin')->get("/dashboard/portfolios/{$portfolioB->id}/nav-items")->assertStatus(403);
        $this->actingAs($this->adminA, 'admin')->post("/dashboard/portfolios/{$portfolioB->id}/nav-items", ['label' => 'Hack', 'section_key' => 'hack'])->assertStatus(403);
        $this->actingAs($this->adminA, 'admin')->delete("/dashboard/portfolios/{$portfolioB->id}/nav-items/{$navB->id}")->assertStatus(403);

        // Confirm database untouched
        $this->assertDatabaseHas('portfolios', ['id' => $portfolioB->id, 'name' => 'Beta Owned', 'status' => 'draft']);
        $this->assertDatabaseHas('portfolio_skills', ['id' => $skillB->id, 'name' => 'SecretSkill']);
    }

    public function test_super_admin_can_still_manage_all_portfolios_across_owners(): void
    {
        $portfolioB = $this->portfolioService->create(
            $this->adminB,
            ['name' => 'Beta Managed By Super', 'slug' => 'beta-super-' . uniqid()],
            ['full_name' => 'Beta Owner'],
            [['label' => 'Home', 'section_key' => 'home', 'sort_order' => 1, 'is_visible' => true]]
        );

        $this->actingAs($this->superAdmin, 'super_admin')
            ->get('/admin/profiles')
            ->assertStatus(200)
            ->assertSee('Beta Managed By Super');

        $this->actingAs($this->superAdmin, 'super_admin')
            ->get("/admin/portfolios/{$portfolioB->id}/edit")
            ->assertStatus(200);

        $this->actingAs($this->superAdmin, 'super_admin')
            ->patch("/admin/portfolios/{$portfolioB->id}/publish")
            ->assertRedirect();

        $portfolioB->refresh();
        $this->assertSame('published', $portfolioB->status);
    }
}
