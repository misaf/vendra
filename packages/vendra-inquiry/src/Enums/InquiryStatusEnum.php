<?php

declare(strict_types=1);

namespace Misaf\VendraInquiry\Enums;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

/**
 * Where an enquiry stands in the studio's inbox. Answering is the point, so
 * the states track a reply rather than a workflow.
 */
enum InquiryStatusEnum: string implements HasColor, HasIcon, HasLabel
{
    case New = 'new';
    case Answered = 'answered';
    case Closed = 'closed';

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
            self::New => Color::Amber,
            self::Answered => Color::Green,
            self::Closed => Color::Gray,
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::New => Heroicon::OutlinedInbox,
            self::Answered => Heroicon::OutlinedCheckCircle,
            self::Closed => Heroicon::OutlinedArchiveBox,
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::New => __('vendra-inquiry::enums.inquiry_status_new'),
            self::Answered => __('vendra-inquiry::enums.inquiry_status_answered'),
            self::Closed => __('vendra-inquiry::enums.inquiry_status_closed'),
        };
    }
}
