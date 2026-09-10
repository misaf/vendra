<?php

declare(strict_types=1);

namespace Misaf\VendraUserProfile\Filament\Clusters\Resources\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\Layout\Component as LayoutComponent;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\BooleanConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\RelationshipConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\RelationshipConstraint\Operators\IsRelatedToOperator;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Table;

final class UserProfileTable
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

            TextColumn::make('user.username')
                ->alignStart()
                ->badge()
                ->expandableLimitedList()
                ->icon(Heroicon::UserGroup)
                ->label(__('vendra-user-profile::table.columns.user'))
                ->limitList(2)
                ->listWithLineBreaks(),

            TextColumn::make('name')
                ->alignStart()
                ->label(__('vendra-user-profile::table.columns.name'))
                ->icon(Heroicon::Tag)
                ->searchable()
                ->sortable(),

            TextColumn::make('description')
                ->label(__('vendra-user-profile::table.columns.description'))
                ->icon(Heroicon::DocumentText)
                ->toggleable(isToggledHiddenByDefault: true),

            ToggleColumn::make('active')
                ->label(__('vendra-user-profile::attributes.active'))
                ->onIcon(Heroicon::Bolt),

            TextColumn::make('created_at')
                ->extraCellAttributes(['dir' => 'ltr'])
                ->label(__('vendra-user-profile::table.columns.created_at'))
                ->sinceTooltip()
                ->when(
                    app()->isLocale('fa'),
                    fn (TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                    fn (TextColumn $column) => $column->dateTime('Y-m-d H:i')
                ),

            TextColumn::make('updated_at')
                ->extraCellAttributes(['dir' => 'ltr'])
                ->label(__('vendra-user-profile::table.columns.updated_at'))
                ->sinceTooltip()
                ->when(
                    app()->isLocale('fa'),
                    fn (TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                    fn (TextColumn $column) => $column->dateTime('Y-m-d H:i')
                ),
        ];

        return $table
            ->columns($columns)
            ->description(__('vendra-user-profile::tables.description.user_profiles'))
            ->emptyStateHeading(__('vendra-user-profile::tables.empty_state.heading.user_profiles'))
            ->emptyStateDescription(__('vendra-user-profile::tables.empty_state.description.user_profiles'))
            ->emptyStateIcon(Heroicon::OutlinedIdentification)
            ->filters(
                [
                    QueryBuilder::make()
                        ->constraints([
                            TextConstraint::make('name')
                                ->label(__('vendra-user-profile::table.columns.name')),

                            RelationshipConstraint::make('user')
                                ->selectable(
                                    IsRelatedToOperator::make()
                                        ->preload()
                                        ->searchable()
                                        ->titleAttribute('username'),
                                ),
                            TextConstraint::make('slug'),
                            BooleanConstraint::make('is_default'),
                            BooleanConstraint::make('active'),
                        ]),
                ],
                layout: FiltersLayout::AboveContentCollapsible,
            )
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
