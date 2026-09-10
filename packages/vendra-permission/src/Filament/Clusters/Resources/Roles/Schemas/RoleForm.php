<?php

declare(strict_types=1);

namespace Misaf\VendraPermission\Filament\Clusters\Resources\Roles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rules\Unique;
use Livewire\Component as Livewire;
use Misaf\VendraSupport\Tenancy\TenantAwareness;

final class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.name'))
                    ->autofocus()
                    ->columnSpan(['lg' => 1])
                    ->label(__('vendra-permission::attributes.name'))
                    ->live(debounce: 500)
                    ->maxLength(255)
                    ->required()
                    ->string()
                    ->unique(
                        modifyRuleUsing: function (Unique $rule, Get $get): void {
                            TenantAwareness::constrainUniqueRule($rule);

                            $guardName = $get->string('guard_name', isNullable: true);

                            if ($guardName !== null) {
                                $rule->where('guard_name', $guardName);
                            }
                        },
                    ),

                Select::make('guard_name')
                    ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.guard_name'))
                    ->columnSpan(['lg' => 1])
                    ->helperText(__('vendra-permission::attributes.guard_name_helper_text'))
                    ->label(__('vendra-permission::attributes.guard_name'))
                    ->live()
                    ->native(false)
                    ->options(self::guardOptions())
                    ->preload()
                    ->required()
                    ->searchable()
                    ->string(),

                Textarea::make('description')
                    ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.description'))
                    ->columnSpanFull()
                    ->label(__('vendra-permission::attributes.description'))
                    ->live(debounce: 500)
                    ->maxLength(255)
                    ->nullable()
                    ->rows(5)
                    ->string(),
            ]);
    }

    /**
     * @return array<string, string>
     */
    private static function guardOptions(): array
    {
        $options = [];

        foreach (array_keys(Config::array('auth.guards')) as $guardName) {
            $guardName = (string) $guardName;
            $options[$guardName] = $guardName;
        }

        return $options;
    }
}
