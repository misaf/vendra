<?php

declare(strict_types=1);

namespace Misaf\VendraPermission\Actions;

use Illuminate\Database\Eloquent\Model;
use Misaf\VendraPermission\Models\Permission;

final class CreatePermissionAction
{
    /**
     * When a tenant is supplied its scoped `execute()` context is used so the
     * permission is created for that tenant; without one (tenant-agnostic
     * install) the permission is created globally.
     */
    public function execute(
        ?Model $tenant,
        string $name,
        ?string $description,
        string $guardName,
    ): Permission {
        $create = static fn (): Permission => Permission::create([
            'name' => $name,
            'description' => $description,
            'guard_name' => $guardName,
        ]);

        if ($tenant instanceof Model && method_exists($tenant, 'execute')) {
            /** @var Permission $permission */
            $permission = $tenant->execute($create);

            return $permission;
        }

        return $create();
    }
}
