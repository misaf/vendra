<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Listeners;

use Misaf\VendraSubscription\Actions\ActivateSubscriptionAction;
use Misaf\VendraSubscription\Events\SubscriptionPaymentPaid;

final readonly class ActivateSubscriptionOnPayment
{
    public function __construct(private ActivateSubscriptionAction $activateSubscriptionAction) {}

    public function handle(SubscriptionPaymentPaid $event): void
    {
        $this->activateSubscriptionAction->execute($event->payment);
    }
}
