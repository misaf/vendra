<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;
use Misaf\VendraSupport\Contracts\TenantEntitlements;
use Misaf\VendraSupport\Contracts\TenantResolver;
use Misaf\VendraSupport\Enums\PlanLimit;
use Misaf\VendraSupport\Tenancy\TenantLimitOverages;
use Misaf\VendraSupport\Tenancy\TenantUsageRegistry;

/**
 * Show the current store how much of each plan limit it uses.
 *
 * {@see statsFor()} and {@see descriptionFor()} serve widgets that show a
 * store other than the current one.
 */
final class PlanUsageWidget extends StatsOverviewWidget
{
    /**
     * The share of a limit, in percent, at which its usage shows a warning.
     */
    private const int NEARING_LIMIT_PERCENT = 80;

    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return self::stats() !== [];
    }

    protected function getHeading(): string
    {
        return __('vendra-support::entitlements.plan_usage');
    }

    protected function getDescription(): ?string
    {
        $tenant = resolve(TenantResolver::class)->current();

        return $tenant instanceof Model ? self::descriptionFor($tenant) : null;
    }

    protected function getStats(): array
    {
        return self::stats();
    }

    /**
     * Warn when the tenant is over any plan limit, or null while it fits.
     */
    public static function descriptionFor(Model $tenant): ?string
    {
        if (resolve(TenantLimitOverages::class)->exceeded($tenant) === []) {
            return null;
        }

        return __('vendra-support::entitlements.plan_exceeded');
    }

    /**
     * One stat per plan limit the tenant has a cap and a usage counter for.
     *
     * @return list<Stat>
     */
    public static function statsFor(Model $tenant): array
    {
        $entitlements = resolve(TenantEntitlements::class);
        $usageRegistry = resolve(TenantUsageRegistry::class);
        $stats = [];

        foreach (PlanLimit::cases() as $limit) {
            $allowed = $entitlements->limit($limit, $tenant);
            $used = $usageRegistry->usage($limit, $tenant);

            if ($allowed !== null && $used !== null) {
                $stats[] = self::stat($limit, $limit->toUnits($used), $allowed);
            }
        }

        return $stats;
    }

    /**
     * @return list<Stat>
     */
    private static function stats(): array
    {
        $tenant = resolve(TenantResolver::class)->current();

        return $tenant instanceof Model ? self::statsFor($tenant) : [];
    }

    private static function stat(PlanLimit $limit, int $used, int $allowed): Stat
    {
        return Stat::make($limit->getLabel(), $used.' / '.$allowed)
            ->description(match (true) {
                $used > $allowed => __('vendra-support::entitlements.usage_over_limit', ['count' => $used - $allowed]),
                $used >= $allowed => __('vendra-support::entitlements.usage_at_limit'),
                default => __('vendra-support::entitlements.usage_remaining', ['count' => $allowed - $used]),
            })
            ->color(match (true) {
                $used >= $allowed => 'danger',
                $used * 100 >= $allowed * self::NEARING_LIMIT_PERCENT => 'warning',
                default => 'primary',
            });
    }
}
