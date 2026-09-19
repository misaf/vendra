<?php

declare(strict_types=1);

namespace Misaf\VendraInquiry\States;

use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;

final class Closed extends InquiryState
{
    public static string $name = 'closed';

    /**
     * @return array<string>
     */
    public function getColor(): array
    {
        return Color::Gray;
    }

    public function getIcon(): Heroicon
    {
        return Heroicon::OutlinedArchiveBox;
    }

    public function getLabel(): string
    {
        return __('vendra-inquiry::enums.inquiry_status_closed');
    }
}
