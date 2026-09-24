<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Filament\Clusters\Resources\TransactionGateways\Tables;

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
use Livewire\Component as Livewire;
use Misaf\VendraMultimedia\Filament\Tables\Columns\ModelImageColumn;
use Misaf\VendraSupport\Filament\Concerns\HasDefaultAvatarImageUrl;
use Misaf\VendraSupport\Filament\Concerns\InteractsWithTranslatedTableRecords;
use Misaf\VendraSupport\Filament\Tables\Columns\CreatedAtColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\DescriptionColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\IsActiveToggleColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\IsDefaultIconColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\NameColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\RowIndexColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\SlugColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\UpdatedAtColumn;
use Misaf\VendraSupport\Filament\Tables\Filters\QueryBuilder\Constraints\IsActiveConstraint;
use Misaf\VendraSupport\Filament\Tables\Filters\QueryBuilder\Constraints\IsDefaultConstraint;
use Misaf\VendraSupport\Filament\Tables\Filters\QueryBuilder\Constraints\NameConstraint;
use Misaf\VendraSupport\Filament\Tables\Filters\QueryBuilder\Constraints\SlugConstraint;
use Misaf\VendraTransaction\Filament\Clusters\Resources\TransactionGateways\Actions\SetDefaultTransactionGatewayTableAction;
use Misaf\VendraTransaction\Models\TransactionGateway;

final class TransactionGatewayTable
{
    use HasDefaultAvatarImageUrl;
    use InteractsWithTranslatedTableRecords;

    public static function configure(Table $table): Table
    {
        return $table
            ->description(__('vendra-transaction::tables.description.transaction_gateways'))
            ->emptyStateHeading(__('vendra-transaction::tables.empty_state.heading.transaction_gateways'))
            ->emptyStateDescription(__('vendra-transaction::tables.empty_state.description.transaction_gateways'))
            ->emptyStateIcon(Heroicon::OutlinedCreditCard)
            ->columns([
                RowIndexColumn::make(),

                ModelImageColumn::make()
                    ->collection(TransactionGateway::MEDIA_COLLECTION)
                    ->defaultImageUrl(fn (TransactionGateway $record, Livewire $livewire): string => self::defaultAvatarImageUrl(self::translatedAttribute($record, 'name', $livewire))),

                NameColumn::make()
                    ->searchable(),

                IsDefaultIconColumn::make(),

                DescriptionColumn::make()
                    ->state(fn (TransactionGateway $record, Livewire $livewire): string => self::translatedAttribute($record, 'description', $livewire)),

                SlugColumn::make()
                    ->searchable(),

                TextColumn::make('transactions_count')
                    ->alignCenter()
                    ->badge()
                    ->counts('transactions')
                    ->label(__('vendra-transaction::navigation.transactions')),

                IsActiveToggleColumn::make(),

                CreatedAtColumn::make(),

                UpdatedAtColumn::make(),
            ])
            ->filters(
                [
                    QueryBuilder::make()
                        ->constraints([
                            NameConstraint::make(),

                            SlugConstraint::make(),

                            IsActiveConstraint::make(),

                            IsDefaultConstraint::make(),
                        ]),
                ],
                layout: FiltersLayout::AboveContentCollapsible,
            )
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),

                    EditAction::make(),

                    SetDefaultTransactionGatewayTableAction::make(),

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
