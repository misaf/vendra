<?php

declare(strict_types=1);

namespace Misaf\VendraWishlist\Filament\Clusters\Resources\Wishlists;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Number;
use Misaf\VendraSupport\Filament\Clusters\CustomersCluster;
use Misaf\VendraSupport\Filament\Navigation\NavigationPriority;
use Misaf\VendraWishlist\Filament\Clusters\Resources\Wishlists\Pages\ListWishlists;
use Misaf\VendraWishlist\Filament\Clusters\Resources\Wishlists\Pages\ViewWishlist;
use Misaf\VendraWishlist\Filament\Clusters\Resources\Wishlists\RelationManagers\WishlistItemsRelationManager;
use Misaf\VendraWishlist\Filament\Clusters\Resources\Wishlists\Schemas\WishlistForm;
use Misaf\VendraWishlist\Filament\Clusters\Resources\Wishlists\Schemas\WishlistInfolist;
use Misaf\VendraWishlist\Filament\Clusters\Resources\Wishlists\Tables\WishlistTable;
use Misaf\VendraWishlist\Models\Wishlist;

final class WishlistResource extends Resource
{
    protected static ?string $model = Wishlist::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHeart;

    protected static ?int $navigationSort = NavigationPriority::Wishlists->value;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'wishlists';

    protected static ?string $cluster = CustomersCluster::class;

    public static function getBreadcrumb(): string
    {
        return __('vendra-wishlist::navigation.wishlist');
    }

    public static function getModelLabel(): string
    {
        return __('vendra-wishlist::navigation.wishlist');
    }

    public static function getNavigationLabel(): string
    {
        return __('vendra-wishlist::navigation.wishlists');
    }

    public static function getNavigationBadge(): string
    {
        return (string) Number::format(Wishlist::query()->count());
    }

    public static function getNavigationBadgeTooltip(): string
    {
        return __('vendra-wishlist::navigation.navigation_badge_tooltip');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vendra-wishlist::navigation.wishlists');
    }

    /**
     * @return array<int, string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'token'];
    }

    public static function form(Schema $schema): Schema
    {
        return WishlistForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return WishlistInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WishlistTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            WishlistItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWishlists::route('/'),
            'view' => ViewWishlist::route('/{record}'),
        ];
    }
}
