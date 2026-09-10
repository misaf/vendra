<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Filament\Clusters\Resources\AffiliatePayouts;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\AffiliatePayouts\Pages\ListAffiliatePayouts;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\AffiliatePayouts\Pages\ViewAffiliatePayout;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\AffiliatePayouts\Schemas\AffiliatePayoutInfolist;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\AffiliatePayouts\Tables\AffiliatePayoutTable;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\AffiliatePayouts\Widgets\AffiliatePayoutOverviewWidget;
use Misaf\VendraAffiliate\Models\AffiliatePayout;
use Misaf\VendraSupport\Filament\Clusters\MarketingCluster;
use Misaf\VendraSupport\Filament\Navigation\NavigationPriority;

final class AffiliatePayoutResource extends Resource
{
    protected static ?string $model = AffiliatePayout::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?int $navigationSort = NavigationPriority::AffiliatePayouts->value;

    protected static ?string $slug = 'affiliate-payouts';

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
        $payout = self::payout($record);

        return [
            __('vendra-affiliate::attributes.affiliate') => $payout->affiliate?->code ?? '',
            __('vendra-user::attributes.email') => $payout->affiliate?->user?->email ?? '',
        ];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return '#'.self::payout($record)->id;
    }

    public static function getBreadcrumb(): string
    {
        return __('vendra-affiliate::navigation.affiliate_payout');
    }

    public static function getModelLabel(): string
    {
        return __('vendra-affiliate::navigation.affiliate_payout');
    }

    public static function getNavigationLabel(): string
    {
        return __('vendra-affiliate::navigation.affiliate_payouts');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vendra-affiliate::navigation.affiliate_payouts');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAffiliatePayouts::route('/'),
            'view' => ViewAffiliatePayout::route('/{record}'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            AffiliatePayoutOverviewWidget::class,
        ];
    }

    public static function infolist(Schema $schema): Schema
    {
        return AffiliatePayoutInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AffiliatePayoutTable::configure($table);
    }

    private static function payout(Model $record): AffiliatePayout
    {
        throw_unless($record instanceof AffiliatePayout, InvalidArgumentException::class, 'Affiliate Payout resources require an AffiliatePayout record.');

        return $record;
    }
}
