<?php

declare(strict_types=1);

namespace Misaf\VendraCart\Filament\Clusters\Resources\Carts\Widgets;

use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Number;
use Misaf\VendraCart\Models\Cart;

final class CartOverviewWidget extends StatsOverviewWidget
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
        $carts = Cart::query();
        $activeCarts = Cart::query()->where(function (Builder $query): void {
            $query
                ->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });

        $expiredCarts = Cart::query()->where('expires_at', '<=', now());
        $cartTrend = Trend::query(clone $carts)->between($startDate, $endDate)->perDay()->count();
        $activeCartTrend = Trend::query(clone $activeCarts)->between($startDate, $endDate)->perDay()->count();
        $expiredCartTrend = Trend::query(clone $expiredCarts)->between($startDate, $endDate)->perDay()->count();

        return [
            Stat::make('total_cart_stats', Number::format($carts->count()))
                ->chart($this->chartValues($cartTrend))
                ->color('primary')
                ->description(__('vendra-cart::widgets.total_carts_description'))
                ->descriptionIcon(Heroicon::OutlinedShoppingCart, IconPosition::Before)
                ->icon(Heroicon::OutlinedShoppingCart)
                ->label(__('vendra-cart::widgets.total_carts')),
            Stat::make('active_cart_stats', Number::format($activeCarts->count()))
                ->chart($this->chartValues($activeCartTrend))
                ->color('success')
                ->description(__('vendra-cart::widgets.active_carts_description'))
                ->descriptionIcon(Heroicon::OutlinedCheckCircle, IconPosition::Before)
                ->icon(Heroicon::OutlinedShoppingCart)
                ->label(__('vendra-cart::widgets.active_carts')),
            Stat::make('expired_cart_stats', Number::format($expiredCarts->count()))
                ->chart($this->chartValues($expiredCartTrend))
                ->color('danger')
                ->description(__('vendra-cart::widgets.expired_carts_description'))
                ->descriptionIcon(Heroicon::OutlinedClock, IconPosition::Before)
                ->icon(Heroicon::OutlinedShoppingCart)
                ->label(__('vendra-cart::widgets.expired_carts')),
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
