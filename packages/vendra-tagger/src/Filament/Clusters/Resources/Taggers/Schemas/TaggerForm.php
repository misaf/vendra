<?php

declare(strict_types=1);

namespace Misaf\VendraTagger\Filament\Clusters\Resources\Taggers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use Livewire\Component as Livewire;
use Misaf\VendraSupport\Tenancy\TenantAwareness;

final class TaggerForm
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
                    ->label(__('vendra-tagger::attributes.name'))
                    ->live(onBlur: true)
                    ->maxLength(255)
                    ->required()
                    ->unique(
                        modifyRuleUsing: function (Unique $rule, Get $get): void {
                            TenantAwareness::constrainUniqueRule($rule);

                            $type = $get->string('type', isNullable: true);

                            if ($type !== null) {
                                $rule->where('type', $type);
                            }
                        },
                    ),

                TextInput::make('slug')
                    ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.slug'))
                    ->columnSpan(['lg' => 1])
                    ->helperText(__('vendra-tagger::attributes.slug_helper_text'))
                    ->label(__('vendra-tagger::attributes.slug'))
                    ->live(onBlur: true)
                    ->maxLength(255)
                    ->required()
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
