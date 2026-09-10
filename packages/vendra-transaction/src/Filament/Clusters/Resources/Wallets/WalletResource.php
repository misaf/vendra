<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Filament\Clusters\Resources\Wallets;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Misaf\VendraSupport\Filament\Clusters\SalesCluster;
use Misaf\VendraSupport\Filament\Navigation\NavigationPriority;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Wallets\Pages\ListWallets;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Wallets\Pages\ViewWallet;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Wallets\RelationManagers\LedgerEntriesRelationManager;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Wallets\RelationManagers\TransactionLimitsRelationManager;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Wallets\Schemas\WalletInfolist;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Wallets\Tables\WalletTable;
use Misaf\VendraTransaction\Models\Wallet;

final class WalletResource extends Resource
{
    protected static ?string $model = Wallet::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWallet;

    protected static ?int $navigationSort = NavigationPriority::Wallets->value;

    protected static ?string $slug = 'wallets';

    protected static ?string $cluster = SalesCluster::class;

    /**
     * @return array<int, string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['currency_code', 'user.username', 'user.email'];
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
        $wallet = self::wallet($record);

        return [
            __('vendra-user::attributes.email') => (string) $wallet->user?->getAttribute('email'),
            __('vendra-transaction::attributes.currency') => $wallet->currency_code,
        ];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        $wallet = self::wallet($record);
        $userName = $wallet->user?->getAttribute('username')
            ?? $wallet->user?->getAttribute('name')
            ?? "#{$wallet->user_id}";

        if (! is_string($userName)) {
            $userName = "#{$wallet->user_id}";
        }

        return "{$userName} ({$wallet->currency_code})";
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getBreadcrumb(): string
    {
        return __('vendra-transaction::navigation.wallet');
    }

    public static function getModelLabel(): string
    {
        return __('vendra-transaction::navigation.wallet');
    }

    public static function getNavigationLabel(): string
    {
        return __('vendra-transaction::navigation.wallets');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vendra-transaction::navigation.wallets');
    }

    public static function getRelations(): array
    {
        return [
            LedgerEntriesRelationManager::class,
            TransactionLimitsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWallets::route('/'),
            'view' => ViewWallet::route('/{record}'),
        ];
    }

    public static function infolist(Schema $schema): Schema
    {
        return WalletInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WalletTable::configure($table);
    }

    private static function wallet(Model $record): Wallet
    {
        if (! $record instanceof Wallet) {
            throw new InvalidArgumentException('Wallet resources require a Wallet record.');
        }

        return $record;
    }
}
