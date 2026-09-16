<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Filament\Concerns;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Date;

trait BuildsDailyTrend
{
    /**
     * Per-day record counts over the trailing window, oldest day first, for use
     * as a stat sparkline.
     *
     * The window is read once and bucketed in PHP rather than grouped in SQL,
     * because truncating a timestamp to its date has no portable expression
     * across the drivers this app runs on. One query beats a count per day, and
     * a trailing week of new records is small enough to bucket in memory.
     *
     * @param  Builder<*>  $query
     * @return list<float>
     */
    protected function dailyTrend(Builder $query, string $column = 'created_at', int $days = 7): array
    {
        $counts = [];

        for ($offset = $days - 1; $offset >= 0; $offset--) {
            $counts[now()->subDays($offset)->toDateString()] = 0.0;
        }

        $timestamps = (clone $query)
            ->reorder()
            ->whereBetween($column, [now()->subDays($days - 1)->startOfDay(), now()->endOfDay()])
            ->pluck($column);

        foreach ($timestamps as $timestamp) {
            if ($timestamp === null) {
                continue;
            }

            $day = $timestamp instanceof CarbonInterface
                ? $timestamp->toDateString()
                : Date::parse((string) $timestamp)->toDateString();

            if (array_key_exists($day, $counts)) {
                $counts[$day]++;
            }
        }

        return array_values($counts);
    }
}
