<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Context;

use Misaf\VendraSupport\Context\RequestJobContext;

/**
 * Subscription-owned observability context keys passed through
 * {@see RequestJobContext::$metadata}.
 */
final class SubscriptionContextKeys
{
    public const string SUBSCRIPTION_ID = 'subscription_id';

    public const string PAYMENT_ID = 'payment_id';
}
