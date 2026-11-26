<?php

namespace Tests\Feature;

use App\Enums\ThemeStatus;
use App\Models\Portfolio;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class ThemePolicyTest extends TestCase
{
    use DatabaseTransactions;

    private function createTheme(User $creator, ThemeStatus $status): Theme
    {
        $suffix = uniqid();

        $theme = new Theme([
            'name' => 'Theme ' . $suffix,
            'slug' => 'theme-' . $suffix,
            'theme_key' => 'theme_key_' . $suffix,
            'version' => '1.0.0',
            'status' => $status,
        ]);
        $theme->created_by = $creator->id;
        $theme->save();

        return $theme;
    }

    public function test_active_super_admin_is_authorized_for_all_super_admin_theme_abilities(): void
    {
        $superAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);
        $draftTheme = $this->createTheme($superAdmin, ThemeStatus::Draft);

        $gate = Gate::forUser($superAdmin);

        $this->assertTrue($gate->allows('viewAny', Theme::class));
        $this->assertTrue($gate->allows('view', $draftTheme));
        $this->assertTrue($gate->allows('create', Theme::class));
        $this->assertTrue($gate->allows('update', $draftTheme));
        $this->assertTrue($gate->allows('delete', $draftTheme));
        $this->assertTrue($gate->allows('publish', $draftTheme));
        $this->assertTrue($gate->allows('unpublish', $draftTheme));
        $this->assertTrue($gate->allows('archive', $draftTheme));
    }

    public function test_inactive_super_admin_and_guests_are_denied_all_theme_abilities(): void
    {
        $activeCreator = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);
        $inactiveSuperAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => false,
        ]);
        $owner = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $publishedTheme = $this->createTheme($activeCreator, ThemeStatus::Published);
        $portfolio = Portfolio::create([
            'user_id' => $owner->id,
            'name' => 'Owner Portfolio',
            'slug' => 'owner-pf-' . uniqid(),
            'status' => 'draft',
        ]);

        foreach ([Gate::forUser($inactiveSuperAdmin), Gate::forUser(null)] as $gate) {
            $this->assertFalse($gate->allows('viewAny', Theme::class));
            $this->assertFalse($gate->allows('view', $publishedTheme));
            $this->assertFalse($gate->allows('create', Theme::class));
            $this->assertFalse($gate->allows('update', $publishedTheme));
            $this->assertFalse($gate->allows('delete', $publishedTheme));
            $this->assertFalse($gate->allows('publish', $publishedTheme));
            $this->assertFalse($gate->allows('unpublish', $publishedTheme));
            $this->assertFalse($gate->allows('archive', $publishedTheme));
            $this->assertFalse($gate->allows('browsePublished', Theme::class));
            $this->assertFalse($gate->allows('viewPublished', $publishedTheme));
            $this->assertFalse($gate->allows('apply', [$publishedTheme, $portfolio]));
        }
    }

    public function test_individual_admin_is_denied_super_admin_theme_abilities_and_can_browse_only_published(): void
    {
        $superAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);
        $individualAdmin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $draftTheme = $this->createTheme($superAdmin, ThemeStatus::Draft);
        $publishedTheme = $this->createTheme($superAdmin, ThemeStatus::Published);
        $archivedTheme = $this->createTheme($superAdmin, ThemeStatus::Archived);

        $gate = Gate::forUser($individualAdmin);

        // Super Admin abilities must all be denied
        $this->assertFalse($gate->allows('viewAny', Theme::class));
        $this->assertFalse($gate->allows('view', $publishedTheme));
        $this->assertFalse($gate->allows('create', Theme::class));
        $this->assertFalse($gate->allows('update', $publishedTheme));
        $this->assertFalse($gate->allows('delete', $publishedTheme));
        $this->assertFalse($gate->allows('publish', $draftTheme));
        $this->assertFalse($gate->allows('unpublish', $publishedTheme));
        $this->assertFalse($gate->allows('archive', $publishedTheme));

        // Published catalog browsing is allowed, draft/archived details denied
        $this->assertTrue($gate->allows('browsePublished', Theme::class));
        $this->assertTrue($gate->allows('viewPublished', $publishedTheme));
        $this->assertFalse($gate->allows('viewPublished', $draftTheme));
        $this->assertFalse($gate->allows('viewPublished', $archivedTheme));
    }

    public function test_apply_enforces_published_status_and_portfolio_ownership(): void
    {
        $superAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);
        $ownerA = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);
        $ownerB = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);
        $inactiveAdmin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => false,
        ]);

        $publishedTheme = $this->createTheme($superAdmin, ThemeStatus::Published);
        $draftTheme = $this->createTheme($superAdmin, ThemeStatus::Draft);
        $archivedTheme = $this->createTheme($superAdmin, ThemeStatus::Archived);

        $portfolioA = Portfolio::create([
            'user_id' => $ownerA->id,
            'name' => 'Owner A Site',
            'slug' => 'owner-a-' . uniqid(),
            'status' => 'draft',
        ]);

        $gateA = Gate::forUser($ownerA);
        $gateB = Gate::forUser($ownerB);

        // Owner A can apply published theme to own portfolio
        $this->assertTrue($gateA->allows('apply', [$publishedTheme, $portfolioA]));

        // Draft, archived, or missing portfolio must be rejected
        $this->assertFalse($gateA->allows('apply', [$draftTheme, $portfolioA]));
        $this->assertFalse($gateA->allows('apply', [$archivedTheme, $portfolioA]));
        $this->assertFalse($gateA->allows('apply', $publishedTheme));

        // Cross-user portfolio application must be denied
        $this->assertFalse($gateB->allows('apply', [$publishedTheme, $portfolioA]));

        // Inactive Individual Admin owning the portfolio must be denied
        $portfolioInactive = Portfolio::create([
            'user_id' => $inactiveAdmin->id,
            'name' => 'Inactive Site',
            'slug' => 'inactive-' . uniqid(),
            'status' => 'draft',
        ]);
        $this->assertFalse(Gate::forUser($inactiveAdmin)->allows('apply', [$publishedTheme, $portfolioInactive]));
    }

    public function test_theme_deletion_is_allowed_only_when_no_portfolios_reference_it(): void
    {
        $superAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);
        $owner = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $unusedTheme = $this->createTheme($superAdmin, ThemeStatus::Archived);
        $inUseTheme = $this->createTheme($superAdmin, ThemeStatus::Published);

        $portfolio = new Portfolio([
            'user_id' => $owner->id,
            'name' => 'Linked Portfolio',
            'slug' => 'linked-' . uniqid(),
            'status' => 'published',
        ]);
        $portfolio->theme_id = $inUseTheme->id;
        $portfolio->save();

        $gate = Gate::forUser($superAdmin);

        $this->assertTrue($gate->allows('delete', $unusedTheme));
        $this->assertFalse($gate->allows('delete', $inUseTheme));
    }
}
