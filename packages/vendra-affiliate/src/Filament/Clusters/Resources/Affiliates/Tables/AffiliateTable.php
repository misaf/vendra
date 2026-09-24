<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SpatieTagsColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\RelationshipConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\RelationshipConstraint\Operators\IsRelatedToOperator;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Misaf\VendraAffiliate\Enums\AffiliateStatusEnum;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates\Actions\ProcessPayoutTableAction;
use Misaf\VendraAffiliate\Models\Affiliate;
use Misaf\VendraAffiliate\Models\AffiliateCommission;
use Misaf\VendraSupport\Capabilities\TagIntegration;
use Misaf\VendraSupport\Filament\Tables\Columns\CreatedAtColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\RowIndexColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\UpdatedAtColumn;
use Misaf\VendraTagger\Filament\Tables\Columns\ModelTagsColumn;

final class AffiliateTable
{
    public static function configure(Table $table): Table
    {
        /**
         * @var array<int, TextColumn|SpatieTagsColumn> $columns
         */
        $columns = [
            RowIndexColumn::make(),

            TextColumn::make('user.username')
                ->label(__('vendra-affiliate::attributes.user'))
                ->icon(Heroicon::User)
                ->searchable()
                ->sortable(),

            TextColumn::make('code')
                ->copyable()
                ->copyMessage(__('vendra-affiliate::messages.link_copied'))
                ->copyMessageDuration(1500)
                ->copyableState(fn (Affiliate $record): string => $record->referralUrl())
                ->label(__('vendra-affiliate::attributes.code'))
                ->icon(Heroicon::CodeBracket)
                ->searchable()
                ->tooltip(fn (Affiliate $record): string => $record->referralUrl()),

            TextColumn::make('commission_percent')
                ->alignCenter()
                ->label(__('vendra-affiliate::attributes.commission_percent'))
                ->sortable()
                ->suffix('%'),

            TextColumn::make('pending_balance')
                ->alignCenter()
                ->extraCellAttributes(['dir' => 'ltr'])
                ->label(__('vendra-affiliate::attributes.pending_balance'))
                ->numeric(locale: 'en', maxDecimalPlaces: 0)
                ->state(fn (Affiliate $record): int => $record->pendingBalance()),

            TextColumn::make('status')
                ->alignCenter()
                ->badge()
                ->label(__('vendra-affiliate::attributes.status')),

            CreatedAtColumn::make(),

            UpdatedAtColumn::make(),
        ];

        if (TagIntegration::isAvailable()) {
            $columns[] = ModelTagsColumn::make()
                ->type(Affiliate::TAG_TYPE);
        }

        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withSum([
                'commissions as pending_balance' => fn (Builder $commissionQuery): Builder => self::payableCommissions($commissionQuery),
            ], 'amount'))
            ->columns($columns)
            ->description(__('vendra-affiliate::tables.description.affiliates'))
            ->emptyStateHeading(__('vendra-affiliate::tables.empty_state.heading.affiliates'))
            ->emptyStateDescription(__('vendra-affiliate::tables.empty_state.description.affiliates'))
            ->emptyStateIcon(Heroicon::OutlinedLink)
            ->filters(
                [
                    SelectFilter::make('status')
                        ->label(__('vendra-affiliate::attributes.status'))
                        ->options(AffiliateStatusEnum::class),

                    QueryBuilder::make()
                        ->constraints([
                            RelationshipConstraint::make('user')
                                ->selectable(
                                    IsRelatedToOperator::make()
                                        ->preload()
                                        ->searchable()
                                        ->titleAttribute('email'),
                                ),

                            TextConstraint::make('code')
                                ->label(__('vendra-affiliate::attributes.code')),

                            NumberConstraint::make('commission_percent')
                                ->label(__('vendra-affiliate::attributes.commission_percent')),

                            DateConstraint::make('created_at')
                                ->label(__('vendra-affiliate::attributes.created_at')),
                        ]),
                ],
                layout: FiltersLayout::AboveContentCollapsible,
            )
            ->recordActions([
                ActionGroup::make([
                    ProcessPayoutTableAction::make(),

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

    /**
     * @param  Builder<AffiliateCommission>  $query
     * @return Builder<AffiliateCommission>
     */
    private static function payableCommissions(Builder $query): Builder
    {
        return $query->payable();
    }
}
