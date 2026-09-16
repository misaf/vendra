<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Misaf\VendraStore\Filament\Concerns\BuildsDailyTrend;
use Misaf\VendraStore\Models\Store;

/*
 | The sparkline behind every overview stat. It used to run one count per day,
 | so a dashboard of three charts cost twenty-one queries; the window is now
 | read once and bucketed in PHP.
 */
function dailyTrendOf(Builder $query, string $column = 'created_at', int $days = 7): array
{
    $widget = new class
    {
        use BuildsDailyTrend;

        /** @param Builder<*> $query */
        public function trend(Builder $query, string $column, int $days): array
        {
            return $this->dailyTrend($query, $column, $days);
        }
    };

    return $widget->trend($query, $column, $days);
}

it('counts one bar per day, oldest first, ignoring records outside the window', function (): void {
    Store::factory()->create(['created_at' => now()->subDays(2)]);
    Store::factory()->count(2)->create(['created_at' => now()]);
    Store::factory()->create(['created_at' => now()->subDays(30)]);

    expect(dailyTrendOf(Store::query()))->toBe([0.0, 0.0, 0.0, 0.0, 1.0, 0.0, 2.0]);
});

it('reads the whole window in a single query', function (): void {
    Store::factory()->count(3)->create(['created_at' => now()]);

    DB::flushQueryLog();
    DB::enableQueryLog();

    dailyTrendOf(Store::query());

    expect(DB::getQueryLog())->toHaveCount(1);
});

it('honours a different timestamp column', function (): void {
    Store::factory()->create(['created_at' => now()->subDays(30), 'updated_at' => now()]);

    expect(dailyTrendOf(Store::query(), 'updated_at'))->toBe([0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 1.0]);
});
