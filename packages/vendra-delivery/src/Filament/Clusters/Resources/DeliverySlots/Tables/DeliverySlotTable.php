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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Table;
use Misaf\VendraDelivery\Filament\Clusters\Resources\DeliverySlots\DeliverySlotResource;
use Misaf\VendraDelivery\Models\DeliverySlot;
use Misaf\VendraSupport\Filament\Tables\Columns\CreatedAtColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\IsActiveToggleColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\RowIndexColumn;
use Misaf\VendraSupport\Filament\Tables\Filters\QueryBuilder\Constraints\IsActiveConstraint;

final class DeliverySlotTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                RowIndexColumn::make()
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

                IsActiveToggleColumn::make()
                    ->disabled(fn (DeliverySlot $record): bool => ! DeliverySlotResource::canEdit($record)),

                CreatedAtColumn::make()
                    ->sortable(),
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
                        IsActiveConstraint::make(),
                    ]),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->defaultSort(column: 'position', direction: 'asc')
            ->reorderable('position');
    }
}
