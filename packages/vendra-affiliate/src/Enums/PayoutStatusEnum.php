<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Enums;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum PayoutStatusEnum: string implements HasColor, HasIcon, HasLabel
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Failed = 'failed';

    /**
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * @return array<string>
     */
    public function getColor(): array
    {
        return match ($this) {
            self::Pending => Color::Yellow,
            self::Completed => Color::Green,
            self::Failed => Color::Red,
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Pending => Heroicon::OutlinedClock,
            self::Completed => Heroicon::OutlinedCheckCircle,
            self::Failed => Heroicon::OutlinedExclamationCircle,
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => __('vendra-affiliate::enums.payout_status_pending'),
            self::Completed => __('vendra-affiliate::enums.payout_status_completed'),
            self::Failed => __('vendra-affiliate::enums.payout_status_failed'),
        };
    }
}
