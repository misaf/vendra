<?php

declare(strict_types=1);

namespace Misaf\VendraInquiry\States;

use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;

final class Answered extends InquiryState
{
    public static string $name = 'answered';

    /**
     * @return array<string>
     */
    public function getColor(): array
    {
        return Color::Green;
    }

    public function getIcon(): Heroicon
    {
        return Heroicon::OutlinedCheckCircle;
    }

    public function getLabel(): string
    {
        return __('vendra-inquiry::enums.inquiry_status_answered');
    }
}
