<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Enums;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Misaf\VendraAffiliate\Settings\AffiliateSettings;

enum ConversionTypeEnum: string implements HasColor, HasIcon, HasLabel
{
    case Deposit = 'deposit';
    case Signup = 'signup';
    case Checkout = 'checkout';

    /**
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function isEnabled(): bool
    {
        return resolve(AffiliateSettings::class)->earnsOn($this);
    }

    /**
     * @return array<string>
     */
    public function getColor(): array
    {
        return match ($this) {
            self::Deposit => Color::Green,
            self::Signup => Color::Blue,
            self::Checkout => Color::Purple,
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Deposit => Heroicon::OutlinedCurrencyDollar,
            self::Signup => Heroicon::OutlinedUserPlus,
            self::Checkout => Heroicon::OutlinedShoppingCart,
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Deposit => __('vendra-affiliate::enums.conversion_type_deposit'),
            self::Signup => __('vendra-affiliate::enums.conversion_type_signup'),
            self::Checkout => __('vendra-affiliate::enums.conversion_type_checkout'),
        };
    }
}
