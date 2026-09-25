<?php

declare(strict_types=1);

namespace Misaf\VendraDelivery\Settings;

use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Spatie\LaravelSettings\Settings;

final class DeliverySettings extends Settings implements ShouldLogActivity
{
    public int $advance_days;

    /**
     * The hour after which today can no longer be booked.
     */
    public int $same_day_cutoff_hour;

    public static function group(): string
    {
        return 'delivery';
    }
}
