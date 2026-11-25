<?php

namespace Tests\Feature;

use App\Models\Portfolio;
use App\Models\User;
use App\Services\PortfolioPreviewService;
use App\Services\PortfolioService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SuperAdminUserManagementTest extends TestCase
{
    use DatabaseTransactions;

    private function createSuperAdmin(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'name' => 'Root Super Admin',
            'email' => 'super-' . uniqid() . '@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ], $overrides));
    }

    private function createIndividualAdmin(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'email' => 'admin-' . uniqid() . '@example.com',
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ], $overrides));
    }

    private function createDraftPortfolio(User $owner, string $label): Portfolio
    {
        return app(PortfolioService::class)->create(
            $owner,
            ['name' => $label, 'slug' => 'draft-' . uniqid()],
            ['full_name' => $label . ' Profile'],
            [['label' => 'Home', 'section_key' => 'home', 'sort_order' => 1, 'is_visible' => true]]
        );
    }

    public function test_guests_and_individual_admins_cannot_access_super_admin_user_management(): void
    {
        $target = $this->createIndividualAdmin(['name' => 'Target Admin']);
        $actor = $this->createIndividualAdmin(['name' => 'Other Admin']);

        $this->get('http://localhost/admin/users')
            ->assertRedirect('http://localhost/admin/login');

        $this->patch("http://localhost/admin/users/{$target->id}/toggle-status")
            ->assertRedirect('http://localhost/admin/login');

        $this->actingAs($actor, 'admin')
            ->get('http://localhost/admin/users')
            ->assertRedirect('http://localhost/admin/login');

        $this->actingAs($actor, 'super_admin')
            ->get('http://localhost/admin/users')
            ->assertForbidden();

        $this->actingAs($actor, 'super_admin')
            ->patch("http://localhost/admin/users/{$target->id}/toggle-status")
            ->assertForbidden();

        $this->assertTrue($target->fresh()->is_active);
    }

    public function test_super_admin_can_search_filter_paginate_and_view_portfolio_counts(): void
    {
        $superAdmin = $this->createSuperAdmin(['name' => 'Unique Super Admin Name']);
        $this->createSuperAdmin([
            'name' => 'Hidden Super Admin',
            'email' => 'hidden-super-' . uniqid() . '@example.com',
        ]);

        for ($i = 1; $i <= 11; $i++) {
            $this->createIndividualAdmin([
                'name' => "Paged Admin {$i}",
                'email' => "paged-{$i}-" . uniqid() . '@example.com',
                'is_active' => true,
            ]);
        }

        $alice = $this->createIndividualAdmin([
            'name' => 'Alice Designer',
            'email' => 'alice-designer-' . uniqid() . '@example.com',
            'is_active' => true,
        ]);

        $bob = $this->createIndividualAdmin([
            'name' => 'Bob Developer',
            'email' => 'bob-dev-' . uniqid() . '@example.com',
            'is_active' => false,
        ]);

        $this->createDraftPortfolio($alice, 'Alice Site 1');
        $this->createDraftPortfolio($alice, 'Alice Site 2');
        $this->createDraftPortfolio($bob, 'Bob Site 1');

        $listResponse = $this->actingAs($superAdmin, 'super_admin')->get('http://localhost/admin/users');
        $listResponse->assertOk();
        $listResponse->assertDontSee('Hidden Super Admin');
        $listResponse->assertSee('Alice Designer');
        $listResponse->assertSee('Bob Developer');

        $listedUsers = $listResponse->viewData('users');
        $this->assertFalse($listedUsers->contains('id', $superAdmin->id));
        $aliceRow = $listedUsers->firstWhere('id', $alice->id);
        $bobRow = $listedUsers->firstWhere('id', $bob->id);
        $this->assertNotNull($aliceRow);
        $this->assertNotNull($bobRow);
        $this->assertSame(2, (int) $aliceRow->portfolios_count);
        $this->assertSame(1, (int) $bobRow->portfolios_count);

        // Over 10 Individual Admins -> page 2 exists and includes earlier paged records
        $pageTwo = $this->actingAs($superAdmin, 'super_admin')->get('http://localhost/admin/users?page=2');
        $pageTwo->assertOk();
        $pageTwo->assertSee('Paged Admin 1');

        // Search by email/name
        $searchResponse = $this->actingAs($superAdmin, 'super_admin')->get('http://localhost/admin/users?search=Alice+Designer');
        $searchResponse->assertOk();
        $searchResponse->assertSee('Alice Designer');
        $searchResponse->assertDontSee('Bob Developer');

        // Filter inactive accounts
        $inactiveResponse = $this->actingAs($superAdmin, 'super_admin')->get('http://localhost/admin/users?status=inactive');
        $inactiveResponse->assertOk();
        $inactiveResponse->assertSee('Bob Developer');
        $inactiveResponse->assertDontSee('Alice Designer');
    }

    public function test_super_admin_cannot_deactivate_or_modify_own_or_other_super_admin_accounts(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $secondSuperAdmin = $this->createSuperAdmin([
            'name' => 'Second Super Admin',
        ]);

        $this->actingAs($superAdmin, 'super_admin')
            ->patch("http://localhost/admin/users/{$superAdmin->id}/toggle-status")
            ->assertForbidden();

        $this->actingAs($superAdmin, 'super_admin')
            ->patch("http://localhost/admin/users/{$secondSuperAdmin->id}/toggle-status")
            ->assertForbidden();

        $this->assertTrue($superAdmin->fresh()->is_active);
        $this->assertTrue($secondSuperAdmin->fresh()->is_active);
    }

    public function test_super_admin_can_toggle_individual_admin_status_and_deactivated_admin_loses_access(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $owner = $this->createIndividualAdmin([
            'name' => 'Charlie Owner',
            'is_active' => true,
        ]);

        $draft = $this->createDraftPortfolio($owner, 'Charlie Draft');

        $previewUrl = app(PortfolioPreviewService::class)->generatePreviewUrl($draft, $owner);

        // Before deactivation, preview link works
        $this->get($previewUrl)->assertOk();

        // Deactivate Individual Admin
        $this->actingAs($superAdmin, 'super_admin')
            ->patch("http://localhost/admin/users/{$owner->id}/toggle-status")
            ->assertRedirect('http://localhost/admin/users');

        $this->assertFalse($owner->fresh()->is_active);
        // User and portfolios must never be hard-deleted
        $this->assertDatabaseHas('users', ['id' => $owner->id, 'is_active' => false]);
        $this->assertDatabaseHas('portfolios', ['id' => $draft->id, 'user_id' => $owner->id]);

        // Clear Super Admin auth session before probing the guest preview link
        $this->app['auth']->forgetGuards();

        // Existing signed preview link is immediately invalidated
        $this->get($previewUrl)->assertNotFound();

        // Authenticated Individual Admin (re-hydrated from DB via session) loses access immediately
        $this->actingAs($owner->fresh(), 'admin')
            ->get('http://localhost/dashboard')
            ->assertRedirect('http://localhost/login');

        $this->assertGuest('admin');

        // Reactivate Individual Admin
        $this->actingAs($superAdmin, 'super_admin')
            ->patch("http://localhost/admin/users/{$owner->id}/toggle-status")
            ->assertRedirect('http://localhost/admin/users');

        $this->assertTrue($owner->fresh()->is_active);

        $this->actingAs($owner->fresh(), 'admin')
            ->get('http://localhost/dashboard')
            ->assertOk();
    }
}
