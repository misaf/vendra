<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Forms\Components;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;
use Livewire\Component as Livewire;
use Misaf\VendraSupport\Filament\Concerns\ValidatesUniquenessWithinTenant;

final class SluggableNameInput extends TextInput
{
    use ValidatesUniquenessWithinTenant;

    public static function getDefaultName(): string
    {
        return 'name';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('vendra-support::attributes.name'))
            ->autofocus()
            ->live(onBlur: true)
            ->afterStateUpdated(function (SluggableNameInput $component, Livewire $livewire, Get $get, Set $set, ?string $old, ?string $state): void {
                $livewire->validateOnly($component->getStatePath());

                if (($get->string('slug', isNullable: true) ?? '') === Str::slug($old ?? '')) {
                    $set('slug', Str::slug($state ?? ''));
                }
            })
            ->maxLength(255)
            ->required()
            ->columnSpan(['lg' => 1]);
    }
}
