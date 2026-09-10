<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Filament\Clusters\Resources\TransactionGateways\Schemas;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
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
use Misaf\VendraTransaction\Models\TransactionGateway;

final class TransactionGatewayForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->afterStateUpdated(function (Livewire $livewire, Get $get, Set $set, ?string $old, ?string $state): void {
                        $livewire->validateOnly('data.name');

                        if (($get->string('slug', isNullable: true) ?? '') === Str::slug($old ?? '')) {
                            $set('slug', Str::slug($state ?? ''));
                        }
                    })
                    ->autofocus()
                    ->columnSpan(['lg' => 1])
                    ->label(__('vendra-transaction::attributes.name'))
                    ->live(onBlur: true)
                    ->maxLength(255)
                    ->required(),

                TextInput::make('slug')
                    ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.slug'))
                    ->columnSpan(['lg' => 1])
                    ->extraInputAttributes(['dir' => 'ltr'])
                    ->helperText(__('vendra-transaction::attributes.slug_helper_text'))
                    ->label(__('vendra-transaction::attributes.slug'))
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
                    ->label(__('vendra-transaction::attributes.description'))
                    ->live(onBlur: true)
                    ->maxLength(1000)
                    ->rows(3),

                SpatieMediaLibraryFileUpload::make('image')
                    ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.image'))
                    ->collection(TransactionGateway::MEDIA_COLLECTION)
                    ->columnSpanFull()
                    ->image()
                    ->label(__('vendra-transaction::attributes.image'))
                    ->live(),

                Toggle::make('active')
                    ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.active'))
                    ->columnSpanFull()
                    ->default(false)
                    ->label(__('vendra-transaction::attributes.active'))
                    ->live()
                    ->onIcon(Heroicon::Bolt)
                    ->required()
                    ->rules(['boolean']),

                Toggle::make('is_default')
                    ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.is_default'))
                    ->columnSpanFull()
                    ->default(false)
                    ->helperText(__('vendra-transaction::attributes.is_default_helper_text'))
                    ->label(__('vendra-transaction::attributes.is_default'))
                    ->live()
                    ->onIcon(Heroicon::Bolt)
                    ->required()
                    ->rules(['boolean']),
            ]);
    }
}
