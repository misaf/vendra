<?php

declare(strict_types=1);

namespace Misaf\VendraTagger\Filament\Clusters\Resources\Taggers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;
use Livewire\Component as Livewire;
use Misaf\VendraSupport\Filament\Forms\Components\SluggableNameInput;
use Misaf\VendraSupport\Filament\Forms\Components\SlugInput;
use Misaf\VendraSupport\Tenancy\TenantAwareness;

final class TaggerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                SluggableNameInput::make()
                    ->unique(
                        modifyRuleUsing: function (Unique $rule, Get $get): void {
                            TenantAwareness::constrainUniqueRule($rule);

                            $type = $get->string('type', isNullable: true);

                            if ($type !== null) {
                                $rule->where('type', $type);
                            }
                        },
                    ),

                SlugInput::make()
                    ->unique(
                        modifyRuleUsing: function (Unique $rule, Get $get): void {
                            TenantAwareness::constrainUniqueRule($rule);

                            $type = $get->string('type', isNullable: true);

                            if ($type !== null) {
                                $rule->where('type', $type);
                            }
                        },
                    ),

                TextInput::make('type')
                    ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.type'))
                    ->columnSpanFull()
                    ->helperText(__('vendra-tagger::attributes.type_helper_text'))
                    ->label(__('vendra-tagger::attributes.type'))
                    ->live(onBlur: true)
                    ->maxLength(255),
            ]);
    }
}
