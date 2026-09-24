<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Tables\Columns;

use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;

final class DescriptionColumn extends TextColumn
{
    public static function getDefaultName(): string
    {
        return 'description';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('vendra-support::attributes.description'))
            ->icon(Heroicon::DocumentText)
            ->limit(50)
            ->tooltip(fn (): ?string => $this->truncatedState())
            ->toggleable(isToggledHiddenByDefault: true);
    }

    private function truncatedState(): ?string
    {
        $state = $this->getState();

        if (! is_string($state) || mb_strlen($state) <= $this->getCharacterLimit()) {
            return null;
        }

        return $state;
    }
}
