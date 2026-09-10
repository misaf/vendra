<?php

declare(strict_types=1);

use Misaf\VendraPermission\Enums\PermissionPolicyEnum;
use Misaf\VendraPermission\Enums\RolePolicyEnum;
use Misaf\VendraPermission\Models\Permission;
use Misaf\VendraPermission\Models\Role;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;

it('applies shared tenant ownership to permission models', function (): void {
    expect(class_uses_recursive(Permission::class))->toContain(BelongsToTenant::class)
        ->and(class_uses_recursive(Role::class))->toContain(BelongsToTenant::class);
});

it('hides the tenant association from permission serialization', function (): void {
    expect((new Permission)->getHidden())->toContain('tenant_id')
        ->and((new Role)->getHidden())->toContain('tenant_id');
});

it('defines policy permissions for the permission resource', function (): void {
    $permissions = array_column(PermissionPolicyEnum::cases(), 'value');

    expect($permissions)->toHaveCount(10);
});

it('defines policy permissions for the role resource', function (): void {
    $permissions = array_column(RolePolicyEnum::cases(), 'value');

    expect($permissions)->toHaveCount(10);
});

it('uses kebab-case permission names scoped per model', function (): void {
    $permissionPermissions = array_column(PermissionPolicyEnum::cases(), 'value');
    $rolePermissions = array_column(RolePolicyEnum::cases(), 'value');

    expect($permissionPermissions)->toHaveCount(count(array_unique($permissionPermissions)))
        ->each->toMatch('/^[a-z]+(-[a-z]+)*$/');

    expect($rolePermissions)->toHaveCount(count(array_unique($rolePermissions)))
        ->each->toMatch('/^[a-z]+(-[a-z]+)*$/');
});
