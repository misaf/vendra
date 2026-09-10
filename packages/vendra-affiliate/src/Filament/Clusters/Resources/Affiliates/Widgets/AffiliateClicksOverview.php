<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates\Widgets;

use Illuminate\Support\Facades\Date;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Collection;
use Illuminate\Support\Number;
use Misaf\VendraAffiliate\Models\AffiliateClick;

final class AffiliateClicksOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = ['sm' => 1];

    protected ?string $pollingInterval = null;

    protected function getColumns(): int
    {
        return 1;
    }

    protected function getStats(): array
    {
        $trend = Trend::model(AffiliateClick::class)
            ->between(Date::now()->startOfWeek(), Date::now()->endOfWeek())
            ->perDay()
            ->count();

        return [
            Stat::make('affiliate_click_stats', Number::format(AffiliateClick::query()->count()))
                ->chart($this->chartValues($trend))
                ->color('primary')
                ->description(__('vendra-affiliate::widgets.affiliate_click_stats_description'))
                ->descriptionIcon(Heroicon::CursorArrowRays, IconPosition::Before)
                ->label(__('vendra-affiliate::widgets.affiliate_click_stats')),
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
