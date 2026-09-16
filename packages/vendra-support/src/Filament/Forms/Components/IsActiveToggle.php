<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Forms\Components;

use Filament\Forms\Components\Toggle;
use Filament\Support\Icons\Heroicon;
use Livewire\Component as Livewire;

final class IsActiveToggle extends Toggle
{
    public static function getDefaultName(): string
    {
        return 'active';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('vendra-support::attributes.active'))
            ->onIcon(Heroicon::Bolt)
            ->live()
            ->afterStateUpdated(fn (IsActiveToggle $component, Livewire $livewire) => $livewire->validateOnly($component->getStatePath()))
            ->required()
            ->rules(['boolean'])
            ->columnSpanFull();
    }
}
