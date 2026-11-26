<?php

namespace Tests\Feature;

use App\Enums\ThemeStatus;
use App\Models\Portfolio;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SuperAdminThemeControllerTest extends TestCase
{
    use DatabaseTransactions;

    private User $superAdmin;
    private User $individualAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        $this->individualAdmin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);
    }

    private function createTheme(ThemeStatus $status = ThemeStatus::Draft): Theme
    {
        $suffix = uniqid();

        $theme = new Theme([
            'name' => 'Controller Theme ' . $suffix,
            'slug' => 'controller-theme-' . $suffix,
            'theme_key' => 'controller_theme_' . $suffix,
            'description' => 'Sample theme description.',
            'category' => 'developer',
            'thumbnail' => '/images/themes/sample.png',
            'version' => '1.0.0',
            'status' => $status,
            'is_featured' => false,
        ]);
        $theme->created_by = $this->superAdmin->id;
        $theme->save();

        return $theme;
    }

    public function test_guest_and_individual_admin_cannot_access_super_admin_theme_routes(): void
    {
        $theme = $this->createTheme();

        // Guest redirected to Super Admin login
        $this->get('http://localhost/admin/themes')->assertRedirect('http://localhost/admin/login');
        $this->post('http://localhost/admin/themes', [])->assertRedirect('http://localhost/admin/login');
        $this->get("http://localhost/admin/themes/{$theme->id}")->assertRedirect('http://localhost/admin/login');
        $this->put("http://localhost/admin/themes/{$theme->id}", [])->assertRedirect('http://localhost/admin/login');
        $this->delete("http://localhost/admin/themes/{$theme->id}")->assertRedirect('http://localhost/admin/login');

        // JSON guest gets 401
        $this->getJson('http://localhost/admin/themes')->assertUnauthorized();

        // Individual Admin authenticated on 'admin' guard cannot access Super Admin theme routes
        $this->actingAs($this->individualAdmin, 'admin')
            ->get('http://localhost/admin/themes')
            ->assertRedirect('http://localhost/admin/login');

        $this->actingAs($this->individualAdmin, 'admin')
            ->postJson('http://localhost/admin/themes', [])
            ->assertUnauthorized();

        $this->actingAs($this->individualAdmin, 'admin')
            ->deleteJson("http://localhost/admin/themes/{$theme->id}")
            ->assertUnauthorized();
    }

    public function test_super_admin_can_list_themes_with_filters(): void
    {
        $suffix = uniqid();
        $matching = new Theme([
            'name' => "Monolith {$suffix}",
            'slug' => "monolith-{$suffix}",
            'theme_key' => "monolith_{$suffix}",
            'category' => 'developer',
            'version' => '1.0.0',
            'status' => ThemeStatus::Draft,
            'is_featured' => true,
        ]);
        $matching->created_by = $this->superAdmin->id;
        $matching->save();

        $response = $this->actingAs($this->superAdmin, 'super_admin')
            ->getJson("http://localhost/admin/themes?search={$suffix}&status=draft&is_featured=1");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $matching->id)
            ->assertJsonPath('data.0.status', 'draft');
    }

    public function test_super_admin_can_create_draft_theme_and_invalid_input_is_rejected(): void
    {
        $suffix = uniqid();

        $response = $this->actingAs($this->superAdmin, 'super_admin')
            ->post("http://localhost/admin/themes", [
                'name' => "Editorial Noir {$suffix}",
                'slug' => "editorial-noir-{$suffix}",
                'theme_key' => "editorial_noir_{$suffix}",
                'description' => 'Dark editorial theme.',
                'category' => 'editorial',
                'thumbnail' => '/images/themes/noir.png',
                'version' => '1.0.0',
                'is_featured' => true,
            ]);

        $response->assertRedirect('http://localhost/admin/themes')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('themes', [
            'slug' => "editorial-noir-{$suffix}",
            'theme_key' => "editorial_noir_{$suffix}",
            'status' => 'draft',
            'created_by' => $this->superAdmin->id,
            'published_at' => null,
        ]);

        // Protected field & missing required input must fail validation
        $invalidResponse = $this->actingAs($this->superAdmin, 'super_admin')
            ->postJson('http://localhost/admin/themes', [
                'name' => '',
                'status' => 'published',
                'created_by' => $this->individualAdmin->id,
            ]);

        $invalidResponse->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'slug', 'theme_key', 'version', 'status', 'created_by']);
    }

    public function test_super_admin_can_view_and_update_theme_without_exposing_sensitive_data(): void
    {
        $theme = $this->createTheme(ThemeStatus::Published);
        $theme->published_at = now()->subHour();
        $theme->save();

        $showResponse = $this->actingAs($this->superAdmin, 'super_admin')
            ->get("http://localhost/admin/themes/{$theme->id}");

        $showResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $theme->id)
            ->assertJsonPath('data.slug', $theme->slug)
            ->assertJsonMissing(['email' => $this->superAdmin->email])
            ->assertJsonMissingPath('data.created_by');

        $updateResponse = $this->actingAs($this->superAdmin, 'super_admin')
            ->patch("http://localhost/admin/themes/{$theme->id}", [
                'name' => 'Updated Portfolio Theme',
                'slug' => $theme->slug,
                'theme_key' => $theme->theme_key,
                'description' => 'Updated metadata only.',
                'category' => 'designer',
                'thumbnail' => '/images/themes/updated.png',
                'version' => '1.2.0',
                'is_featured' => true,
            ]);

        $updateResponse->assertRedirect('http://localhost/admin/themes')
            ->assertSessionHas('success');

        $fresh = $theme->fresh();
        $this->assertSame('Updated Portfolio Theme', $fresh->name);
        $this->assertSame('1.2.0', $fresh->version);
        $this->assertSame(ThemeStatus::Published, $fresh->status);
        $this->assertNotNull($fresh->published_at);
    }

    public function test_super_admin_can_delete_unused_theme_and_is_blocked_when_in_use(): void
    {
        $unused = $this->createTheme();

        $deleteUnused = $this->actingAs($this->superAdmin, 'super_admin')
            ->delete("http://localhost/admin/themes/{$unused->id}");

        $deleteUnused->assertRedirect('http://localhost/admin/themes')
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('themes', ['id' => $unused->id]);

        $inUse = $this->createTheme(ThemeStatus::Published);
        $portfolio = new Portfolio([
            'user_id' => $this->individualAdmin->id,
            'name' => 'Using Theme',
            'slug' => 'using-theme-' . uniqid(),
            'status' => 'draft',
        ]);
        $portfolio->theme_id = $inUse->id;
        $portfolio->save();

        $deleteBlocked = $this->actingAs($this->superAdmin, 'super_admin')
            ->delete("http://localhost/admin/themes/{$inUse->id}");

        $deleteBlocked->assertRedirect('http://localhost/admin/themes')
            ->assertSessionHas('error');

        $this->deleteJson("http://localhost/admin/themes/{$inUse->id}")
            ->assertStatus(422)
            ->assertJsonPath('success', false);

        $this->assertDatabaseHas('themes', ['id' => $inUse->id]);
        $this->assertDatabaseHas('portfolios', ['id' => $portfolio->id, 'theme_id' => $inUse->id]);
    }

    public function test_existing_auth_and_portfolio_routes_remain_unaffected(): void
    {
        $this->get('http://localhost/login')->assertOk();
        $this->get('http://localhost/admin/login')->assertOk();

        $this->actingAs($this->superAdmin, 'super_admin')
            ->get('http://localhost/admin/dashboard')
            ->assertOk();

        $this->actingAs($this->individualAdmin, 'admin')
            ->get('http://localhost/dashboard')
            ->assertOk();

        // Admin theme endpoints remain blocked on subdomains
        $this->actingAs($this->superAdmin, 'super_admin')
            ->get('http://mahfuj.localhost/admin/themes')
            ->assertNotFound();
    }

    public function test_super_admin_can_execute_theme_lifecycle_endpoints_preserving_assignments_and_history(): void
    {
        $draft = $this->createTheme(ThemeStatus::Draft);

        $portfolio = new Portfolio([
            'user_id' => $this->individualAdmin->id,
            'name' => 'Preserved Portfolio',
            'slug' => 'preserved-pf-' . uniqid(),
            'status' => 'published',
        ]);
        $portfolio->theme_id = $draft->id;
        $portfolio->save();

        // Publish draft
        $publishRes = $this->actingAs($this->superAdmin, 'super_admin')
            ->patchJson("http://localhost/admin/themes/{$draft->id}/publish");

        $publishRes->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'published');

        $publishedAt = $draft->fresh()->published_at;
        $this->assertNotNull($publishedAt);

        // Unpublish -> Draft while keeping published_at history and portfolio assignment
        $unpublishRes = $this->actingAs($this->superAdmin, 'super_admin')
            ->patch("http://localhost/admin/themes/{$draft->id}/unpublish");

        $unpublishRes->assertRedirect('http://localhost/admin/themes')
            ->assertSessionHas('success');

        $afterUnpublish = $draft->fresh();
        $this->assertSame(ThemeStatus::Draft, $afterUnpublish->status);
        $this->assertTrue($publishedAt->equalTo($afterUnpublish->published_at));

        // Archive -> Archived while keeping portfolio assignment
        $archiveRes = $this->actingAs($this->superAdmin, 'super_admin')
            ->patchJson("http://localhost/admin/themes/{$draft->id}/archive");

        $archiveRes->assertOk()
            ->assertJsonPath('data.status', 'archived');
        $this->assertDatabaseHas('portfolios', [
            'id' => $portfolio->id,
            'theme_id' => $draft->id,
        ]);

        // Archived theme cannot be directly published (422 JSON / redirect error)
        $this->actingAs($this->superAdmin, 'super_admin')
            ->patchJson("http://localhost/admin/themes/{$draft->id}/publish")
            ->assertStatus(422)
            ->assertJsonPath('success', false);

        $this->actingAs($this->superAdmin, 'super_admin')
            ->patch("http://localhost/admin/themes/{$draft->id}/publish")
            ->assertRedirect('http://localhost/admin/themes')
            ->assertSessionHas('error');

        // Restore -> Draft (not Published) preserving published_at
        $restoreRes = $this->actingAs($this->superAdmin, 'super_admin')
            ->patchJson("http://localhost/admin/themes/{$draft->id}/restore");

        $restoreRes->assertOk()
            ->assertJsonPath('data.status', 'draft');

        $afterRestore = $draft->fresh();
        $this->assertSame(ThemeStatus::Draft, $afterRestore->status);
        $this->assertTrue($publishedAt->equalTo($afterRestore->published_at));

        // Invalid transition: restoring a non-archived theme fails gracefully
        $this->actingAs($this->superAdmin, 'super_admin')
            ->patchJson("http://localhost/admin/themes/{$draft->id}/restore")
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_guests_individual_admins_and_inactive_super_admins_cannot_access_lifecycle_endpoints(): void
    {
        $theme = $this->createTheme(ThemeStatus::Draft);
        $inactiveSuperAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => false,
        ]);

        foreach (['publish', 'unpublish', 'archive', 'restore'] as $action) {
            $url = "http://localhost/admin/themes/{$theme->id}/{$action}";

            // Guest
            $this->app['auth']->forgetGuards();
            $this->patch($url)->assertRedirect('http://localhost/admin/login');
            $this->patchJson($url)->assertUnauthorized();

            // Individual Admin
            $this->actingAs($this->individualAdmin, 'admin')
                ->patch($url)
                ->assertRedirect('http://localhost/admin/login');
            $this->actingAs($this->individualAdmin, 'admin')
                ->patchJson($url)
                ->assertUnauthorized();

            // Inactive Super Admin
            $this->actingAs($inactiveSuperAdmin, 'super_admin')
                ->patch($url)
                ->assertForbidden();
        }
    }
}
