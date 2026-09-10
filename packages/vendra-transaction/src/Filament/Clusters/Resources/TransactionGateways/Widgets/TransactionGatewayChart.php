<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Filament\Clusters\Resources\TransactionGateways\Widgets;

use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Misaf\VendraTransaction\Models\Transaction;

final class TransactionGatewayChart extends ChartWidget
{
    /**
     * @var int|string|array<string, int|null>
     */
    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = null;

    public function getHeading(): string
    {
        return __('vendra-transaction::messages.weekly_chart');
    }

    protected function getData(): array
    {
        $trend = Trend::query(Transaction::query())
            ->between(now()->startOfWeek(6), now()->endOfWeek())
            ->perDay()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => __('vendra-transaction::messages.weekly_chart'),
                    'data' => $trend
                        ->map(static fn (TrendValue $value): int => is_numeric($value->aggregate) ? (int) $value->aggregate : 0)
                        ->values()
                        ->all(),
                ],
            ],
            'labels' => $this->getWeekdayLabels(),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function getWeekdayLabels(): array
    {
        return [
            __('vendra-transaction::messages.weekday_saturday'),
            __('vendra-transaction::messages.weekday_sunday'),
            __('vendra-transaction::messages.weekday_monday'),
            __('vendra-transaction::messages.weekday_tuesday'),
            __('vendra-transaction::messages.weekday_wednesday'),
            __('vendra-transaction::messages.weekday_thursday'),
            __('vendra-transaction::messages.weekday_friday'),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
