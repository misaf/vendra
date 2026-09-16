<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Forms\Components;

use Filament\Forms\Components\TextInput;
use Livewire\Component as Livewire;
use Misaf\VendraSupport\Filament\Concerns\ValidatesUniquenessWithinTenant;

final class SlugInput extends TextInput
{
    use ValidatesUniquenessWithinTenant;

    public static function getDefaultName(): string
    {
        return 'slug';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('vendra-support::attributes.slug'))
            ->helperText(__('vendra-support::attributes.slug_helper_text'))
            ->live(onBlur: true)
            ->afterStateUpdated(fn (SlugInput $component, Livewire $livewire) => $livewire->validateOnly($component->getStatePath()))
            ->maxLength(255)
            ->required()
            ->columnSpan(['lg' => 1]);
    }
}
