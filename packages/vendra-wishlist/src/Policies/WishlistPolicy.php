<?php

declare(strict_types=1);

namespace Misaf\VendraWishlist\Policies;

use Illuminate\Contracts\Auth\Access\Authorizable;
use Misaf\VendraSupport\Authorization\AuthorizesDeleteAbilities;
use Misaf\VendraSupport\Authorization\AuthorizesSandboxMode;
use Misaf\VendraSupport\Authorization\AuthorizesViewAbilities;
use Misaf\VendraSupport\Authorization\ResolvesPolicyPermissions;
use Misaf\VendraWishlist\Enums\WishlistPolicyEnum;
use Misaf\VendraWishlist\Models\Wishlist;

/**
 * Administrators may view and delete wishlists, but never write to them.
 */
final class WishlistPolicy
{
    use AuthorizesDeleteAbilities;
    use AuthorizesSandboxMode;
    use AuthorizesViewAbilities;
    use ResolvesPolicyPermissions;

    protected static function permissionEnum(): string
    {
        return WishlistPolicyEnum::class;
    }

    public function create(Authorizable $user): bool
    {
        return false;
    }

    public function update(Authorizable $user, Wishlist $wishlist): bool
    {
        return false;
    }
}
