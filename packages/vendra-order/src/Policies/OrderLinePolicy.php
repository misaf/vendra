<?php

declare(strict_types=1);

namespace Misaf\VendraOrder\Policies;

use Illuminate\Contracts\Auth\Access\Authorizable;
use Misaf\VendraOrder\Enums\OrderLinePolicyEnum;
use Misaf\VendraOrder\Models\OrderLine;
use Misaf\VendraSupport\Authorization\AuthorizesSandboxMode;
use Misaf\VendraSupport\Authorization\AuthorizesViewAbilities;
use Misaf\VendraSupport\Authorization\ResolvesPolicyPermissions;

/**
 * Order lines are created with their order and never edited or removed.
 */
final class OrderLinePolicy
{
    use AuthorizesSandboxMode;
    use AuthorizesViewAbilities;
    use ResolvesPolicyPermissions;

    protected static function permissionEnum(): string
    {
        return OrderLinePolicyEnum::class;
    }

    public function create(Authorizable $user): bool
    {
        return false;
    }

    public function update(Authorizable $user, OrderLine $orderLine): bool
    {
        return false;
    }

    public function delete(Authorizable $user, OrderLine $orderLine): bool
    {
        return false;
    }
}
