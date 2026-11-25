<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use DatabaseTransactions;

    public function test_guests_are_shown_the_landing_page(): void
    {
        foreach (['http://localhost/', 'http://127.0.0.1/'] as $rootUrl) {
            $response = $this->get($rootUrl);

            $response->assertOk();
            $response->assertSee('Your Work Deserves', false);
            $response->assertSee('One Platform. Endless Possibilities.', false);
            $response->assertSee('Get Started', false);
        }
    }

    public function test_authenticated_super_admin_is_redirected_to_the_admin_dashboard(): void
    {
        $superAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        foreach (['http://localhost/', 'http://127.0.0.1/'] as $rootUrl) {
            $this->actingAs($superAdmin, 'super_admin')
                ->get($rootUrl)
                ->assertRedirect('/admin/dashboard');
        }
    }

    public function test_authenticated_individual_admin_is_redirected_to_the_account_dashboard(): void
    {
        $individualAdmin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        foreach (['http://localhost/', 'http://127.0.0.1/'] as $rootUrl) {
            $this->actingAs($individualAdmin, 'admin')
                ->get($rootUrl)
                ->assertRedirect('/dashboard');
        }
    }

    public function test_logged_out_individual_admin_and_super_admin_see_landing_page_on_root(): void
    {
        $individualAdmin = User::factory()->create([
            'email' => 'root-admin-' . uniqid() . '@example.com',
            'password' => Hash::make('AdminPass123!'),
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $this->post('http://127.0.0.1/login', [
            'email' => $individualAdmin->email,
            'password' => 'AdminPass123!',
        ])->assertRedirect('http://127.0.0.1/dashboard');

        $this->post('http://127.0.0.1/logout')
            ->assertRedirect('http://127.0.0.1/login');

        $this->assertGuest('admin');
        $this->get('http://127.0.0.1/')
            ->assertOk()
            ->assertSee('Your Work Deserves', false);

        $superAdmin = User::factory()->create([
            'email' => 'root-super-' . uniqid() . '@example.com',
            'password' => Hash::make('SuperPass123!'),
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        $this->post('http://localhost/admin/login', [
            'email' => $superAdmin->email,
            'password' => 'SuperPass123!',
        ])->assertRedirect('http://localhost/admin/dashboard');

        $this->post('http://localhost/admin/logout')
            ->assertRedirect('http://localhost/admin/login');

        $this->assertGuest('super_admin');
        $this->get('http://localhost/')
            ->assertOk()
            ->assertSee('Your Work Deserves', false);
    }

    public function test_valid_session_persists_across_root_requests_after_fresh_auth_resolution(): void
    {
        $individualAdmin = User::factory()->create([
            'email' => 'persist-admin-' . uniqid() . '@example.com',
            'password' => Hash::make('PersistPass123!'),
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $this->post('http://127.0.0.1/login', [
            'email' => $individualAdmin->email,
            'password' => 'PersistPass123!',
        ])->assertRedirect('http://127.0.0.1/dashboard');

        // Simulate a fresh request cycle (refresh / server restart) with the persisted session
        $this->app['auth']->forgetGuards();

        $this->get('http://127.0.0.1/')
            ->assertRedirect('http://127.0.0.1/dashboard');
    }

    public function test_inactive_individual_admin_cannot_reach_dashboard_and_is_logged_out_on_root(): void
    {
        $inactiveAdmin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => false,
        ]);

        $this->actingAs($inactiveAdmin, 'admin')
            ->get('http://127.0.0.1/')
            ->assertOk()
            ->assertSee('Your Work Deserves', false);

        $this->assertGuest('admin');

        $this->get('http://127.0.0.1/dashboard')
            ->assertRedirect('http://127.0.0.1/login');
    }

    public function test_guard_sessions_never_authenticate_other_portal_and_dual_guards_render_portal_choice(): void
    {
        $superAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        $individualAdmin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        // Super Admin guard cannot access Individual Admin dashboard
        $this->actingAs($superAdmin, 'super_admin')
            ->get('http://localhost/dashboard')
            ->assertRedirect('http://localhost/login');

        // Individual Admin guard cannot access Super Admin dashboard
        $this->app['auth']->forgetGuards();
        $this->actingAs($individualAdmin, 'admin')
            ->get('http://localhost/admin/dashboard')
            ->assertRedirect('http://localhost/admin/login');

        // Both guards simultaneously authenticated on "/" -> explicit portal selection page (200 OK)
        $response = $this->actingAs($superAdmin, 'super_admin')
            ->actingAs($individualAdmin, 'admin')
            ->get('http://localhost/');

        $response->assertOk();
        $response->assertSee('Choose a portal to continue');
        $response->assertSee('/admin/dashboard', false);
        $response->assertSee('/dashboard', false);
        $this->assertAuthenticatedAs($superAdmin, 'super_admin');
        $this->assertAuthenticatedAs($individualAdmin, 'admin');
    }

    public function test_existing_auth_and_registration_routes_work_on_both_localhost_and_127_0_0_1(): void
    {
        foreach (['http://localhost', 'http://127.0.0.1'] as $baseUrl) {
            $this->get("{$baseUrl}/login")->assertOk();
            $this->get("{$baseUrl}/register")->assertOk();
            $this->get("{$baseUrl}/admin/login")->assertOk();
        }

        // Base-domain auth and admin routes remain blocked on subdomains and unknown external hosts
        $this->get('http://mahfuj.localhost/login')->assertNotFound();
        $this->get('http://mahfuj.localhost/admin/dashboard')->assertNotFound();
        $this->get('http://unknown-host.example.test/')->assertNotFound();
    }
}
