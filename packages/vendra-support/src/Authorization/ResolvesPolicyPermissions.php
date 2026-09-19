<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Authorization;

use BackedEnum;
use Illuminate\Contracts\Auth\Access\Authorizable;

trait ResolvesPolicyPermissions
{
    /**
     * Get the policy's permission enum, with one case per ability such as `ViewAny`.
     *
     * @return class-string<BackedEnum>
     */
    abstract protected static function permissionEnum(): string;

    private function allowed(Authorizable $user, string $ability): bool
    {
        $permission = constant(static::permissionEnum().'::'.$ability);

        return $permission instanceof BackedEnum && $user->can((string) $permission->value);
    }
}
