<?php

declare(strict_types=1);

namespace Misaf\VendraTagger\Filament\Clusters\Resources\Taggers\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Table;

final class TaggerTable
{
    public static function configure(Table $table): Table
    {
        $columns = [
            TextColumn::make('row')
                ->label('#')
                ->rowIndex()
                ->sortable(['id']),

            TextColumn::make('name')
                ->alignStart()
                ->label(__('vendra-tagger::attributes.name'))
                ->icon(Heroicon::Tag),

            TextColumn::make('slug')
                ->alignStart()
                ->label(__('vendra-tagger::attributes.slug'))
                ->icon(Heroicon::Link)
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('type')
                ->alignStart()
                ->label(__('vendra-tagger::attributes.type'))
                ->icon(Heroicon::Tag),

            TextColumn::make('created_at')
                ->extraCellAttributes(['dir' => 'ltr'])
                ->label(__('vendra-tagger::attributes.created_at'))
                ->sinceTooltip()
                ->sortable()
                ->when(
                    app()->isLocale('fa'),
                    fn (TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                    fn (TextColumn $column) => $column->dateTime('Y-m-d H:i')
                ),

            TextColumn::make('updated_at')
                ->extraCellAttributes(['dir' => 'ltr'])
                ->label(__('vendra-tagger::attributes.updated_at'))
                ->sinceTooltip()
                ->when(
                    app()->isLocale('fa'),
                    fn (TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                    fn (TextColumn $column) => $column->dateTime('Y-m-d H:i')
                ),
        ];

        return $table
            ->columns($columns)
            ->description(__('vendra-tagger::tables.description.taggers'))
            ->emptyStateHeading(__('vendra-tagger::tables.empty_state.heading.taggers'))
            ->emptyStateDescription(__('vendra-tagger::tables.empty_state.description.taggers'))
            ->emptyStateIcon(Heroicon::OutlinedHashtag)
            ->filters(
                [
                    QueryBuilder::make()
                        ->constraints([
                            TextConstraint::make('name')
                                ->label(__('vendra-tagger::attributes.name')),

                            TextConstraint::make('slug')
                                ->label(__('vendra-tagger::attributes.slug')),

                            TextConstraint::make('type')
                                ->label(__('vendra-tagger::attributes.type')),

                            DateConstraint::make('created_at')
                                ->label(__('vendra-tagger::attributes.created_at')),

                            DateConstraint::make('updated_at')
                                ->label(__('vendra-tagger::attributes.updated_at')),

                            NumberConstraint::make('position'),
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
            ->defaultSort(column: 'id', direction: 'desc')
            ->reorderable(column: 'position', direction: 'desc');
    }
}
