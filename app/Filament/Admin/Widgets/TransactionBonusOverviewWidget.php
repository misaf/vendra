<?php

declare(strict_types=1);

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Number;
use Misaf\VendraTransaction\Models\Transaction;

final class TransactionBonusOverviewWidget extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = [
        'sm' => 1,
    ];

    protected function getColumns(): int
    {
        return 1;
    }

    public static function isDiscovered(): bool
    {
        return true;
    }

    public static function canView(): bool
    {
        return true;
    }

    /**
     * @return array<int, Stat>
     */
    protected function getStats(): array
    {
        $startDate = now()->subDays(6)->startOfDay();
        $endDate = now()->endOfDay();

        $today = now()->toDateString();

        $bonusTrend = Trend::query(Transaction::query()
            ->bonus()
            ->approved())
            ->between($startDate, $endDate)
            ->perDay()
            ->sum('amount');

        $todayTotal = (int) Transaction::query()
            ->bonus()
            ->approved()
            ->whereDate('created_at', $today)
            ->sum('amount');

        return [
            Stat::make('bonus_transaction_stats', Number::format($todayTotal))
                ->label(__('transaction::widgets.bonus_transaction_stats'))
                ->description(__('transaction::widgets.bonus_transaction_stats_description'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart(
                    $bonusTrend->map(fn(TrendValue $value) => (int) $value->aggregate)->toArray(),
                )
                ->color('primary'),
        ];
    }
}
