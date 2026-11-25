<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureIndividualAdmin;
use App\Http\Middleware\EnsureSuperAdmin;
use App\Models\Portfolio;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class PortfolioPolicyTest extends TestCase
{
    use DatabaseTransactions;

    private User $superAdmin;
    private User $adminA;
    private User $adminB;
    private User $inactiveAdmin;
    private Portfolio $portfolioA;
    private Portfolio $portfolioB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        $this->adminA = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $this->adminB = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $this->inactiveAdmin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => false,
        ]);

        $this->portfolioA = Portfolio::create([
            'user_id' => $this->adminA->id,
            'name' => 'Admin A Portfolio',
            'slug' => 'policy-admin-a-' . uniqid(),
            'status' => 'draft',
        ]);

        $this->portfolioB = Portfolio::create([
            'user_id' => $this->adminB->id,
            'name' => 'Admin B Portfolio',
            'slug' => 'policy-admin-b-' . uniqid(),
            'status' => 'draft',
        ]);
    }

    public function test_user_role_constants_and_helpers(): void
    {
        $this->assertSame('super_admin', User::ROLE_SUPER_ADMIN);
        $this->assertSame('admin', User::ROLE_ADMIN);

        $this->assertTrue($this->superAdmin->isSuperAdmin());
        $this->assertFalse($this->superAdmin->isAdmin());
        $this->assertTrue($this->superAdmin->isActive());

        $this->assertTrue($this->adminA->isAdmin());
        $this->assertFalse($this->adminA->isSuperAdmin());
        $this->assertTrue($this->adminA->hasRole(User::ROLE_ADMIN));

        $this->assertFalse($this->inactiveAdmin->isActive());
        $this->assertFalse($this->inactiveAdmin->isAdmin());
        $this->assertFalse($this->inactiveAdmin->isSuperAdmin());
    }

    public function test_super_admin_can_perform_all_portfolio_actions_across_all_owners(): void
    {
        $gate = Gate::forUser($this->superAdmin);

        $this->assertTrue($gate->allows('viewAny', Portfolio::class));
        $this->assertTrue($gate->allows('create', Portfolio::class));

        foreach (['view', 'update', 'delete', 'publish', 'unpublish'] as $ability) {
            $this->assertTrue($gate->allows($ability, $this->portfolioA));
            $this->assertTrue($gate->allows($ability, $this->portfolioB));
        }
    }

    public function test_individual_admin_can_only_manage_own_portfolios(): void
    {
        $gate = Gate::forUser($this->adminA);

        $this->assertTrue($gate->allows('viewAny', Portfolio::class));
        $this->assertTrue($gate->allows('create', Portfolio::class));

        foreach (['view', 'update', 'delete', 'publish', 'unpublish'] as $ability) {
            $this->assertTrue($gate->allows($ability, $this->portfolioA), "Owner should be allowed to {$ability}.");
            $this->assertFalse($gate->allows($ability, $this->portfolioB), "Non-owner must be denied {$ability}.");
        }
    }

    public function test_inactive_or_non_admin_users_are_denied_all_portfolio_abilities(): void
    {
        $plainUser = User::factory()->create([
            'role' => 'user',
            'is_active' => true,
        ]);

        foreach ([$this->inactiveAdmin, $plainUser] as $deniedUser) {
            $gate = Gate::forUser($deniedUser);
            $this->assertFalse($gate->allows('viewAny', Portfolio::class));
            $this->assertFalse($gate->allows('create', Portfolio::class));
            foreach (['view', 'update', 'delete', 'publish', 'unpublish'] as $ability) {
                $this->assertFalse($gate->allows($ability, $this->portfolioA));
            }
        }
    }

    public function test_ensure_individual_admin_middleware_allows_active_admin_and_blocks_others(): void
    {
        $middleware = new EnsureIndividualAdmin();

        // Active Individual Admin passes
        $request = Request::create('/dashboard', 'GET');
        $request->setUserResolver(fn () => $this->adminA);
        $response = $middleware->handle($request, fn () => response('OK', 200));
        $this->assertSame(200, $response->getStatusCode());

        // Super Admin is blocked with 403 on Individual Admin routes
        $superReq = Request::create('/dashboard', 'GET');
        $superReq->setUserResolver(fn () => $this->superAdmin);
        $this->expectException(HttpException::class);
        try {
            $middleware->handle($superReq, fn () => response('OK', 200));
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
            throw $e;
        }
    }

    public function test_ensure_individual_admin_middleware_blocks_inactive_admin(): void
    {
        $middleware = new EnsureIndividualAdmin();

        $jsonReq = Request::create('/dashboard/portfolios', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
        $jsonReq->setUserResolver(fn () => $this->inactiveAdmin);

        $response = $middleware->handle($jsonReq, fn () => response('OK', 200));
        $this->assertSame(403, $response->getStatusCode());
    }

    public function test_ensure_super_admin_middleware_rejects_individual_admin(): void
    {
        $middleware = new EnsureSuperAdmin();

        $request = Request::create('/admin/dashboard', 'GET');
        $request->setUserResolver(fn () => $this->adminA);

        $this->expectException(HttpException::class);
        try {
            $middleware->handle($request, fn () => response('OK', 200));
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
            throw $e;
        }
    }
}
