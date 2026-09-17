<?php

declare(strict_types=1);

namespace Misaf\VendraDelivery\Filament\Clusters\Resources\DeliveryZones\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\BooleanConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Table;
use Misaf\VendraDelivery\Filament\Clusters\Resources\DeliveryZones\DeliveryZoneResource;
use Misaf\VendraDelivery\Models\DeliveryZone;
use Misaf\VendraSupport\Filament\Tables\Columns\CreatedAtColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\IsActiveToggleColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\RowIndexColumn;
use Misaf\VendraSupport\Filament\Tables\Filters\QueryBuilder\Constraints\IsActiveConstraint;

final class DeliveryZoneTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                RowIndexColumn::make()
                    ->sortable(['position']),

                TextColumn::make('name')
                    ->icon(Heroicon::MapPin)
                    ->label(__('vendra-delivery::attributes.name'))
                    ->searchable(),

                TextColumn::make('max_distance_km')
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('vendra-delivery::attributes.max_distance_km'))
                    ->placeholder('∞')
                    ->sortable(),

                TextColumn::make('fee_amount')
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('vendra-delivery::attributes.fee_amount')),

                ToggleColumn::make('requires_quote')
                    ->disabled(fn (DeliveryZone $record): bool => ! DeliveryZoneResource::canEdit($record))
                    ->label(__('vendra-delivery::attributes.requires_quote'))
                    ->onIcon(Heroicon::Bolt),

                IsActiveToggleColumn::make()
                    ->disabled(fn (DeliveryZone $record): bool => ! DeliveryZoneResource::canEdit($record)),

                CreatedAtColumn::make()
                    ->sortable(),
            ])
            ->description(__('vendra-delivery::tables.description.delivery_zones'))
            ->emptyStateHeading(__('vendra-delivery::tables.empty_state.heading.delivery_zones'))
            ->emptyStateDescription(__('vendra-delivery::tables.empty_state.description.delivery_zones'))
            ->emptyStateIcon(Heroicon::OutlinedMapPin)
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
                        NumberConstraint::make('max_distance_km')
                            ->label(__('vendra-delivery::attributes.max_distance_km')),

                        BooleanConstraint::make('requires_quote')
                            ->label(__('vendra-delivery::attributes.requires_quote')),

                        IsActiveConstraint::make(),
                    ]),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->defaultSort(column: 'position', direction: 'asc')
            ->reorderable('position');
    }
}
