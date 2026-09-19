<?php

declare(strict_types=1);

namespace Misaf\VendraDelivery\Policies;

use Illuminate\Contracts\Auth\Access\Authorizable;
use Misaf\VendraDelivery\Enums\DeliveryPolicyEnum;
use Misaf\VendraSupport\Authorization\AuthorizesDeleteAbilities;
use Misaf\VendraSupport\Authorization\AuthorizesSandboxMode;
use Misaf\VendraSupport\Authorization\AuthorizesUpdateAbilities;
use Misaf\VendraSupport\Authorization\AuthorizesViewAbilities;
use Misaf\VendraSupport\Authorization\ResolvesPolicyPermissions;

/**
 * Deliveries are scheduled by checkout and never created by hand.
 */
final class DeliveryPolicy
{
    use AuthorizesDeleteAbilities;
    use AuthorizesSandboxMode;
    use AuthorizesUpdateAbilities;
    use AuthorizesViewAbilities;
    use ResolvesPolicyPermissions;

    protected static function permissionEnum(): string
    {
        return DeliveryPolicyEnum::class;
    }

    public function create(Authorizable $user): bool
    {
        return false;
    }
}
