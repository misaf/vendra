<?php

declare(strict_types=1);

namespace Misaf\VendraPermission\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Misaf\VendraPermission\Models\Role;

final class CreateRoleAction
{
    /**
     * A null tenant creates a global role.
     */
    public function execute(
        ?Model $tenant,
        string $name,
        ?string $description = null,
        ?string $guardName = null,
    ): Role {
        $guardName = blank($guardName)
            ? Config::string('auth.defaults.guard')
            : $guardName;

        $create = static fn (): Role => Role::create([
            'name' => $name,
            'description' => $description,
            'guard_name' => $guardName,
        ]);

        if ($tenant instanceof Model && method_exists($tenant, 'execute')) {
            /** @var Role $role */
            $role = $tenant->execute($create);

            return $role;
        }

        return $create();
    }
}
