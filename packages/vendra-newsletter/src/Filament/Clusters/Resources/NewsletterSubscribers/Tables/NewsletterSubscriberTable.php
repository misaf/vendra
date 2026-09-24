<?php

declare(strict_types=1);

namespace Misaf\VendraNewsletter\Filament\Clusters\Resources\NewsletterSubscribers\Tables;

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
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Misaf\VendraNewsletter\Actions\ResubscribeNewsletterSubscriberAction;
use Misaf\VendraNewsletter\Actions\UnsubscribeNewsletterSubscriberAction;
use Misaf\VendraNewsletter\Filament\Clusters\Resources\NewsletterSubscribers\NewsletterSubscriberResource;
use Misaf\VendraNewsletter\Models\NewsletterSubscriber;
use Misaf\VendraSupport\Filament\Tables\Columns\CreatedAtColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\NameColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\RowIndexColumn;

final class NewsletterSubscriberTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                RowIndexColumn::make(),

                TextColumn::make('email')
                    ->alignStart()
                    ->copyable()
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('vendra-newsletter::attributes.email'))
                    ->icon(Heroicon::Envelope)
                    ->searchable(),

                NameColumn::make()
                    ->placeholder('—')
                    ->searchable(),

                ToggleColumn::make('subscribed')
                    ->alignCenter()
                    ->disabled(fn (NewsletterSubscriber $record): bool => ! NewsletterSubscriberResource::canEdit($record))
                    ->label(__('vendra-newsletter::attributes.active'))
                    ->onIcon(Heroicon::Bolt)
                    ->state(fn (NewsletterSubscriber $record): bool => $record->isSubscribed())
                    ->updateStateUsing(function (NewsletterSubscriber $record, bool $state): bool {
                        $state
                            ? resolve(ResubscribeNewsletterSubscriberAction::class)->execute($record)
                            : resolve(UnsubscribeNewsletterSubscriberAction::class)->execute($record);

                        return $state;
                    }),

                TextColumn::make('subscribed_at')
                    ->alignCenter()
                    ->badge()
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('vendra-newsletter::attributes.subscribed_at'))
                    ->placeholder('—')
                    ->toggleable()
                    ->when(
                        app()->isLocale('fa'),
                        fn (TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                        fn (TextColumn $column) => $column->dateTime('Y-m-d H:i')
                    ),

                TextColumn::make('unsubscribed_at')
                    ->alignCenter()
                    ->badge()
                    ->color('danger')
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('vendra-newsletter::attributes.unsubscribed_at'))
                    ->placeholder('—')
                    ->toggleable()
                    ->when(
                        app()->isLocale('fa'),
                        fn (TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                        fn (TextColumn $column) => $column->dateTime('Y-m-d H:i')
                    ),

                CreatedAtColumn::make(),
            ])
            ->filters(
                [
                    TernaryFilter::make('subscribed')
                        ->label(__('vendra-newsletter::attributes.active'))
                        ->trueLabel(__('vendra-newsletter::attributes.active'))
                        ->falseLabel(__('vendra-newsletter::attributes.inactive'))
                        ->queries(
                            true: fn (Builder $query): Builder => self::subscribed($query),
                            false: fn (Builder $query): Builder => self::unsubscribed($query),
                        ),
                    QueryBuilder::make()
                        ->constraints([
                            TextConstraint::make('email')
                                ->label(__('vendra-newsletter::attributes.email')),
                        ]),
                ],
                layout: FiltersLayout::AboveContentCollapsible,
            )
            ->description(__('vendra-newsletter::tables.description.newsletter_subscribers'))
            ->emptyStateHeading(__('vendra-newsletter::tables.empty_state.heading.newsletter_subscribers'))
            ->emptyStateDescription(__('vendra-newsletter::tables.empty_state.description.newsletter_subscribers'))
            ->emptyStateIcon(Heroicon::OutlinedUserGroup)
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
            ->defaultSort(column: 'id', direction: 'desc');
    }

    /**
     * @param  Builder<NewsletterSubscriber>  $query
     * @return Builder<NewsletterSubscriber>
     */
    private static function subscribed(Builder $query): Builder
    {
        return $query->subscribed();
    }

    /**
     * @param  Builder<NewsletterSubscriber>  $query
     * @return Builder<NewsletterSubscriber>
     */
    private static function unsubscribed(Builder $query): Builder
    {
        return $query->unsubscribed();
    }
}
