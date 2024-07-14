<?php

declare(strict_types=1);

namespace Termehsoft\Product\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use InvalidArgumentException;

enum ProductStatusEnum: string implements HasLabel, HasColor, HasIcon
{
    case AvailableSoon = 'available_soon';
    case InStock = 'in_stock';
    case OutOfStock = 'out_of_stock';

    /**
     * Get all enum values.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get the color associated with the status.
     *
     * @return string|null
     */
    public function getColor(): ?string
    {
        return $this->getAttributeValue('color');
    }

    /**
     * Get the icon associated with the status.
     *
     * @return string|null
     */
    public function getIcon(): ?string
    {
        return $this->getAttributeValue('icon');
    }

    /**
     * Get the label associated with the status.
     *
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->getAttributeValue('label');
    }

    /**
     * Get the attribute value for the given attribute name.
     *
     * @param string $attribute
     * @return mixed
     */
    private function getAttributeValue(string $attribute): mixed
    {
        return match ($attribute) {
            'color' => match ($this) {
                self::AvailableSoon => 'warning',
                self::InStock       => 'warning',
                self::OutOfStock    => 'warning',
                default             => throw new InvalidArgumentException("Invalid value for color."),
            },
            'icon' => match ($this) {
                self::AvailableSoon => 'heroicon-m-x-mark',
                self::InStock       => 'heroicon-m-x-mark',
                self::OutOfStock    => 'heroicon-m-x-mark',
                default             => throw new InvalidArgumentException("Invalid value for icon."),
            },
            'label' => match ($this) {
                self::AvailableSoon => __('status.available_soon'),
                self::InStock       => __('status.in_stock'),
                self::OutOfStock    => __('status.out_of_stock'),
                default             => throw new InvalidArgumentException("Invalid value for label."),
            },
            default => throw new InvalidArgumentException("Invalid attribute name."),
        };
    }
}
