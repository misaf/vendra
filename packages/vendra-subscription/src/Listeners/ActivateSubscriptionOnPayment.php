<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Listeners;

use Misaf\VendraSubscription\Actions\ActivateSubscriptionAction;
use Misaf\VendraSubscription\Events\SubscriptionPaymentPaid;

/**
 * Activates the subscription of a paid payment. Keeping activation inside the
 * package closes the payment lifecycle (collect -> activate) without a consumer
 * having to wire it, while host-specific side effects stay in SubscriptionActivated consumers.
 */
final readonly class ActivateSubscriptionOnPayment
{
    public function __construct(private ActivateSubscriptionAction $activateSubscriptionAction) {}

    public function handle(SubscriptionPaymentPaid $event): void
    {
        $this->activateSubscriptionAction->execute($event->payment);
    }
}
