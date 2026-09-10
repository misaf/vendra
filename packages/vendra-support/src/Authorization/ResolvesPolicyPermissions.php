<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Authorization;

use BackedEnum;
use Illuminate\Contracts\Auth\Access\Authorizable;

trait ResolvesPolicyPermissions
{
    /**
     * The backed enum holding this policy's permission values, with one
     * TitleCase case per ability (e.g. ViewAny, ForceDelete).
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
