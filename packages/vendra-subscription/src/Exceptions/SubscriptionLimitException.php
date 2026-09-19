<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Exceptions;

use Illuminate\Database\Eloquent\Model;
use Misaf\VendraSubscription\Contracts\SubscriptionSubscriber;
use RuntimeException;

final class SubscriptionLimitException extends RuntimeException
{
    /**
     * @param  Model&SubscriptionSubscriber  $subscriber
     */
    public static function subscriberInactive(SubscriptionSubscriber $subscriber): self
    {
        return new self(sprintf(
            'Subscriber [%s] is inactive.',
            self::formatKey($subscriber->getKey()),
        ));
    }

    /**
     * @param  Model&SubscriptionSubscriber  $subscriber
     */
    public static function noActiveSubscription(SubscriptionSubscriber $subscriber): self
    {
        return new self(sprintf(
            'Subscriber [%s] has no active subscription.',
            self::formatKey($subscriber->getKey()),
        ));
    }

    /**
     * @param  Model&SubscriptionSubscriber  $subscriber
     */
    public static function unitQuotaReached(SubscriptionSubscriber $subscriber, int $maxUnits): self
    {
        return new self(sprintf(
            'Subscriber [%s] has reached its unit limit of [%d].',
            self::formatKey($subscriber->getKey()),
            $maxUnits,
        ));
    }

    /**
     * @param  Model&SubscriptionSubscriber  $subscriber
     */
    public static function planBelowUsage(SubscriptionSubscriber $subscriber, int $maxUnits, int $currentUnits): self
    {
        return new self(sprintf(
            'Subscriber [%s] has %d unit(s), which exceeds the [%d] allowed by the selected plan.',
            self::formatKey($subscriber->getKey()),
            $currentUnits,
            $maxUnits,
        ));
    }

    private static function formatKey(mixed $key): string
    {
        return is_scalar($key) ? (string) $key : '';
    }
}
