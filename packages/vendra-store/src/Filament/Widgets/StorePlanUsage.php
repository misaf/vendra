<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Illuminate\Database\Eloquent\Model;
use LogicException;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraSupport\Filament\Widgets\PlanUsageWidget;

/**
 * Show a store record's usage against each plan limit on its view page.
 *
 * Pages list it only when {@see hasStats()} is true, since a store the console
 * owns directly, or one with no plan limits, has nothing to show.
 */
final class StorePlanUsage extends StatsOverviewWidget
{
    public ?Model $record = null;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = null;

    public static function hasStats(Store $store): bool
    {
        return PlanUsageWidget::statsFor($store) !== [];
    }

    protected function getHeading(): string
    {
        return __('vendra-support::entitlements.plan_usage');
    }

    protected function getDescription(): ?string
    {
        return PlanUsageWidget::descriptionFor($this->store());
    }

    protected function getStats(): array
    {
        return PlanUsageWidget::statsFor($this->store());
    }

    private function store(): Store
    {
        throw_unless($this->record instanceof Store, LogicException::class, 'The plan usage widget requires a Store record.');

        return $this->record;
    }
}
