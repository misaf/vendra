<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Contracts;

use Illuminate\Database\Eloquent\Model;
use Misaf\VendraStore\Support\NullStoreResellerResolver;
use Misaf\VendraSubscription\Contracts\SubscriptionSubscriber;

/**
 * Keeps the store package independent of `misaf/vendra-reseller`, which binds the
 * real implementation. Without it, {@see NullStoreResellerResolver} resolves nothing.
 */
interface StoreResellerResolver
{
    /**
     * @return (Model&SubscriptionSubscriber)|null
     */
    public function find(int|string $resellerId): ?SubscriptionSubscriber;
}
