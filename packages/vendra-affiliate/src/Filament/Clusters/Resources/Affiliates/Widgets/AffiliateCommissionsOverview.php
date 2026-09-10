<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates\Widgets;

use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Number;
use Misaf\VendraAffiliate\Enums\CommissionStatusEnum;
use Misaf\VendraAffiliate\Models\AffiliateCommission;

final class AffiliateCommissionsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = ['sm' => 1];

    protected ?string $pollingInterval = null;

    protected function getColumns(): int
    {
        return 1;
    }

    protected function getStats(): array
    {
        $earnedTotal = (int) AffiliateCommission::query()->whereIn('status', [
            CommissionStatusEnum::Approved,
            CommissionStatusEnum::Paid,
        ])->sum('amount');

        $trend = Trend::query(
            AffiliateCommission::query()->whereIn('status', [
                CommissionStatusEnum::Approved,
                CommissionStatusEnum::Paid,
            ]),
        )
            ->between(Date::now()->startOfWeek(), Date::now()->endOfWeek())
            ->perDay()
            ->sum('amount');

        return [
            Stat::make('affiliate_commission_stats', Number::format($earnedTotal))
                ->chart($this->chartValues($trend))
                ->color('warning')
                ->description(__('vendra-affiliate::widgets.affiliate_commission_stats_description'))
                ->descriptionIcon(Heroicon::Banknotes, IconPosition::Before)
                ->label(__('vendra-affiliate::widgets.affiliate_commission_stats')),
        ];
    }

    /**
     * @param  Collection<(int|string), mixed>  $values
     * @return list<float>
     */
    private function chartValues(Collection $values): array
    {
        $chart = [];

        foreach ($values as $value) {
            if ($value instanceof TrendValue && is_numeric($value->aggregate)) {
                $chart[] = (float) $value->aggregate;
            }
        }

        return $chart;
    }
}
