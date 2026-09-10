<?php

declare(strict_types=1);

namespace Misaf\VendraNewsletter\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum NewsletterStatusEnum: string implements HasColor, HasIcon, HasLabel
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Sent = 'sent';

    public function getLabel(): string
    {
        return __("vendra-newsletter::enums.newsletter_status_{$this->value}");
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Scheduled => 'warning',
            self::Sent => 'success',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Draft => Heroicon::OutlinedPencilSquare,
            self::Scheduled => Heroicon::OutlinedClock,
            self::Sent => Heroicon::OutlinedPaperAirplane,
        };
    }
}
