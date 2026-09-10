<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates\Pages\CreateAffiliate;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates\Pages\EditAffiliate;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates\Pages\ListAffiliates;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates\Pages\ViewAffiliate;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates\RelationManagers\ClicksRelationManager;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates\RelationManagers\CommissionsRelationManager;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates\RelationManagers\PayoutsRelationManager;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates\RelationManagers\ReferralsRelationManager;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates\Schemas\AffiliateForm;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates\Schemas\AffiliateInfolist;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates\Tables\AffiliateTable;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates\Widgets\AffiliateClicksOverview;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates\Widgets\AffiliateCommissionsOverview;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates\Widgets\AffiliateReferralsOverview;
use Misaf\VendraAffiliate\Models\Affiliate;
use Misaf\VendraSupport\Filament\Clusters\MarketingCluster;
use Misaf\VendraSupport\Filament\Navigation\NavigationPriority;

final class AffiliateResource extends Resource
{
    protected static ?string $model = Affiliate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLink;

    protected static ?int $navigationSort = NavigationPriority::Affiliates->value;

    protected static ?string $recordTitleAttribute = 'code';

    protected static ?string $slug = 'affiliates';

    protected static ?string $cluster = MarketingCluster::class;

    /**
     * @return array<int, string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['code', 'user.username', 'user.email'];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with('user');
    }

    /**
     * @return array<string, string>
     */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        $affiliate = self::affiliate($record);

        return [
            __('vendra-user::attributes.email') => $affiliate->user?->email ?? '',
        ];
    }

    public static function getBreadcrumb(): string
    {
        return __('vendra-affiliate::navigation.affiliate');
    }

    public static function getModelLabel(): string
    {
        return __('vendra-affiliate::navigation.affiliate');
    }

    public static function getNavigationLabel(): string
    {
        return __('vendra-affiliate::navigation.affiliates');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vendra-affiliate::navigation.affiliates');
    }

    public static function getRelations(): array
    {
        return [
            ReferralsRelationManager::class,
            CommissionsRelationManager::class,
            PayoutsRelationManager::class,
            ClicksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAffiliates::route('/'),
            'create' => CreateAffiliate::route('/create'),
            'view' => ViewAffiliate::route('/{record}'),
            'edit' => EditAffiliate::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            AffiliateClicksOverview::class,
            AffiliateReferralsOverview::class,
            AffiliateCommissionsOverview::class,
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return AffiliateForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AffiliateInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AffiliateTable::configure($table);
    }

    private static function affiliate(Model $record): Affiliate
    {
        throw_unless($record instanceof Affiliate, InvalidArgumentException::class, 'Affiliate resources require an Affiliate record.');

        return $record;
    }
}
