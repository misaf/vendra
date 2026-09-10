<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Filament\Clusters\Resources\AffiliateCommissions;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\AffiliateCommissions\Pages\ListAffiliateCommissions;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\AffiliateCommissions\Pages\ViewAffiliateCommission;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\AffiliateCommissions\Schemas\AffiliateCommissionInfolist;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\AffiliateCommissions\Tables\AffiliateCommissionTable;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\AffiliateCommissions\Widgets\AffiliateCommissionOverviewWidget;
use Misaf\VendraAffiliate\Models\AffiliateCommission;
use Misaf\VendraSupport\Filament\Clusters\MarketingCluster;
use Misaf\VendraSupport\Filament\Navigation\NavigationPriority;

final class AffiliateCommissionResource extends Resource
{
    protected static ?string $model = AffiliateCommission::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    protected static ?int $navigationSort = NavigationPriority::AffiliateCommissions->value;

    protected static ?string $slug = 'affiliate-commissions';

    protected static ?string $cluster = MarketingCluster::class;

    /**
     * @return array<int, string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['affiliate.code', 'affiliate.user.username', 'affiliate.user.email'];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with('affiliate.user');
    }

    /**
     * @return array<string, string>
     */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        $commission = self::commission($record);

        return [
            __('vendra-affiliate::attributes.affiliate') => $commission->affiliate?->code ?? '',
            __('vendra-user::attributes.email') => $commission->affiliate?->user?->email ?? '',
        ];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return '#'.self::commission($record)->id;
    }

    public static function getBreadcrumb(): string
    {
        return __('vendra-affiliate::navigation.affiliate_commission');
    }

    public static function getModelLabel(): string
    {
        return __('vendra-affiliate::navigation.affiliate_commission');
    }

    public static function getNavigationLabel(): string
    {
        return __('vendra-affiliate::navigation.affiliate_commissions');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vendra-affiliate::navigation.affiliate_commissions');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAffiliateCommissions::route('/'),
            'view' => ViewAffiliateCommission::route('/{record}'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            AffiliateCommissionOverviewWidget::class,
        ];
    }

    public static function infolist(Schema $schema): Schema
    {
        return AffiliateCommissionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AffiliateCommissionTable::configure($table);
    }

    private static function commission(Model $record): AffiliateCommission
    {
        throw_unless($record instanceof AffiliateCommission, InvalidArgumentException::class, 'Affiliate Commission resources require an AffiliateCommission record.');

        return $record;
    }
}
