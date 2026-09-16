<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Forms\Components;

use Filament\Forms\Components\RichEditor;

final class DescriptionRichEditor extends RichEditor
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
            ->json()
            ->required()
            ->columnSpanFull();
    }
}
