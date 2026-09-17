<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Support;

use Illuminate\Database\Eloquent\Builder;
use Misaf\VendraStore\Enums\StoreStatus;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraTenant\Enums\TenantProvisioningStatus;

/**
 * How many stores are in each {@see StoreStatus}, read in one grouped query.
 *
 * The query groups by the three columns a status is derived from and maps each
 * group through {@see StoreStatus::fromColumns()}, so the counts follow the same
 * rule as a store's own status instead of restating it in SQL.
 */
final readonly class StoreStatusCounts
{
    private const string BILLING_SUSPENDED = 'CASE WHEN billing_suspended_at IS NULL THEN 0 ELSE 1 END';

    /**
     * @param  array<string, int>  $counts  keyed by status value
     */
    private function __construct(private array $counts) {}

    /**
     * @param  Builder<Store>|null  $stores  narrow the stores counted, e.g. to one reseller
     */
    public static function for(?Builder $stores = null): self
    {
        $rows = ($stores ?? Store::query())
            ->reorder()
            ->selectRaw('provisioning_status, active, '.self::BILLING_SUSPENDED.' as billing_suspended, count(*) as aggregate')
            ->groupBy('provisioning_status', 'active')
            ->groupByRaw(self::BILLING_SUSPENDED)
            ->toBase()
            ->get();

        $counts = array_fill_keys(array_map(fn (StoreStatus $status): string => $status->value, StoreStatus::cases()), 0);

        foreach ($rows as $row) {
            $provisioningStatus = is_string($row->provisioning_status) ? TenantProvisioningStatus::tryFrom($row->provisioning_status) : null;

            if ($provisioningStatus === null || ! is_numeric($row->aggregate)) {
                continue;
            }

            $status = StoreStatus::fromColumns($provisioningStatus, (bool) $row->active, (bool) $row->billing_suspended);

            $counts[$status->value] += (int) $row->aggregate;
        }

        return new self($counts);
    }

    public function count(StoreStatus $status): int
    {
        return $this->counts[$status->value];
    }

    public function total(): int
    {
        return array_sum($this->counts);
    }

    /**
     * Stores still provisioning or whose provisioning failed.
     */
    public function needingAttention(): int
    {
        return $this->count(StoreStatus::Pending) + $this->count(StoreStatus::Provisioning) + $this->count(StoreStatus::Failed);
    }
}
