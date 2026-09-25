<?php

declare(strict_types=1);

namespace Misaf\VendraDelivery\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Misaf\VendraDelivery\Settings\DeliverySettings;

/**
 * After the store's cutoff hour, the earliest date is tomorrow.
 */
final class DeliverySchedule
{
    /**
     * @return list<string>
     */
    public function bookableDates(?Carbon $from = null): array
    {
        $now = $from?->copy() ?? Date::now();
        $settings = resolve(DeliverySettings::class);
        $advanceDays = max(1, $settings->advance_days);
        $cutoffHour = $settings->same_day_cutoff_hour;

        $firstDate = $now->hour >= $cutoffHour
            ? $now->copy()->addDay()
            : $now->copy();

        $dates = [];

        for ($offset = 0; $offset < $advanceDays; $offset++) {
            $dates[] = $firstDate->copy()->addDays($offset)->toDateString();
        }

        return $dates;
    }

    public function isBookable(string $date, ?Carbon $from = null): bool
    {
        return in_array($date, $this->bookableDates($from), true);
    }
}
