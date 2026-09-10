<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Misaf\VendraSubscription\Models\SubscriptionPayment;

/**
 * A durable subscription payment reached the Failed terminal state. Consumers
 * react to it (e.g. notifying the subscriber) without the payment engine
 * knowing the concrete subscriber or its side effects.
 */
final readonly class SubscriptionPaymentFailed implements ShouldDispatchAfterCommit
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public SubscriptionPayment $payment) {}
}
