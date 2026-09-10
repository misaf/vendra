<?php

declare(strict_types=1);

namespace Misaf\VendraUserProfile\Filament\Clusters\Resources\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use Livewire\Component as Livewire;
use Misaf\VendraSupport\Tenancy\TenantAwareness;

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

                TextInput::make('name')
                    ->afterStateUpdated(function (Livewire $livewire, Get $get, Set $set, ?string $old, ?string $state): void {
                        $livewire->validateOnly('data.name');

                        if (($get->string('slug', isNullable: true) ?? '') === Str::slug($old ?? '')) {
                            $set('slug', Str::slug($state ?? ''));
                        }
                    })
                    ->autofocus()
                    ->columnSpan(['lg' => 1])
                    ->label(__('vendra-user-profile::attributes.name'))
                    ->live(onBlur: true)
                    ->maxLength(255)
                    ->required()
                    ->unique(
                        modifyRuleUsing: fn (Unique $rule): Unique => TenantAwareness::constrainUniqueRule($rule)
                            ->withoutTrashed(),
                    ),

                TextInput::make('slug')
                    ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.slug'))
                    ->columnSpan(['lg' => 1])
                    ->helperText(__('vendra-user-profile::attributes.slug_helper_text'))
                    ->label(__('vendra-user-profile::attributes.slug'))
                    ->live(onBlur: true)
                    ->maxLength(255)
                    ->required()
                    ->unique(
                        modifyRuleUsing: fn (Unique $rule): Unique => TenantAwareness::constrainUniqueRule($rule)
                            ->withoutTrashed(),
                    ),

                Textarea::make('description')
                    ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.description'))
                    ->columnSpanFull()
                    ->label(__('vendra-user-profile::attributes.description'))
                    ->live(onBlur: true)
                    ->maxLength(255),

                Toggle::make('is_default')
                    ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.is_default'))
                    ->columnSpanFull()
                    ->default(false)
                    ->label(__('vendra-user-profile::attributes.is_default'))
                    ->live()
                    ->onIcon(Heroicon::Bolt)
                    ->required()
                    ->rules([
                        'boolean',
                    ]),

                Toggle::make('active')
                    ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.active'))
                    ->columnSpanFull()
                    ->default(false)
                    ->label(__('vendra-user-profile::attributes.active'))
                    ->live()
                    ->onIcon(Heroicon::Bolt)
                    ->required()
                    ->rules([
                        'boolean',
                    ]),
            ]);
    }
}
