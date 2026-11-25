<?php

namespace Tests\Feature;

use App\Models\Portfolio;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PortfolioCreationTest extends TestCase
{
    use DatabaseTransactions;

    private User $superAdmin;

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
    }

    private function baseValidPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Apex Architecture Studio',
            'slug' => 'apex-studio-' . uniqid(),
            'description' => 'A luxury architectural portfolio showcase.',
            'status' => 'draft',
            'full_name' => 'Alexander Vance',
            'professional_title' => 'Lead Architect',
            'email' => 'alex@apexstudio.design',
            'location' => 'Dhaka, Bangladesh',
            'bio' => 'Passionate about minimalist structures.',
            'nav_items' => [
                [
                    'label' => 'Home',
                    'type' => 'section',
                    'section_key' => 'home',
                    'sort_order' => 1,
                    'is_visible' => true,
                ],
            ],
        ], $overrides);
    }

    public function test_guest_cannot_access_portfolio_creation_page(): void
    {
        $response = $this->get('/admin/portfolios/create');

        $response->assertStatus(302);
        $response->assertRedirect('/admin/login');
    }

    public function test_super_admin_can_access_portfolio_creation_page(): void
    {
        $response = $this->actingAs($this->superAdmin, 'super_admin')
            ->get('/admin/portfolios/create');

        $response->assertStatus(200);
        $response->assertSee('Create New Profile');
        $response->assertSee('Create a personal portfolio profile, configure its navigation and prepare it for publishing.');
        
        // Admin assignment
        $response->assertSee('Assigned Administrator');
        $response->assertSee('Read-Only Assignment');
        $response->assertSee($this->superAdmin->name);
        $response->assertSee($this->superAdmin->email);

        // Portfolio info & preview
        $response->assertSee('Portfolio Information');
        $response->assertSee('http://127.0.0.1:8000/');
        $response->assertDontSee('Draft / Staging');
        $response->assertDontSee('Published / Live');

        // Profile info & photo absence
        $response->assertSee('Owner Profile Information');
        $response->assertSee('name="full_name"', false);
        $response->assertSee('name="professional_title"', false);
        $response->assertSee('name="email"', false);
        $response->assertSee('name="location"', false);
        $response->assertSee('name="bio"', false);
        $response->assertDontSee('Owner Profile Photo');
        $response->assertDontSee('name="profile_photo"', false);

        // Navbar Configuration
        $response->assertSee('Navbar Configuration');
        $response->assertSee('/ 10 Items');
        $response->assertSee('navbar-count-display');
        $response->assertSee('Add Navbar Item');
        $response->assertSee('Default Home');
        $response->assertSee('value="home"', false);

        // Form actions
        $response->assertSee('Cancel');
        $response->assertSee('Create Profile');
        $response->assertDontSee('Save as Draft');
    }

    public function test_super_admin_can_create_published_portfolio_with_profile_photo(): void
    {
        $slug = 'apex-studio-' . uniqid();

        $data = $this->baseValidPayload([
            'slug' => $slug,
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->superAdmin, 'super_admin')
            ->post('/admin/portfolios', $data);

        $response->assertStatus(302);
        $response->assertRedirect(route('admin.profiles.index'));
        $response->assertSessionHas('success', 'Portfolio created successfully.');

        // Assert Portfolio in DB
        $portfolio = Portfolio::where('slug', $slug)->first();
        $this->assertNotNull($portfolio);
        $this->assertEquals('draft', $portfolio->status);
        $this->assertNull($portfolio->published_at);
        $this->assertEquals($this->superAdmin->id, $portfolio->user_id);

        // Assert Profile in DB (profile photo upload deferred, stored as null)
        $profile = $portfolio->profile;
        $this->assertNotNull($profile);
        $this->assertEquals('Alexander Vance', $profile->full_name);
        $this->assertEquals('Lead Architect', $profile->professional_title);
        $this->assertNull($profile->profile_photo_path);

        // Assert Default Nav Item (Home) in DB
        $navItem = $portfolio->navItems()->first();
        $this->assertNotNull($navItem);
        $this->assertEquals('Home', $navItem->label);
        $this->assertEquals('home', $navItem->section_key);
        $this->assertEquals(1, $navItem->sort_order);
        $this->assertTrue((bool)$navItem->is_visible);
    }

    public function test_super_admin_can_create_draft_portfolio(): void
    {
        $slug = 'draft-studio-' . uniqid();

        $data = $this->baseValidPayload([
            'slug' => $slug,
            'status' => 'draft',
            'full_name' => 'Elena Rostova',
            'professional_title' => 'Design Director',
        ]);

        $response = $this->actingAs($this->superAdmin, 'super_admin')
            ->post('/admin/portfolios', $data);

        $response->assertStatus(302);
        $response->assertRedirect(route('admin.profiles.index'));

        $portfolio = Portfolio::where('slug', $slug)->first();
        $this->assertNotNull($portfolio);
        $this->assertEquals('draft', $portfolio->status);
        $this->assertNull($portfolio->published_at);
    }

    public function test_duplicate_slug_is_rejected_for_same_user(): void
    {
        $slug = 'duplicate-test-' . uniqid();

        // Create first portfolio
        $this->actingAs($this->superAdmin, 'super_admin')->post('/admin/portfolios', $this->baseValidPayload([
            'name' => 'First Portfolio',
            'slug' => $slug,
            'full_name' => 'First Owner',
        ]));

        // Attempt second portfolio with identical slug
        $response = $this->actingAs($this->superAdmin, 'super_admin')->post('/admin/portfolios', $this->baseValidPayload([
            'name' => 'Second Portfolio',
            'slug' => $slug,
            'full_name' => 'Second Owner',
        ]));

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['slug']);
    }

    public function test_required_fields_validation(): void
    {
        $response = $this->actingAs($this->superAdmin, 'super_admin')->post('/admin/portfolios', []);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['name', 'slug', 'full_name', 'nav_items']);
    }

    public function test_duplicate_slug_is_rejected_globally_across_different_admins(): void
    {
        $slug = 'global-slug-' . uniqid();

        $secondAdmin = User::firstOrCreate(
            ['email' => 'second_admin@portfoliocms.test'],
            [
                'name' => 'Second Super Admin',
                'password' => bcrypt('Admin@123456'),
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );

        // First admin creates portfolio
        $this->actingAs($this->superAdmin, 'super_admin')->post('/admin/portfolios', $this->baseValidPayload([
            'name' => 'First Admin Portfolio',
            'slug' => $slug,
            'full_name' => 'First Owner',
        ]));

        // Second admin tries to use the same slug
        $response = $this->actingAs($secondAdmin, 'super_admin')->post('/admin/portfolios', $this->baseValidPayload([
            'name' => 'Second Admin Portfolio',
            'slug' => $slug,
            'full_name' => 'Second Owner',
        ]));

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['slug']);
    }

    public function test_database_failure_rolls_back_entire_transaction(): void
    {
        $slug = 'fail-slug-' . uniqid();
        $service = app(\App\Services\PortfolioService::class);

        $initialPortfolios = Portfolio::count();

        try {
            $service->create(
                $this->superAdmin,
                ['name' => null, 'slug' => $slug], // name is non-nullable in DB
                ['full_name' => 'Failing Owner'],
                [
                    [
                        'label' => 'Home',
                        'type' => 'section',
                        'section_key' => 'home',
                        'sort_order' => 1,
                        'is_visible' => true,
                    ],
                ]
            );
            $this->fail('Expected exception was not thrown.');
        } catch (\Throwable $e) {
            $this->assertDatabaseMissing('portfolios', ['slug' => $slug]);
            $this->assertEquals($initialPortfolios, Portfolio::count());
        }
    }

    public function test_existing_navbar_routes_continue_to_function(): void
    {
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('admin.portfolios.nav-items.index'));
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('admin.portfolios.nav-items.store'));
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('admin.portfolios.nav-items.reorder'));
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('admin.portfolios.nav-items.update'));
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('admin.portfolios.nav-items.destroy'));

        $slug = 'nav-check-' . uniqid();

        $this->actingAs($this->superAdmin, 'super_admin')->post('/admin/portfolios', $this->baseValidPayload([
            'name' => 'Navbar Route Verification',
            'slug' => $slug,
            'full_name' => 'Nav Owner',
        ]));

        $portfolio = Portfolio::where('slug', $slug)->firstOrFail();

        // Super Admin can store an additional nav item through existing route
        $storeResponse = $this->actingAs($this->superAdmin, 'super_admin')
            ->from('/admin/dashboard')
            ->post("/admin/portfolios/{$portfolio->id}/nav-items", [
                'label' => 'About',
                'section_key' => 'about',
            ]);

        $storeResponse->assertStatus(302);
        $storeResponse->assertSessionHas('success', 'Navigation link created successfully.');

        $this->assertEquals(2, $portfolio->navItems()->count());
    }

    // =========================================================================
    // Phase 02 Step 1: Comprehensive Form Request Validation Tests
    // =========================================================================

    public function test_valid_portfolio_profile_and_navbar_payload_passes_validation(): void
    {
        $payload = $this->baseValidPayload([
            'nav_items' => [
                [
                    'label' => 'Home',
                    'type' => 'section',
                    'section_key' => 'home',
                    'sort_order' => 1,
                    'is_visible' => true,
                ],
                [
                    'label' => 'Projects',
                    'type' => 'section',
                    'section_key' => 'projects',
                    'sort_order' => 2,
                    'is_visible' => true,
                ],
                [
                    'label' => 'GitHub',
                    'type' => 'url',
                    'url' => 'https://github.com/example',
                    'sort_order' => 3,
                    'is_visible' => false,
                ],
            ],
        ]);

        $response = $this->actingAs($this->superAdmin, 'super_admin')->post('/admin/portfolios', $payload);

        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
    }

    public function test_ten_navbar_items_accepted(): void
    {
        $navItems = [
            [
                'label' => 'Home',
                'type' => 'section',
                'section_key' => 'home',
                'sort_order' => 1,
                'is_visible' => true,
            ],
        ];

        for ($i = 2; $i <= 10; $i++) {
            $navItems[] = [
                'label' => 'Section ' . $i,
                'type' => 'section',
                'section_key' => 'sec_' . $i,
                'sort_order' => $i,
                'is_visible' => true,
            ];
        }

        $payload = $this->baseValidPayload(['nav_items' => $navItems]);

        $response = $this->actingAs($this->superAdmin, 'super_admin')->post('/admin/portfolios', $payload);

        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
    }

    public function test_eleven_navbar_items_rejected(): void
    {
        $navItems = [
            [
                'label' => 'Home',
                'type' => 'section',
                'section_key' => 'home',
                'sort_order' => 1,
                'is_visible' => true,
            ],
        ];

        for ($i = 2; $i <= 11; $i++) {
            $navItems[] = [
                'label' => 'Section ' . $i,
                'type' => 'section',
                'section_key' => 'sec_' . $i,
                'sort_order' => $i,
                'is_visible' => true,
            ];
        }

        $payload = $this->baseValidPayload(['nav_items' => $navItems]);

        $response = $this->actingAs($this->superAdmin, 'super_admin')->post('/admin/portfolios', $payload);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['nav_items']);
    }

    public function test_missing_home_navbar_item_rejected(): void
    {
        $payload = $this->baseValidPayload([
            'nav_items' => [
                [
                    'label' => 'About',
                    'type' => 'section',
                    'section_key' => 'about',
                    'sort_order' => 1,
                    'is_visible' => true,
                ],
            ],
        ]);

        $response = $this->actingAs($this->superAdmin, 'super_admin')->post('/admin/portfolios', $payload);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['nav_items']);
    }

    public function test_duplicate_home_navbar_items_rejected(): void
    {
        $payload = $this->baseValidPayload([
            'nav_items' => [
                [
                    'label' => 'Home',
                    'type' => 'section',
                    'section_key' => 'home',
                    'sort_order' => 1,
                    'is_visible' => true,
                ],
                [
                    'label' => 'Home',
                    'type' => 'section',
                    'section_key' => 'home',
                    'sort_order' => 2,
                    'is_visible' => true,
                ],
            ],
        ]);

        $response = $this->actingAs($this->superAdmin, 'super_admin')->post('/admin/portfolios', $payload);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['nav_items']);
    }

    public function test_invalid_home_sort_order_rejected(): void
    {
        $payload = $this->baseValidPayload([
            'nav_items' => [
                [
                    'label' => 'About',
                    'type' => 'section',
                    'section_key' => 'about',
                    'sort_order' => 1,
                    'is_visible' => true,
                ],
                [
                    'label' => 'Home',
                    'type' => 'section',
                    'section_key' => 'home',
                    'sort_order' => 2,
                    'is_visible' => true,
                ],
            ],
        ]);

        $response = $this->actingAs($this->superAdmin, 'super_admin')->post('/admin/portfolios', $payload);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['nav_items.1.sort_order']);
    }

    public function test_navbar_item_with_both_destinations_rejected(): void
    {
        $payload = $this->baseValidPayload([
            'nav_items' => [
                [
                    'label' => 'Home',
                    'type' => 'section',
                    'section_key' => 'home',
                    'sort_order' => 1,
                    'is_visible' => true,
                ],
                [
                    'label' => 'Hybrid Item',
                    'type' => 'section',
                    'section_key' => 'about',
                    'url' => 'https://example.com',
                    'sort_order' => 2,
                    'is_visible' => true,
                ],
            ],
        ]);

        $response = $this->actingAs($this->superAdmin, 'super_admin')->post('/admin/portfolios', $payload);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['nav_items.1']);
    }

    public function test_navbar_item_with_missing_destination_rejected(): void
    {
        $payload = $this->baseValidPayload([
            'nav_items' => [
                [
                    'label' => 'Home',
                    'type' => 'section',
                    'section_key' => 'home',
                    'sort_order' => 1,
                    'is_visible' => true,
                ],
                [
                    'label' => 'Empty Link',
                    'type' => 'section',
                    'section_key' => '',
                    'url' => null,
                    'sort_order' => 2,
                    'is_visible' => true,
                ],
            ],
        ]);

        $response = $this->actingAs($this->superAdmin, 'super_admin')->post('/admin/portfolios', $payload);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['nav_items.1.section_key']);
    }

    public function test_invalid_external_url_rejected(): void
    {
        $payload = $this->baseValidPayload([
            'nav_items' => [
                [
                    'label' => 'Home',
                    'type' => 'section',
                    'section_key' => 'home',
                    'sort_order' => 1,
                    'is_visible' => true,
                ],
                [
                    'label' => 'Invalid Link',
                    'type' => 'url',
                    'url' => 'not-a-valid-url',
                    'sort_order' => 2,
                    'is_visible' => true,
                ],
            ],
        ]);

        $response = $this->actingAs($this->superAdmin, 'super_admin')->post('/admin/portfolios', $payload);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['nav_items.1.url']);
    }

    public function test_invalid_section_key_regex_rejected(): void
    {
        $payload = $this->baseValidPayload([
            'nav_items' => [
                [
                    'label' => 'Home',
                    'type' => 'section',
                    'section_key' => 'home',
                    'sort_order' => 1,
                    'is_visible' => true,
                ],
                [
                    'label' => 'Bad Section',
                    'type' => 'section',
                    'section_key' => '123_invalid_start_with_number',
                    'sort_order' => 2,
                    'is_visible' => true,
                ],
            ],
        ]);

        $response = $this->actingAs($this->superAdmin, 'super_admin')->post('/admin/portfolios', $payload);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['nav_items.1.section_key']);
    }

    public function test_duplicate_sort_order_rejected(): void
    {
        $payload = $this->baseValidPayload([
            'nav_items' => [
                [
                    'label' => 'Home',
                    'type' => 'section',
                    'section_key' => 'home',
                    'sort_order' => 1,
                    'is_visible' => true,
                ],
                [
                    'label' => 'Duplicate Order',
                    'type' => 'section',
                    'section_key' => 'duplicate',
                    'sort_order' => 1,
                    'is_visible' => true,
                ],
            ],
        ]);

        $response = $this->actingAs($this->superAdmin, 'super_admin')->post('/admin/portfolios', $payload);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['nav_items']);
    }

    public function test_non_consecutive_array_keys_handled_correctly(): void
    {
        $payload = $this->baseValidPayload([
            'nav_items' => [
                0 => [
                    'label' => 'Home',
                    'type' => 'section',
                    'section_key' => 'home',
                    'sort_order' => 1,
                    'is_visible' => true,
                ],
                4 => [
                    'label' => 'Projects',
                    'type' => 'section',
                    'section_key' => 'projects',
                    'sort_order' => 2,
                    'is_visible' => true,
                ],
                8 => [
                    'label' => 'Contact',
                    'type' => 'section',
                    'section_key' => 'contact',
                    'sort_order' => 3,
                    'is_visible' => false,
                ],
            ],
        ]);

        $response = $this->actingAs($this->superAdmin, 'super_admin')->post('/admin/portfolios', $payload);

        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
    }

    public function test_inactive_super_admin_rejected_with_403(): void
    {
        $inactiveAdmin = User::create([
            'name' => 'Inactive Admin',
            'email' => 'inactive_' . uniqid() . '@portfoliocms.test',
            'password' => bcrypt('Password@123'),
            'role' => 'super_admin',
            'is_active' => false,
        ]);

        $payload = $this->baseValidPayload();

        $response = $this->actingAs($inactiveAdmin, 'super_admin')->post('/admin/portfolios', $payload);

        $response->assertStatus(403);
    }

    public function test_validation_failure_rehydrates_old_navbar_items(): void
    {
        // First portfolio created to reserve slug
        $existingSlug = 'duplicate-rehydrate-' . uniqid();
        $this->actingAs($this->superAdmin, 'super_admin')->post('/admin/portfolios', $this->baseValidPayload([
            'slug' => $existingSlug,
        ]));

        // Second portfolio attempt with identical slug and 3 navbar items
        $response = $this->actingAs($this->superAdmin, 'super_admin')
            ->from('/admin/portfolios/create')
            ->post('/admin/portfolios', [
                'name' => 'Duplicate Portfolio Test',
                'slug' => $existingSlug,
                'full_name' => 'Test Owner',
                'nav_items' => [
                    [
                        'label' => 'Home',
                        'type' => 'section',
                        'section_key' => 'home',
                        'sort_order' => 1,
                        'is_visible' => true,
                    ],
                    [
                        'label' => 'Projects',
                        'type' => 'section',
                        'section_key' => 'projects',
                        'sort_order' => 2,
                        'is_visible' => true,
                    ],
                    [
                        'label' => 'GitHub',
                        'type' => 'url',
                        'url' => 'https://github.com/my-profile',
                        'sort_order' => 3,
                        'is_visible' => false,
                    ],
                ],
            ]);

        $response->assertStatus(302);
        $response->assertRedirect('/admin/portfolios/create');
        $response->assertSessionHasErrors(['slug']);

        // Follow redirect to create page with session
        $createPageResponse = $this->actingAs($this->superAdmin, 'super_admin')
            ->get('/admin/portfolios/create');

        $createPageResponse->assertStatus(200);
        $createPageResponse->assertSee('value="Home"', false);
        $createPageResponse->assertSee('value="Projects"', false);
        $createPageResponse->assertSee('value="GitHub"', false);
        $createPageResponse->assertSee('https://github.com/my-profile');
    }
}
