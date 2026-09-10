<?php

declare(strict_types=1);

namespace Misaf\VendraDelivery\Support;

use Illuminate\Support\Facades\Date;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;

/**
 * The dates a customer may still choose from.
 *
 * Same-day delivery closes at the configured cutoff hour — the studio buys at
 * the dawn market and ties the bouquet the morning it travels, so an order
 * placed after the cutoff starts from tomorrow.
 */
final class DeliverySchedule
{
    /**
     * @return list<string>
     */
    public function bookableDates(?Carbon $from = null): array
    {
        $now = $from?->copy() ?? Date::now();
        $advanceDays = max(1, Config::integer('vendra-delivery.schedule.advance_days', 14));
        $cutoffHour = Config::integer('vendra-delivery.schedule.same_day_cutoff_hour', 14);

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
