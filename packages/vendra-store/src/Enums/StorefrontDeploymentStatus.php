<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StorefrontDeploymentStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Requested = 'requested';
    case Ready = 'ready';
    case Failed = 'failed';

    /**
     * @return list<self>
     */
    public function transitions(): array
    {
        return match ($this) {
            self::Pending => [self::Processing],
            self::Processing => [self::Ready, self::Requested, self::Failed, self::Processing],
            self::Requested => [self::Processing, self::Ready, self::Failed],
            self::Ready => [self::Processing],
            self::Failed => [self::Processing],
        };
    }

    public function canTransitionTo(self $status): bool
    {
        return in_array($status, $this->transitions(), true);
    }

    public function isSettled(): bool
    {
        return match ($this) {
            self::Ready, self::Failed => true,
            default => false,
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'gray',
            self::Processing => 'info',
            self::Requested => 'info',
            self::Ready => 'success',
            self::Failed => 'danger',
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => __('vendra-store::enums.deployment_status_pending'),
            self::Processing => __('vendra-store::enums.deployment_status_processing'),
            self::Requested => __('vendra-store::enums.deployment_status_requested'),
            self::Ready => __('vendra-store::enums.deployment_status_ready'),
            self::Failed => __('vendra-store::enums.deployment_status_failed'),
        };
    }
}
