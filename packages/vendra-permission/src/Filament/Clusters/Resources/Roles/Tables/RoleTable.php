<?php

declare(strict_types=1);

namespace Misaf\VendraPermission\Filament\Clusters\Resources\Roles\Tables;

use Awcodes\BadgeableColumn\Components\Badge;
use Awcodes\BadgeableColumn\Components\BadgeableColumn;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\QueryBuilder\Constraints\SelectConstraint;
use Filament\Support\Enums\Size;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\Layout\Component as LayoutComponent;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Number;
use Misaf\VendraPermission\Models\Role;

final class RoleTable
{
    public static function configure(Table $table): Table
    {
        $guards = array_map(strval(...), array_keys(Config::array('auth.guards')));

        /**
         * @var array<int, Column|ColumnGroup|LayoutComponent> $columns
         */
        $columns = [
            TextColumn::make('row')
                ->label('#')
                ->rowIndex()
                ->sortable(['id']),

            BadgeableColumn::make('name')
                ->alignStart()
                ->label(__('vendra-permission::table.columns.name'))
                ->icon(Heroicon::Tag)
                ->searchable()
                ->suffixBadges([
                    Badge::make('count')
                        ->label(fn (Role $record): string => (string) Number::format($record->permissions_count))
                        ->size(Size::Small),
                ])
                ->suffix(''),

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
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withCount('permissions'))
            ->columns($columns)
            ->filters(
                [
                    QueryBuilder::make()
                        ->constraints([
                            TextConstraint::make('name')
                                ->label(__('vendra-permission::table.columns.name')),

                            SelectConstraint::make('guard_name')
                                ->label(__('vendra-permission::table.columns.guard_name'))
                                ->options(array_combine($guards, $guards))
                                ->multiple(),
                        ]),
                ],
                layout: FiltersLayout::AboveContentCollapsible,
            )
            ->description(__('vendra-permission::tables.description.roles'))
            ->emptyStateHeading(__('vendra-permission::tables.empty_state.heading.roles'))
            ->emptyStateDescription(__('vendra-permission::tables.empty_state.description.roles'))
            ->emptyStateIcon(Heroicon::OutlinedShieldCheck)
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),

                    EditAction::make(),

                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort(column: 'id', direction: 'desc');
    }
}
