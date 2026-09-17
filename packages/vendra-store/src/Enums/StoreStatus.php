<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * The one status an administrator reads a store by.
 *
 * A store's condition is stored across three columns — `provisioning_status`,
 * `active`, and `billing_suspended_at` — because each is written by a different
 * concern: the provisioner, the administrator, and billing enforcement. Nobody
 * reading a console table wants those three; they want to know whether the
 * store is up. This enum is that reading, derived rather than persisted, so the
 * columns stay the source of truth and no new state can drift out of sync with
 * them.
 */
enum StoreStatus: string implements HasColor, HasLabel
{
    /** Created, provisioning not started. */
    case Pending = 'pending';

    /** Provisioning is running. */
    case Provisioning = 'provisioning';

    /** Provisioned, enabled, and not suspended — the store serves requests. */
    case Active = 'active';

    /** Provisioned, but disabled by an administrator or suspended by billing. */
    case Suspended = 'suspended';

    /** Provisioning gave up. */
    case Failed = 'failed';

    /**
     * Whether a store in this status may serve requests.
     *
     * The counterpart of `Store::scopeAccessible()`, which is the same rule
     * expressed as a query.
     */
    public function isServing(): bool
    {
        return $this === self::Active;
    }

    /**
     * Whether provisioning has finished, successfully or not.
     */
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
