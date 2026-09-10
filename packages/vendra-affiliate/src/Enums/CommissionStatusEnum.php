<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Enums;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum CommissionStatusEnum: string implements HasColor, HasIcon, HasLabel
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Paid = 'paid';
    case Reversed = 'reversed';

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
            self::Approved => Color::Green,
            self::Paid => Color::Blue,
            self::Reversed => Color::Red,
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Pending => Heroicon::OutlinedClock,
            self::Approved => Heroicon::OutlinedCheckCircle,
            self::Paid => Heroicon::OutlinedBanknotes,
            self::Reversed => Heroicon::OutlinedArrowUturnLeft,
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => __('vendra-affiliate::enums.commission_status_pending'),
            self::Approved => __('vendra-affiliate::enums.commission_status_approved'),
            self::Paid => __('vendra-affiliate::enums.commission_status_paid'),
            self::Reversed => __('vendra-affiliate::enums.commission_status_reversed'),
        };
    }
}
