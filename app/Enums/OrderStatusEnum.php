<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use InvalidArgumentException;

enum OrderStatusEnum: string implements HasLabel, HasColor, HasIcon
{
    case Cancelled = 'cancelled';
    case Delivered = 'delivered';
    case Pending = 'pending';
    case Processing = 'processing';
    case Refund = 'refund';
    case Shipped = 'shipped';

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
     * @return string|null
     */
    private function getAttributeValue(string $attribute): ?string
    {
        return match ($attribute) {
            'color' => match ($this) {
                self::Cancelled  => 'warning',
                self::Delivered  => 'warning',
                self::Pending    => 'gray',
                self::Processing => 'warning',
                self::Refund     => 'warning',
                self::Shipped    => 'warning',
                default          => throw new InvalidArgumentException("Invalid value for color."),
            },
            'icon' => match ($this) {
                self::Cancelled  => 'heroicon-m-check',
                self::Delivered  => 'heroicon-m-check',
                self::Pending    => 'heroicon-m-check',
                self::Processing => 'heroicon-m-check',
                self::Refund     => 'heroicon-m-check',
                self::Shipped    => 'heroicon-m-check',
                default          => throw new InvalidArgumentException("Invalid value for icon."),
            },
            'label' => match ($this) {
                self::Cancelled  => 'Cancelled',
                self::Delivered  => 'Delivered',
                self::Pending    => 'Pending',
                self::Processing => 'Processing',
                self::Refund     => 'Refund',
                self::Shipped    => 'Shiped',
                default          => throw new InvalidArgumentException("Invalid value for label."),
            },
            default => throw new InvalidArgumentException("Invalid attribute name."),
        };
    }
}
