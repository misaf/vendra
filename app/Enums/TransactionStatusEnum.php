<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use InvalidArgumentException;

enum TransactionStatusEnum: string implements HasLabel, HasColor, HasIcon
{
    case Approved = 'approved';
    case Declined = 'declined';
    case Failed = 'failed';
    case Pending = 'pending';

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
                self::Approved => 'warning',
                self::Declined => 'warning',
                self::Failed   => 'warning',
                self::Pending  => 'gray',
                default        => throw new InvalidArgumentException("Invalid value for color."),
            },
            'icon' => match ($this) {
                self::Approved => 'heroicon-m-x-mark',
                self::Declined => 'heroicon-m-x-mark',
                self::Failed   => 'heroicon-m-x-mark',
                self::Pending  => 'heroicon-m-check',
                default        => throw new InvalidArgumentException("Invalid value for icon."),
            },
            'label' => match ($this) {
                self::Approved => 'Approved',
                self::Declined => 'Declined',
                self::Failed   => 'Failed',
                self::Pending  => 'Pending',
                default        => throw new InvalidArgumentException("Invalid value for label."),
            },
            default => throw new InvalidArgumentException("Invalid attribute name."),
        };
    }
}
