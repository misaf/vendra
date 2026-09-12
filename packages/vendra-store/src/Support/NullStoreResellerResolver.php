<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Support;

use Misaf\VendraStore\Contracts\StoreResellerResolver;
use Misaf\VendraSubscription\Contracts\SubscriptionSubscriber;

/**
 * The default reseller resolver: with no reseller domain installed, a store
 * cannot have a billing reseller, so nothing resolves. Callers treat an
 * unresolvable reseller as "not paying", which fails closed rather than
 * granting free access.
 */
final class NullStoreResellerResolver implements StoreResellerResolver
{
    public function find(int|string $resellerId): ?SubscriptionSubscriber
    {
        return null;
    }
}
