<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Filament\Widgets;

use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Number;
use Misaf\VendraAffiliate\Enums\CommissionStatusEnum;
use Misaf\VendraAffiliate\Models\AffiliateClick;
use Misaf\VendraAffiliate\Models\AffiliateCommission;
use Misaf\VendraAffiliate\Models\AffiliateReferral;

abstract class AffiliateStatsOverviewWidget extends StatsOverviewWidget
{
    protected bool $isAffiliateScoped = false;

    protected ?string $pollingInterval = null;

    /**
     * @return array<int, Stat>
     */
    protected function getStats(): array
    {
        $startDate = now()->startOfWeek();
        $endDate = now()->endOfWeek();

        $clicks = $this->scopeToAffiliate(AffiliateClick::query());
        $clickTrend = Trend::query(clone $clicks)
            ->between($startDate, $endDate)
            ->perDay()
            ->count();

        $referrals = $this->scopeToAffiliate(AffiliateReferral::query());
        $referralTrend = Trend::query(clone $referrals)
            ->between($startDate, $endDate)
            ->perDay()
            ->count();

        $earnedCommissions = $this->scopeToAffiliate(AffiliateCommission::query())
            ->whereIn('status', [
                CommissionStatusEnum::Approved,
                CommissionStatusEnum::Paid,
            ]);

        $commissionTrend = Trend::query(clone $earnedCommissions)
            ->between($startDate, $endDate)
            ->perDay()
            ->sum('amount');

        return [
            Stat::make('affiliate_click_stats', Number::format($clicks->count()))
                ->chart($this->chartValues($clickTrend))
                ->color('primary')
                ->description(__('vendra-affiliate::widgets.affiliate_click_stats_description'))
                ->descriptionIcon(Heroicon::CursorArrowRays, IconPosition::Before)
                ->icon(Heroicon::OutlinedLink)
                ->label(__('vendra-affiliate::widgets.affiliate_click_stats')),
            Stat::make('affiliate_referral_stats', Number::format($referrals->count()))
                ->chart($this->chartValues($referralTrend))
                ->color('success')
                ->description(__('vendra-affiliate::widgets.affiliate_referral_stats_description'))
                ->descriptionIcon(Heroicon::UserPlus, IconPosition::Before)
                ->icon(Heroicon::OutlinedLink)
                ->label(__('vendra-affiliate::widgets.affiliate_referral_stats')),
            Stat::make('affiliate_commission_stats', Number::format((int) $earnedCommissions->sum('amount')))
                ->chart($this->chartValues($commissionTrend))
                ->color('danger')
                ->description(__('vendra-affiliate::widgets.affiliate_commission_stats_description'))
                ->descriptionIcon(Heroicon::Banknotes, IconPosition::Before)
                ->icon(Heroicon::OutlinedLink)
                ->label(__('vendra-affiliate::widgets.affiliate_commission_stats')),
        ];
    }

    protected function getAffiliateId(): ?int
    {
        return null;
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    private function scopeToAffiliate(Builder $query): Builder
    {
        if (! $this->isAffiliateScoped) {
            return $query;
        }

        return $query->where('affiliate_id', $this->getAffiliateId() ?? 0);
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
