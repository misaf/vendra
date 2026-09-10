<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Filament\Clusters\Resources\AffiliatePayouts\Widgets;

use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Collection;
use Illuminate\Support\Number;
use Misaf\VendraAffiliate\Enums\PayoutStatusEnum;
use Misaf\VendraAffiliate\Models\AffiliatePayout;

final class AffiliatePayoutOverviewWidget extends StatsOverviewWidget
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
        $pendingPayouts = AffiliatePayout::query()->where('status', PayoutStatusEnum::Pending);
        $completedPayouts = AffiliatePayout::query()->where('status', PayoutStatusEnum::Completed);
        $failedPayouts = AffiliatePayout::query()->where('status', PayoutStatusEnum::Failed);
        $pendingTrend = Trend::query(clone $pendingPayouts)->between($startDate, $endDate)->perDay()->count();
        $completedTrend = Trend::query(clone $completedPayouts)->between($startDate, $endDate)->perDay()->count();
        $failedTrend = Trend::query(clone $failedPayouts)->between($startDate, $endDate)->perDay()->count();

        return [
            Stat::make('pending_affiliate_payout_stats', Number::format($pendingPayouts->count()))
                ->chart($this->chartValues($pendingTrend))
                ->color('warning')
                ->description(__('vendra-affiliate::widgets.pending_payouts_description'))
                ->descriptionIcon(Heroicon::OutlinedClock, IconPosition::Before)
                ->icon(Heroicon::OutlinedLink)
                ->label(__('vendra-affiliate::widgets.pending_payouts')),
            Stat::make('completed_affiliate_payout_stats', Number::format($completedPayouts->count()))
                ->chart($this->chartValues($completedTrend))
                ->color('success')
                ->description(__('vendra-affiliate::widgets.completed_payouts_description'))
                ->descriptionIcon(Heroicon::OutlinedCheckCircle, IconPosition::Before)
                ->icon(Heroicon::OutlinedLink)
                ->label(__('vendra-affiliate::widgets.completed_payouts')),
            Stat::make('failed_affiliate_payout_stats', Number::format($failedPayouts->count()))
                ->chart($this->chartValues($failedTrend))
                ->color('danger')
                ->description(__('vendra-affiliate::widgets.failed_payouts_description'))
                ->descriptionIcon(Heroicon::OutlinedExclamationCircle, IconPosition::Before)
                ->icon(Heroicon::OutlinedLink)
                ->label(__('vendra-affiliate::widgets.failed_payouts')),
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
