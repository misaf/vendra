<?php

declare(strict_types=1);

namespace Misaf\VendraPermission\Filament\Clusters\Resources\Permissions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rules\Unique;
use Livewire\Component as Livewire;
use Misaf\VendraPermission\Filament\Clusters\Resources\Permissions\RelationManagers\PermissionRelationManager;
use Misaf\VendraPermission\Filament\Clusters\Resources\Permissions\Schemas\Components\RolesSelect;
use Misaf\VendraPermission\Models\Permission;
use Misaf\VendraSupport\Tenancy\TenantAwareness;

final class PermissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                RolesSelect::make('roles')
                    ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.roles'))
                    ->dehydrated(false)
                    ->hidden(fn (Livewire $livewire, string $operation): bool => $livewire instanceof PermissionRelationManager || $operation === 'create')
                    ->live()
                    ->relationship(
                        name: 'roles',
                        titleAttribute: 'name',
                        modifyQueryUsing: function (Builder $query, Get $get, string $operation, ?Permission $record): void {
                            if ($operation !== 'edit') {
                                return;
                            }

                            $guardName = $get->string('guard_name', isNullable: true) ?? $record?->guard_name;

                            if ($guardName === null) {
                                return;
                            }

                            $query->where('guard_name', $guardName);
                        },
                    )
                    ->nullable()
                    ->saved(false),

                TextInput::make('name')
                    ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.name'))
                    ->autofocus()
                    ->columnSpan(fn (Get $get) => empty($get('roles')) ? ['lg' => 1] : 'full')
                    ->label(__('vendra-permission::attributes.name'))
                    ->live()
                    ->maxLength(255)
                    ->required()
                    ->string()
                    ->unique(
                        modifyRuleUsing: function (Unique $rule, Get $get, string $operation): void {
                            TenantAwareness::constrainUniqueRule($rule);

                            if ($operation === 'create' && ! empty($get('roles'))) {
                                $rule->where('id', 0);

                                return;
                            }

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
                    ->hiddenOn(PermissionRelationManager::class)
                    ->label(__('vendra-permission::attributes.guard_name'))
                    ->live()
                    ->native(false)
                    ->options(
                        collect(Config::array('auth.guards'))
                            ->keys()
                            ->mapWithKeys(fn ($value): array => [$value => $value])
                            ->all()
                    )
                    ->preload()
                    ->required(fn (Get $get): bool => empty($get('roles')))
                    ->saved(fn (Get $get) => empty($get('roles')))
                    ->searchable()
                    ->string()
                    ->visible(fn (Get $get) => empty($get('roles'))),

                Textarea::make('description')
                    ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.description'))
                    ->columnSpanFull()
                    ->label(__('vendra-permission::attributes.description'))
                    ->live()
                    ->maxLength(255)
                    ->rows(5)
                    ->string(),
            ]);
    }
}
