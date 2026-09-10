<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Filament\Clusters\Resources\AffiliateCommissions\Widgets;

use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Collection;
use Illuminate\Support\Number;
use Misaf\VendraAffiliate\Enums\CommissionStatusEnum;
use Misaf\VendraAffiliate\Models\AffiliateCommission;

final class AffiliateCommissionOverviewWidget extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = null;

    /**
     * @return array<int, Stat>
     */
    protected function getStats(): array
    {
        $startDate = now()->startOfWeek();
        $endDate = now()->endOfWeek();
        $commissions = AffiliateCommission::query();
        $payableCommissions = AffiliateCommission::query()
            ->where('status', CommissionStatusEnum::Approved)
            ->whereNull('affiliate_payout_id');
        $paidCommissions = AffiliateCommission::query()->where('status', CommissionStatusEnum::Paid);
        $commissionTrend = Trend::query(clone $commissions)->between($startDate, $endDate)->perDay()->count();
        $payableTrend = Trend::query(clone $payableCommissions)->between($startDate, $endDate)->perDay()->count();
        $paidTrend = Trend::query(clone $paidCommissions)->between($startDate, $endDate)->perDay()->count();

        return [
            Stat::make('total_affiliate_commission_stats', Number::format($commissions->count()))
                ->chart($this->chartValues($commissionTrend))
                ->color('primary')
                ->description(__('vendra-affiliate::widgets.total_commissions_description'))
                ->descriptionIcon(Heroicon::OutlinedReceiptPercent, IconPosition::Before)
                ->icon(Heroicon::OutlinedLink)
                ->label(__('vendra-affiliate::widgets.total_commissions')),
            Stat::make('payable_affiliate_commission_stats', Number::format($payableCommissions->count()))
                ->chart($this->chartValues($payableTrend))
                ->color('warning')
                ->description(__('vendra-affiliate::widgets.payable_commissions_description'))
                ->descriptionIcon(Heroicon::OutlinedClock, IconPosition::Before)
                ->icon(Heroicon::OutlinedLink)
                ->label(__('vendra-affiliate::widgets.payable_commissions')),
            Stat::make('paid_affiliate_commission_stats', Number::format($paidCommissions->count()))
                ->chart($this->chartValues($paidTrend))
                ->color('success')
                ->description(__('vendra-affiliate::widgets.paid_commissions_description'))
                ->descriptionIcon(Heroicon::OutlinedBanknotes, IconPosition::Before)
                ->icon(Heroicon::OutlinedLink)
                ->label(__('vendra-affiliate::widgets.paid_commissions')),
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
