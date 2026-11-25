<?php

namespace Tests\Feature;

use App\Models\Portfolio;
use App\Models\User;
use App\Services\PortfolioPreviewService;
use App\Services\PortfolioService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OwnerDraftPreviewTest extends TestCase
{
    use DatabaseTransactions;

    private User $superAdmin;
    private User $ownerA;
    private User $ownerB;
    private PortfolioService $portfolioService;
    private PortfolioPreviewService $previewService;
    private Portfolio $draftA;
    private Portfolio $draftB;

    protected function setUp(): void
    {
        parent::setUp();

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

        $this->portfolioService = app(PortfolioService::class);
        $this->previewService = app(PortfolioPreviewService::class);

        $this->draftA = $this->portfolioService->create(
            $this->ownerA,
            ['name' => 'Owner A Draft', 'slug' => 'owner-a-draft-' . uniqid()],
            ['full_name' => 'Owner A Private Draft'],
            [['label' => 'Home', 'section_key' => 'home', 'sort_order' => 1, 'is_visible' => true]]
        );

        $this->draftB = $this->portfolioService->create(
            $this->ownerB,
            ['name' => 'Owner B Draft', 'slug' => 'owner-b-draft-' . uniqid()],
            ['full_name' => 'Owner B Private Draft'],
            [['label' => 'Home', 'section_key' => 'home', 'sort_order' => 1, 'is_visible' => true]]
        );
    }

    public function test_owner_and_super_admin_can_preview_draft_while_guests_and_other_admins_are_blocked(): void
    {
        $domainA = $this->draftA->primaryDomain->domain;

        // Public guest blocked
        $this->get("http://{$domainA}/")->assertStatus(404);

        // Other Individual Admin blocked
        $this->actingAs($this->ownerB, 'admin')->get("http://{$domainA}/")->assertStatus(404);
        $this->actingAs($this->ownerB, 'admin')->get("http://localhost/{$this->draftA->slug}")->assertStatus(404);

        // Owning Individual Admin can preview
        $ownerRes = $this->actingAs($this->ownerA, 'admin')->get("http://{$domainA}/");
        $ownerRes->assertStatus(200);
        $ownerRes->assertSee('Admin Preview Mode');
        $ownerRes->assertSee('Owner A Private Draft');
        $ownerRes->assertHeader('X-Robots-Tag', 'noindex, nofollow');

        // Super Admin retains access to all drafts
        $superRes = $this->actingAs($this->superAdmin, 'super_admin')->get("http://{$domainA}/");
        $superRes->assertStatus(200);
        $superRes->assertSee('Admin Preview Mode');
        $superRes->assertSee('Owner A Private Draft');
    }

    public function test_owner_can_generate_and_use_signed_preview_url_without_subdomain_session(): void
    {
        $redirectResponse = $this->actingAs($this->ownerA, 'admin')
            ->get("http://localhost/dashboard/portfolios/{$this->draftA->id}/preview");

        $redirectResponse->assertStatus(302);
        $signedUrl = $redirectResponse->headers->get('Location');
        $this->assertNotNull($signedUrl);
        $this->assertStringContainsString("{$this->draftA->primaryDomain->domain}/?preview_token=", $signedUrl);

        // Simulate browser opening signed URL without session cookie (guest)
        $this->app['auth']->forgetGuards();
        $previewRes = $this->get($signedUrl);
        $previewRes->assertStatus(200);
        $previewRes->assertSee('Admin Preview Mode');
        $previewRes->assertSee('Owner A Private Draft');
        $previewRes->assertHeader('X-Robots-Tag', 'noindex, nofollow');

        // Non-owner cannot generate a preview URL for Owner A's draft
        $this->actingAs($this->ownerB, 'admin')
            ->get("http://localhost/dashboard/portfolios/{$this->draftA->id}/preview")
            ->assertStatus(403);
    }

    public function test_tampered_expired_cross_portfolio_and_revoked_preview_tokens_are_rejected(): void
    {
        $domainA = $this->draftA->primaryDomain->domain;
        $domainB = $this->draftB->primaryDomain->domain;

        $validTokenA = $this->previewService->generateToken($this->draftA, $this->ownerA, 15);

        // 1. Tampered token rejected
        $this->get("http://{$domainA}/?preview_token=" . urlencode($validTokenA . 'tamper'))->assertStatus(404);

        // 2. Token for Draft A cannot preview Draft B
        $this->get("http://{$domainB}/?preview_token=" . urlencode($validTokenA))->assertStatus(404);

        // 3. Expired token rejected
        $expiredToken = $this->previewService->generateToken($this->draftA, $this->ownerA, -5);
        $this->get("http://{$domainA}/?preview_token=" . urlencode($expiredToken))->assertStatus(404);

        // 4. Revoked tokens rejected
        $this->actingAs($this->ownerA, 'admin')
            ->post("http://localhost/dashboard/portfolios/{$this->draftA->id}/preview/revoke")
            ->assertRedirect();

        $this->app['auth']->forgetGuards();
        $this->get("http://{$domainA}/?preview_token=" . urlencode($validTokenA))->assertStatus(404);
    }

    public function test_deactivated_owner_invalidates_active_preview_token(): void
    {
        $domainA = $this->draftA->primaryDomain->domain;
        $token = $this->previewService->generateToken($this->draftA, $this->ownerA, 15);

        $this->get("http://{$domainA}/?preview_token=" . urlencode($token))->assertStatus(200);

        $this->ownerA->is_active = false;
        $this->ownerA->save();

        $this->get("http://{$domainA}/?preview_token=" . urlencode($token))->assertStatus(404);
    }
}
