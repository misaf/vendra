<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Misaf\VendraPermission\Enums\PermissionFeatureEnum;
use Misaf\VendraPermission\Filament\Clusters\Resources\Permissions\Pages\CreatePermission;
use Misaf\VendraPermission\Filament\Clusters\Resources\Permissions\Pages\EditPermission;
use Misaf\VendraPermission\Filament\Clusters\Resources\Permissions\Pages\ListPermissions;
use Misaf\VendraPermission\Filament\Clusters\Resources\Permissions\Pages\ViewPermission;
use Misaf\VendraPermission\Filament\Clusters\Resources\Permissions\PermissionResource;
use Misaf\VendraPermission\Tests\Support\PermissionModuleTestContext;

it('allows permission resource access when module and permission management features are active', function (): void {
    PermissionModuleTestContext::createCurrentTenant([
        PermissionFeatureEnum::ModuleEnabled->value,
        PermissionFeatureEnum::PermissionManagement->value,
    ]);

    expect(PermissionResource::canAccess())->toBeTrue();
});

it('denies permission resource access when permission management feature is inactive', function (): void {
    PermissionModuleTestContext::createCurrentTenant([
        PermissionFeatureEnum::ModuleEnabled->value,
    ]);

    expect(PermissionResource::canAccess())->toBeFalse();
});

it('wires permission resource pages correctly', function (): void {
    $pages = PermissionResource::getPages();

    expect($pages)->toHaveKeys([
        'index',
        'create',
        'view',
        'edit',
    ])
        ->and(Arr::get($pages, 'index')->getPage())->toBe(ListPermissions::class)
        ->and(Arr::get($pages, 'create')->getPage())->toBe(CreatePermission::class)
        ->and(Arr::get($pages, 'view')->getPage())->toBe(ViewPermission::class)
        ->and(Arr::get($pages, 'edit')->getPage())->toBe(EditPermission::class);
});
