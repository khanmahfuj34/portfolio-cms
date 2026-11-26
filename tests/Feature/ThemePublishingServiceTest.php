<?php

namespace Tests\Feature;

use App\Enums\ThemeStatus;
use App\Models\Portfolio;
use App\Models\Theme;
use App\Models\User;
use App\Services\ThemePublishingService;
use App\Services\ThemeService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;
use RuntimeException;
use Tests\TestCase;

class ThemePublishingServiceTest extends TestCase
{
    use DatabaseTransactions;

    private ThemePublishingService $publishingService;
    private ThemeService $themeService;
    private User $superAdmin;
    private User $inactiveSuperAdmin;
    private User $individualAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->publishingService = app(ThemePublishingService::class);
        $this->themeService = app(ThemeService::class);

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

    private function createDraftTheme(array $overrides = []): Theme
    {
        $suffix = uniqid();

        return $this->themeService->create(array_merge([
            'name' => 'Lifecycle Theme ' . $suffix,
            'slug' => 'lifecycle-theme-' . $suffix,
            'theme_key' => 'lifecycle_theme_' . $suffix,
            'description' => 'Lifecycle metadata test.',
            'category' => 'developer',
            'thumbnail' => '/images/themes/lifecycle.png',
            'version' => '1.0.0',
            'is_featured' => true,
        ], $overrides), $this->superAdmin);
    }

    public function test_full_lifecycle_transitions_preserve_metadata_and_historical_published_at(): void
    {
        $theme = $this->createDraftTheme();
        $originalName = $theme->name;
        $originalSlug = $theme->slug;
        $originalKey = $theme->theme_key;
        $originalVersion = $theme->version;

        $this->assertSame(ThemeStatus::Draft, $theme->status);
        $this->assertNull($theme->published_at);

        // Draft -> Published
        $published = $this->publishingService->publish($theme, $this->superAdmin);
        $firstPublishedAt = $published->published_at;
        $this->assertSame(ThemeStatus::Published, $published->status);
        $this->assertNotNull($firstPublishedAt);

        // Published -> Draft (unpublish preserves historical published_at and metadata)
        $unpublished = $this->publishingService->unpublish($published, $this->superAdmin);
        $this->assertSame(ThemeStatus::Draft, $unpublished->status);
        $this->assertNotNull($unpublished->published_at);
        $this->assertTrue($firstPublishedAt->equalTo($unpublished->published_at));
        $this->assertSame($originalName, $unpublished->name);
        $this->assertSame($originalSlug, $unpublished->slug);
        $this->assertSame($originalKey, $unpublished->theme_key);
        $this->assertSame($originalVersion, $unpublished->version);

        // Republishing an unpublished draft succeeds
        $republished = $this->publishingService->publish($unpublished, $this->superAdmin);
        $republishedAt = $republished->published_at;
        $this->assertSame(ThemeStatus::Published, $republished->status);
        $this->assertNotNull($republishedAt);

        // Published -> Archived (preserves published_at)
        $archived = $this->publishingService->archive($republished, $this->superAdmin);
        $this->assertSame(ThemeStatus::Archived, $archived->status);
        $this->assertTrue($republishedAt->equalTo($archived->published_at));

        // Archived -> Draft (restore moves to Draft, not Published, and keeps published_at)
        $restored = $this->publishingService->restore($archived, $this->superAdmin);
        $this->assertSame(ThemeStatus::Draft, $restored->status);
        $this->assertTrue($republishedAt->equalTo($restored->published_at));
    }

    public function test_archived_theme_cannot_be_directly_published_or_newly_applied(): void
    {
        $theme = $this->createDraftTheme();
        $published = $this->publishingService->publish($theme, $this->superAdmin);
        $archived = $this->publishingService->archive($published, $this->superAdmin);

        $portfolio = Portfolio::create([
            'user_id' => $this->individualAdmin->id,
            'name' => 'Target Portfolio',
            'slug' => 'target-pf-' . uniqid(),
            'status' => 'draft',
        ]);
        $this->assertFalse(Gate::forUser($this->individualAdmin)->allows('apply', [$archived, $portfolio]));

        $this->expectException(InvalidArgumentException::class);
        $this->publishingService->publish($archived, $this->superAdmin);
    }

    public function test_unpublishing_and_archiving_preserve_existing_portfolio_assignments(): void
    {
        $theme = $this->publishingService->publish($this->createDraftTheme(), $this->superAdmin);

        $portfolio = new Portfolio([
            'user_id' => $this->individualAdmin->id,
            'name' => 'Assigned Portfolio',
            'slug' => 'assigned-pf-' . uniqid(),
            'status' => 'published',
        ]);
        $portfolio->theme_id = $theme->id;
        $portfolio->save();

        $this->publishingService->unpublish($theme, $this->superAdmin);
        $this->assertDatabaseHas('portfolios', [
            'id' => $portfolio->id,
            'theme_id' => $theme->id,
        ]);

        $this->publishingService->archive($theme, $this->superAdmin);
        $this->assertDatabaseHas('portfolios', [
            'id' => $portfolio->id,
            'theme_id' => $theme->id,
        ]);
    }

    public function test_unauthorized_actors_are_denied_all_lifecycle_operations(): void
    {
        $draft = $this->createDraftTheme();
        $published = $this->publishingService->publish($this->createDraftTheme(), $this->superAdmin);
        $archived = $this->publishingService->archive($this->publishingService->publish($this->createDraftTheme(), $this->superAdmin), $this->superAdmin);

        foreach ([$this->individualAdmin, $this->inactiveSuperAdmin] as $actor) {
            foreach ([
                fn () => $this->publishingService->publish($draft, $actor),
                fn () => $this->publishingService->unpublish($published, $actor),
                fn () => $this->publishingService->archive($published, $actor),
                fn () => $this->publishingService->restore($archived, $actor),
            ] as $attempt) {
                try {
                    $attempt();
                    $this->fail('Expected AuthorizationException for unauthorized lifecycle actor.');
                } catch (AuthorizationException $e) {
                    $this->assertInstanceOf(AuthorizationException::class, $e);
                }
            }
        }
    }

    public function test_lifecycle_transition_rolls_back_on_database_failure(): void
    {
        $theme = $this->createDraftTheme();

        Event::listen('eloquent.saving: ' . Theme::class, function (): void {
            throw new RuntimeException('Simulated persistence error');
        });

        try {
            $this->publishingService->publish($theme, $this->superAdmin);
            $this->fail('Expected RuntimeException during simulated failure.');
        } catch (RuntimeException $e) {
            $this->assertSame('Simulated persistence error', $e->getMessage());
        } finally {
            Event::forget('eloquent.saving: ' . Theme::class);
        }

        $fresh = $theme->fresh();
        $this->assertSame(ThemeStatus::Draft, $fresh->status);
        $this->assertNull($fresh->published_at);
    }
}
