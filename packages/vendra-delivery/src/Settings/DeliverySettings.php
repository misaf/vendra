<?php

declare(strict_types=1);

namespace Misaf\VendraDelivery\Settings;

use Spatie\LaravelSettings\Settings;

final class DeliverySettings extends Settings
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
