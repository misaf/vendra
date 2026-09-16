<?php

declare(strict_types=1);

namespace Misaf\VendraAttribute\Filament\Clusters\Resources\Attributes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;
use Livewire\Component as Livewire;
use Misaf\VendraAttribute\Models\Attribute;
use Misaf\VendraAttribute\Support\AttributeUnits;
use Misaf\VendraSupport\Capabilities\TagIntegration;
use Misaf\VendraSupport\Filament\Forms\Components\ActiveToggle;
use Misaf\VendraSupport\Tenancy\TenantAwareness;
use Misaf\VendraTagger\Filament\Forms\Components\ModelTagsInput;

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

            ActiveToggle::make()
                ->default(true),
        ];

        if (TagIntegration::isAvailable()) {
            $components[] = ModelTagsInput::make()
                ->type(Attribute::TAG_TYPE);
        }

        return $schema
            ->components($components)
            ->columns(2);
    }
}
