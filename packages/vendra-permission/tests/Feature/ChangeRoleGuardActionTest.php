<?php

declare(strict_types=1);

use Misaf\VendraPermission\Actions\ChangeRoleGuardAction;
use Misaf\VendraPermission\Filament\Clusters\Resources\Roles\Pages\EditRole;
use Misaf\VendraPermission\Models\Permission;
use Misaf\VendraPermission\Models\Role;
use Misaf\VendraPermission\Tests\Support\PermissionModuleTestContext;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    switchToTestTenant(createTestTenant());
});

it('leaves permissions shared with other roles on their own guard', function (): void {
    $permission = Permission::create(['name' => 'view-any-report', 'guard_name' => 'web']);
    $movedRole = Role::create(['name' => 'editor', 'guard_name' => 'web']);
    $otherRole = Role::create(['name' => 'viewer', 'guard_name' => 'web']);
    $movedRole->givePermissionTo($permission);
    $otherRole->givePermissionTo($permission);

    resolve(ChangeRoleGuardAction::class)->execute($movedRole, 'sanctum');

    expect($permission->fresh()->guard_name)->toBe('web')
        ->and($otherRole->fresh()->permissions->pluck('id')->all())->toBe([$permission->id]);
});

it('swaps each permission for its namesake under the new guard and drops the rest', function (): void {
    $webReport = Permission::create(['name' => 'view-any-report', 'guard_name' => 'web']);
    $webExport = Permission::create(['name' => 'export-report', 'guard_name' => 'web']);
    $sanctumReport = Permission::create(['name' => 'view-any-report', 'guard_name' => 'sanctum']);
    $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);
    $role->givePermissionTo($webReport, $webExport);

    $role = resolve(ChangeRoleGuardAction::class)->execute($role, 'sanctum');

    expect($role->guard_name)->toBe('sanctum')
        ->and($role->permissions()->pluck('id')->all())->toBe([$sanctumReport->id]);
});

it('changes the guard from the edit page without touching shared permissions', function (): void {
    PermissionModuleTestContext::setUpFilamentAdminContext();

    $permission = Permission::create(['name' => 'view-any-report', 'guard_name' => 'web']);
    $movedRole = Role::create(['name' => 'editor', 'guard_name' => 'web']);
    $otherRole = Role::create(['name' => 'viewer', 'guard_name' => 'web']);
    $movedRole->givePermissionTo($permission);
    $otherRole->givePermissionTo($permission);

    livewire(EditRole::class, ['record' => $movedRole->getKey()])
        ->fillForm(['guard_name' => 'sanctum'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($movedRole->fresh()->guard_name)->toBe('sanctum')
        ->and($movedRole->fresh()->permissions)->toBeEmpty()
        ->and($permission->fresh()->guard_name)->toBe('web');
});
