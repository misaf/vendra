<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Contracts;

use Illuminate\Database\Eloquent\Model;
use Misaf\VendraStore\Support\NullStoreOwnerResolver;
use Misaf\VendraSubscription\Contracts\SubscriptionSubscriber;

/**
 * Resolves the billing owner behind a store's `reseller_id`.
 *
 * A store may be created directly by the platform console (no owner) or by a
 * reseller that owns several. The reseller domain is a layer *above* the store,
 * so the store package holds only the owner's key and asks through this port
 * when it needs the owner itself — that is what keeps `misaf/vendra-store`
 * installable, and testable, without `misaf/vendra-reseller`.
 *
 * `misaf/vendra-reseller` binds the Eloquent implementation; without it the
 * {@see NullStoreOwnerResolver} resolves nothing.
 */
interface StoreOwnerResolver
{
    /**
     * @return (Model&SubscriptionSubscriber)|null
     */
    public function find(int|string $ownerId): ?SubscriptionSubscriber;
}
