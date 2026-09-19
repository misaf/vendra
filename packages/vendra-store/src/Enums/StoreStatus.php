<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Misaf\VendraTenant\Enums\TenantProvisioningStatus;

/**
 * `provisioning_status`, `active`, and `billing_suspended_at` each have their own
 * writer and stay the source of truth.
 */
enum StoreStatus: string implements HasColor, HasLabel
{
    /** Created, provisioning not started. */
    case Pending = 'pending';

    /** Provisioning is running. */
    case Provisioning = 'provisioning';

    /** Provisioned, enabled, and not suspended. */
    case Active = 'active';

    /** Provisioned, but disabled by an administrator or suspended by billing. */
    case Suspended = 'suspended';

    /** Provisioning gave up. */
    case Failed = 'failed';

    /**
     * Derive the status from the store's columns.
     *
     * Suspension outranks readiness but not unfinished or failed provisioning.
     */
    public static function fromColumns(TenantProvisioningStatus $provisioningStatus, bool $active, bool $billingSuspended): self
    {
        return match (true) {
            $provisioningStatus === TenantProvisioningStatus::Pending => self::Pending,
            $provisioningStatus === TenantProvisioningStatus::Processing => self::Provisioning,
            $provisioningStatus === TenantProvisioningStatus::Failed => self::Failed,
            ! $active, $billingSuspended => self::Suspended,
            default => self::Active,
        };
    }

    public function isSettled(): bool
    {
        return match ($this) {
            self::Pending, self::Provisioning => false,
            default => true,
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'gray',
            self::Provisioning => 'info',
            self::Active => 'success',
            self::Suspended => 'warning',
            self::Failed => 'danger',
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => __('vendra-store::enums.store_status_pending'),
            self::Provisioning => __('vendra-store::enums.store_status_provisioning'),
            self::Active => __('vendra-store::enums.store_status_active'),
            self::Suspended => __('vendra-store::enums.store_status_suspended'),
            self::Failed => __('vendra-store::enums.store_status_failed'),
        };
    }
}
