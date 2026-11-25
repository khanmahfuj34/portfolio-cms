<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AdminAccountAuthTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('127.0.0.1');
    }

    public function test_individual_admin_registration_and_login_pages_render_and_are_not_shadowed_by_slug(): void
    {
        $registerRes = $this->get('/register');
        $registerRes->assertStatus(200);
        $registerRes->assertSee('Create account');
        $registerRes->assertSee('name="_token"', false);

        $loginRes = $this->get('/login');
        $loginRes->assertStatus(200);
        $loginRes->assertSee('Sign in');
        $loginRes->assertSee('name="_token"', false);
    }

    public function test_individual_admin_can_register_and_cannot_spoof_role_or_is_active(): void
    {
        $email = 'new.creator.' . uniqid() . '@example.com';

        $oldSessionId = session()->getId();

        $response = $this->post('/register', [
            'name' => '  Mahfuj Creator  ',
            'email' => strtoupper($email),
            'password' => 'StrongPass123!',
            'password_confirmation' => 'StrongPass123!',
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => false,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/dashboard');

        $this->assertAuthenticated('admin');
        $this->assertGuest('super_admin');
        $this->assertNotEquals($oldSessionId, session()->getId());

        $user = User::where('email', strtolower($email))->firstOrFail();
        $this->assertSame('Mahfuj Creator', $user->name);
        $this->assertSame(User::ROLE_ADMIN, $user->role);
        $this->assertTrue($user->is_active);
        $this->assertTrue(Hash::check('StrongPass123!', $user->password));
        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user->isSuperAdmin());
    }

    public function test_registration_validates_required_fields_and_unique_email(): void
    {
        $existingEmail = 'taken.' . uniqid() . '@example.com';
        User::factory()->create([
            'email' => $existingEmail,
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $this->from('/register')->post('/register', [
            'name' => '',
            'email' => $existingEmail,
            'password' => 'short',
            'password_confirmation' => 'mismatch',
        ])->assertRedirect('/register')
            ->assertSessionHasErrors(['name', 'email', 'password']);

        $this->assertGuest('admin');
    }

    public function test_individual_admin_can_login_and_logout_with_session_regeneration(): void
    {
        $email = 'admin.login.' . uniqid() . '@example.com';
        $user = User::factory()->create([
            'email' => $email,
            'password' => 'SecretPass123!',
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $loginResponse = $this->post('/login', [
            'email' => '  ' . strtoupper($email) . '  ',
            'password' => 'SecretPass123!',
        ]);

        $loginResponse->assertStatus(302);
        $loginResponse->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user, 'admin');
        $this->assertGuest('super_admin');

        $logoutResponse = $this->post('/logout');
        $logoutResponse->assertStatus(302);
        $logoutResponse->assertRedirect('/login');
        $this->assertGuest('admin');
    }

    public function test_role_separation_prevents_cross_portal_login(): void
    {
        $superAdmin = User::factory()->create([
            'email' => 'super.' . uniqid() . '@example.com',
            'password' => 'SuperSecret123!',
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        $individualAdmin = User::factory()->create([
            'email' => 'indiv.' . uniqid() . '@example.com',
            'password' => 'IndivSecret123!',
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        // 1. Super Admin cannot log into /login (Individual Admin portal)
        $this->from('/login')->post('/login', [
            'email' => $superAdmin->email,
            'password' => 'SuperSecret123!',
        ])->assertRedirect('/login')->assertSessionHasErrors(['email']);
        $this->assertGuest('admin');

        // 2. Individual Admin cannot log into /admin/login (Super Admin portal)
        $this->from('/admin/login')->post('/admin/login', [
            'email' => $individualAdmin->email,
            'password' => 'IndivSecret123!',
        ])->assertRedirect('/admin/login')->assertSessionHasErrors(['email']);
        $this->assertGuest('super_admin');

        // 3. Super Admin login on /admin/login still works cleanly
        $this->post('/admin/login', [
            'email' => $superAdmin->email,
            'password' => 'SuperSecret123!',
        ])->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($superAdmin, 'super_admin');
        $this->assertGuest('admin');

        // 4. Individual Admin hitting /admin/dashboard cannot access Super Admin portal
        $this->post('/admin/logout');
        $this->actingAs($individualAdmin, 'admin')->get('/admin/dashboard')->assertRedirect('/admin/login');
        $this->actingAs($individualAdmin, 'super_admin')->get('/admin/dashboard')->assertStatus(403);
    }

    public function test_inactive_individual_admin_cannot_login(): void
    {
        $inactive = User::factory()->create([
            'email' => 'inactive.' . uniqid() . '@example.com',
            'password' => 'SecretPass123!',
            'role' => User::ROLE_ADMIN,
            'is_active' => false,
        ]);

        $this->from('/login')->post('/login', [
            'email' => $inactive->email,
            'password' => 'SecretPass123!',
        ])->assertRedirect('/login')->assertSessionHasErrors(['email']);

        $this->assertGuest('admin');
    }

    public function test_login_rate_limiting_blocks_repeated_failed_attempts(): void
    {
        $email = 'ratelimit.' . uniqid() . '@example.com';
        User::factory()->create([
            'email' => $email,
            'password' => 'CorrectPass123!',
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->from('/login')->post('/login', [
                'email' => $email,
                'password' => 'WrongPassword!',
            ])->assertSessionHasErrors(['email']);
        }

        $response = $this->from('/login')->post('/login', [
            'email' => $email,
            'password' => 'CorrectPass123!',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['email']);
        $this->assertStringContainsString('Too many login attempts', session('errors')->first('email'));
        $this->assertGuest('admin');
    }

    public function test_register_is_reserved_as_slug_and_subdomain(): void
    {
        $superAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        $this->actingAs($superAdmin, 'super_admin')->post('/admin/portfolios', [
            'name' => 'Register Spoof',
            'slug' => 'register',
            'full_name' => 'Spoof Owner',
            'nav_items' => [
                [
                    'label' => 'Home',
                    'type' => 'section',
                    'section_key' => 'home',
                    'sort_order' => 1,
                    'is_visible' => true,
                ],
            ],
        ])->assertSessionHasErrors(['slug']);

        $this->get('http://register.localhost/')->assertStatus(404);
    }
}
