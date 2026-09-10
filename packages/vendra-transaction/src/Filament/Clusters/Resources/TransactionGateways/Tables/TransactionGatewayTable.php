<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Filament\Clusters\Resources\TransactionGateways\Tables;

use Awcodes\BadgeableColumn\Components\Badge;
use Awcodes\BadgeableColumn\Components\BadgeableColumn;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Size;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\BooleanConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Table;
use Livewire\Component as Livewire;
use Misaf\VendraSupport\Filament\Concerns\HasDefaultAvatarImageUrl;
use Misaf\VendraSupport\Filament\Concerns\InteractsWithTranslatedTableRecords;
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
                TextColumn::make('row')
                    ->label('#')
                    ->rowIndex()
                    ->sortable(['id']),

                SpatieMediaLibraryImageColumn::make('image')
                    ->alignCenter()
                    ->collection(TransactionGateway::MEDIA_COLLECTION)
                    ->conversion('thumb-table')
                    ->defaultImageUrl(fn (TransactionGateway $record): string => self::defaultAvatarImageUrl($record->name))
                    ->extraImgAttributes(['class' => 'saturate-50', 'loading' => 'lazy'])
                    ->label(__('vendra-transaction::attributes.image'))
                    ->stacked(),

                BadgeableColumn::make('name')
                    ->alignStart()
                    ->label(__('vendra-transaction::attributes.name'))
                    ->icon(Heroicon::Tag)
                    ->searchable()
                    ->prefixBadges([
                        Badge::make('is_default')
                            ->label(__('vendra-transaction::attributes.is_default'))
                            ->color('success')
                            ->size(Size::ExtraSmall)
                            ->hidden(fn (TransactionGateway $record): bool => ! $record->is_default),
                    ]),

                TextColumn::make('description')
                    ->label(__('vendra-transaction::attributes.description'))
                    ->icon(Heroicon::DocumentText)
                    ->state(fn (TransactionGateway $record, Livewire $livewire): string => self::translatedAttribute($record, 'description', $livewire))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('slug')
                    ->alignStart()
                    ->label(__('vendra-transaction::attributes.slug'))
                    ->icon(Heroicon::Link)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('transactions_count')
                    ->alignCenter()
                    ->badge()
                    ->counts('transactions')
                    ->label(__('vendra-transaction::navigation.transactions')),

                ToggleColumn::make('active')
                    ->label(__('vendra-transaction::attributes.active'))
                    ->onIcon(Heroicon::Bolt),

                TextColumn::make('created_at')
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('vendra-transaction::attributes.created_at'))
                    ->sinceTooltip()
                    ->when(
                        app()->isLocale('fa'),
                        fn (TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                        fn (TextColumn $column) => $column->dateTime('Y-m-d H:i'),
                    ),

                TextColumn::make('updated_at')
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('vendra-transaction::attributes.updated_at'))
                    ->sinceTooltip()
                    ->when(
                        app()->isLocale('fa'),
                        fn (TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                        fn (TextColumn $column) => $column->dateTime('Y-m-d H:i'),
                    ),
            ])
            ->filters(
                [
                    QueryBuilder::make()
                        ->constraints([
                            TextConstraint::make('name')
                                ->label(__('vendra-transaction::attributes.name')),

                            TextConstraint::make('slug')
                                ->label(__('vendra-transaction::attributes.slug')),

                            BooleanConstraint::make('active')
                                ->label(__('vendra-transaction::attributes.active')),

                            BooleanConstraint::make('is_default')
                                ->label(__('vendra-transaction::attributes.is_default')),
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
