<?php

declare(strict_types=1);

namespace Misaf\VendraUserProfile\Filament\Clusters\Resources\Schemas;

use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Livewire\Component as Livewire;
use Misaf\VendraSupport\Filament\Forms\Components\DescriptionTextarea;
use Misaf\VendraSupport\Filament\Forms\Components\IsActiveToggle;
use Misaf\VendraSupport\Filament\Forms\Components\IsDefaultToggle;
use Misaf\VendraSupport\Filament\Forms\Components\SluggableNameInput;
use Misaf\VendraSupport\Filament\Forms\Components\SlugInput;

final class UserProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.user_id'))
                    ->columnSpanFull()
                    ->label(__('vendra-user-profile::attributes.user'))
                    ->live()
                    ->native(false)
                    ->preload()
                    ->relationship('user', 'username')
                    ->required()
                    ->searchable(),

                SluggableNameInput::make()
                    ->uniqueWithinTenant(),

                SlugInput::make()
                    ->uniqueWithinTenant(),

                DescriptionTextarea::make()
                    ->maxLength(255),

                IsDefaultToggle::make(),

                IsActiveToggle::make()
                    ->default(false),
            ]);
    }
}
