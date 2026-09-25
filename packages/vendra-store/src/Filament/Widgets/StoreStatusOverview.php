<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Misaf\VendraStore\Enums\StorefrontDeploymentStatus;
use Misaf\VendraStore\Enums\StoreStatus;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Support\StoreStatusCounts;

/**
 * Count a panel's stores by status above its store list.
 *
 * Panels supply the stores they may see with {@see stores()} and, optionally,
 * the list URL that filters by a status. Store counts change slowly, so the
 * widget does not poll.
 */
abstract class StoreStatusOverview extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    /**
     * @return Builder<Store>
     */
    abstract protected function stores(): Builder;

    protected function statusUrl(StoreStatus $status): ?string
    {
        return null;
    }

    protected function failedStorefrontsUrl(): ?string
    {
        return null;
    }

    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        $counts = StoreStatusCounts::for($this->stores());

        $stats = array_map(
            fn (StoreStatus $status): Stat => Stat::make($status->getLabel(), $counts->count($status))
                ->color($counts->count($status) > 0 ? $status->getColor() : 'gray')
                ->url($this->statusUrl($status)),
            StoreStatus::cases(),
        );

        $failedStorefronts = $this->stores()
            ->whereHas('storefrontDeployment', fn (Builder $deployment): Builder => $deployment->where('status', StorefrontDeploymentStatus::Failed))
            ->count();

        $stats[] = Stat::make(__('vendra-store::attributes.failed_storefronts'), $failedStorefronts)
            ->color($failedStorefronts > 0 ? 'danger' : 'gray')
            ->url($this->failedStorefrontsUrl());

        return $stats;
    }
}
