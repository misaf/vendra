<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Forms\Components;

use Filament\Forms\Components\Toggle;
use Filament\Support\Icons\Heroicon;
use Livewire\Component as Livewire;

final class IsPrimaryToggle extends Toggle
{
    public static function getDefaultName(): string
    {
        return 'is_primary';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('vendra-support::attributes.is_primary'))
            ->onIcon(Heroicon::Bolt)
            ->live()
            ->afterStateUpdated(fn (IsPrimaryToggle $component, Livewire $livewire) => $livewire->validateOnly($component->getStatePath()))
            ->default(false)
            ->required()
            ->rules(['boolean'])
            ->columnSpanFull();
    }
}
