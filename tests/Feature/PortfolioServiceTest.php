<?php

namespace Tests\Feature;

use App\Models\Portfolio;
use App\Models\PortfolioNavItem;
use App\Models\PortfolioProfile;
use App\Models\User;
use App\Services\PortfolioService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Tests\TestCase;
use Throwable;

class PortfolioServiceTest extends TestCase
{
    use DatabaseTransactions;

    private User $superAdmin;
    private PortfolioService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::firstOrCreate(
            ['email' => 'admin@portfoliocms.test'],
            [
                'name' => 'Super Admin Test',
                'password' => bcrypt('Admin@123456'),
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );

        $this->service = app(PortfolioService::class);
    }

    private function defaultPortfolioData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Studio Vanguard',
            'slug' => 'vanguard-' . uniqid(),
            'description' => 'Architectural Portfolio',
            'status' => 'draft',
        ], $overrides);
    }

    private function defaultProfileData(array $overrides = []): array
    {
        return array_merge([
            'full_name' => 'Adrian Vance',
            'professional_title' => 'Principal Architect',
            'email' => 'adrian@vanguard.test',
            'location' => 'Dhaka, Bangladesh',
            'bio' => 'Award-winning architecture practice.',
        ], $overrides);
    }

    private function defaultNavItemsData(): array
    {
        return [
            [
                'label' => 'Home',
                'type' => 'section',
                'section_key' => 'home',
                'url' => null,
                'sort_order' => 1,
                'is_visible' => true,
            ],
            [
                'label' => 'Projects',
                'type' => 'section',
                'section_key' => 'projects',
                'url' => null,
                'sort_order' => 2,
                'is_visible' => true,
            ],
            [
                'label' => 'GitHub Profile',
                'type' => 'url',
                'section_key' => null,
                'url' => 'https://github.com/adrian-vance',
                'sort_order' => 3,
                'is_visible' => false,
            ],
        ];
    }

    // 1. Portfolio is created
    public function test_portfolio_is_created_belonging_to_super_admin(): void
    {
        $portfolioData = $this->defaultPortfolioData(['name' => 'Created Portfolio Test']);
        $portfolio = $this->service->create(
            $this->superAdmin,
            $portfolioData,
            $this->defaultProfileData(),
            $this->defaultNavItemsData()
        );

        $this->assertInstanceOf(Portfolio::class, $portfolio);
        $this->assertDatabaseHas('portfolios', [
            'id' => $portfolio->id,
            'name' => 'Created Portfolio Test',
            'user_id' => $this->superAdmin->id,
        ]);
        $this->assertEquals($this->superAdmin->id, $portfolio->user_id);
    }

    // 2. Profile is associated with the correct Portfolio
    public function test_profile_is_associated_with_the_correct_portfolio(): void
    {
        $portfolio = $this->service->create(
            $this->superAdmin,
            $this->defaultPortfolioData(),
            $this->defaultProfileData(['full_name' => 'Elena Rostova']),
            $this->defaultNavItemsData()
        );

        $profile = $portfolio->profile;
        $this->assertNotNull($profile);
        $this->assertInstanceOf(PortfolioProfile::class, $profile);
        $this->assertEquals($portfolio->id, $profile->portfolio_id);
        $this->assertEquals('Elena Rostova', $profile->full_name);
        $this->assertDatabaseHas('portfolio_profiles', [
            'portfolio_id' => $portfolio->id,
            'full_name' => 'Elena Rostova',
        ]);
    }

    // 3. All submitted Navbar items are persisted
    public function test_all_submitted_navbar_items_are_persisted(): void
    {
        $navItems = $this->defaultNavItemsData();
        $portfolio = $this->service->create(
            $this->superAdmin,
            $this->defaultPortfolioData(),
            $this->defaultProfileData(),
            $navItems
        );

        $this->assertEquals(3, $portfolio->navItems()->count());
        $this->assertDatabaseHas('portfolio_nav_items', ['portfolio_id' => $portfolio->id, 'label' => 'Home']);
        $this->assertDatabaseHas('portfolio_nav_items', ['portfolio_id' => $portfolio->id, 'label' => 'Projects']);
        $this->assertDatabaseHas('portfolio_nav_items', ['portfolio_id' => $portfolio->id, 'label' => 'GitHub Profile']);
    }

    // 4. Exactly one Home item exists
    public function test_exactly_one_home_item_exists(): void
    {
        $portfolio = $this->service->create(
            $this->superAdmin,
            $this->defaultPortfolioData(),
            $this->defaultProfileData(),
            $this->defaultNavItemsData()
        );

        $homeItems = $portfolio->navItems()
            ->where('label', 'Home')
            ->where('section_key', 'home')
            ->get();

        $this->assertCount(1, $homeItems);
    }

    // 5. Home is first
    public function test_home_item_is_first_with_sort_order_one(): void
    {
        $portfolio = $this->service->create(
            $this->superAdmin,
            $this->defaultPortfolioData(),
            $this->defaultProfileData(),
            $this->defaultNavItemsData()
        );

        $firstItem = $portfolio->navItems()->orderBy('sort_order')->first();
        $this->assertNotNull($firstItem);
        $this->assertEquals('Home', $firstItem->label);
        $this->assertEquals('home', $firstItem->section_key);
        $this->assertEquals(1, $firstItem->sort_order);
    }

    // 6. Navbar sort order is preserved
    public function test_navbar_sort_order_is_preserved(): void
    {
        $portfolio = $this->service->create(
            $this->superAdmin,
            $this->defaultPortfolioData(),
            $this->defaultProfileData(),
            $this->defaultNavItemsData()
        );

        $items = $portfolio->navItems()->orderBy('sort_order')->get();
        $this->assertEquals(1, $items[0]->sort_order);
        $this->assertEquals('Home', $items[0]->label);
        $this->assertEquals(2, $items[1]->sort_order);
        $this->assertEquals('Projects', $items[1]->label);
        $this->assertEquals(3, $items[2]->sort_order);
        $this->assertEquals('GitHub Profile', $items[2]->label);
    }

    // 7. Visibility values are preserved
    public function test_visibility_values_are_preserved(): void
    {
        $portfolio = $this->service->create(
            $this->superAdmin,
            $this->defaultPortfolioData(),
            $this->defaultProfileData(),
            $this->defaultNavItemsData()
        );

        $visibleItem = $portfolio->navItems()->where('label', 'Projects')->first();
        $hiddenItem = $portfolio->navItems()->where('label', 'GitHub Profile')->first();

        $this->assertTrue((bool) $visibleItem->is_visible);
        $this->assertFalse((bool) $hiddenItem->is_visible);
    }

    // 8. Internal section destination is saved correctly
    public function test_internal_section_destination_is_saved_correctly(): void
    {
        $portfolio = $this->service->create(
            $this->superAdmin,
            $this->defaultPortfolioData(),
            $this->defaultProfileData(),
            $this->defaultNavItemsData()
        );

        $sectionItem = $portfolio->navItems()->where('label', 'Projects')->first();
        $this->assertEquals('projects', $sectionItem->section_key);
        $this->assertNull($sectionItem->url);
    }

    // 9. External URL destination is saved correctly
    public function test_external_url_destination_is_saved_correctly(): void
    {
        $portfolio = $this->service->create(
            $this->superAdmin,
            $this->defaultPortfolioData(),
            $this->defaultProfileData(),
            $this->defaultNavItemsData()
        );

        $urlItem = $portfolio->navItems()->where('label', 'GitHub Profile')->first();
        $this->assertEquals('https://github.com/adrian-vance', $urlItem->url);
        $this->assertNull($urlItem->section_key);
    }

    // 10. Opposite destination column is null
    public function test_opposite_destination_column_is_null(): void
    {
        $portfolio = $this->service->create(
            $this->superAdmin,
            $this->defaultPortfolioData(),
            $this->defaultProfileData(),
            $this->defaultNavItemsData()
        );

        foreach ($portfolio->navItems as $item) {
            if ($item->section_key !== null) {
                $this->assertNull($item->url, "Item {$item->label} has section_key but url is not null");
            } else {
                $this->assertNotNull($item->url, "Item {$item->label} must have url when section_key is null");
                $this->assertNull($item->section_key);
            }
        }
    }

    // 11. UI-only `type` is not persisted
    public function test_ui_only_type_field_is_not_persisted(): void
    {
        $this->assertFalse(
            Schema::hasColumn('portfolio_nav_items', 'type'),
            'Database table portfolio_nav_items must not contain a type column.'
        );

        $portfolio = $this->service->create(
            $this->superAdmin,
            $this->defaultPortfolioData(),
            $this->defaultProfileData(),
            $this->defaultNavItemsData()
        );

        $rawRecord = (array) \Illuminate\Support\Facades\DB::table('portfolio_nav_items')
            ->where('portfolio_id', $portfolio->id)
            ->first();

        $this->assertArrayNotHasKey('type', $rawRecord);
    }

    // 12. Portfolio status is always draft
    public function test_portfolio_status_is_always_forced_to_draft(): void
    {
        // Even if payload maliciously asks for 'published'
        $portfolioData = $this->defaultPortfolioData(['status' => 'published']);

        $portfolio = $this->service->create(
            $this->superAdmin,
            $portfolioData,
            $this->defaultProfileData(),
            $this->defaultNavItemsData()
        );

        $this->assertEquals('draft', $portfolio->status);
        $this->assertDatabaseHas('portfolios', [
            'id' => $portfolio->id,
            'status' => 'draft',
        ]);
    }

    // 13. published_at is null
    public function test_published_at_is_always_null_on_creation(): void
    {
        // Even if payload passes a published_at timestamp
        $portfolioData = $this->defaultPortfolioData([
            'status' => 'published',
            'published_at' => now(),
        ]);

        $portfolio = $this->service->create(
            $this->superAdmin,
            $portfolioData,
            $this->defaultProfileData(),
            $this->defaultNavItemsData()
        );

        $this->assertNull($portfolio->published_at);
        $this->assertDatabaseHas('portfolios', [
            'id' => $portfolio->id,
            'published_at' => null,
        ]);
    }

    // 14. Empty Navbar array is rejected
    public function test_empty_navbar_array_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Navbar items array cannot be empty.');

        $this->service->create(
            $this->superAdmin,
            $this->defaultPortfolioData(),
            $this->defaultProfileData(),
            []
        );
    }

    // 15. More than 10 Navbar items are rejected
    public function test_more_than_ten_navbar_items_are_rejected(): void
    {
        $items = [
            [
                'label' => 'Home',
                'type' => 'section',
                'section_key' => 'home',
                'sort_order' => 1,
                'is_visible' => true,
            ],
        ];

        for ($i = 2; $i <= 11; $i++) {
            $items[] = [
                'label' => 'Item ' . $i,
                'type' => 'section',
                'section_key' => 'item_' . $i,
                'sort_order' => $i,
                'is_visible' => true,
            ];
        }

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Navbar items cannot exceed 10 items.');

        $this->service->create(
            $this->superAdmin,
            $this->defaultPortfolioData(),
            $this->defaultProfileData(),
            $items
        );
    }

    // 16. Database failure rolls back the entire operation
    public function test_database_failure_rolls_back_the_entire_operation(): void
    {
        $slug = 'rollback-atomic-' . uniqid();

        $initialPortfolios = Portfolio::count();
        $initialProfiles = PortfolioProfile::count();
        $initialNavItems = PortfolioNavItem::count();

        try {
            $this->service->create(
                $this->superAdmin,
                ['name' => null, 'slug' => $slug], // 'name' is NOT NULL in MySQL schema
                $this->defaultProfileData(),
                $this->defaultNavItemsData()
            );
            $this->fail('Expected database exception was not thrown.');
        } catch (Throwable $e) {
            $this->assertDatabaseMissing('portfolios', ['slug' => $slug]);
            $this->assertEquals($initialPortfolios, Portfolio::count());
            $this->assertEquals($initialProfiles, PortfolioProfile::count());
            $this->assertEquals($initialNavItems, PortfolioNavItem::count());
        }
    }

    // 17. Navbar items parameter is strictly required
    public function test_nav_items_argument_is_strictly_required(): void
    {
        $this->expectException(\ArgumentCountError::class);

        // Calling create without 4th argument must throw ArgumentCountError
        /** @phpstan-ignore-next-line */
        $this->service->create(
            $this->superAdmin,
            $this->defaultPortfolioData(),
            $this->defaultProfileData()
        );
    }

    // 18. Missing Home navbar item is rejected
    public function test_missing_home_navbar_item_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Exactly one Home navbar item is required.');

        $items = [
            [
                'label' => 'About',
                'type' => 'section',
                'section_key' => 'about',
                'url' => null,
                'sort_order' => 1,
                'is_visible' => true,
            ],
        ];

        $this->service->create(
            $this->superAdmin,
            $this->defaultPortfolioData(),
            $this->defaultProfileData(),
            $items
        );
    }

    // 19. Home item with external URL is rejected
    public function test_home_item_with_external_url_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The Home navbar item cannot use an external URL destination.');

        $items = [
            [
                'label' => 'Home',
                'type' => 'url',
                'section_key' => null,
                'url' => 'https://example.com/home',
                'sort_order' => 1,
                'is_visible' => true,
            ],
        ];

        $this->service->create(
            $this->superAdmin,
            $this->defaultPortfolioData(),
            $this->defaultProfileData(),
            $items
        );
    }

    // 20. Home item with invalid section key is rejected
    public function test_home_item_with_invalid_section_key_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The Home navbar item must use section key 'home'.");

        $items = [
            [
                'label' => 'Home',
                'type' => 'section',
                'section_key' => 'not-home',
                'url' => null,
                'sort_order' => 1,
                'is_visible' => true,
            ],
        ];

        $this->service->create(
            $this->superAdmin,
            $this->defaultPortfolioData(),
            $this->defaultProfileData(),
            $items
        );
    }

    // 21. Home item with invalid sort order is rejected
    public function test_home_item_with_invalid_sort_order_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The Home navbar item must have sort order 1.');

        $items = [
            [
                'label' => 'Home',
                'type' => 'section',
                'section_key' => 'home',
                'url' => null,
                'sort_order' => 2,
                'is_visible' => true,
            ],
        ];

        $this->service->create(
            $this->superAdmin,
            $this->defaultPortfolioData(),
            $this->defaultProfileData(),
            $items
        );
    }

    // 22. Multiple Home items are rejected
    public function test_multiple_home_items_are_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Exactly one Home navbar item is required.');

        $items = [
            [
                'label' => 'Home',
                'type' => 'section',
                'section_key' => 'home',
                'url' => null,
                'sort_order' => 1,
                'is_visible' => true,
            ],
            [
                'label' => 'Home',
                'type' => 'section',
                'section_key' => 'home',
                'url' => null,
                'sort_order' => 1,
                'is_visible' => true,
            ],
        ];

        $this->service->create(
            $this->superAdmin,
            $this->defaultPortfolioData(),
            $this->defaultProfileData(),
            $items
        );
    }

    // 23. Mutual destination exclusivity is enforced
    public function test_mutual_destination_exclusivity_is_enforced(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Navbar item 'Projects' must provide exactly one destination.");

        $items = [
            [
                'label' => 'Home',
                'type' => 'section',
                'section_key' => 'home',
                'url' => null,
                'sort_order' => 1,
                'is_visible' => true,
            ],
            [
                'label' => 'Projects',
                'type' => 'section',
                'section_key' => 'projects',
                'url' => 'https://example.com/projects',
                'sort_order' => 2,
                'is_visible' => true,
            ],
        ];

        $this->service->create(
            $this->superAdmin,
            $this->defaultPortfolioData(),
            $this->defaultProfileData(),
            $items
        );
    }

    // 24. Duplicate sort orders are rejected
    public function test_duplicate_sort_orders_are_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Navbar item sort orders must be unique.');

        $items = [
            [
                'label' => 'Home',
                'type' => 'section',
                'section_key' => 'home',
                'url' => null,
                'sort_order' => 1,
                'is_visible' => true,
            ],
            [
                'label' => 'Projects',
                'type' => 'section',
                'section_key' => 'projects',
                'url' => null,
                'sort_order' => 1,
                'is_visible' => true,
            ],
        ];

        $this->service->create(
            $this->superAdmin,
            $this->defaultPortfolioData(),
            $this->defaultProfileData(),
            $items
        );
    }
}
