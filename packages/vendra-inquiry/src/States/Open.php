<?php

declare(strict_types=1);

namespace Misaf\VendraInquiry\States;

use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;

final class Open extends InquiryState
{
    public static string $name = 'new';

    /**
     * @return array<string>
     */
    public function getColor(): array
    {
        return Color::Amber;
    }

    public function getIcon(): Heroicon
    {
        return Heroicon::OutlinedInbox;
    }

    public function getLabel(): string
    {
        return __('vendra-inquiry::enums.inquiry_status_new');
    }
}
