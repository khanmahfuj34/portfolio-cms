<?php

namespace Tests\Feature;

use App\Models\Portfolio;
use App\Models\PortfolioDomain;
use App\Models\PortfolioNavItem;
use App\Models\PortfolioProject;
use App\Models\PortfolioSkill;
use App\Models\User;
use App\Services\PortfolioPreviewService;
use App\Services\PortfolioService;
use App\Services\UserService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MultiAdminSecurityAuditTest extends TestCase
{
    use DatabaseTransactions;

    private User $superAdmin;
    private User $adminA;
    private User $adminB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'email' => 'audit-super-' . uniqid() . '@example.com',
            'password' => Hash::make('SuperPass123!'),
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        $this->adminA = User::factory()->create([
            'email' => 'audit-a-' . uniqid() . '@example.com',
            'password' => Hash::make('OwnerAPass123!'),
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $this->adminB = User::factory()->create([
            'email' => 'audit-b-' . uniqid() . '@example.com',
            'password' => Hash::make('OwnerBPass123!'),
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);
    }

    private function makePortfolio(User $owner, string $slug, bool $publish = false): Portfolio
    {
        $portfolio = app(PortfolioService::class)->create(
            $owner,
            ['name' => 'Site ' . $slug, 'slug' => $slug, 'description' => 'Audit portfolio'],
            ['full_name' => 'Owner ' . $slug, 'email' => $owner->email],
            [
                ['label' => 'Home', 'section_key' => 'home', 'sort_order' => 1, 'is_visible' => true],
                ['label' => 'Projects', 'section_key' => 'projects', 'sort_order' => 2, 'is_visible' => true],
            ],
            [['name' => 'Laravel']],
            [[
                'title' => 'Audit Project ' . $slug,
                'description' => 'Work sample',
                'thumbnail' => 'https://example.com/thumb.png',
                'live_url' => 'https://example.com/live',
                'github_url' => 'https://github.com/example/repo',
                'technologies' => ['PHP'],
                'is_featured' => true,
                'is_visible' => true,
                'sort_order' => 1,
            ]]
        );

        if ($publish) {
            return app(PortfolioService::class)->publish($portfolio);
        }

        return $portfolio;
    }

    public function test_role_separation_rate_limiting_and_inactive_super_admin_session_invalidation(): void
    {
        // Role separation across isolated guards & portals
        $this->actingAs($this->adminA, 'admin')->get('http://localhost/admin/dashboard')->assertRedirect('http://localhost/admin/login');
        $this->actingAs($this->adminA, 'super_admin')->get('http://localhost/admin/dashboard')->assertForbidden();
        $this->actingAs($this->adminA, 'super_admin')->get('http://localhost/admin/users')->assertForbidden();

        $this->app['auth']->forgetGuards();
        $this->actingAs($this->superAdmin, 'super_admin')->get('http://localhost/dashboard')->assertRedirect('http://localhost/login');
        $this->actingAs($this->superAdmin, 'admin')->get('http://localhost/dashboard')->assertForbidden();
        $this->actingAs($this->superAdmin, 'admin')->get('http://localhost/dashboard/account')->assertForbidden();

        // Clear authenticated guard before testing guest Super Admin login rate limiting
        $this->app['auth']->forgetGuards();

        // Super Admin login rate limiting (5 failed attempts -> throttled on 6th)
        for ($i = 0; $i < 5; $i++) {
            $this->post('http://localhost/admin/login', [
                'email' => $this->superAdmin->email,
                'password' => 'WrongPassword123',
            ])->assertSessionHasErrors('email');
        }

        $this->post('http://localhost/admin/login', [
            'email' => $this->superAdmin->email,
            'password' => 'SuperPass123!',
        ])->assertSessionHasErrors('email');

        // Inactive Super Admin gets 403 and session logged out
        $inactiveSuper = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => false,
        ]);

        $this->actingAs($inactiveSuper, 'super_admin')
            ->get('http://localhost/admin/dashboard')
            ->assertForbidden();

        $this->assertGuest('super_admin');
    }

    public function test_mass_assignment_and_privilege_escalation_vectors_are_blocked(): void
    {
        $email = 'escalate-' . uniqid() . '@example.com';

        $this->post('http://localhost/register', [
            'name' => 'Self Registered',
            'email' => $email,
            'password' => 'StrongPass123!',
            'password_confirmation' => 'StrongPass123!',
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => false,
        ])->assertRedirect('http://localhost/dashboard');

        $registered = User::where('email', $email)->firstOrFail();
        $this->assertSame(User::ROLE_ADMIN, $registered->role);
        $this->assertTrue($registered->is_active);

        // Account update cannot escalate role or toggle is_active
        $this->actingAs($registered, 'admin')
            ->patch('http://localhost/dashboard/account', [
                'name' => 'Updated Name',
                'email' => $email,
                'role' => User::ROLE_SUPER_ADMIN,
                'is_active' => false,
            ])
            ->assertRedirect('http://localhost/dashboard/account');

        $registered->refresh();
        $this->assertSame(User::ROLE_ADMIN, $registered->role);
        $this->assertTrue($registered->is_active);

        // Portfolio create & update ignore spoofed status, published_at, and user_id
        $slug = 'esc-site-' . uniqid();
        $this->actingAs($registered, 'admin')
            ->post('http://localhost/dashboard/portfolios', [
                'name' => 'Draft Forced',
                'slug' => $slug,
                'status' => 'published',
                'published_at' => now()->toDateTimeString(),
                'user_id' => $this->adminB->id,
                'full_name' => 'Self Registered',
                'nav_items' => [
                    ['label' => 'Home', 'type' => 'section', 'section_key' => 'home', 'sort_order' => 1, 'is_visible' => 1],
                ],
            ])
            ->assertRedirect('http://localhost/dashboard/portfolios');

        $created = Portfolio::where('slug', $slug)->firstOrFail();
        $this->assertSame($registered->id, $created->user_id);
        $this->assertSame('draft', $created->status);
        $this->assertNull($created->published_at);

        $this->actingAs($registered, 'admin')
            ->put("http://localhost/dashboard/portfolios/{$created->id}", [
                'name' => 'Updated Name',
                'slug' => $slug,
                'full_name' => 'Self Registered',
                'status' => 'published',
                'user_id' => $this->adminB->id,
            ])
            ->assertRedirect("http://localhost/dashboard/portfolios/{$created->id}/edit");

        $created->refresh();
        $this->assertSame($registered->id, $created->user_id);
        $this->assertSame('draft', $created->status);
    }

    public function test_cross_user_nested_resource_idor_and_scoped_bindings_are_blocked(): void
    {
        $portfolioA = $this->makePortfolio($this->adminA, 'audit-a-' . uniqid());
        $portfolioB = $this->makePortfolio($this->adminB, 'audit-b-' . uniqid());

        $skillA = $portfolioA->skills()->firstOrFail();
        $projectA = $portfolioA->projects()->firstOrFail();
        $navA = $portfolioA->navItems()->firstOrFail();
        $skillB = $portfolioB->skills()->firstOrFail();
        $projectB = $portfolioB->projects()->firstOrFail();
        $navB = $portfolioB->navItems()->firstOrFail();

        // Owner B cannot access or mutate Owner A's nested skills, projects, or nav items
        $this->actingAs($this->adminB, 'admin')->getJson("http://localhost/dashboard/portfolios/{$portfolioA->id}/skills")->assertForbidden();
        $this->actingAs($this->adminB, 'admin')->postJson("http://localhost/dashboard/portfolios/{$portfolioA->id}/skills", ['name' => 'Hack'])->assertForbidden();
        $this->actingAs($this->adminB, 'admin')->putJson("http://localhost/dashboard/portfolios/{$portfolioA->id}/skills/{$skillA->id}", ['name' => 'Hack'])->assertForbidden();
        $this->actingAs($this->adminB, 'admin')->deleteJson("http://localhost/dashboard/portfolios/{$portfolioA->id}/skills/{$skillA->id}")->assertForbidden();

        $this->actingAs($this->adminB, 'admin')->getJson("http://localhost/dashboard/portfolios/{$portfolioA->id}/projects")->assertForbidden();
        $this->actingAs($this->adminB, 'admin')->postJson("http://localhost/dashboard/portfolios/{$portfolioA->id}/projects", ['title' => 'Hack'])->assertForbidden();
        $this->actingAs($this->adminB, 'admin')->putJson("http://localhost/dashboard/portfolios/{$portfolioA->id}/projects/{$projectA->id}", ['title' => 'Hack'])->assertForbidden();
        $this->actingAs($this->adminB, 'admin')->deleteJson("http://localhost/dashboard/portfolios/{$portfolioA->id}/projects/{$projectA->id}")->assertForbidden();

        $this->actingAs($this->adminB, 'admin')->get("http://localhost/dashboard/portfolios/{$portfolioA->id}/nav-items")->assertForbidden();
        $this->actingAs($this->adminB, 'admin')->post("http://localhost/dashboard/portfolios/{$portfolioA->id}/nav-items", ['label' => 'Hack', 'section_key' => 'hack'])->assertForbidden();
        $this->actingAs($this->adminB, 'admin')->put("http://localhost/dashboard/portfolios/{$portfolioA->id}/nav-items/{$navA->id}", ['label' => 'Hack'])->assertForbidden();
        $this->actingAs($this->adminB, 'admin')->delete("http://localhost/dashboard/portfolios/{$portfolioA->id}/nav-items/{$navA->id}")->assertForbidden();
        $this->actingAs($this->adminB, 'admin')->patch("http://localhost/dashboard/portfolios/{$portfolioA->id}/nav-items/{$navA->id}/visibility")->assertForbidden();
        $this->actingAs($this->adminB, 'admin')->patch("http://localhost/dashboard/portfolios/{$portfolioA->id}/nav-items/reorder", ['items' => [$navA->id]])->assertForbidden();

        // Owner A cannot sneak Owner B's child IDs under Portfolio A's URL (scopeBindings / child lookup)
        $this->actingAs($this->adminA, 'admin')->putJson("http://localhost/dashboard/portfolios/{$portfolioA->id}/skills/{$skillB->id}", ['name' => 'Tampered'])->assertNotFound();
        $this->actingAs($this->adminA, 'admin')->deleteJson("http://localhost/dashboard/portfolios/{$portfolioA->id}/skills/{$skillB->id}")->assertNotFound();
        $this->actingAs($this->adminA, 'admin')->putJson("http://localhost/dashboard/portfolios/{$portfolioA->id}/projects/{$projectB->id}", ['title' => 'Tampered'])->assertNotFound();
        $this->actingAs($this->adminA, 'admin')->deleteJson("http://localhost/dashboard/portfolios/{$portfolioA->id}/projects/{$projectB->id}")->assertNotFound();
        $this->actingAs($this->adminA, 'admin')->put("http://localhost/dashboard/portfolios/{$portfolioA->id}/nav-items/{$navB->id}", ['label' => 'Tampered'])->assertNotFound();
        $this->actingAs($this->adminA, 'admin')->delete("http://localhost/dashboard/portfolios/{$portfolioA->id}/nav-items/{$navB->id}")->assertNotFound();
        $this->actingAs($this->adminA, 'admin')->patch("http://localhost/dashboard/portfolios/{$portfolioA->id}/nav-items/{$navB->id}/visibility")->assertNotFound();

        $this->assertSame('Laravel', $skillB->fresh()->name);
        $this->assertStringStartsWith('Audit Project', $projectB->fresh()->title);
        $this->assertSame('Home', $navB->fresh()->label);
    }

    public function test_draft_preview_security_headers_same_second_rotation_and_unpublish_revocation(): void
    {
        $draft = $this->makePortfolio($this->adminA, 'preview-sec-' . uniqid());
        $domain = $draft->primaryDomain->domain;

        $previewService = app(PortfolioPreviewService::class);
        $oldUrl = $previewService->generatePreviewUrl($draft, $this->adminA);

        $previewResponse = $this->get($oldUrl);
        $previewResponse->assertOk();
        $previewResponse->assertHeader('Referrer-Policy', 'no-referrer');
        $previewResponse->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $previewResponse->assertSee('<meta name="referrer" content="no-referrer">', false);

        // Revoke, wait briefly (>1ms), and issue a brand-new token in the same second
        $previewService->revokeAllForPortfolio($draft);
        usleep(5000);
        $newUrl = $previewService->generatePreviewUrl($draft, $this->adminA);

        $this->get($oldUrl)->assertNotFound();
        $this->get($newUrl)->assertOk();

        // Publish then unpublish must revoke previously issued preview tokens
        $published = app(PortfolioService::class)->publish($draft);

        // /preview on a published portfolio redirects directly without issuing a preview_token
        $publishedRedirect = $this->actingAs($this->adminA, 'admin')
            ->get("http://localhost/dashboard/portfolios/{$published->id}/preview");
        $publishedRedirect->assertRedirect();
        $this->assertStringNotContainsString('preview_token=', (string) $publishedRedirect->headers->get('Location'));

        usleep(5000);
        app(PortfolioService::class)->unpublish($published);

        $this->app['auth']->forgetGuards();
        $this->get($newUrl)->assertNotFound();
        $this->get("http://{$domain}/")->assertNotFound();
    }

    public function test_unsafe_project_urls_and_slug_subdomain_collisions_are_rejected(): void
    {
        $portfolioA = $this->makePortfolio($this->adminA, 'slug_alpha_' . uniqid());
        $portfolioB = $this->makePortfolio($this->adminB, 'slug-beta-' . uniqid());

        // Reject javascript: and data: URIs on project URLs and thumbnails
        $this->actingAs($this->adminA, 'admin')
            ->postJson("http://localhost/dashboard/portfolios/{$portfolioA->id}/projects", [
                'title' => 'XSS Attempt',
                'live_url' => 'javascript:alert(1)',
                'github_url' => 'ftp://example.com/repo',
                'thumbnail' => 'javascript:alert(1)',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['live_url', 'github_url', 'thumbnail']);

        // Reject subdomain collision derived from underscore vs hyphen normalization
        $hyphenCandidate = str_replace('_', '-', $portfolioA->slug);
        $this->actingAs($this->adminB, 'admin')
            ->put("http://localhost/dashboard/portfolios/{$portfolioB->id}", [
                'name' => 'Colliding Site',
                'slug' => $hyphenCandidate,
                'full_name' => 'Owner B',
            ])
            ->assertSessionHasErrors('slug');

        // Reject reserved subdomain on update
        $this->actingAs($this->adminB, 'admin')
            ->put("http://localhost/dashboard/portfolios/{$portfolioB->id}", [
                'name' => 'Reserved Site',
                'slug' => 'register',
                'full_name' => 'Owner B',
            ])
            ->assertSessionHasErrors('slug');
    }

    public function test_database_sessions_are_pruned_on_password_change_and_deactivation(): void
    {
        config(['session.driver' => 'database']);
        $table = (string) config('session.table', 'sessions');

        if (! Schema::hasTable($table)) {
            $this->markTestSkipped('Sessions table does not exist in current schema.');
        }

        $keepId = 'keep-session-' . uniqid();
        $staleId = 'stale-session-' . uniqid();

        DB::table($table)->insert([
            [
                'id' => $keepId,
                'user_id' => $this->adminA->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Audit Browser 1',
                'payload' => base64_encode('a:0:{}'),
                'last_activity' => now()->timestamp,
            ],
            [
                'id' => $staleId,
                'user_id' => $this->adminA->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Audit Browser 2',
                'payload' => base64_encode('a:0:{}'),
                'last_activity' => now()->timestamp,
            ],
        ]);

        $userService = app(UserService::class);
        $userService->revokeOtherSessions($this->adminA, $keepId);

        $this->assertDatabaseHas($table, ['id' => $keepId]);
        $this->assertDatabaseMissing($table, ['id' => $staleId]);

        $userService->toggleIndividualAdminStatus($this->superAdmin, $this->adminA);

        $this->assertDatabaseMissing($table, ['id' => $keepId]);
        $this->assertFalse($this->adminA->fresh()->is_active);
    }
}
