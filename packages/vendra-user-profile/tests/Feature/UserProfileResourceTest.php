<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Laravel\Pennant\Feature;
use Misaf\VendraUser\Models\User;
use Misaf\VendraUserProfile\Database\Factories\UserProfileFactory;
use Misaf\VendraUserProfile\Features\ModuleEnabled;
use Misaf\VendraUserProfile\Filament\Clusters\Resources\Pages\CreateUserProfile;
use Misaf\VendraUserProfile\Filament\Clusters\Resources\Pages\ListUserProfiles;
use Misaf\VendraUserProfile\Filament\Clusters\Resources\UserProfileResource;
use Misaf\VendraUserProfile\Models\UserProfile;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    if (! Schema::hasTable('user_profiles')) {
        $migration = require __DIR__.'/../../database/migrations/create_user_profiles_table.php.stub';
        $migration->up();
    }
});

it('exposes the installed module by default', function (): void {
    makeCurrentTestTenant();

    expect(Config::boolean('vendra-user-profile.features_enabled'))->toBeTrue()
        ->and(Config::boolean('vendra-user-profile.module_enabled'))->toBeTrue()
        ->and(UserProfileResource::canAccess())->toBeTrue();
});

it('lets the global module switch override a persisted inactive value', function (): void {
    Config::set('vendra-user-profile.features', [
        'enabled' => true,
        'discover' => false,
    ]);
    Config::set('vendra-user-profile.features_enabled', true);
    Config::set('vendra-user-profile.module_enabled', true);
    $tenant = makeCurrentTestTenant();
    Feature::for($tenant)->deactivate(ModuleEnabled::class);
    Feature::flushCache();

    expect(UserProfileResource::canAccess())->toBeTrue();
});

it('lets the global feature switch override a persisted active value', function (): void {
    Config::set('vendra-user-profile.features_enabled', false);
    Config::set('vendra-user-profile.module_enabled', false);
    $tenant = makeCurrentTestTenant();
    Feature::for($tenant)->activate(ModuleEnabled::class);
    Feature::flushCache();

    expect(UserProfileResource::canAccess())->toBeFalse();
});

it('rejects feature scopes that are not tenants', function (): void {
    Config::set('vendra-user-profile.features_enabled', true);
    Config::set('vendra-user-profile.module_enabled', true);
    makeCurrentTestTenant();

    expect(Feature::for(createTestUser())->active(ModuleEnabled::class))->toBeFalse();
});

it('renders user profiles in the Filament resource table', function (): void {
    setUpFilamentAdminTestContext([UserProfileResource::class]);

    $profile = UserProfile::query()->create([
        'user_id' => User::query()->valueOrFail('id'),
        'name' => 'Primary Profile',
        'description' => 'Primary user profile',
        'slug' => 'primary-profile',
        'is_default' => true,
        'active' => true,
    ]);

    livewire(ListUserProfiles::class)
        ->assertOk()
        ->call('loadTable')
        ->assertCanSeeTableRecords([$profile]);
});

it('toggles a user profile active state from the Filament resource table', function (): void {
    setUpFilamentAdminTestContext([UserProfileResource::class]);

    $profile = UserProfileFactory::new()
        ->inactive()
        ->createOne();

    livewire(ListUserProfiles::class)
        ->call('updateTableColumnState', 'active', (string) $profile->getKey(), true);

    expect($profile->refresh()->active)->toBeTrue();

    livewire(ListUserProfiles::class)
        ->loadTable()
        ->assertCanSeeTableRecords([$profile])
        ->assertTableColumnStateSet('active', true, $profile);
});

it('exposes the default toggle and keeps one default profile per user', function (): void {
    setUpFilamentAdminTestContext([UserProfileResource::class]);

    $user = User::factory()->create();
    $firstProfile = UserProfileFactory::new()->forUser($user)->createOne(['is_default' => true]);
    $secondProfile = UserProfileFactory::new()->forUser($user)->createOne(['is_default' => true]);

    expect($firstProfile->refresh()->is_default)->toBeFalse()
        ->and($secondProfile->refresh()->is_default)->toBeTrue();

    livewire(CreateUserProfile::class)
        ->assertOk()
        ->assertFormFieldExists('is_default');
});
