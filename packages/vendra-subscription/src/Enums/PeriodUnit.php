<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Enums;

use Carbon\CarbonInterface;
use Filament\Support\Contracts\HasLabel;

enum PeriodUnit: string implements HasLabel
{
    case Day = 'day';
    case Week = 'week';
    case Month = 'month';
    case Year = 'year';

    public function advance(CarbonInterface $date, int $count): CarbonInterface
    {
        return match ($this) {
            self::Day => $date->copy()->addDays($count),
            self::Week => $date->copy()->addWeeks($count),
            self::Month => $date->copy()->addMonths($count),
            self::Year => $date->copy()->addYears($count),
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Day => __('vendra-subscription::enums.period_unit_day'),
            self::Week => __('vendra-subscription::enums.period_unit_week'),
            self::Month => __('vendra-subscription::enums.period_unit_month'),
            self::Year => __('vendra-subscription::enums.period_unit_year'),
        };
    }
}
