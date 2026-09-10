<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates\Widgets;

use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Number;
use Misaf\VendraAffiliate\Models\AffiliateReferral;

final class AffiliateReferralsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = ['sm' => 1];

    protected ?string $pollingInterval = null;

    protected function getColumns(): int
    {
        return 1;
    }

    protected function getStats(): array
    {
        $trend = Trend::model(AffiliateReferral::class)
            ->between(Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek())
            ->perDay()
            ->count();

        return [
            Stat::make('affiliate_referral_stats', Number::format(AffiliateReferral::count()))
                ->chart($this->chartValues($trend))
                ->color('success')
                ->description(__('vendra-affiliate::widgets.affiliate_referral_stats_description'))
                ->descriptionIcon(Heroicon::UserPlus, IconPosition::Before)
                ->label(__('vendra-affiliate::widgets.affiliate_referral_stats')),
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
