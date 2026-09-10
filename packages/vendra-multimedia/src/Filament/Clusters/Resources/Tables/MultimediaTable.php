<?php

declare(strict_types=1);

namespace Misaf\VendraMultimedia\Filament\Clusters\Resources\Tables;

use Awcodes\BadgeableColumn\Components\Badge;
use Awcodes\BadgeableColumn\Components\BadgeableColumn;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Size;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\Layout\Component as LayoutComponent;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Table;
use Illuminate\Support\Number;
use Misaf\VendraMultimedia\Models\Multimedia;

final class MultimediaTable
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

            BadgeableColumn::make('model_type')
                ->alignStart()
                ->label(__('vendra-multimedia::tables.model_type'))
                ->suffixBadges([
                    Badge::make('count')
                        ->label(fn (Multimedia $record): string => Number::format((int) $record->model_id) ?: '0')
                        ->size(Size::Small),
                ])
                ->suffix(''),

            TextColumn::make('uuid')
                ->alignStart()
                ->badge()
                ->label(__('vendra-multimedia::tables.uuid'))
                ->icon(Heroicon::FingerPrint),

            BadgeableColumn::make('collection_name')
                ->alignCenter()
                ->description(fn (Multimedia $record): string => $record->name)
                ->icon(Heroicon::QueueList)
                ->label(__('vendra-multimedia::tables.collection_name'))
                ->searchable()
                ->sortable(),

            TextColumn::make('file_name')
                ->alignStart()
                ->label(__('vendra-multimedia::tables.file_name'))
                ->searchable()
                ->toggleable(),

            TextColumn::make('mime_type')
                ->alignStart()
                ->label(__('vendra-multimedia::tables.mime_type'))
                ->searchable()
                ->toggleable(),

            TextColumn::make('size')
                ->alignEnd()
                ->formatStateUsing(fn (int $state): string => Number::fileSize($state))
                ->label(__('vendra-multimedia::tables.size'))
                ->sortable()
                ->toggleable(),

            TextColumn::make('created_at')
                ->extraCellAttributes(['dir' => 'ltr'])
                ->label(__('vendra-multimedia::tables.created_at'))
                ->sinceTooltip()
                ->when(
                    app()->isLocale('fa'),
                    fn (TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                    fn (TextColumn $column) => $column->dateTime('Y-m-d H:i')
                ),

            TextColumn::make('updated_at')
                ->extraCellAttributes(['dir' => 'ltr'])
                ->label(__('vendra-multimedia::tables.updated_at'))
                ->sinceTooltip()
                ->when(
                    app()->isLocale('fa'),
                    fn (TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                    fn (TextColumn $column) => $column->dateTime('Y-m-d H:i')
                ),
        ];

        return $table
            ->columns($columns)
            ->description(__('vendra-multimedia::tables.description.media_items'))
            ->emptyStateHeading(__('vendra-multimedia::tables.empty_state.heading.media_items'))
            ->emptyStateDescription(__('vendra-multimedia::tables.empty_state.description.media_items'))
            ->emptyStateIcon(Heroicon::OutlinedPhoto)
            ->filters(
                [
                    QueryBuilder::make()
                        ->constraints([
                            TextConstraint::make('model_type')
                                ->label(__('vendra-multimedia::attributes.model_type')),

                            NumberConstraint::make('model_id')
                                ->label(__('vendra-multimedia::attributes.model_id')),

                            TextConstraint::make('uuid')
                                ->label(__('vendra-multimedia::attributes.uuid')),

                            TextConstraint::make('collection_name')
                                ->label(__('vendra-multimedia::attributes.collection_name')),

                            TextConstraint::make('name')
                                ->label(__('vendra-multimedia::attributes.name')),

                            TextConstraint::make('file_name')
                                ->label(__('vendra-multimedia::attributes.file_name')),

                            TextConstraint::make('mime_type')
                                ->label(__('vendra-multimedia::attributes.mime_type')),

                            TextConstraint::make('disk')
                                ->label(__('vendra-multimedia::attributes.disk')),

                            NumberConstraint::make('size')
                                ->label(__('vendra-multimedia::attributes.size')),

                            NumberConstraint::make('order_column')
                                ->label(__('vendra-multimedia::attributes.order_column')),

                            DateConstraint::make('created_at')
                                ->label(__('vendra-multimedia::attributes.created_at')),

                            DateConstraint::make('updated_at')
                                ->label(__('vendra-multimedia::attributes.updated_at')),
                        ]),
                ],
                layout: FiltersLayout::AboveContentCollapsible,
            )
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                ]),
            ])
            ->defaultSort(column: 'id', direction: 'desc');
    }
}
