<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Misaf\VendraSubscription\Models\SubscriptionPayment;

/**
 * A durable subscription payment reached the Paid terminal state. Consumers
 * react to it (e.g. activating the subscriber's access) without the payment
 * engine knowing the concrete subscriber or its side effects.
 */
final readonly class SubscriptionPaymentPaid implements ShouldDispatchAfterCommit
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public SubscriptionPayment $payment) {}
}
