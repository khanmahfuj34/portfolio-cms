<?php

namespace Tests\Feature;

use App\Enums\ThemeStatus;
use App\Http\Requests\SuperAdmin\StoreThemeRequest;
use App\Http\Requests\SuperAdmin\UpdateThemeRequest;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ThemeRequestValidationTest extends TestCase
{
    use DatabaseTransactions;

    private function makeFormRequest(
        string $requestClass,
        array $payload,
        ?User $superAdminUser = null,
        mixed $boundTheme = null
    ): StoreThemeRequest {
        /** @var StoreThemeRequest $request */
        $request = $requestClass::create('/admin/themes', 'POST', $payload);
        $request->setContainer($this->app);
        $request->setRedirector($this->app['redirect']);

        $request->setUserResolver(function (?string $guard = null) use ($superAdminUser) {
            return $guard === 'super_admin' ? $superAdminUser : null;
        });

        if ($boundTheme !== null) {
            $route = new RoutingRoute(['PUT'], '/admin/themes/{theme}', fn () => null);
            $route->bind($request);
            $route->setParameter('theme', $boundTheme);
            $request->setRouteResolver(fn () => $route);
        }

        return $request;
    }

    private function validPayload(string $suffix = ''): array
    {
        $suffix = $suffix !== '' ? $suffix : uniqid();

        return [
            'name' => 'Developer Dark ' . $suffix,
            'slug' => 'developer-dark-' . $suffix,
            'theme_key' => 'developer_dark_' . $suffix,
            'description' => 'Minimal dark developer theme.',
            'category' => 'developer',
            'thumbnail' => '/images/themes/developer-dark.png',
            'version' => '1.0.0',
            'is_featured' => true,
        ];
    }

    public function test_authorization_enforces_active_super_admin_on_super_admin_guard(): void
    {
        $superAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);
        $inactiveSuperAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => false,
        ]);
        $individualAdmin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        // Super admin guard authorizes store & update requests
        $this->assertTrue($this->makeFormRequest(StoreThemeRequest::class, [], $superAdmin)->authorize());
        $this->assertTrue($this->makeFormRequest(UpdateThemeRequest::class, [], $superAdmin)->authorize());

        // Guest, inactive Super Admin, and Individual Admin fail authorization
        $this->assertFalse($this->makeFormRequest(StoreThemeRequest::class, [], null)->authorize());
        $this->assertFalse($this->makeFormRequest(StoreThemeRequest::class, [], $inactiveSuperAdmin)->authorize());
        $this->assertFalse($this->makeFormRequest(StoreThemeRequest::class, [], $individualAdmin)->authorize());
        $this->assertFalse($this->makeFormRequest(UpdateThemeRequest::class, [], $individualAdmin)->authorize());

        // Individual Admin authenticated only on 'admin' or default guard cannot authorize
        $adminRequest = StoreThemeRequest::create('/admin/themes', 'POST', []);
        $adminRequest->setUserResolver(fn (?string $guard = null) => $guard === 'admin' || $guard === null ? $individualAdmin : null);
        $this->assertFalse($adminRequest->authorize());
    }

    public function test_store_theme_request_accepts_valid_input_and_rejects_missing_or_invalid_fields(): void
    {
        $request = $this->makeFormRequest(StoreThemeRequest::class, $this->validPayload());
        $validator = Validator::make($request->all(), $request->rules());
        $this->assertFalse($validator->fails());

        // Missing required fields
        $missingValidator = Validator::make([], $request->rules());
        $this->assertTrue($missingValidator->fails());
        $this->assertArrayHasKey('name', $missingValidator->errors()->toArray());
        $this->assertArrayHasKey('slug', $missingValidator->errors()->toArray());
        $this->assertArrayHasKey('theme_key', $missingValidator->errors()->toArray());
        $this->assertArrayHasKey('version', $missingValidator->errors()->toArray());

        // Invalid field types
        $invalidTypeValidator = Validator::make([
            'name' => ['bad'],
            'slug' => 12345,
            'theme_key' => 999,
            'description' => ['bad'],
            'category' => 42,
            'thumbnail' => ['bad'],
            'version' => 100,
            'is_featured' => 'not-a-boolean',
        ], $request->rules());

        $this->assertTrue($invalidTypeValidator->fails());
        foreach (['name', 'slug', 'theme_key', 'description', 'category', 'thumbnail', 'version', 'is_featured'] as $field) {
            $this->assertArrayHasKey($field, $invalidTypeValidator->errors()->toArray());
        }
    }

    public function test_store_theme_request_enforces_length_limits_and_uniqueness(): void
    {
        $creator = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN, 'is_active' => true]);
        $suffix = uniqid();

        $existing = new Theme([
            'name' => 'Existing Theme',
            'slug' => 'existing-slug-' . $suffix,
            'theme_key' => 'existing_key_' . $suffix,
            'version' => '1.0.0',
            'status' => ThemeStatus::Draft,
        ]);
        $existing->created_by = $creator->id;
        $existing->save();

        $request = $this->makeFormRequest(StoreThemeRequest::class, []);

        // Max length violations
        $tooLongPayload = [
            'name' => str_repeat('a', 151),
            'slug' => str_repeat('b', 181),
            'theme_key' => str_repeat('c', 101),
            'category' => str_repeat('d', 101),
            'thumbnail' => str_repeat('e', 256),
            'version' => str_repeat('1', 21),
        ];
        $lengthValidator = Validator::make($tooLongPayload, $request->rules());
        $this->assertTrue($lengthValidator->fails());
        foreach (['name', 'slug', 'theme_key', 'category', 'thumbnail', 'version'] as $field) {
            $this->assertArrayHasKey($field, $lengthValidator->errors()->toArray());
        }

        // Duplicate slug and duplicate theme_key
        $duplicatePayload = $this->validPayload();
        $duplicatePayload['slug'] = $existing->slug;
        $duplicatePayload['theme_key'] = $existing->theme_key;

        $dupValidator = Validator::make($duplicatePayload, $request->rules());
        $this->assertTrue($dupValidator->fails());
        $this->assertArrayHasKey('slug', $dupValidator->errors()->toArray());
        $this->assertArrayHasKey('theme_key', $dupValidator->errors()->toArray());
    }

    public function test_update_theme_request_ignores_current_theme_id_for_uniqueness(): void
    {
        $creator = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN, 'is_active' => true]);
        $suffix = uniqid();

        $themeA = new Theme([
            'name' => 'Theme A',
            'slug' => 'theme-a-' . $suffix,
            'theme_key' => 'theme_a_' . $suffix,
            'version' => '1.0.0',
            'status' => ThemeStatus::Published,
        ]);
        $themeA->created_by = $creator->id;
        $themeA->save();

        $themeB = new Theme([
            'name' => 'Theme B',
            'slug' => 'theme-b-' . $suffix,
            'theme_key' => 'theme_b_' . $suffix,
            'version' => '1.0.0',
            'status' => ThemeStatus::Draft,
        ]);
        $themeB->created_by = $creator->id;
        $themeB->save();

        // Keeping own slug & theme_key on Theme A must pass
        $sameData = [
            'name' => 'Theme A Updated',
            'slug' => $themeA->slug,
            'theme_key' => $themeA->theme_key,
            'version' => '1.1.0',
        ];
        $updateOwnRequest = $this->makeFormRequest(UpdateThemeRequest::class, $sameData, $creator, $themeA);
        $ownValidator = Validator::make($updateOwnRequest->all(), $updateOwnRequest->rules());
        $this->assertFalse($ownValidator->fails());

        // Colliding with Theme B must fail
        $collisionData = [
            'name' => 'Theme A Collision',
            'slug' => $themeB->slug,
            'theme_key' => $themeB->theme_key,
            'version' => '1.1.0',
        ];
        $collisionValidator = Validator::make($collisionData, $updateOwnRequest->rules());
        $this->assertTrue($collisionValidator->fails());
        $this->assertArrayHasKey('slug', $collisionValidator->errors()->toArray());
        $this->assertArrayHasKey('theme_key', $collisionValidator->errors()->toArray());
    }

    public function test_protected_fields_cannot_be_submitted_on_store_or_update(): void
    {
        $creator = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN, 'is_active' => true]);
        $suffix = uniqid();

        $theme = new Theme([
            'name' => 'Protected Theme',
            'slug' => 'protected-theme-' . $suffix,
            'theme_key' => 'protected_theme_' . $suffix,
            'version' => '1.0.0',
            'status' => ThemeStatus::Draft,
        ]);
        $theme->created_by = $creator->id;
        $theme->save();

        $protectedPayload = array_merge($this->validPayload(), [
            'id' => 999,
            'created_by' => 999,
            'status' => 'published',
            'published_at' => '2026-11-26 10:00:00',
        ]);

        $storeRequest = $this->makeFormRequest(StoreThemeRequest::class, $protectedPayload, $creator);
        $storeValidator = Validator::make($storeRequest->all(), $storeRequest->rules());
        $this->assertTrue($storeValidator->fails());
        foreach (['id', 'created_by', 'status', 'published_at'] as $field) {
            $this->assertArrayHasKey($field, $storeValidator->errors()->toArray());
        }

        $updateRequest = $this->makeFormRequest(UpdateThemeRequest::class, $protectedPayload, $creator, $theme);
        $updateValidator = Validator::make($updateRequest->all(), $updateRequest->rules());
        $this->assertTrue($updateValidator->fails());
        foreach (['id', 'created_by', 'status', 'published_at'] as $field) {
            $this->assertArrayHasKey($field, $updateValidator->errors()->toArray());
        }
    }
}
