<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

final class ReferralsRelationManager extends RelationManager
{
    protected static string $relationship = 'referrals';

    protected static bool $isLazy = false;

    public static function getModelLabel(): string
    {
        return __('vendra-affiliate::navigation.affiliate_referral');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('vendra-affiliate::navigation.affiliate_referral');
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('row')
                    ->label('#')
                    ->rowIndex()
                    ->sortable(['id']),

                TextColumn::make('user.username')
                    ->label(__('vendra-affiliate::attributes.user'))
                    ->icon(Heroicon::User)
                    ->searchable(),

                TextColumn::make('attributed_at')
                    ->alignCenter()
                    ->badge()
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('vendra-affiliate::attributes.attributed_at'))
                    ->sinceTooltip()
                    ->when(
                        app()->isLocale('fa'),
                        fn (TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                        fn (TextColumn $column) => $column->dateTime('Y-m-d H:i')
                    ),
            ])
            ->defaultSort(column: 'attributed_at', direction: 'desc');
    }
}
