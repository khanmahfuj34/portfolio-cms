<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PortalGuardIsolationTest extends TestCase
{
    use DatabaseTransactions;

    private User $superAdmin;
    private User $individualAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'name' => 'Root Super Admin',
            'email' => 'portal-super-' . uniqid() . '@example.com',
            'password' => Hash::make('SuperPass123!'),
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        $this->individualAdmin = User::factory()->create([
            'name' => 'Individual Creator',
            'email' => 'portal-admin-' . uniqid() . '@example.com',
            'password' => Hash::make('AdminPass123!'),
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);
    }

    public function test_authenticated_super_admin_can_view_individual_admin_login_page(): void
    {
        $response = $this->actingAs($this->superAdmin, 'super_admin')
            ->get('http://localhost/login');

        $response->assertOk();
        $response->assertSee('Sign in');
        $response->assertSee(route('login.submit'), false);
        $this->assertAuthenticatedAs($this->superAdmin, 'super_admin');
        $this->assertGuest('admin');
    }

    public function test_authenticated_super_admin_posting_empty_credentials_to_login_stays_on_login_with_errors(): void
    {
        // Even if previous URL was /admin/dashboard, failed validation on /login must stay on /login
        $this->actingAs($this->superAdmin, 'super_admin')
            ->get('http://localhost/admin/dashboard')
            ->assertOk();

        $response = $this->post('http://localhost/login', [
            'email' => '',
            'password' => '',
        ]);

        $response->assertRedirect('http://localhost/login');
        $response->assertSessionHasErrors(['email', 'password']);
        $this->assertAuthenticatedAs($this->superAdmin, 'super_admin');
        $this->assertGuest('admin');
    }

    public function test_authenticated_super_admin_can_log_into_individual_portal_only_with_individual_credentials(): void
    {
        $this->actingAs($this->superAdmin, 'super_admin')
            ->get('http://localhost/admin/dashboard')
            ->assertOk();

        // Attempting /login with Super Admin credentials must fail and redirect back to /login
        $this->post('http://localhost/login', [
            'email' => $this->superAdmin->email,
            'password' => 'SuperPass123!',
        ])
            ->assertRedirect('http://localhost/login')
            ->assertSessionHasErrors(['email']);

        $this->assertGuest('admin');
        $this->assertAuthenticatedAs($this->superAdmin, 'super_admin');

        // Submitting valid Individual Admin credentials logs into 'admin' guard and redirects to /dashboard
        $this->post('http://localhost/login', [
            'email' => $this->individualAdmin->email,
            'password' => 'AdminPass123!',
        ])->assertRedirect('http://localhost/dashboard');

        $this->assertAuthenticatedAs($this->individualAdmin, 'admin');
        $this->assertAuthenticatedAs($this->superAdmin, 'super_admin');
    }

    public function test_authenticated_individual_admin_can_view_and_post_to_super_admin_login_page(): void
    {
        $this->actingAs($this->individualAdmin, 'admin')
            ->get('http://localhost/dashboard')
            ->assertOk();

        // GET /admin/login renders Super Admin login page rather than redirecting to /dashboard
        $this->get('http://localhost/admin/login')
            ->assertOk()
            ->assertSee('Administrator')
            ->assertSee(route('admin.login.submit'), false);

        // Empty POST /admin/login redirects back to /admin/login with validation errors
        $this->post('http://localhost/admin/login', [
            'email' => '',
            'password' => '',
        ])
            ->assertRedirect('http://localhost/admin/login')
            ->assertSessionHasErrors(['email', 'password']);

        $this->assertGuest('super_admin');
        $this->assertAuthenticatedAs($this->individualAdmin, 'admin');

        // POST /admin/login with Individual Admin credentials fails back to /admin/login
        $this->post('http://localhost/admin/login', [
            'email' => $this->individualAdmin->email,
            'password' => 'AdminPass123!',
        ])
            ->assertRedirect('http://localhost/admin/login')
            ->assertSessionHasErrors(['email']);

        $this->assertGuest('super_admin');

        // POST /admin/login with valid Super Admin credentials succeeds on 'super_admin' guard
        $this->post('http://localhost/admin/login', [
            'email' => $this->superAdmin->email,
            'password' => 'SuperPass123!',
        ])->assertRedirect('http://localhost/admin/dashboard');

        $this->assertAuthenticatedAs($this->superAdmin, 'super_admin');
        $this->assertAuthenticatedAs($this->individualAdmin, 'admin');
    }

    public function test_failed_login_never_redirects_to_another_roles_dashboard(): void
    {
        // Super Admin authenticated, prior request was /admin/users, post wrong password to /login
        $this->actingAs($this->superAdmin, 'super_admin')
            ->get('http://localhost/admin/users')
            ->assertOk();

        $this->post('http://localhost/login', [
            'email' => $this->individualAdmin->email,
            'password' => 'WrongPassword999!',
        ])
            ->assertRedirect('http://localhost/login')
            ->assertSessionHasErrors(['email']);

        // Individual Admin authenticated, prior request was /dashboard/account, post wrong password to /admin/login
        $this->app['auth']->forgetGuards();
        $this->actingAs($this->individualAdmin, 'admin')
            ->get('http://localhost/dashboard/account')
            ->assertOk();

        $this->post('http://localhost/admin/login', [
            'email' => $this->superAdmin->email,
            'password' => 'WrongPassword999!',
        ])
            ->assertRedirect('http://localhost/admin/login')
            ->assertSessionHasErrors(['email']);
    }

    public function test_portals_operate_independently_and_logout_from_one_preserves_the_other(): void
    {
        // Log into both portals independently
        $this->post('http://localhost/admin/login', [
            'email' => $this->superAdmin->email,
            'password' => 'SuperPass123!',
        ])->assertRedirect('http://localhost/admin/dashboard');

        $this->post('http://localhost/login', [
            'email' => $this->individualAdmin->email,
            'password' => 'AdminPass123!',
        ])->assertRedirect('http://localhost/dashboard');

        // Both dashboards work concurrently and render their own identities
        $this->get('http://localhost/admin/dashboard')
            ->assertOk()
            ->assertSee('Root Super Admin');

        $this->get('http://localhost/dashboard')
            ->assertOk()
            ->assertSee('Welcome, Individual Creator');

        // Logging out of Individual Admin portal preserves Super Admin session
        $this->post('http://localhost/logout')
            ->assertRedirect('http://localhost/login');

        $this->assertGuest('admin');
        $this->assertAuthenticatedAs($this->superAdmin, 'super_admin');
        $this->get('http://localhost/admin/dashboard')->assertOk();
        $this->get('http://localhost/dashboard')->assertRedirect('http://localhost/login');

        // Re-login Individual Admin, then log out Super Admin portal -> Individual Admin stays logged in
        $this->post('http://localhost/login', [
            'email' => $this->individualAdmin->email,
            'password' => 'AdminPass123!',
        ])->assertRedirect('http://localhost/dashboard');

        $this->post('http://localhost/admin/logout')
            ->assertRedirect('http://localhost/admin/login');

        $this->assertGuest('super_admin');
        $this->assertAuthenticatedAs($this->individualAdmin, 'admin');
        $this->get('http://localhost/dashboard')->assertOk();
        $this->get('http://localhost/admin/dashboard')->assertRedirect('http://localhost/admin/login');

        // Final logout invalidates remaining session completely
        $this->post('http://localhost/logout')
            ->assertRedirect('http://localhost/login');

        $this->assertGuest('admin');
        $this->assertGuest('super_admin');
    }
}
