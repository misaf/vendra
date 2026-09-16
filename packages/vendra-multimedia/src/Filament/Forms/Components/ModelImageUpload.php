<?php

declare(strict_types=1);

namespace Misaf\VendraMultimedia\Filament\Forms\Components;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Livewire\Component as Livewire;

final class ModelImageUpload extends SpatieMediaLibraryFileUpload
{
    public static function getDefaultName(): string
    {
        return 'image';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('vendra-multimedia::attributes.image'))
            ->image()
            ->live()
            ->afterStateUpdated(fn (ModelImageUpload $component, Livewire $livewire) => $livewire->validateOnly($component->getStatePath()))
            ->panelLayout('grid')
            ->responsiveImages()
            ->columnSpanFull();
    }
}
