<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MutuallyExclusiveAuthenticationTest extends TestCase
{
    use DatabaseTransactions;

    private User $superAdmin;
    private User $individualAdmin;
    private User $inactiveIndividualAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'name' => 'Exclusive Super Admin',
            'email' => 'exclusive-super-' . uniqid() . '@example.test',
            'password' => Hash::make('SuperSecret123!'),
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        $this->individualAdmin = User::factory()->create([
            'name' => 'Exclusive Individual Admin',
            'email' => 'exclusive-admin-' . uniqid() . '@example.test',
            'password' => Hash::make('AdminSecret123!'),
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $this->inactiveIndividualAdmin = User::factory()->create([
            'name' => 'Inactive Individual Admin',
            'email' => 'exclusive-inactive-' . uniqid() . '@example.test',
            'password' => Hash::make('AdminSecret123!'),
            'role' => User::ROLE_ADMIN,
            'is_active' => false,
        ]);
    }

    public function test_super_admin_login_clears_individual_admin_session_and_regenerates_session_id(): void
    {
        // Authenticate Individual Admin first
        $this->post('http://localhost/login', [
            'email' => $this->individualAdmin->email,
            'password' => 'AdminSecret123!',
        ])->assertRedirect('http://localhost/dashboard');

        $oldSessionId = session()->getId();
        $oldCsrfToken = csrf_token();
        $this->assertAuthenticatedAs($this->individualAdmin, 'admin');

        // Login as Super Admin -> clears admin guard, regenerates session ID & CSRF token, enters /admin/dashboard
        $response = $this->post('http://localhost/admin/login', [
            'email' => $this->superAdmin->email,
            'password' => 'SuperSecret123!',
        ]);

        $response->assertRedirect('http://localhost/admin/dashboard');

        $this->assertAuthenticatedAs($this->superAdmin, 'super_admin');
        $this->assertGuest('admin');
        $this->assertGuest('web');
        $this->assertNotSame($oldSessionId, session()->getId());
        $this->assertNotSame($oldCsrfToken, csrf_token());

        // Old Individual Admin dashboard access is blocked
        $this->get('http://localhost/dashboard')->assertRedirect('http://localhost/login');
        $this->get('http://localhost/admin/dashboard')->assertOk();
    }

    public function test_individual_admin_login_clears_super_admin_session_and_regenerates_session_id(): void
    {
        // Authenticate Super Admin first
        $this->post('http://localhost/admin/login', [
            'email' => $this->superAdmin->email,
            'password' => 'SuperSecret123!',
        ])->assertRedirect('http://localhost/admin/dashboard');

        $oldSessionId = session()->getId();
        $oldCsrfToken = csrf_token();
        $this->assertAuthenticatedAs($this->superAdmin, 'super_admin');

        // Login as Individual Admin -> clears super_admin guard, regenerates session ID & CSRF token, enters /dashboard
        $response = $this->post('http://localhost/login', [
            'email' => $this->individualAdmin->email,
            'password' => 'AdminSecret123!',
        ]);

        $response->assertRedirect('http://localhost/dashboard');

        $this->assertAuthenticatedAs($this->individualAdmin, 'admin');
        $this->assertGuest('super_admin');
        $this->assertGuest('web');
        $this->assertNotSame($oldSessionId, session()->getId());
        $this->assertNotSame($oldCsrfToken, csrf_token());

        // Old Super Admin dashboard access is blocked
        $this->get('http://localhost/admin/dashboard')->assertRedirect('http://localhost/admin/login');
        $this->get('http://localhost/dashboard')->assertOk();
    }

    public function test_preexisting_dual_authenticated_session_fails_closed_without_exposing_identities(): void
    {
        // Simulate pre-existing dual-authenticated session in memory and session store
        $this->get('http://localhost/login')->assertOk();
        Auth::guard('super_admin')->login($this->superAdmin);
        Auth::guard('admin')->login($this->individualAdmin);
        session()->save();

        $dualSessionId = session()->getId();
        $dualCsrfToken = csrf_token();

        $rootResponse = $this->get('http://localhost/');

        // Never serves portal choice or either dashboard; redirects to guest landing page cleanly
        $rootResponse->assertRedirect('http://localhost')
            ->assertSessionHas('status', 'Your session was reset for security reasons. Please sign in again.');

        $this->assertGuest('super_admin');
        $this->assertGuest('admin');
        $this->assertGuest('web');
        $this->assertNotSame($dualSessionId, session()->getId());
        $this->assertNotSame($dualCsrfToken, csrf_token());

        // Recovery landing page shows generic notice and zero account emails/names/workspace UI
        $landingPage = $this->get('http://localhost/');
        $landingPage->assertOk()
            ->assertSee('Your Work Deserves', false)
            ->assertSee('Your session was reset for security reasons. Please sign in again.')
            ->assertDontSee('Choose a portal to continue')
            ->assertDontSee('Active Sessions Detected')
            ->assertDontSee('Open Super Admin')
            ->assertDontSee('Sign out Super Admin')
            ->assertDontSee($this->superAdmin->email)
            ->assertDontSee($this->superAdmin->name)
            ->assertDontSee($this->individualAdmin->email)
            ->assertDontSee($this->individualAdmin->name);

        // Subsequent protected dashboard attempts remain denied
        $this->get('http://localhost/admin/dashboard')->assertRedirect('http://localhost/admin/login');
        $this->get('http://localhost/dashboard')->assertRedirect('http://localhost/login');

        // Direct hit to protected routes with dual guards fails closed via central middleware
        $this->app['auth']->forgetGuards();
        Auth::guard('super_admin')->login($this->superAdmin);
        Auth::guard('admin')->login($this->individualAdmin);

        $this->get('http://localhost/admin/dashboard')
            ->assertRedirect('http://localhost')
            ->assertSessionHas('status', 'Your session was reset for security reasons. Please sign in again.');
        $this->assertGuest('super_admin');
        $this->assertGuest('admin');

        $this->app['auth']->forgetGuards();
        Auth::guard('super_admin')->login($this->superAdmin);
        Auth::guard('admin')->login($this->individualAdmin);

        $this->get('http://localhost/dashboard')
            ->assertRedirect('http://localhost')
            ->assertSessionHas('status', 'Your session was reset for security reasons. Please sign in again.');
        $this->assertGuest('super_admin');
        $this->assertGuest('admin');
    }

    public function test_workspace_selection_ui_and_obsolete_routes_are_completely_removed(): void
    {
        $this->assertFileDoesNotExist(resource_path('views/auth/portal-choice.blade.php'));

        foreach (['workspace', 'choose-workspace', 'portal-choice', 'choose-portal'] as $obsoletePath) {
            $this->get("http://localhost/{$obsoletePath}")->assertNotFound();
        }

        // Both standalone login pages remain functional
        $this->get('http://localhost/login')->assertOk()->assertSee('Sign in');
        $this->get('http://localhost/admin/login')->assertOk()->assertSee('Administrator');
    }

    public function test_intended_redirects_and_dashboards_are_role_locked_without_redirect_loops(): void
    {
        // 1. Dashboards return 200 directly for authorized guards and never bounce back to "/"
        $this->actingAs($this->superAdmin, 'super_admin')
            ->get('http://localhost/admin/dashboard')
            ->assertOk();

        $this->actingAs($this->superAdmin, 'super_admin')
            ->get('http://localhost/admin/login')
            ->assertRedirect('http://localhost/admin/dashboard');

        $this->app['auth']->forgetGuards();
        $this->actingAs($this->individualAdmin, 'admin')
            ->get('http://localhost/dashboard')
            ->assertOk();

        $this->actingAs($this->individualAdmin, 'admin')
            ->get('http://localhost/login')
            ->assertRedirect('http://localhost/dashboard');

        // 2. Cross-role, logout, or external url.intended values are stripped on Super Admin login
        $this->app['auth']->forgetGuards();
        foreach (['http://localhost/dashboard', 'http://localhost/admin/logout', 'https://evil.example/admin/dashboard'] as $badIntended) {
            session()->put('url.intended', $badIntended);
            $this->post('http://localhost/admin/login', [
                'email' => $this->superAdmin->email,
                'password' => 'SuperSecret123!',
            ])->assertRedirect('http://localhost/admin/dashboard');

            $this->post('http://localhost/admin/logout')->assertRedirect('http://localhost/admin/login');
        }

        // 3. Cross-role or external url.intended values are stripped on Individual Admin login
        foreach (['http://localhost/admin/dashboard', 'https://evil.example/dashboard'] as $badIntended) {
            session()->put('url.intended', $badIntended);
            $this->post('http://localhost/login', [
                'email' => $this->individualAdmin->email,
                'password' => 'AdminSecret123!',
            ])->assertRedirect('http://localhost/dashboard');

            $this->post('http://localhost/logout')->assertRedirect('http://localhost/login');
        }
    }

    public function test_root_routing_and_guard_isolation_enforce_single_role_access(): void
    {
        // 1. Guest sees landing page
        $this->get('http://localhost/')
            ->assertOk()
            ->assertSee('Your Work Deserves', false)
            ->assertDontSee('Choose a portal to continue');

        // 2. Super Admin alone reaches /admin -> /admin/dashboard and is blocked from /dashboard
        $this->actingAs($this->superAdmin, 'super_admin')
            ->get('http://localhost/')
            ->assertRedirect('/admin/dashboard');

        $this->actingAs($this->superAdmin, 'super_admin')
            ->get('http://localhost/admin')
            ->assertRedirect('http://localhost/admin/dashboard');

        $this->actingAs($this->superAdmin, 'super_admin')
            ->get('http://localhost/dashboard')
            ->assertRedirect('http://localhost/login');

        // 3. Individual Admin alone reaches /dashboard and is blocked from /admin/*
        $this->app['auth']->forgetGuards();
        $this->actingAs($this->individualAdmin, 'admin')
            ->get('http://localhost/')
            ->assertRedirect('/dashboard');

        $this->actingAs($this->individualAdmin, 'admin')
            ->get('http://localhost/admin/dashboard')
            ->assertRedirect('http://localhost/admin/login');

        // 4. Default web guard does not authorize either privileged workspace
        $this->app['auth']->forgetGuards();
        $this->actingAs($this->superAdmin, 'web')
            ->get('http://localhost/admin/dashboard')
            ->assertRedirect('http://localhost/admin/login');

        $this->actingAs($this->individualAdmin, 'web')
            ->get('http://localhost/dashboard')
            ->assertRedirect('http://localhost/login');

        // 5. Inactive Individual Admin loses access on both root and dashboard
        $this->app['auth']->forgetGuards();
        $this->actingAs($this->inactiveIndividualAdmin, 'admin')
            ->get('http://localhost/dashboard')
            ->assertRedirect('http://localhost/login');
        $this->assertGuest('admin');
    }

    public function test_stale_session_cannot_restore_previous_guard_after_switch_or_logout(): void
    {
        config(['session.driver' => 'database']);

        // Create real DB session for Super Admin and capture the browser session cookie
        $superLoginRes = $this->post('http://localhost/admin/login', [
            'email' => $this->superAdmin->email,
            'password' => 'SuperSecret123!',
        ]);
        $superLoginRes->assertRedirect('http://localhost/admin/dashboard');

        $staleSuperSessionId = session()->getId();
        $cookieName = config('session.cookie');
        $staleCookieValue = collect($superLoginRes->headers->getCookies())
            ->first(fn ($c) => $c->getName() === $cookieName)?->getValue();

        $this->assertNotNull($staleCookieValue);
        $this->assertNotNull(DB::table('sessions')->where('id', $staleSuperSessionId)->first());

        // Switch to Individual Admin sending the existing session cookie -> old DB session is destroyed
        $this->app['auth']->forgetGuards();
        $this->withUnencryptedCookie($cookieName, $staleCookieValue)
            ->post('http://localhost/login', [
                'email' => $this->individualAdmin->email,
                'password' => 'AdminSecret123!',
            ])->assertRedirect('http://localhost/dashboard');

        $this->assertNull(DB::table('sessions')->where('id', $staleSuperSessionId)->first());

        // Replaying the stale Super Admin session cookie cannot restore Super Admin access
        $this->app['auth']->forgetGuards();
        $this->withUnencryptedCookie($cookieName, $staleCookieValue)
            ->get('http://localhost/admin/dashboard')
            ->assertRedirect('http://localhost/admin/login');
    }

    public function test_logout_invalidates_session_regenerates_csrf_and_prevents_back_access_and_caching(): void
    {
        // Super Admin login & logout
        $this->post('http://localhost/admin/login', [
            'email' => $this->superAdmin->email,
            'password' => 'SuperSecret123!',
        ])->assertRedirect('http://localhost/admin/dashboard');

        $dashboardRes = $this->get('http://localhost/admin/dashboard');
        $dashboardRes->assertOk();
        $this->assertStringContainsString('no-store', (string) $dashboardRes->headers->get('Cache-Control'));

        $beforeLogoutSession = session()->getId();
        $beforeLogoutCsrf = csrf_token();

        $this->post('http://localhost/admin/logout')->assertRedirect('http://localhost/admin/login');

        $this->assertGuest('super_admin');
        $this->assertGuest('admin');
        $this->assertNotSame($beforeLogoutSession, session()->getId());
        $this->assertNotSame($beforeLogoutCsrf, csrf_token());
        $this->get('http://localhost/admin/dashboard')->assertRedirect('http://localhost/admin/login');

        // Individual Admin login & logout (also verify unrelated user's DB session is NOT wiped)
        DB::table('sessions')->insert([
            'id' => 'unrelated_user_session_' . uniqid(),
            'user_id' => $this->superAdmin->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'TestBrowser',
            'payload' => base64_encode('test'),
            'last_activity' => time(),
        ]);
        $unrelatedCountBefore = DB::table('sessions')->where('user_id', $this->superAdmin->id)->count();

        $this->post('http://localhost/login', [
            'email' => $this->individualAdmin->email,
            'password' => 'AdminSecret123!',
        ])->assertRedirect('http://localhost/dashboard');

        $indivDashboard = $this->get('http://localhost/dashboard');
        $indivDashboard->assertOk();
        $this->assertStringContainsString('no-store', (string) $indivDashboard->headers->get('Cache-Control'));

        $beforeIndivLogoutSession = session()->getId();
        $beforeIndivLogoutCsrf = csrf_token();

        $this->post('http://localhost/logout')->assertRedirect('http://localhost/login');

        $this->assertGuest('admin');
        $this->assertGuest('super_admin');
        $this->assertNotSame($beforeIndivLogoutSession, session()->getId());
        $this->assertNotSame($beforeIndivLogoutCsrf, csrf_token());
        $this->get('http://localhost/dashboard')->assertRedirect('http://localhost/login');

        // Unrelated user session in DB remains completely untouched
        $this->assertSame(
            $unrelatedCountBefore,
            DB::table('sessions')->where('user_id', $this->superAdmin->id)->count()
        );
    }
}
