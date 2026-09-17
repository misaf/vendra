<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * What the platform intends for a storefront, independent of what is running.
 *
 * Without this, "stopped" and "failed to start" are the same row, and
 * reconciliation cannot tell a storefront somebody deliberately stopped from one
 * that fell over — so it would start the first back up on every pass.
 */
enum StorefrontDesiredState: string implements HasColor, HasLabel
{
    case Running = 'running';
    case Stopped = 'stopped';

    /**
     * Whether reconciliation should be trying to get this storefront serving.
     */
    public function expectsRunning(): bool
    {
        return $this === self::Running;
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Running => 'success',
            self::Stopped => 'gray',
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Running => __('vendra-store::enums.desired_state_running'),
            self::Stopped => __('vendra-store::enums.desired_state_stopped'),
        };
    }
}
