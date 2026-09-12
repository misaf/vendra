<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Contracts;

use Illuminate\Database\Eloquent\Model;
use Misaf\VendraStore\Support\NullStoreResellerResolver;
use Misaf\VendraSubscription\Contracts\SubscriptionSubscriber;

/**
 * Resolves the billing reseller behind a store's `reseller_id`.
 *
 * A store may be created directly by the platform console (no reseller) or by a
 * reseller that runs several. The reseller domain is a layer *above* the store,
 * so the store package holds only the reseller's key and asks through this port
 * when it needs the reseller itself — that is what keeps `misaf/vendra-store`
 * installable, and testable, without `misaf/vendra-reseller`.
 *
 * `misaf/vendra-reseller` binds the Eloquent implementation; without it the
 * {@see NullStoreResellerResolver} resolves nothing.
 */
interface StoreResellerResolver
{
    /**
     * @return (Model&SubscriptionSubscriber)|null
     */
    public function find(int|string $resellerId): ?SubscriptionSubscriber;
}
