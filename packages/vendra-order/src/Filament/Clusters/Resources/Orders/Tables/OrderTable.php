<?php

declare(strict_types=1);

namespace Misaf\VendraOrder\Filament\Clusters\Resources\Orders\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\SelectConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Misaf\VendraOrder\Filament\Clusters\Resources\Orders\Actions\CancelOrderTableAction;
use Misaf\VendraOrder\Filament\Clusters\Resources\Orders\Actions\CompleteOrderTableAction;
use Misaf\VendraOrder\Filament\Clusters\Resources\Orders\Actions\ConfirmOrderTableAction;
use Misaf\VendraOrder\Models\Order;
use Misaf\VendraOrder\States\OrderState;

final class OrderTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('row')
                    ->label('#')
                    ->rowIndex()
                    ->sortable(['id']),

                TextColumn::make('number')
                    ->copyable()
                    ->icon(Heroicon::Hashtag)
                    ->label(__('vendra-order::attributes.number'))
                    ->searchable(),

                TextColumn::make('customer_label')
                    ->label(__('vendra-order::attributes.customer'))
                    ->placeholder('—'),

                TextColumn::make('lines_count')
                    ->badge()
                    ->counts('lines')
                    ->label(__('vendra-order::attributes.lines')),

                TextColumn::make('total_amount')
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('vendra-order::attributes.total_amount'))
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (OrderState $state): array => $state->getColor())
                    ->formatStateUsing(fn (OrderState $state): string => $state->getLabel())
                    ->icon(fn (OrderState $state) => $state->getIcon())
                    ->label(__('vendra-order::attributes.status')),

                TextColumn::make('payment_reference')
                    ->label(__('vendra-order::attributes.payment_reference'))
                    ->placeholder('—')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('placed_at')
                    ->alignCenter()
                    ->badge()
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('vendra-order::attributes.placed_at'))
                    ->placeholder('—')
                    ->sinceTooltip()
                    ->sortable()
                    ->when(
                        app()->isLocale('fa'),
                        fn (TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                        fn (TextColumn $column) => $column->dateTime('Y-m-d H:i'),
                    ),

                TextColumn::make('created_at')
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('vendra-order::attributes.created_at'))
                    ->sinceTooltip()
                    ->sortable()
                    ->when(
                        app()->isLocale('fa'),
                        fn (TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                        fn (TextColumn $column) => $column->dateTime('Y-m-d H:i'),
                    ),
            ])
            ->description(__('vendra-order::tables.description.orders'))
            ->emptyStateHeading(__('vendra-order::tables.empty_state.heading.orders'))
            ->emptyStateDescription(__('vendra-order::tables.empty_state.description.orders'))
            ->emptyStateIcon(Heroicon::OutlinedShoppingBag)
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),

                    ConfirmOrderTableAction::make(),

                    CompleteOrderTableAction::make(),

                    CancelOrderTableAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('customer'))
            ->filters([
                QueryBuilder::make()
                    ->constraints([
                        TextConstraint::make('number')
                            ->label(__('vendra-order::attributes.number')),

                        SelectConstraint::make('status')
                            ->label(__('vendra-order::attributes.status'))
                            ->options(self::statusOptions()),

                        NumberConstraint::make('total_amount')
                            ->label(__('vendra-order::attributes.total_amount')),

                        DateConstraint::make('placed_at')
                            ->label(__('vendra-order::attributes.placed_at')),
                    ]),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->defaultSort(column: 'id', direction: 'desc');
    }

    /**
     * The selectable lifecycle states, keyed by the value persisted in the
     * status column.
     *
     * @return array<string, string>
     */
    private static function statusOptions(): array
    {
        $options = [];

        foreach (OrderState::all() as $state) {
            if (! is_string($state) || ! is_subclass_of($state, OrderState::class)) {
                continue;
            }

            $options[$state::getMorphClass()] = new $state(new Order)->getLabel();
        }

        return $options;
    }
}
