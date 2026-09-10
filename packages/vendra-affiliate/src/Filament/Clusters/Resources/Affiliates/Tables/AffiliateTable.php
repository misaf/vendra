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
use Misaf\VendraAffiliate\Enums\CommissionStatusEnum;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates\Actions\ProcessPayoutAction;
use Misaf\VendraAffiliate\Models\Affiliate;
use Misaf\VendraSupport\Capabilities\TagIntegration;

final class AffiliateTable
{
    public static function configure(Table $table): Table
    {
        /**
         * @var array<int, TextColumn|SpatieTagsColumn> $columns
         */
        $columns = [
            TextColumn::make('row')
                ->label('#')
                ->rowIndex()
                ->sortable(['id']),

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

            TextColumn::make('created_at')
                ->extraCellAttributes(['dir' => 'ltr'])
                ->label(__('vendra-affiliate::attributes.created_at'))
                ->sinceTooltip()
                ->when(
                    app()->isLocale('fa'),
                    fn (TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                    fn (TextColumn $column) => $column->dateTime('Y-m-d H:i')
                ),

            TextColumn::make('updated_at')
                ->extraCellAttributes(['dir' => 'ltr'])
                ->label(__('vendra-affiliate::attributes.updated_at'))
                ->sinceTooltip()
                ->when(
                    app()->isLocale('fa'),
                    fn (TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                    fn (TextColumn $column) => $column->dateTime('Y-m-d H:i')
                ),
        ];

        if (TagIntegration::isAvailable()) {
            $columns[] = SpatieTagsColumn::make('tags')
                ->label(__('vendra-support::attributes.tags'))
                ->type(Affiliate::TAG_TYPE)
                ->toggleable();
        }

        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withSum([
                'commissions as pending_balance' => fn (Builder $commissionQuery): Builder => $commissionQuery
                    ->where('status', CommissionStatusEnum::Approved)
                    ->whereNull('affiliate_payout_id'),
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
                    ProcessPayoutAction::make(),

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
}
