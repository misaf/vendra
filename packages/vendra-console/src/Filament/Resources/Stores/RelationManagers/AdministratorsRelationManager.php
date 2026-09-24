<?php

declare(strict_types=1);

namespace Misaf\VendraConsole\Filament\Resources\Stores\RelationManagers;

use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Misaf\VendraConsole\Filament\Resources\Stores\Actions\AddAdministratorTableAction;
use Misaf\VendraConsole\Filament\Resources\Stores\Actions\ChangeAdministratorEmailTableAction;
use Misaf\VendraConsole\Filament\Resources\Stores\Actions\ChangeAdministratorPasswordTableAction;
use Misaf\VendraConsole\Filament\Resources\Stores\Actions\Concerns\InteractsWithAdministratorRecord;
use Misaf\VendraConsole\Filament\Resources\Stores\Actions\DemoteAdministratorTableAction;
use Misaf\VendraConsole\Filament\Resources\Stores\Actions\DisableAdministratorTableAction;
use Misaf\VendraConsole\Filament\Resources\Stores\Actions\EnableAdministratorTableAction;
use Misaf\VendraConsole\Filament\Resources\Stores\Actions\RemoveAdministratorTableAction;
use Misaf\VendraSupport\Tenancy\TenantSchema;
use Misaf\VendraUser\Models\User;

final class AdministratorsRelationManager extends RelationManager
{
    use InteractsWithAdministratorRecord;

    protected static string $relationship = 'users';

    protected static string|BackedEnum|null $icon = Heroicon::OutlinedUsers;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('vendra-console::attributes.store_administrators');
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $this->onlyAdministrators($query))
            ->columns([
                TextColumn::make('username')
                    ->label(__('vendra-console::attributes.username'))
                    ->searchable(),

                TextColumn::make('email')
                    ->label(__('vendra-console::attributes.email'))
                    ->searchable(),

                IconColumn::make('active')
                    ->label(__('vendra-support::attributes.active'))
                    ->boolean()
                    ->state(fn (User $record): bool => ! $record->trashed()),
            ])
            ->filters([TrashedFilter::make()->default(true)])
            ->headerActions([AddAdministratorTableAction::make()])
            ->recordActions([
                ActionGroup::make([
                    ActionGroup::make([
                        ChangeAdministratorPasswordTableAction::make(),
                        ChangeAdministratorEmailTableAction::make(),
                    ])->dropdown(false),
                    ActionGroup::make([
                        DemoteAdministratorTableAction::make(),
                    ])->dropdown(false),
                    ActionGroup::make([
                        DisableAdministratorTableAction::make(),
                        EnableAdministratorTableAction::make(),
                    ])->dropdown(false),
                    ActionGroup::make([
                        RemoveAdministratorTableAction::make(),
                    ])->dropdown(false),
                ]),
            ]);
    }

    /**
     * Keep users holding the store's admin role; other store users are listed in the store's own panel.
     *
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    private function onlyAdministrators(Builder $query): Builder
    {
        $store = self::administratorStore($this);

        return $query->whereHas('roles', fn (Builder $roles): Builder => $roles
            ->withoutGlobalScopes()
            ->where($roles->qualifyColumn(TenantSchema::column()), $store->getKey())
            ->where($roles->qualifyColumn('name'), Config::string('vendra-permission.admin_role'))
            ->where($roles->qualifyColumn('guard_name'), 'web'));
    }
}
