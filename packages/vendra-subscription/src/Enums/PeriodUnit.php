<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Enums;

use Carbon\CarbonInterface;

enum PeriodUnit: string
{
    case Day = 'day';
    case Week = 'week';
    case Month = 'month';
    case Year = 'year';

    /**
     * Advance the given date by the supplied number of this unit.
     */
    public function advance(CarbonInterface $date, int $count): CarbonInterface
    {
        return match ($this) {
            self::Day => $date->copy()->addDays($count),
            self::Week => $date->copy()->addWeeks($count),
            self::Month => $date->copy()->addMonths($count),
            self::Year => $date->copy()->addYears($count),
        };
    }
}
