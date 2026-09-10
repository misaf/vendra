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
     * The current tenant, or null when the application is not tenant-aware.
     *
     * When tenant awareness is enabled a current tenant is still required, so a
     * misconfigured tenant-aware seeding run fails loudly instead of silently
     * producing unscoped records.
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
