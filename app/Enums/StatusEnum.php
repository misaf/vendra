<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use InvalidArgumentException;

enum StatusEnum: string implements HasLabel, HasColor, HasIcon
{
    case Disable = 'disabled';
    case Enable = 'enabled';

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
                self::Disable => 'warning',
                self::Enable  => 'gray',
                default       => throw new InvalidArgumentException("Invalid value for color."),
            },
            'icon' => match ($this) {
                self::Disable => 'heroicon-m-x-mark',
                self::Enable  => 'heroicon-m-check',
                default       => throw new InvalidArgumentException("Invalid value for icon."),
            },
            'label' => match ($this) {
                self::Disable => 'Disable',
                self::Enable  => 'Enable',
                default       => throw new InvalidArgumentException("Invalid value for label."),
            },
            default => throw new InvalidArgumentException("Invalid attribute name."),
        };
    }
}
