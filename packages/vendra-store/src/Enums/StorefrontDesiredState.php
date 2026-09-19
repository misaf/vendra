<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Lets reconciliation tell a deliberately stopped storefront from a crashed one.
 */
enum StorefrontDesiredState: string implements HasColor, HasLabel
{
    case Running = 'running';
    case Stopped = 'stopped';

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
