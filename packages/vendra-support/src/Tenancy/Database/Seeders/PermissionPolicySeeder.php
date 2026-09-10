<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Tenancy\Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Misaf\VendraSupport\Tenancy\RequiresCurrentTenant;
use Misaf\VendraSupport\Tenancy\TenantSchema;
use UnexpectedValueException;

abstract class PermissionPolicySeeder extends Seeder
{
    use RequiresCurrentTenant;

    public function run(): void
    {
        $tenantKey = $this->currentTenantOrNull()?->getKey();

        if ($tenantKey !== null && ! is_int($tenantKey) && ! is_string($tenantKey)) {
            throw new UnexpectedValueException('The current tenant key must be an integer or string.');
        }

        $this->seedPermissionPolicies($tenantKey);
    }

    /**
     * @return list<string>
     */
    abstract protected function policies(): array;

    protected function seedPermissionPolicies(int|string|null $tenantKey = null): void
    {
        /** @var class-string<Model> $permissionModel */
        $permissionModel = Config::string('permission.models.permission');

        $guardName = Config::string('auth.defaults.guard');

        foreach (array_unique($this->policies()) as $policy) {
            /** @var Model $permission */
            $permission = $permissionModel::query()->make();
            $permission->fill([
                'name' => $policy,
                'guard_name' => $guardName,
            ]);

            if ($tenantKey !== null) {
                $permission->setAttribute(TenantSchema::column(), $tenantKey);
            }

            $permission->save();
        }
    }
}
