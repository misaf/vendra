<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Support;

use Misaf\VendraStore\Contracts\StoreResellerResolver;
use Misaf\VendraSubscription\Contracts\SubscriptionSubscriber;

/**
 * Callers treat an unresolved reseller as not paying, so this fails closed.
 */
final class NullStoreResellerResolver implements StoreResellerResolver
{
    public function find(int|string $resellerId): ?SubscriptionSubscriber
    {
        return null;
    }
}
