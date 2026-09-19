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
use Misaf\VendraInquiry\Filament\Clusters\Resources\Inquiries\Actions\AnswerInquiryTableAction;
use Misaf\VendraInquiry\Filament\Clusters\Resources\Inquiries\Actions\CloseInquiryTableAction;
use Misaf\VendraInquiry\Filament\Clusters\Resources\Inquiries\Actions\ReopenInquiryTableAction;
use Misaf\VendraInquiry\States\InquiryState;
use Misaf\VendraSupport\Filament\Tables\Columns\CreatedAtColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\RowIndexColumn;

final class InquiryTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                RowIndexColumn::make(),

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
                    ->color(fn (InquiryState $state): array => $state->getColor())
                    ->formatStateUsing(fn (InquiryState $state): string => $state->getLabel())
                    ->icon(fn (InquiryState $state): Heroicon => $state->getIcon())
                    ->label(__('vendra-inquiry::attributes.status')),

                TextColumn::make('message')
                    ->label(__('vendra-inquiry::attributes.message'))
                    ->limit(60)
                    ->searchable()
                    ->tooltip(fn (string $state): string => $state),

                CreatedAtColumn::make()
                    ->sortable(),
            ])
            ->description(__('vendra-inquiry::tables.description.inquiries'))
            ->emptyStateHeading(__('vendra-inquiry::tables.empty_state.heading.inquiries'))
            ->emptyStateDescription(__('vendra-inquiry::tables.empty_state.description.inquiries'))
            ->emptyStateIcon(Heroicon::OutlinedInbox)
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),

                    AnswerInquiryTableAction::make(),

                    CloseInquiryTableAction::make(),

                    ReopenInquiryTableAction::make(),
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
                            ->options(InquiryState::options()),

                        DateConstraint::make('created_at')
                            ->label(__('vendra-inquiry::attributes.created_at')),
                    ]),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->defaultSort(column: 'id', direction: 'desc');
    }
}
