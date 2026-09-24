<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates\RelationManagers;

use BackedEnum;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Misaf\VendraSupport\Filament\Tables\Columns\CreatedAtColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\RowIndexColumn;

final class ClicksRelationManager extends RelationManager
{
    protected static string $relationship = 'clicks';

    protected static string|BackedEnum|null $icon = Heroicon::OutlinedCursorArrowRays;

    protected static bool $isLazy = false;

    public static function getModelLabel(): string
    {
        return __('vendra-affiliate::navigation.affiliate_click');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('vendra-affiliate::navigation.affiliate_click');
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                RowIndexColumn::make(),

                TextColumn::make('ip_address')
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('vendra-affiliate::attributes.ip_address'))
                    ->searchable(),

                TextColumn::make('user_agent')
                    ->label(__('vendra-affiliate::attributes.user_agent'))
                    ->limit(40)
                    ->toggleable(),

                TextColumn::make('referer')
                    ->label(__('vendra-affiliate::attributes.referer'))
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),

                CreatedAtColumn::make()
                    ->alignCenter()
                    ->badge(),
            ])
            ->defaultSort(column: 'created_at', direction: 'desc');
    }
}
