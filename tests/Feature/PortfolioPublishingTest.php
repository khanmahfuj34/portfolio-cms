<?php

namespace Tests\Feature;

use App\Models\Portfolio;
use App\Models\User;
use App\Services\PortfolioService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use InvalidArgumentException;
use Tests\TestCase;

class PortfolioPublishingTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private PortfolioService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $this->service = app(PortfolioService::class);
    }

    public function test_draft_portfolio_successfully_publishes(): void
    {
        $portfolio = Portfolio::create([
            'user_id' => $this->admin->id,
            'name' => 'Publish Target',
            'slug' => 'publish-target',
            'status' => 'draft',
            'published_at' => null,
        ]);

        $response = $this->actingAs($this->admin, 'super_admin')->patch(
            route('admin.portfolios.publish', $portfolio)
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $portfolio->refresh();
        $this->assertSame('published', $portfolio->status);
        $this->assertNotNull($portfolio->published_at);
    }

    public function test_published_portfolio_successfully_unpublishes(): void
    {
        $portfolio = Portfolio::create([
            'user_id' => $this->admin->id,
            'name' => 'Unpublish Target',
            'slug' => 'unpublish-target',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($this->admin, 'super_admin')->patch(
            route('admin.portfolios.unpublish', $portfolio)
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $portfolio->refresh();
        $this->assertSame('draft', $portfolio->status);
        $this->assertNull($portfolio->published_at);
    }

    public function test_published_at_is_correctly_assigned_on_publish(): void
    {
        $portfolio = Portfolio::create([
            'user_id' => $this->admin->id,
            'name' => 'Timestamp Check',
            'slug' => 'timestamp-check',
            'status' => 'draft',
            'published_at' => null,
        ]);

        $this->actingAs($this->admin, 'super_admin')->patch(
            route('admin.portfolios.publish', $portfolio)
        );

        $portfolio->refresh();
        $this->assertNotNull($portfolio->published_at);
        $this->assertTrue($portfolio->published_at->isToday());
    }

    public function test_published_at_becomes_null_after_unpublishing(): void
    {
        $portfolio = Portfolio::create([
            'user_id' => $this->admin->id,
            'name' => 'Null Check',
            'slug' => 'null-check',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->actingAs($this->admin, 'super_admin')->patch(
            route('admin.portfolios.unpublish', $portfolio)
        );

        $portfolio->refresh();
        $this->assertNull($portfolio->published_at);
    }

    public function test_unauthorized_guest_cannot_publish_or_unpublish(): void
    {
        $portfolio = Portfolio::create([
            'user_id' => $this->admin->id,
            'name' => 'Guest Block Test',
            'slug' => 'guest-block-test',
            'status' => 'draft',
            'published_at' => null,
        ]);

        // Publish as guest
        $pubRes = $this->patch(route('admin.portfolios.publish', $portfolio));
        $pubRes->assertRedirect(route('admin.login'));

        $portfolio->refresh();
        $this->assertSame('draft', $portfolio->status);
        $this->assertNull($portfolio->published_at);

        // Unpublish as guest
        $unpubRes = $this->patch(route('admin.portfolios.unpublish', $portfolio));
        $unpubRes->assertRedirect(route('admin.login'));
    }

    public function test_inactive_super_admin_cannot_publish(): void
    {
        $inactiveAdmin = User::factory()->create([
            'role' => 'super_admin',
            'is_active' => false,
        ]);

        $portfolio = Portfolio::create([
            'user_id' => $this->admin->id,
            'name' => 'Inactive Test',
            'slug' => 'inactive-test',
            'status' => 'draft',
            'published_at' => null,
        ]);

        $response = $this->actingAs($inactiveAdmin, 'super_admin')->patch(
            route('admin.portfolios.publish', $portfolio)
        );

        $response->assertStatus(403);

        $portfolio->refresh();
        $this->assertSame('draft', $portfolio->status);
    }

    public function test_invalid_portfolio_id_returns_404(): void
    {
        $response = $this->actingAs($this->admin, 'super_admin')->patch(
            '/admin/portfolios/999999/publish'
        );

        $response->assertStatus(404);
    }

    public function test_redundant_publish_is_defensively_handled_via_controller(): void
    {
        $portfolio = Portfolio::create([
            'user_id' => $this->admin->id,
            'name' => 'Redundant Publish Test',
            'slug' => 'redundant-pub',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->actingAs($this->admin, 'super_admin')->patch(
            route('admin.portfolios.publish', $portfolio)
        );

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_redundant_unpublish_is_defensively_handled_via_controller(): void
    {
        $portfolio = Portfolio::create([
            'user_id' => $this->admin->id,
            'name' => 'Redundant Unpublish Test',
            'slug' => 'redundant-unpub',
            'status' => 'draft',
            'published_at' => null,
        ]);

        $response = $this->actingAs($this->admin, 'super_admin')->patch(
            route('admin.portfolios.unpublish', $portfolio)
        );

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_service_methods_throw_exceptions_on_invalid_state_transitions(): void
    {
        $portfolio = Portfolio::create([
            'user_id' => $this->admin->id,
            'name' => 'Service Exception Test',
            'slug' => 'service-exception-test',
            'status' => 'draft',
            'published_at' => null,
        ]);

        // Unpublishing an already draft portfolio must throw InvalidArgumentException
        $this->expectException(InvalidArgumentException::class);
        $this->service->unpublish($portfolio);
    }

    public function test_service_publish_throws_exception_if_already_published(): void
    {
        $portfolio = Portfolio::create([
            'user_id' => $this->admin->id,
            'name' => 'Service Publish Exception Test',
            'slug' => 'service-pub-exception-test',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->service->publish($portfolio);
    }

    public function test_existing_portfolio_creation_remains_strictly_draft(): void
    {
        $created = $this->service->create(
            $this->admin,
            ['name' => 'Creation Invariant', 'slug' => 'creation-invariant'],
            ['full_name' => 'Owner Person'],
            [['label' => 'Home', 'section_key' => 'home', 'sort_order' => 1, 'is_visible' => true]]
        );

        $this->assertSame('draft', $created->status);
        $this->assertNull($created->published_at);
    }
}
