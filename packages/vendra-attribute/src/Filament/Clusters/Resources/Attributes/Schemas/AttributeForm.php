<?php

declare(strict_types=1);

namespace Misaf\VendraAttribute\Filament\Clusters\Resources\Attributes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Validation\Rules\Unique;
use Livewire\Component as Livewire;
use Misaf\VendraAttribute\Models\Attribute;
use Misaf\VendraAttribute\Support\AttributeUnits;
use Misaf\VendraSupport\Capabilities\TagIntegration;
use Misaf\VendraSupport\Tenancy\TenantAwareness;

final class AttributeForm
{
    public static function configure(Schema $schema): Schema
    {
        $components = [
            TextInput::make('name')
                ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.name'))
                ->autofocus()
                ->label(__('vendra-attribute::attributes.name'))
                ->live(onBlur: true)
                ->maxLength(255)
                ->required()
                ->unique(
                    modifyRuleUsing: fn (Unique $rule): Unique => TenantAwareness::constrainUniqueRule($rule)
                        ->withoutTrashed(),
                ),

            Select::make('unit')
                ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.unit'))
                ->label(__('vendra-attribute::attributes.unit'))
                ->live()
                ->native(false)
                ->options(AttributeUnits::options())
                ->searchable(),

            Textarea::make('description')
                ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.description'))
                ->columnSpanFull()
                ->label(__('vendra-attribute::attributes.description'))
                ->live(onBlur: true)
                ->maxLength(65535)
                ->rows(4),

            Toggle::make('active')
                ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.active'))
                ->columnSpanFull()
                ->default(true)
                ->label(__('vendra-attribute::attributes.active'))
                ->live()
                ->onIcon(Heroicon::Bolt)
                ->required()
                ->rules([
                    'boolean',
                ]),
        ];

        if (TagIntegration::isAvailable()) {
            $components[] = SpatieTagsInput::make('tags')
                ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.tags'))
                ->columnSpanFull()
                ->label(__('vendra-support::attributes.tags'))
                ->live()
                ->type(Attribute::TAG_TYPE);
        }

        return $schema
            ->components($components)
            ->columns(2);
    }
}
