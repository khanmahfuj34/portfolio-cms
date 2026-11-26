<?php

namespace Tests\Feature;

use App\Enums\ThemeStatus;
use App\Models\Portfolio;
use App\Models\PortfolioProfile;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SuperAdminThemeManagementUITest extends TestCase
{
    use DatabaseTransactions;

    private User $superAdmin;
    private User $inactiveSuperAdmin;
    private User $individualAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        $this->inactiveSuperAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => false,
        ]);

        $this->individualAdmin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);
    }

    private function createTheme(array $overrides = []): Theme
    {
        $suffix = uniqid();

        $theme = new Theme(array_merge([
            'name' => 'UI Theme ' . $suffix,
            'slug' => 'ui-theme-' . $suffix,
            'theme_key' => 'ui_theme_' . $suffix,
            'description' => 'Sample UI governance theme.',
            'category' => 'Developer',
            'thumbnail' => null,
            'version' => '1.0.0',
            'status' => ThemeStatus::Draft,
            'is_featured' => false,
        ], $overrides));
        $theme->created_by = $this->superAdmin->id;
        $theme->save();

        return $theme;
    }

    private function extractCardHtml(string $html, int $themeId): string
    {
        $marker = 'data-theme-id="' . $themeId . '"';
        $pos = strpos($html, $marker);
        $this->assertNotFalse($pos, "Expected card for theme ID {$themeId} to be present.");

        $start = strrpos(substr($html, 0, $pos), '<article');
        $end = strpos($html, '</article>', $pos);

        $this->assertNotFalse($start);
        $this->assertNotFalse($end);

        return substr($html, $start, $end - $start + strlen('</article>'));
    }

    public function test_super_admin_can_access_theme_management_page_and_sidebar_link(): void
    {
        $draft = $this->createTheme([
            'name' => 'Atlas Draft ' . uniqid(),
            'status' => ThemeStatus::Draft,
            'category' => 'Architecture',
            'version' => '1.2.0',
            'is_featured' => true,
        ]);

        $response = $this->actingAs($this->superAdmin, 'super_admin')
            ->get('http://localhost/admin/themes');

        $response->assertOk()
            ->assertViewIs('admin.themes.index')
            ->assertSee('Theme Management')
            ->assertSee('Manage available portfolio themes.')
            ->assertSee('http://localhost/admin/themes')
            ->assertSee('Total Themes')
            ->assertSee('Published')
            ->assertSee('Draft')
            ->assertSee('Archived')
            ->assertSee($draft->name)
            ->assertSee($draft->theme_key)
            ->assertSee('Architecture')
            ->assertSee('v1.2.0')
            ->assertSee('Featured');
    }

    public function test_guest_individual_admin_and_inactive_super_admin_are_denied_page_and_preview(): void
    {
        $theme = $this->createTheme();

        // Guest
        $this->get('http://localhost/admin/themes')->assertRedirect('http://localhost/admin/login');
        $this->get("http://localhost/admin/themes/{$theme->id}/preview")->assertRedirect('http://localhost/admin/login');

        // Individual Admin on 'admin' guard
        $this->actingAs($this->individualAdmin, 'admin')
            ->get('http://localhost/admin/themes')
            ->assertRedirect('http://localhost/admin/login');

        $this->actingAs($this->individualAdmin, 'admin')
            ->get("http://localhost/admin/themes/{$theme->id}/preview")
            ->assertRedirect('http://localhost/admin/login');

        // Inactive Super Admin
        $this->actingAs($this->inactiveSuperAdmin, 'super_admin')
            ->get('http://localhost/admin/themes')
            ->assertForbidden();

        $this->app['auth']->forgetGuards();

        $this->actingAs($this->inactiveSuperAdmin, 'super_admin')
            ->get("http://localhost/admin/themes/{$theme->id}/preview")
            ->assertForbidden();
    }

    public function test_filters_and_status_conditional_actions_render_accurately_with_no_edit_create_delete_controls(): void
    {
        $suffix = uniqid();
        $draft = $this->createTheme([
            'name' => "FilterDraft {$suffix}",
            'slug' => "filter-draft-{$suffix}",
            'status' => ThemeStatus::Draft,
            'category' => "CatA-{$suffix}",
            'is_featured' => false,
        ]);

        $published = $this->createTheme([
            'name' => "FilterPub {$suffix}",
            'slug' => "filter-pub-{$suffix}",
            'status' => ThemeStatus::Published,
            'published_at' => now(),
            'category' => "CatB-{$suffix}",
            'is_featured' => true,
        ]);

        $archived = $this->createTheme([
            'name' => "FilterArch {$suffix}",
            'slug' => "filter-arch-{$suffix}",
            'status' => ThemeStatus::Archived,
            'category' => "CatC-{$suffix}",
            'is_featured' => false,
        ]);

        $response = $this->actingAs($this->superAdmin, 'super_admin')
            ->get("http://localhost/admin/themes?search={$suffix}");

        $response->assertOk();
        $html = $response->getContent();

        // No Create / Upload / Edit / Delete / Visual Builder UI controls
        $response->assertDontSee('Create Theme');
        $response->assertDontSee('New Theme');
        $response->assertDontSee('Upload Theme');
        $response->assertDontSee('Edit Theme');
        $response->assertDontSee('Delete Theme');

        // Draft card shows Preview + Publish only (no Unpublish)
        $draftCard = $this->extractCardHtml($html, $draft->id);
        $this->assertStringContainsString("http://localhost/admin/themes/{$draft->id}/preview", $draftCard);
        $this->assertStringContainsString('data-lifecycle-action="publish"', $draftCard);
        $this->assertStringNotContainsString('data-lifecycle-action="unpublish"', $draftCard);

        // Published card shows Preview + Unpublish only (no Publish)
        $pubCard = $this->extractCardHtml($html, $published->id);
        $this->assertStringContainsString("http://localhost/admin/themes/{$published->id}/preview", $pubCard);
        $this->assertStringContainsString('data-lifecycle-action="unpublish"', $pubCard);
        $this->assertStringNotContainsString('data-lifecycle-action="publish"', $pubCard);

        // Archived card shows Preview + Restore (never Publish)
        $archCard = $this->extractCardHtml($html, $archived->id);
        $this->assertStringContainsString("http://localhost/admin/themes/{$archived->id}/preview", $archCard);
        $this->assertStringContainsString('data-lifecycle-action="restore"', $archCard);
        $this->assertStringNotContainsString('data-lifecycle-action="publish"', $archCard);

        // Filter by status=published returns only published match
        $this->actingAs($this->superAdmin, 'super_admin')
            ->get("http://localhost/admin/themes?search={$suffix}&status=published&featured=1")
            ->assertOk()
            ->assertSee($published->name)
            ->assertDontSee($draft->name)
            ->assertDontSee($archived->name);

        // Filter by category
        $this->actingAs($this->superAdmin, 'super_admin')
            ->get("http://localhost/admin/themes?category=CatA-{$suffix}")
            ->assertOk()
            ->assertSee($draft->name)
            ->assertDontSee($published->name);
    }

    public function test_theme_preview_renders_safely_with_neutral_fixture_and_never_leaks_private_user_data(): void
    {
        // Create a real private draft portfolio belonging to an Individual Admin
        $privatePortfolio = Portfolio::create([
            'user_id' => $this->individualAdmin->id,
            'name' => 'Secret Client Work ' . uniqid(),
            'slug' => 'secret-client-' . uniqid(),
            'description' => 'Confidential portfolio text.',
            'status' => 'draft',
        ]);
        PortfolioProfile::create([
            'portfolio_id' => $privatePortfolio->id,
            'full_name' => 'Private Person Name ' . uniqid(),
            'email' => 'private-secret-' . uniqid() . '@example.test',
        ]);

        $draftRifah = Theme::query()->where('theme_key', 'rifah-modern')->first()
            ?? $this->createTheme([
                'name' => 'Preview Draft Theme ' . uniqid(),
                'theme_key' => 'rifah-modern',
                'status' => ThemeStatus::Draft,
                'category' => 'Editorial',
            ]);

        $publishedDefault = Theme::query()->where('theme_key', 'default')->first()
            ?? $this->createTheme([
                'name' => 'Preview Default Theme ' . uniqid(),
                'theme_key' => 'default',
                'status' => ThemeStatus::Published,
                'published_at' => now(),
            ]);

        $unregisteredTheme = $this->createTheme([
            'name' => 'Unregistered Layout ' . uniqid(),
            'theme_key' => 'unregistered_custom_' . uniqid(),
            'status' => ThemeStatus::Draft,
        ]);

        $portfolioCountBefore = Portfolio::count();

        // Draft preview of rifah-modern works with safe neutral fixture
        $draftPreview = $this->actingAs($this->superAdmin, 'super_admin')
            ->get("http://localhost/admin/themes/{$draftRifah->id}/preview");

        $draftPreview->assertOk()
            ->assertSee('Portfolio Owner')
            ->assertSee('Sample Showcase Module A')
            ->assertDontSee($privatePortfolio->name)
            ->assertDontSee($privatePortfolio->profile->full_name)
            ->assertDontSee($privatePortfolio->profile->email);

        $this->assertStringContainsString('no-store', (string) $draftPreview->headers->get('Cache-Control'));
        $this->assertSame('noindex, nofollow', $draftPreview->headers->get('X-Robots-Tag'));

        // Published preview of default works
        $this->actingAs($this->superAdmin, 'super_admin')
            ->get("http://localhost/admin/themes/{$publishedDefault->id}/preview")
            ->assertOk()
            ->assertSee('Portfolio Owner')
            ->assertDontSee($privatePortfolio->profile->email);

        // Unregistered theme key renders clean preview-unavailable state cleanly
        $this->actingAs($this->superAdmin, 'super_admin')
            ->get("http://localhost/admin/themes/{$unregisteredTheme->id}/preview")
            ->assertOk()
            ->assertSee('Theme Preview Unavailable')
            ->assertSee($unregisteredTheme->theme_key);

        // Must never create or mutate DB portfolio records
        $this->assertSame($portfolioCountBefore, Portfolio::count());
    }

    public function test_publish_and_unpublish_actions_persist_and_preserve_portfolio_assignments(): void
    {
        $theme = $this->createTheme([
            'status' => ThemeStatus::Draft,
            'published_at' => null,
        ]);

        $portfolio = Portfolio::create([
            'user_id' => $this->individualAdmin->id,
            'name' => 'Assigned Portfolio ' . uniqid(),
            'slug' => 'assigned-portfolio-' . uniqid(),
            'status' => 'published',
            'published_at' => now(),
        ]);
        $portfolio->theme_id = $theme->id;
        $portfolio->save();

        // Publish via JSON (modal submission path)
        $publishRes = $this->actingAs($this->superAdmin, 'super_admin')
            ->patchJson("http://localhost/admin/themes/{$theme->id}/publish");

        $publishRes->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'published');

        $publishedTheme = $theme->fresh();
        $this->assertSame(ThemeStatus::Published, $publishedTheme->status);
        $this->assertNotNull($publishedTheme->published_at);

        // Unpublish via JSON while preserving published_at history & existing portfolio assignment
        $unpublishRes = $this->actingAs($this->superAdmin, 'super_admin')
            ->patchJson("http://localhost/admin/themes/{$theme->id}/unpublish");

        $unpublishRes->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'draft');

        $unpublishedTheme = $theme->fresh();
        $this->assertSame(ThemeStatus::Draft, $unpublishedTheme->status);
        $this->assertTrue($publishedTheme->published_at->equalTo($unpublishedTheme->published_at));
        $this->assertSame($theme->id, $portfolio->fresh()->theme_id);
    }
}
