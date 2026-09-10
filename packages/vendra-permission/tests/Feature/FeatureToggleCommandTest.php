<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Laravel\Pennant\Feature;
use Misaf\VendraPermission\Enums\PermissionFeatureEnum;

beforeEach(function (): void {
    Config::set('vendra-permission.features.enabled', true);
});

it('activates a single feature for the tenant by short value', function (): void {
    $tenant = createTestTenant();
    Feature::for($tenant)->deactivate(PermissionFeatureEnum::RoleManagement->value);

    $this->artisan('vendra-permission:feature', [
        'action' => 'activate',
        'feature' => 'role-management',
        'tenant' => $tenant->slug,
    ])->assertSuccessful();

    expect(Feature::for($tenant)->active(PermissionFeatureEnum::RoleManagement->value))->toBeTrue();
});

it('deactivates every feature for the tenant with the all keyword', function (): void {
    $tenant = createTestTenant();
    Feature::for($tenant)->activate(array_map(
        static fn (PermissionFeatureEnum $feature): string => $feature->value,
        PermissionFeatureEnum::cases(),
    ));

    $this->artisan('vendra-permission:feature', [
        'action' => 'deactivate',
        'feature' => 'all',
        'tenant' => (string) $tenant->id,
    ])->assertSuccessful();

    foreach (PermissionFeatureEnum::cases() as $feature) {
        expect(Feature::for($tenant)->inactive($feature->value))->toBeTrue();
    }
});

it('rejects unknown actions', function (): void {
    $tenant = createTestTenant();

    $this->artisan('vendra-permission:feature', [
        'action' => 'enable',
        'feature' => 'all',
        'tenant' => $tenant->slug,
    ])->assertFailed();
});

it('rejects unknown features and lists the accepted values', function (): void {
    $tenant = createTestTenant();

    $this->artisan('vendra-permission:feature', [
        'action' => 'activate',
        'feature' => 'not-a-feature',
        'tenant' => $tenant->slug,
    ])
        ->expectsOutputToContain('is invalid')
        ->assertFailed();
});

it('fails for an unknown tenant', function (): void {
    $this->artisan('vendra-permission:feature', [
        'action' => 'activate',
        'feature' => 'all',
        'tenant' => 'missing-tenant',
    ])->assertFailed();
});

it('refuses to run while feature resolution is disabled', function (): void {
    Config::set('vendra-permission.features.enabled', false);
    $tenant = createTestTenant();

    $this->artisan('vendra-permission:feature', [
        'action' => 'activate',
        'feature' => 'all',
        'tenant' => $tenant->slug,
    ])->assertFailed();
});
