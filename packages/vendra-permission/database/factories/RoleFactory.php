<?php

declare(strict_types=1);

namespace Misaf\VendraPermission\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Misaf\VendraPermission\Models\Role;
use Misaf\VendraSupport\Tenancy\TenantAwareness;
use RuntimeException;

/**
 * @extends Factory<Role>
 */
#[UseModel(Role::class)]
final class RoleFactory extends Factory
{
    public function definition(): array
    {
        $guardNames = $this->configuredGuardNames();

        return [
            'name' => fake()->sentences(1, true),
            'description' => fake()->realTextBetween(100, 200),
            'guard_name' => Arr::random($guardNames),
        ];
    }

    /**
     * No-op without a tenant provider, since there is no `tenant_id` column.
     */
    public function forTenant(Model|int $tenant): static
    {
        if (! TenantAwareness::enabled()) {
            return $this;
        }

        return $this->state(fn (): array => [
            'tenant_id' => $tenant instanceof Model ? $tenant->getKey() : $tenant,
        ]);
    }

    public function forGuard(string $guardName): static
    {
        $configuredGuardNames = $this->configuredGuardNames();

        throw_unless(in_array($guardName, $configuredGuardNames, true), RuntimeException::class, "The guard [{$guardName}] is not configured in auth.guards.");

        return $this->state(fn (): array => ['guard_name' => $guardName]);
    }

    /**
     * @return list<string>
     */
    private function configuredGuardNames(): array
    {
        $guardNames = array_keys(Config::array('auth.guards'));

        throw_if($guardNames === [], RuntimeException::class, 'No guards are configured in auth.guards.');

        return $guardNames;
    }
}
