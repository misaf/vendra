<?php

declare(strict_types=1);

namespace Misaf\VendraPermission\Actions;

use Illuminate\Support\Facades\DB;
use Misaf\VendraPermission\Models\Permission;
use Misaf\VendraPermission\Models\Role;

/**
 * Permissions are shared between roles, so they are never rewritten. Each one
 * is swapped for the permission of the same name under the new guard, and
 * dropped when that guard has none.
 */
final class ChangeRoleGuardAction
{
    public function execute(Role $role, string $guardName): Role
    {
        return DB::transaction(function () use ($role, $guardName): Role {
            $lockedRole = $role->refreshForUpdate();

            if ($lockedRole->guard_name === $guardName) {
                return $lockedRole;
            }

            $permissionNames = $lockedRole->permissions()->pluck('name');

            $lockedRole->forceFill(['guard_name' => $guardName])->save();

            $lockedRole->permissions()->sync(
                Permission::query()
                    ->where('guard_name', $guardName)
                    ->whereIn('name', $permissionNames)
                    ->pluck('id'),
            );

            $lockedRole->forgetCachedPermissions();

            return $lockedRole;
        });
    }
}
