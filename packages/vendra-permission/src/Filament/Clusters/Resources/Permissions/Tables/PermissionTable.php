<?php

declare(strict_types=1);

namespace Misaf\VendraPermission\Filament\Clusters\Resources\Permissions\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\QueryBuilder\Constraints\SelectConstraint;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\Layout\Component as LayoutComponent;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Config;
use Laravel\Pennant\Feature;
use Misaf\VendraPermission\Enums\PermissionFeatureEnum;
use Misaf\VendraPermission\Filament\Clusters\Resources\Permissions\Actions\Permissions\DeleteBulkAction;
use Misaf\VendraPermission\Filament\Clusters\Resources\Permissions\Actions\Roles\SyncBulkAction;
use Misaf\VendraSupport\Contracts\TenantResolver;

final class PermissionTable
{
    public static function configure(Table $table): Table
    {
        /**
         * @var array<int, Column|ColumnGroup|LayoutComponent> $columns
         */
        $columns = [
            TextColumn::make('row')
                ->label('#')
                ->rowIndex()
                ->sortable(['id']),

            TextColumn::make('roles.name')
                ->alignStart()
                ->badge()
                ->expandableLimitedList()
                ->icon(Heroicon::UserGroup)
                ->label(__('vendra-permission::table.columns.role'))
                ->limitList(2)
                ->listWithLineBreaks(),

            TextColumn::make('name')
                ->alignStart()
                ->label(__('vendra-permission::table.columns.name'))
                ->icon(Heroicon::Tag)
                ->searchable()
                ->sortable(),

            TextColumn::make('description')
                ->label(__('vendra-permission::table.columns.description'))
                ->icon(Heroicon::DocumentText)
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('created_at')
                ->extraCellAttributes(['dir' => 'ltr'])
                ->label(__('vendra-permission::table.columns.created_at'))
                ->sinceTooltip()
                ->when(
                    app()->isLocale('fa'),
                    fn (TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                    fn (TextColumn $column) => $column->dateTime('Y-m-d H:i')
                ),

            TextColumn::make('updated_at')
                ->extraCellAttributes(['dir' => 'ltr'])
                ->label(__('vendra-permission::table.columns.updated_at'))
                ->sinceTooltip()
                ->when(
                    app()->isLocale('fa'),
                    fn (TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                    fn (TextColumn $column) => $column->dateTime('Y-m-d H:i')
                ),
        ];

        return $table
            ->columns($columns)
            ->filters(
                [
                    QueryBuilder::make()
                        ->constraints([
                            TextConstraint::make('name')
                                ->label(__('vendra-permission::table.columns.name')),

                            SelectConstraint::make('guard_name')
                                ->label(__('vendra-permission::table.columns.guard_name'))
                                ->options(
                                    collect(Config::array('auth.guards'))->keys()->mapWithKeys(fn ($value): array => [$value => $value])->all()
                                )
                                ->multiple(),
                        ]),
                ],
                layout: FiltersLayout::AboveContentCollapsible,
            )
            ->description(__('vendra-permission::tables.description.permissions'))
            ->emptyStateHeading(__('vendra-permission::tables.empty_state.heading.permissions'))
            ->emptyStateDescription(__('vendra-permission::tables.empty_state.description.permissions'))
            ->emptyStateIcon(Heroicon::OutlinedKey)
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),

                    EditAction::make(),

                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    SyncBulkAction::make()
                        ->visible(fn (): bool => self::canUseBulkRoleAssignment()),

                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort(column: 'id', direction: 'desc');
    }

    private static function canUseBulkRoleAssignment(): bool
    {
        $tenant = resolve(TenantResolver::class)->current();

        return Feature::for($tenant)->active(PermissionFeatureEnum::ModuleEnabled->value)
            && Feature::for($tenant)->active(PermissionFeatureEnum::BulkRoleAssignment->value);
    }
}
