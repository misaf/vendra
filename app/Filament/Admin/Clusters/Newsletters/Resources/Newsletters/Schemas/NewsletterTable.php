<?php

declare(strict_types=1);

namespace App\Filament\Admin\Clusters\Newsletters\Resources\Newsletters\Schemas;

use Awcodes\BadgeableColumn\Components\Badge;
use Awcodes\BadgeableColumn\Components\BadgeableColumn;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Size;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\BooleanConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;
use Misaf\Newsletter\Models\Newsletter;

final class NewsletterTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // ->modifyQueryUsing(fn(Builder $query) => $query->withCount(['newsletterPosts', 'newsletterSubscribers',  'newsletterSubscribedUsers', 'newsletterUnsubscribedUsers']))
            ->columns([
                TextColumn::make('row')
                    ->label('#')
                    ->rowIndex(),

                TextColumn::make('name')
                    ->label(__('newsletter::attributes.name'))
                    ->searchable(),

                TextColumn::make('slug')
                    ->label(__('newsletter::attributes.slug'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('newsletter_posts_count')
                    ->badge()
                    ->formatStateUsing(fn(int $state) => Number::format($state))
                    ->label(__('newsletter::attributes.post_count'))
                    ->sortable(),

                BadgeableColumn::make('newsletter_subscribers_count')
                    ->alignCenter()
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(function () {
                        $totalSubscribers = __('newsletter::attributes.total_subscribers');
                        $subscribedCount = __('newsletter::attributes.subscribed_count');
                        $unsubscribedCount = __('newsletter::attributes.unsubscribed_count');
                        return $totalSubscribers . ' : ' . $subscribedCount . ' / ' . $unsubscribedCount;
                    })
                    ->prefixBadges([
                        Badge::make('unsubscribed')
                            ->label(fn(Newsletter $record) => Number::format($record->newsletter_unsubscribed_users_count))
                            ->color('danger')
                            ->size(Size::Small),

                        Badge::make('subscribed')
                            ->label(fn(Newsletter $record) => Number::format($record->newsletter_subscribed_users_count))
                            ->color('success')
                            ->size(Size::Small),
                    ])
                    ->separator(':'),

                TextColumn::make('scheduled_at')
                    ->alignCenter()
                    ->badge()
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('newsletter::attributes.scheduled_at'))
                    ->sinceTooltip()
                    ->sortable()
                    ->unless(app()->isLocale('fa'), fn(TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', toLatin: true), fn(TextColumn $column) => $column->dateTime('Y-m-d H:i')),

                TextColumn::make('last_sent_at')
                    ->alignCenter()
                    ->badge()
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->getStateUsing(fn(Newsletter $record) => $record->newsletterSendHistories()->latest('completed_at')->value('completed_at'))
                    ->label(__('newsletter::attributes.last_sent_at'))
                    ->sinceTooltip()
                    ->sortable()
                    ->unless(app()->isLocale('fa'), fn(TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', toLatin: true), fn(TextColumn $column) => $column->dateTime('Y-m-d H:i')),

                ToggleColumn::make('status')
                    ->label(__('newsletter::attributes.status'))
                    ->onIcon('heroicon-m-bolt'),

                TextColumn::make('created_at')
                    ->alignCenter()
                    ->badge()
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('newsletter::attributes.created_at'))
                    ->sinceTooltip()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->unless(app()->isLocale('fa'), fn(TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', toLatin: true), fn(TextColumn $column) => $column->dateTime('Y-m-d H:i')),

                TextColumn::make('updated_at')
                    ->alignCenter()
                    ->badge()
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('newsletter::attributes.updated_at'))
                    ->sinceTooltip()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->unless(app()->isLocale('fa'), fn(TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', toLatin: true), fn(TextColumn $column) => $column->dateTime('Y-m-d H:i')),
            ])
            ->filters([
                QueryBuilder::make()
                    ->constraints([
                        TextConstraint::make('name')
                            ->label(__('newsletter::attributes.name')),

                        TextConstraint::make('slug')
                            ->label(__('newsletter::attributes.slug')),

                        DateConstraint::make('scheduled_at')
                            ->label(__('newsletter::attributes.scheduled_at')),

                        BooleanConstraint::make('status')
                            ->label(__('newsletter::attributes.status')),

                        DateConstraint::make('created_at')
                            ->label(__('newsletter::attributes.created_at')),

                        DateConstraint::make('updated_at')
                            ->label(__('newsletter::attributes.updated_at')),
                    ]),
            ], layout: FiltersLayout::AboveContentCollapsible)
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
            ]);
    }
}
