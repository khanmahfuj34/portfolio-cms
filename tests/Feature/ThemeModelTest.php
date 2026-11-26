<?php

namespace Tests\Feature;

use App\Enums\ThemeStatus;
use App\Models\Portfolio;
use App\Models\Theme;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ThemeModelTest extends TestCase
{
    use DatabaseTransactions;

    public function test_theme_fillable_casts_and_mass_assignment_protection(): void
    {
        $theme = new Theme();

        $this->assertFalse($theme->isFillable('id'));
        $this->assertFalse($theme->isFillable('created_by'));

        foreach ([
            'name',
            'slug',
            'theme_key',
            'description',
            'category',
            'thumbnail',
            'version',
            'status',
            'is_featured',
            'published_at',
        ] as $field) {
            $this->assertTrue($theme->isFillable($field), "Expected {$field} to be fillable.");
        }

        $creator = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        $record = new Theme([
            'id' => 999999,
            'created_by' => 999999,
            'name' => 'Editorial Dark',
            'slug' => 'editorial-dark-' . uniqid(),
            'theme_key' => 'editorial_dark_' . uniqid(),
            'description' => 'Minimal dark portfolio theme.',
            'category' => 'developer',
            'thumbnail' => 'themes/editorial.png',
            'version' => '1.0.0',
            'status' => 'published',
            'is_featured' => 1,
            'published_at' => '2026-11-26 10:00:00',
        ]);
        $record->created_by = $creator->id;
        $record->save();

        $fresh = $record->fresh();
        $this->assertNotSame(999999, $fresh->id);
        $this->assertSame($creator->id, $fresh->created_by);
        $this->assertIsBool($fresh->is_featured);
        $this->assertTrue($fresh->is_featured);
        $this->assertInstanceOf(CarbonInterface::class, $fresh->published_at);
    }

    public function test_theme_user_and_portfolio_relationships(): void
    {
        $themeInstance = new Theme();
        $this->assertInstanceOf(BelongsTo::class, $themeInstance->creator());
        $this->assertSame('created_by', $themeInstance->creator()->getForeignKeyName());
        $this->assertInstanceOf(HasMany::class, $themeInstance->portfolios());
        $this->assertSame('theme_id', $themeInstance->portfolios()->getForeignKeyName());

        $portfolioInstance = new Portfolio();
        $this->assertInstanceOf(BelongsTo::class, $portfolioInstance->theme());
        $this->assertSame('theme_id', $portfolioInstance->theme()->getForeignKeyName());

        $userInstance = new User();
        $this->assertInstanceOf(HasMany::class, $userInstance->createdThemes());
        $this->assertSame('created_by', $userInstance->createdThemes()->getForeignKeyName());

        $creator = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN, 'is_active' => true]);
        $owner = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_active' => true]);

        $theme = new Theme([
            'name' => 'Studio Grid',
            'slug' => 'studio-grid-' . uniqid(),
            'theme_key' => 'studio_grid_' . uniqid(),
            'status' => 'published',
        ]);
        $theme->created_by = $creator->id;
        $theme->save();

        $portfolioWithTheme = new Portfolio([
            'user_id' => $owner->id,
            'name' => 'Themed Portfolio',
            'slug' => 'themed-' . uniqid(),
            'status' => 'draft',
        ]);
        $portfolioWithTheme->theme_id = $theme->id;
        $portfolioWithTheme->save();

        $portfolioWithoutTheme = Portfolio::create([
            'user_id' => $owner->id,
            'name' => 'Default Portfolio',
            'slug' => 'unthemed-' . uniqid(),
            'status' => 'draft',
        ]);

        $this->assertTrue($theme->creator->is($creator));
        $this->assertTrue($creator->createdThemes->contains($theme));
        $this->assertTrue($theme->portfolios->contains($portfolioWithTheme));
        $this->assertTrue($portfolioWithTheme->theme->is($theme));
        $this->assertNull($portfolioWithoutTheme->theme);
    }

    public function test_theme_query_scopes(): void
    {
        $creator = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN, 'is_active' => true]);
        $suffix = uniqid();

        $publishedFeatured = new Theme([
            'name' => 'Published Featured ' . $suffix,
            'slug' => 'pub-feat-' . $suffix,
            'theme_key' => 'pub_feat_' . $suffix,
            'status' => 'published',
            'is_featured' => true,
        ]);
        $publishedFeatured->created_by = $creator->id;
        $publishedFeatured->save();

        $publishedRegular = new Theme([
            'name' => 'Published Regular ' . $suffix,
            'slug' => 'pub-reg-' . $suffix,
            'theme_key' => 'pub_reg_' . $suffix,
            'status' => 'published',
            'is_featured' => false,
        ]);
        $publishedRegular->created_by = $creator->id;
        $publishedRegular->save();

        $draftFeatured = new Theme([
            'name' => 'Draft Featured ' . $suffix,
            'slug' => 'draft-feat-' . $suffix,
            'theme_key' => 'draft_feat_' . $suffix,
            'status' => 'draft',
            'is_featured' => true,
        ]);
        $draftFeatured->created_by = $creator->id;
        $draftFeatured->save();

        $archivedTheme = new Theme([
            'name' => 'Archived Theme ' . $suffix,
            'slug' => 'archived-' . $suffix,
            'theme_key' => 'archived_' . $suffix,
            'status' => 'archived',
            'is_featured' => false,
        ]);
        $archivedTheme->created_by = $creator->id;
        $archivedTheme->save();

        $publishedIds = Theme::published()->pluck('id');
        $this->assertTrue($publishedIds->contains($publishedFeatured->id));
        $this->assertTrue($publishedIds->contains($publishedRegular->id));
        $this->assertFalse($publishedIds->contains($draftFeatured->id));
        $this->assertFalse($publishedIds->contains($archivedTheme->id));

        $featuredIds = Theme::featured()->pluck('id');
        $this->assertTrue($featuredIds->contains($publishedFeatured->id));
        $this->assertTrue($featuredIds->contains($draftFeatured->id));
        $this->assertFalse($featuredIds->contains($publishedRegular->id));

        $availableIds = Theme::available()->pluck('id');
        $this->assertTrue($availableIds->contains($publishedFeatured->id));
        $this->assertTrue($availableIds->contains($publishedRegular->id));
        $this->assertFalse($availableIds->contains($draftFeatured->id));
        $this->assertFalse($availableIds->contains($archivedTheme->id));
    }

    public function test_theme_status_enum_cases_helpers_and_eloquent_casting(): void
    {
        $this->assertCount(3, ThemeStatus::cases());
        $this->assertSame('draft', ThemeStatus::Draft->value);
        $this->assertSame('published', ThemeStatus::Published->value);
        $this->assertSame('archived', ThemeStatus::Archived->value);

        $this->assertSame('Draft', ThemeStatus::Draft->label());
        $this->assertSame('Published', ThemeStatus::Published->label());
        $this->assertSame('Archived', ThemeStatus::Archived->label());

        $this->assertFalse(ThemeStatus::Draft->isPublished());
        $this->assertTrue(ThemeStatus::Published->isPublished());
        $this->assertFalse(ThemeStatus::Archived->isPublished());

        $this->assertFalse(ThemeStatus::Draft->canBeApplied());
        $this->assertTrue(ThemeStatus::Published->canBeApplied());
        $this->assertFalse(ThemeStatus::Archived->canBeApplied());

        $creator = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN, 'is_active' => true]);
        $suffix = uniqid();

        $theme = new Theme([
            'name' => 'Enum Cast Theme ' . $suffix,
            'slug' => 'enum-cast-' . $suffix,
            'theme_key' => 'enum_cast_' . $suffix,
            'status' => ThemeStatus::Draft,
        ]);
        $theme->created_by = $creator->id;
        $theme->save();

        $fresh = Theme::findOrFail($theme->id);
        $this->assertInstanceOf(ThemeStatus::class, $fresh->status);
        $this->assertSame(ThemeStatus::Draft, $fresh->status);

        $fresh->update(['status' => ThemeStatus::Published]);
        $this->assertSame(ThemeStatus::Published, $fresh->fresh()->status);
        $this->assertDatabaseHas('themes', [
            'id' => $theme->id,
            'status' => 'published',
        ]);

        $fresh->update(['status' => 'archived']);
        $this->assertSame(ThemeStatus::Archived, $fresh->fresh()->status);
        $this->assertDatabaseHas('themes', [
            'id' => $theme->id,
            'status' => 'archived',
        ]);
    }
}
