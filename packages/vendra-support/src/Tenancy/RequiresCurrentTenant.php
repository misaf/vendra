<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Tenancy;

use Illuminate\Database\Eloquent\Model;
use Misaf\VendraSupport\Contracts\TenantResolver;
use RuntimeException;

trait RequiresCurrentTenant
{
    protected function currentTenant(): Model
    {
        $tenant = resolve(TenantResolver::class)->current();

        if (! $tenant instanceof Model) {
            throw new RuntimeException(sprintf(
                '%s seeding requires a current tenant.',
                $this->tenantModuleName(),
            ));
        }

        return $tenant;
    }

    /**
     * Get the current tenant, or null when tenancy is disabled.
     *
     * With tenancy enabled a missing tenant throws, rather than seeding unscoped records.
     */
    protected function currentTenantOrNull(): ?Model
    {
        if (! TenantAwareness::enabled()) {
            return null;
        }

        return $this->currentTenant();
    }

    private function tenantModuleName(): string
    {
        $constant = static::class.'::MODULE_NAME';

        if (! defined($constant)) {
            return static::class;
        }

        $moduleName = constant($constant);

        if (! is_string($moduleName)) {
            return static::class;
        }

        return $moduleName;
    }
}
