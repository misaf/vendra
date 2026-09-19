<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Misaf\VendraSubscription\Models\SubscriptionPayment;

final readonly class SubscriptionPaymentPaid implements ShouldDispatchAfterCommit
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public SubscriptionPayment $payment) {}
}
