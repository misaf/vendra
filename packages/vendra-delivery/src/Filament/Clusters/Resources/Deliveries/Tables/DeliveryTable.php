<?php

declare(strict_types=1);

namespace Misaf\VendraDelivery\Filament\Clusters\Resources\Deliveries\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class DeliveryTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('row')
                    ->label('#')
                    ->rowIndex()
                    ->sortable(['id']),

                TextColumn::make('order.number')
                    ->copyable()
                    ->icon(Heroicon::Hashtag)
                    ->label(__('vendra-delivery::attributes.order'))
                    ->searchable(),

                TextColumn::make('recipient_name')
                    ->label(__('vendra-delivery::attributes.recipient_name'))
                    ->placeholder('—')
                    ->searchable(),

                TextColumn::make('scheduled_for')
                    ->alignCenter()
                    ->badge()
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('vendra-delivery::attributes.scheduled_for'))
                    ->placeholder('—')
                    ->sortable()
                    ->when(
                        app()->isLocale('fa'),
                        fn (TextColumn $column) => $column->jalaliDate('Y-m-d', latinNumbers: true),
                        fn (TextColumn $column) => $column->date('Y-m-d'),
                    ),

                TextColumn::make('deliverySlot.name')
                    ->label(__('vendra-delivery::attributes.delivery_slot'))
                    ->placeholder('—'),

                TextColumn::make('deliveryZone.name')
                    ->label(__('vendra-delivery::attributes.delivery_zone'))
                    ->placeholder('—'),

                TextColumn::make('distance_km')
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('vendra-delivery::attributes.distance_km'))
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('fee_amount')
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('vendra-delivery::attributes.fee_amount')),

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
            ->description(__('vendra-delivery::tables.description.deliveries'))
            ->emptyStateHeading(__('vendra-delivery::tables.empty_state.heading.deliveries'))
            ->emptyStateDescription(__('vendra-delivery::tables.empty_state.description.deliveries'))
            ->emptyStateIcon(Heroicon::OutlinedTruck)
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['order', 'deliverySlot', 'deliveryZone']))
            ->filters([
                QueryBuilder::make()
                    ->constraints([
                        TextConstraint::make('recipient_name')
                            ->label(__('vendra-delivery::attributes.recipient_name')),

                        DateConstraint::make('scheduled_for')
                            ->label(__('vendra-delivery::attributes.scheduled_for')),
                    ]),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->defaultSort(column: 'scheduled_for', direction: 'asc');
    }
}
