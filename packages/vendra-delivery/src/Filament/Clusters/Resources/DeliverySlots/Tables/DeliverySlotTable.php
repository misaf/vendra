<?php

declare(strict_types=1);

namespace Misaf\VendraDelivery\Filament\Clusters\Resources\DeliverySlots\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\BooleanConstraint;
use Filament\Tables\Table;

final class DeliverySlotTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('row')
                    ->label('#')
                    ->rowIndex()
                    ->sortable(['position']),

                TextColumn::make('name')
                    ->icon(Heroicon::Clock)
                    ->label(__('vendra-delivery::attributes.name'))
                    ->searchable(),

                TextColumn::make('starts_at')
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('vendra-delivery::attributes.starts_at')),

                TextColumn::make('ends_at')
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('vendra-delivery::attributes.ends_at')),

                TextColumn::make('capacity')
                    ->label(__('vendra-delivery::attributes.capacity'))
                    ->placeholder('∞'),

                IconColumn::make('active')
                    ->boolean()
                    ->label(__('vendra-delivery::attributes.active')),

                TextColumn::make('created_at')
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('vendra-delivery::attributes.created_at'))
                    ->sinceTooltip()
                    ->sortable()
                    ->when(
                        app()->isLocale('fa'),
                        fn (TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                        fn (TextColumn $column) => $column->dateTime('Y-m-d H:i'),
                    ),
            ])
            ->description(__('vendra-delivery::tables.description.delivery_slots'))
            ->emptyStateHeading(__('vendra-delivery::tables.empty_state.heading.delivery_slots'))
            ->emptyStateDescription(__('vendra-delivery::tables.empty_state.description.delivery_slots'))
            ->emptyStateIcon(Heroicon::OutlinedClock)
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
            ->filters([
                QueryBuilder::make()
                    ->constraints([
                        BooleanConstraint::make('active')
                            ->label(__('vendra-delivery::attributes.active')),
                    ]),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->defaultSort(column: 'position', direction: 'asc')
            ->reorderable('position');
    }
}
