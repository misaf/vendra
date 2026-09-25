<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * The per-store caps a plan can set, stored in `plans.limits`.
 */
enum PlanLimit: string implements HasLabel
{
    case DomainsPerStore = 'domains_per_store';
    case ProductsPerStore = 'products_per_store';
    case StorageMegabytesPerStore = 'storage_megabytes_per_store';
    case StaffPerStore = 'staff_per_store';

    /**
     * Get how many counted units one unit of the limit stands for.
     *
     * Storage is counted in bytes but sold in megabytes.
     */
    public function unitSize(): int
    {
        return match ($this) {
            self::StorageMegabytesPerStore => 1024 * 1024,
            default => 1,
        };
    }

    /**
     * Convert a counted usage into the limit's own unit, rounding up.
     */
    public function toUnits(int $usage): int
    {
        return (int) ceil($usage / $this->unitSize());
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::DomainsPerStore => __('vendra-support::entitlements.limit_domains_per_store'),
            self::ProductsPerStore => __('vendra-support::entitlements.limit_products_per_store'),
            self::StorageMegabytesPerStore => __('vendra-support::entitlements.limit_storage_megabytes_per_store'),
            self::StaffPerStore => __('vendra-support::entitlements.limit_staff_per_store'),
        };
    }
}
