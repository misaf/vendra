<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Tenancy;

use Illuminate\Validation\Rules\Unique;
use Misaf\VendraSupport\Contracts\TenantResolver;

final class TenantAwareness
{
    /**
     * Tenant awareness is derived from the bound tenant resolver: installing a
     * tenant provider (e.g. misaf/vendra-tenant) binds a resolver that reports
     * itself available, while the default null resolver keeps it disabled.
     */
    public static function enabled(): bool
    {
        return resolve(TenantResolver::class)->available();
    }

    public static function currentId(): ?int
    {
        if (! self::enabled()) {
            return null;
        }

        return resolve(TenantResolver::class)->currentId();
    }

    public static function constrainUniqueRule(Unique $rule): Unique
    {
        $tenantId = self::currentId();

        if ($tenantId === null) {
            return $rule;
        }

        return $rule->where(TenantSchema::column(), $tenantId);
    }
}
