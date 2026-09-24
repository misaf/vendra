<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Filament\Concerns;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Date;

trait BuildsDailyTrend
{
    /**
     * Get per-day record counts over the trailing window, oldest day first.
     *
     * Bucketed in PHP because date truncation has no portable SQL expression.
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
            $day = match (true) {
                $timestamp instanceof CarbonInterface => $timestamp->toDateString(),
                is_string($timestamp) => Date::parse($timestamp)->toDateString(),
                default => null,
            };

            if ($day !== null && array_key_exists($day, $counts)) {
                $counts[$day]++;
            }
        }

        return array_values($counts);
    }
}
