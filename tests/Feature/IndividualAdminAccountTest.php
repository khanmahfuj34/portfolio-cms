<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\PortfolioPreviewService;
use App\Services\PortfolioService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class IndividualAdminAccountTest extends TestCase
{
    use DatabaseTransactions;

    private User $adminA;
    private User $adminB;
    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        $this->adminA = User::factory()->create([
            'name' => 'Alpha Account',
            'email' => 'alpha.acc.' . uniqid() . '@example.com',
            'password' => 'InitialPass123!',
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $this->adminB = User::factory()->create([
            'name' => 'Beta Account',
            'email' => 'beta.acc.' . uniqid() . '@example.com',
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);
    }

    public function test_account_page_requires_individual_admin(): void
    {
        $this->get('/dashboard/account')->assertRedirect('/login');
        $this->actingAs($this->superAdmin, 'super_admin')->get('/dashboard/account')->assertRedirect('/login');
        $this->actingAs($this->superAdmin, 'admin')->get('/dashboard/account')->assertStatus(403);

        $this->actingAs($this->adminA, 'admin')
            ->get('/dashboard/account')
            ->assertStatus(200)
            ->assertSee($this->adminA->email);
    }

    public function test_individual_admin_can_update_own_name_and_email_without_privilege_escalation(): void
    {
        $newEmail = 'alpha.updated.' . uniqid() . '@example.com';

        $response = $this->actingAs($this->adminA, 'admin')
            ->patch('/dashboard/account', [
                'name' => '  Alpha Renamed  ',
                'email' => strtoupper($newEmail),
                'role' => User::ROLE_SUPER_ADMIN,
                'is_active' => false,
            ]);

        $response->assertRedirect('/dashboard/account');
        $response->assertSessionHas('success');

        $this->adminA->refresh();
        $this->assertSame('Alpha Renamed', $this->adminA->name);
        $this->assertSame(strtolower($newEmail), $this->adminA->email);
        $this->assertSame(User::ROLE_ADMIN, $this->adminA->role);
        $this->assertTrue($this->adminA->is_active);
    }

    public function test_account_update_validates_email_uniqueness(): void
    {
        // Rejects another user's email
        $this->actingAs($this->adminA, 'admin')
            ->from('/dashboard/account')
            ->patch('/dashboard/account', [
                'name' => 'Alpha Account',
                'email' => $this->adminB->email,
            ])
            ->assertRedirect('/dashboard/account')
            ->assertSessionHasErrors(['email']);

        // Allows keeping own email when updating name
        $this->actingAs($this->adminA, 'admin')
            ->patch('/dashboard/account', [
                'name' => 'Alpha Same Email',
                'email' => $this->adminA->email,
            ])
            ->assertRedirect('/dashboard/account')
            ->assertSessionHasNoErrors();
    }

    public function test_password_change_requires_current_password_and_rotates_session_and_preview_tokens(): void
    {
        $portfolio = app(PortfolioService::class)->create(
            $this->adminA,
            ['name' => 'Token Revoke Portfolio', 'slug' => 'pw-revoke-' . uniqid()],
            ['full_name' => 'Alpha'],
            [['label' => 'Home', 'section_key' => 'home', 'sort_order' => 1, 'is_visible' => true]]
        );
        $previewService = app(PortfolioPreviewService::class);
        $tokenBeforePwChange = $previewService->generateToken($portfolio, $this->adminA, 15);

        // Wrong current password rejected
        $this->actingAs($this->adminA, 'admin')
            ->from('/dashboard/account')
            ->put('/dashboard/account/password', [
                'current_password' => 'WrongPassword!',
                'password' => 'NewSecretPass123!',
                'password_confirmation' => 'NewSecretPass123!',
            ])
            ->assertRedirect('/dashboard/account')
            ->assertSessionHasErrors(['current_password']);

        $this->adminA->refresh();
        $this->assertTrue(Hash::check('InitialPass123!', $this->adminA->password));

        $oldRemember = $this->adminA->remember_token;

        // Correct current password succeeds
        $this->actingAs($this->adminA, 'admin')
            ->put('/dashboard/account/password', [
                'current_password' => 'InitialPass123!',
                'password' => 'NewSecretPass123!',
                'password_confirmation' => 'NewSecretPass123!',
            ])
            ->assertRedirect('/dashboard/account')
            ->assertSessionHas('success');

        $this->adminA->refresh();
        $this->assertTrue(Hash::check('NewSecretPass123!', $this->adminA->password));
        $this->assertNotSame($oldRemember, $this->adminA->remember_token);

        // Prior draft preview token invalidated by password change
        $this->assertFalse($previewService->validateToken($tokenBeforePwChange, $portfolio));
    }
}
