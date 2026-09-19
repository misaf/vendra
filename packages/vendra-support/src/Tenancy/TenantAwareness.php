<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Tenancy;

use Illuminate\Validation\Rules\Unique;
use Misaf\VendraSupport\Contracts\TenantResolver;

final class TenantAwareness
{
    /**
     * Determine if a tenant provider, such as `misaf/vendra-tenant`, is bound.
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
