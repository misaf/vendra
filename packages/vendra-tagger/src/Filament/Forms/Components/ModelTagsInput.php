<?php

declare(strict_types=1);

namespace Misaf\VendraTagger\Filament\Forms\Components;

use Filament\Forms\Components\SpatieTagsInput;
use Livewire\Component as Livewire;

final class ModelTagsInput extends SpatieTagsInput
{
    public static function getDefaultName(): string
    {
        return 'tags';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('vendra-tagger::attributes.tags'))
            ->live()
            ->afterStateUpdated(fn (ModelTagsInput $component, Livewire $livewire) => $livewire->validateOnly($component->getStatePath()))
            ->columnSpanFull();
    }
}
