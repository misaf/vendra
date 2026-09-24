<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates\RelationManagers;

use BackedEnum;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\AffiliateCommissions\Tables\AffiliateCommissionTable;

final class CommissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'commissions';

    protected static string|BackedEnum|null $icon = Heroicon::OutlinedReceiptPercent;

    protected static bool $isLazy = false;

    public static function getModelLabel(): string
    {
        return __('vendra-affiliate::navigation.affiliate_commission');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('vendra-affiliate::navigation.affiliate_commission');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return AffiliateCommissionTable::configure($table);
    }
}
