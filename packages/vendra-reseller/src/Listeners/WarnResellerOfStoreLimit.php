<?php

declare(strict_types=1);

namespace Misaf\VendraReseller\Listeners;

use Misaf\VendraReseller\Models\Reseller;
use Misaf\VendraReseller\Notifications\StoreLimitApproachedNotification;
use Misaf\VendraStore\Events\StoreLimitApproached;

final class WarnResellerOfStoreLimit
{
    public function handle(StoreLimitApproached $event): void
    {
        $reseller = Reseller::query()->find($event->store->reseller_id);

        $reseller?->notifyContact(new StoreLimitApproachedNotification(
            $event->store->name,
            $event->limit->getLabel(),
            $event->percent,
        ));
    }
}
