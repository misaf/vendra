<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Filament\Panel;
use Filament\PanelRegistry;
use Filament\Tables\Table;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Pennant\Feature;
use Livewire\Livewire;
use Misaf\VendraSupport\Contracts\TenantResolver;
use Misaf\VendraSupport\Tenancy\TenantAwareness;
use PHPUnit\Framework\Assert;

/**
 * @return class-string<Model>
 */
function testTenantModel(): string
{
    return resolve(TenantResolver::class)->modelClass();
}

/**
 * Create an active tenant, or return null when tenancy is disabled.
 *
 * @param  array<string, mixed>  $attributes
 */
function createTestTenant(array $attributes = []): ?Model
{
    if (! TenantAwareness::enabled()) {
        return null;
    }

    $factory = vendraTestingFactoryState(vendraTestingModelFactory(testTenantModel()), 'active');

    $tenant = $factory->create($attributes);

    if (! $tenant instanceof Model) {
        Assert::fail('The tenant factory did not create a single tenant model.');
    }

    return $tenant;
}

/**
 * Create an active tenant and make it current, or return null when tenancy is disabled.
 *
 * @param  array<string, mixed>  $attributes
 */
function makeCurrentTestTenant(array $attributes = []): ?Model
{
    $tenant = createTestTenant($attributes);

    if ($tenant instanceof Model) {
        resolve(TenantResolver::class)->makeCurrent($tenant);
    }

    return $tenant;
}

/**
 * Make the given tenant current; null is a no-op.
 *
 * The Filament panel tenant is switched too, so new records land in this tenant.
 */
function switchToTestTenant(Model|int|string|null $tenant): void
{
    if ($tenant === null) {
        return;
    }

    $resolver = resolve(TenantResolver::class);
    $resolver->makeCurrent($tenant);

    if (Filament::getTenant() instanceof Model) {
        Filament::setTenant($resolver->current(), isQuiet: true);
    }
}

function currentTestTenant(): ?Model
{
    return resolve(TenantResolver::class)->current();
}

function forgetCurrentTestTenant(): void
{
    if (! TenantAwareness::enabled()) {
        return;
    }

    $modelClass = testTenantModel();

    if (method_exists($modelClass, 'forgetCurrent')) {
        $modelClass::forgetCurrent();
    }
}

/**
 * @return class-string<Model>
 */
function testUserModel(): string
{
    $modelClass = config('auth.providers.users.model');

    if (! is_string($modelClass) || ! is_a($modelClass, Model::class, true)) {
        Assert::fail('The auth user provider does not define an Eloquent user model.');
    }

    return $modelClass;
}

/**
 * @param  array<string, mixed>  $attributes
 */
function createTestUser(array $attributes = []): Model
{
    $user = vendraTestingModelFactory(testUserModel())->create($attributes);

    if (! $user instanceof Model) {
        Assert::fail('The user factory did not create a single user model.');
    }

    return $user;
}

/**
 * Create the current tenant with only the requested features active.
 *
 * @param  list<string>  $features
 */
function makeCurrentTestTenantWithFeatures(array $features = []): Model
{
    $tenant = makeCurrentTestTenant();

    if (! $tenant instanceof Model) {
        Assert::fail('This helper requires an installed tenant provider.');
    }

    if (class_exists(Feature::class)) {
        $configuredFeatures = array_keys((array) config('vendra-permission.features.defaults', []));

        Feature::for($tenant)->deactivate(array_values(array_diff($configuredFeatures, $features)));
        Feature::for($tenant)->activate($features);
    }

    return $tenant;
}

/**
 * Boot the admin panel as an admin user and return the current tenant.
 *
 * @param  list<class-string>  $resources
 * @param  list<string>  $features
 */
function setUpFilamentAdminTestContext(array $resources = [], ?array $features = null): Model
{
    $tenant = makeCurrentTestTenantWithFeatures(
        $features ?? array_keys((array) config('vendra-permission.features.defaults', [])),
    );

    $user = vendraTestingFactoryState(vendraTestingModelFactory(testUserModel()), 'forTenant', $tenant)->create([
        'username' => 'admin',
        'email' => 'admin@example.test',
    ]);

    if (! $user instanceof Model) {
        Assert::fail('The user factory did not create a single user model.');
    }

    $roleModel = config('permission.models.role');

    if (is_string($roleModel) && is_a($roleModel, Model::class, true) && method_exists($user, 'assignRole')) {
        $role = vendraTestingFactoryState(vendraTestingModelFactory($roleModel), 'forTenant', $tenant);
        $role = vendraTestingFactoryState($role, 'forGuard', 'web');

        $user->assignRole($role->create([
            'name' => config('vendra-permission.admin_role'),
        ]));
    }

    bootFilamentAdminPanel($user, $tenant, $resources);

    return $tenant;
}

/**
 * Boot the admin panel as the given user, in the given tenant if any.
 *
 * @param  list<class-string>  $resources
 */
function bootFilamentAdminPanel(Model $user, ?Model $tenant = null, array $resources = []): void
{
    $panel = Panel::make()
        ->default()
        ->id('admin')
        ->path('admin')
        ->resources($resources);

    if ($tenant instanceof Model) {
        /*
         | Filament derives the ownership relationship from the tenant model's
         | class name — `Store` would make it look for `$record->store()`. Vendra's
         | tenant-scoped models expose the role, not the business model, so name
         | the relationship explicitly and keep the panel working whichever model
         | is configured as the tenant.
         */
        $panel->tenant(testTenantModel(), ownershipRelationship: 'tenant');
    }

    resolve(PanelRegistry::class)->register($panel);

    Table::configureUsing(static fn (Table $table): Table => $table
        ->paginationPageOptions([10, 25, 50])
        ->deferLoading());

    Filament::setCurrentPanel('admin');
    Livewire::actingAs($user);

    if ($tenant instanceof Model) {
        Filament::setTenant($tenant);
    }

    Filament::bootCurrentPanel();

    resolve(UrlGenerator::class)->resolveMissingNamedRoutesUsing(static fn (): string => '/');
}

/**
 * @param  class-string<Model>  $modelClass
 * @return Factory<Model>
 */
function vendraTestingModelFactory(string $modelClass): Factory
{
    $newFactory = [$modelClass, 'factory'];

    if (! is_callable($newFactory)) {
        Assert::fail("The model [{$modelClass}] does not expose a factory.");
    }

    $factory = $newFactory();

    if (! $factory instanceof Factory) {
        Assert::fail("The model [{$modelClass}] did not resolve an Eloquent factory.");
    }

    return $factory;
}

/**
 * Apply a factory state that only some models' factories define.
 *
 * @param  Factory<Model>  $factory
 * @return Factory<Model>
 */
function vendraTestingFactoryState(Factory $factory, string $state, mixed ...$arguments): Factory
{
    if (! method_exists($factory, $state)) {
        return $factory;
    }

    $factory = $factory->{$state}(...$arguments);

    if (! $factory instanceof Factory) {
        Assert::fail("The factory state [{$state}] did not return an Eloquent factory.");
    }

    return $factory;
}
