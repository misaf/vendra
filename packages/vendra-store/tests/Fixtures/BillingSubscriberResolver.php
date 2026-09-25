<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Tests\Fixtures;

use Misaf\VendraStore\Contracts\StoreResellerResolver;

final class BillingSubscriberResolver implements StoreResellerResolver
{
    public function find(int|string $resellerId): ?BillingSubscriber
    {
        return BillingSubscriber::query()->find($resellerId);
    }
}
