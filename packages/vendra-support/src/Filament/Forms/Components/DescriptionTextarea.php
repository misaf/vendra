<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Forms\Components;

use Filament\Forms\Components\Textarea;
use Livewire\Component as Livewire;

final class DescriptionTextarea extends Textarea
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
            ->live(onBlur: true)
            ->afterStateUpdated(fn (DescriptionTextarea $component, Livewire $livewire) => $livewire->validateOnly($component->getStatePath()))
            ->columnSpanFull();
    }
}
