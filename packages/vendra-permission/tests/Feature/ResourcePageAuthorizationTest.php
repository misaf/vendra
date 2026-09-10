<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Illuminate\Support\Str;
use Misaf\VendraPermission\Database\Factories\PermissionFactory;
use Misaf\VendraPermission\Database\Factories\RoleFactory;
use Misaf\VendraPermission\Filament\Clusters\Resources\Permissions\Pages\CreatePermission;
use Misaf\VendraPermission\Filament\Clusters\Resources\Permissions\Pages\EditPermission;
use Misaf\VendraPermission\Filament\Clusters\Resources\Permissions\Pages\ViewPermission;
use Misaf\VendraPermission\Filament\Clusters\Resources\Roles\Pages\CreateRole;
use Misaf\VendraPermission\Filament\Clusters\Resources\Roles\Pages\EditRole;
use Misaf\VendraPermission\Filament\Clusters\Resources\Roles\Pages\ViewRole;
use Misaf\VendraPermission\Tests\Support\PermissionModuleTestContext;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    PermissionModuleTestContext::setUpFilamentAdminContext();
});

it('renders the create permission page under strict authorization', function (): void {
    Filament::getPanel('admin')->strictAuthorization();

    livewire(CreatePermission::class)
        ->assertOk();
});

it('renders the edit permission page under strict authorization', function (): void {
    Filament::getPanel('admin')->strictAuthorization();

    $permission = PermissionFactory::new()->createOne();

    livewire(EditPermission::class, ['record' => $permission->getKey()])
        ->assertOk();
});

it('renders the view permission page under strict authorization', function (): void {
    Filament::getPanel('admin')->strictAuthorization();

    $permission = PermissionFactory::new()->createOne();

    livewire(ViewPermission::class, ['record' => $permission->getKey()])
        ->assertOk();
});

it('renders the create role page under strict authorization', function (): void {
    Filament::getPanel('admin')->strictAuthorization();

    livewire(CreateRole::class)
        ->assertOk();
});

it('renders the edit role page under strict authorization', function (): void {
    Filament::getPanel('admin')->strictAuthorization();

    $role = RoleFactory::new()->createOne();

    livewire(EditRole::class, ['record' => $role->getKey()])
        ->assertOk();
});

it('renders the view role page under strict authorization', function (): void {
    Filament::getPanel('admin')->strictAuthorization();

    $role = RoleFactory::new()->createOne();

    livewire(ViewRole::class, ['record' => $role->getKey()])
        ->assertOk();
});

it('limits permission and role descriptions to their database length', function (): void {
    $description = Str::repeat('a', 256);

    livewire(CreatePermission::class)
        ->fillForm([
            'name' => 'long-description-permission',
            'guard_name' => 'web',
            'description' => $description,
        ])
        ->call('create')
        ->assertHasFormErrors(['description' => 'max']);

    livewire(CreateRole::class)
        ->fillForm([
            'name' => 'long-description-role',
            'guard_name' => 'web',
            'description' => $description,
        ])
        ->call('create')
        ->assertHasFormErrors(['description' => 'max']);
});
