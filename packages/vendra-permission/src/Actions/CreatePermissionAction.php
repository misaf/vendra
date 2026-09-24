<?php

declare(strict_types=1);

namespace Misaf\VendraPermission\Actions;

use Illuminate\Database\Eloquent\Model;
use LogicException;
use Misaf\VendraPermission\Models\Permission;

final class CreatePermissionAction
{
    /**
     * A null tenant creates a global permission.
     */
    public function execute(
        ?Model $tenant,
        string $name,
        ?string $description,
        string $guardName,
    ): Permission {
        $create = static function () use ($name, $description, $guardName): Permission {
            $permission = Permission::create([
                'name' => $name,
                'description' => $description,
                'guard_name' => $guardName,
            ]);

            throw_unless($permission instanceof Permission, LogicException::class, 'The permission model must be '.Permission::class.'.');

            return $permission;
        };

        if ($tenant instanceof Model && method_exists($tenant, 'execute')) {
            /** @var Permission $permission */
            $permission = $tenant->execute($create);

            return $permission;
        }

        return $create();
    }
}
