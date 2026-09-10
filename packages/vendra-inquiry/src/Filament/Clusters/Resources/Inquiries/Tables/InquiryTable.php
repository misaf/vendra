<?php

declare(strict_types=1);

namespace Misaf\VendraInquiry\Filament\Clusters\Resources\Inquiries\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\SelectConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Table;
use Misaf\VendraInquiry\Enums\InquiryStatusEnum;
use Misaf\VendraInquiry\Filament\Clusters\Resources\Inquiries\Actions\AnswerInquiryAction;
use Misaf\VendraInquiry\Filament\Clusters\Resources\Inquiries\Actions\CloseInquiryAction;
use Misaf\VendraInquiry\Filament\Clusters\Resources\Inquiries\Actions\ReopenInquiryAction;

final class InquiryTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('row')
                    ->label('#')
                    ->rowIndex()
                    ->sortable(['id']),

                TextColumn::make('name')
                    ->icon(Heroicon::User)
                    ->label(__('vendra-inquiry::attributes.name'))
                    ->searchable(),

                TextColumn::make('email')
                    ->copyable()
                    ->label(__('vendra-inquiry::attributes.email'))
                    ->searchable(),

                TextColumn::make('occasion')
                    ->badge()
                    ->label(__('vendra-inquiry::attributes.occasion'))
                    ->placeholder('—'),

                TextColumn::make('status')
                    ->badge()
                    ->label(__('vendra-inquiry::attributes.status')),

                TextColumn::make('message')
                    ->label(__('vendra-inquiry::attributes.message'))
                    ->limit(60)
                    ->searchable()
                    ->tooltip(fn (string $state): string => $state),

                TextColumn::make('created_at')
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('vendra-inquiry::attributes.created_at'))
                    ->sinceTooltip()
                    ->sortable()
                    ->when(
                        app()->isLocale('fa'),
                        fn (TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                        fn (TextColumn $column) => $column->dateTime('Y-m-d H:i'),
                    ),
            ])
            ->description(__('vendra-inquiry::tables.description.inquiries'))
            ->emptyStateHeading(__('vendra-inquiry::tables.empty_state.heading.inquiries'))
            ->emptyStateDescription(__('vendra-inquiry::tables.empty_state.description.inquiries'))
            ->emptyStateIcon(Heroicon::OutlinedInbox)
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),

                    AnswerInquiryAction::make(),

                    CloseInquiryAction::make(),

                    ReopenInquiryAction::make(),
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
                        TextConstraint::make('email')
                            ->label(__('vendra-inquiry::attributes.email')),

                        SelectConstraint::make('status')
                            ->label(__('vendra-inquiry::attributes.status'))
                            ->options(InquiryStatusEnum::class),

                        DateConstraint::make('created_at')
                            ->label(__('vendra-inquiry::attributes.created_at')),
                    ]),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->defaultSort(column: 'id', direction: 'desc');
    }
}
