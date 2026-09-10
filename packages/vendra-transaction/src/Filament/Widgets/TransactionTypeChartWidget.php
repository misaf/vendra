<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Filament\Widgets;

use Illuminate\Support\Facades\Date;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Misaf\VendraTransaction\Enums\TransactionTypeEnum;
use Misaf\VendraTransaction\Models\Transaction;

final class TransactionTypeChartWidget extends ChartWidget
{
    protected ?string $pollingInterval = null;

    public static function isDiscovered(): bool
    {
        return false;
    }

    public function getHeading(): string
    {
        return __('vendra-transaction::widgets.transaction_type_chart');
    }

    protected function getData(): array
    {
        $startDate = now()->subDays(6)->startOfDay();
        $endDate = now()->endOfDay();

        $depositTrend = $this->getTrend(TransactionTypeEnum::Deposit, $startDate, $endDate);
        $withdrawalTrend = $this->getTrend(TransactionTypeEnum::Withdrawal, $startDate, $endDate);
        $bonusTrend = $this->getTrend(TransactionTypeEnum::Bonus, $startDate, $endDate);

        return [
            'datasets' => [
                [
                    'label' => TransactionTypeEnum::Deposit->getLabel(),
                    'data' => $this->getTrendData($depositTrend),
                    'backgroundColor' => '#22c55e',
                ],
                [
                    'label' => TransactionTypeEnum::Withdrawal->getLabel(),
                    'data' => $this->getTrendData($withdrawalTrend),
                    'backgroundColor' => '#ef4444',
                ],
                [
                    'label' => TransactionTypeEnum::Bonus->getLabel(),
                    'data' => $this->getTrendData($bonusTrend),
                    'backgroundColor' => '#a855f7',
                ],
            ],
            'labels' => $depositTrend
                ->map(static fn (TrendValue $value): string => Date::parse($value->date)->translatedFormat('D'))
                ->all(),
        ];
    }

    /**
     * @return Collection<int, TrendValue>
     */
    private function getTrend(TransactionTypeEnum $transactionType, Carbon $startDate, Carbon $endDate): Collection
    {
        return Trend::query(Transaction::query()
            ->approved()
            ->where('transaction_type', $transactionType))
            ->between($startDate, $endDate)
            ->perDay()
            ->sum('amount');
    }

    /**
     * @param  Collection<int, TrendValue>  $trend
     * @return list<int>
     */
    private function getTrendData(Collection $trend): array
    {
        return array_values($trend
            ->map(static fn (TrendValue $value): int => is_numeric($value->aggregate) ? (int) $value->aggregate : 0)
            ->all());
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
