<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Number;
use InvalidArgumentException;
use Misaf\VendraSupport\Filament\Clusters\SalesCluster;
use Misaf\VendraSupport\Filament\Navigation\NavigationPriority;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Pages\CreateTransaction;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Pages\ListTransactions;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Pages\ViewTransaction;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\RelationManagers\TransactionMetadataRelationManager;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Schemas\TransactionForm;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Schemas\TransactionInfolist;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Tables\TransactionTable;
use Misaf\VendraTransaction\Models\Transaction;

final class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static ?int $navigationSort = NavigationPriority::Transactions->value;

    protected static ?string $recordTitleAttribute = 'token';

    protected static ?string $slug = 'transactions';

    protected static ?string $cluster = SalesCluster::class;

    /**
     * @return array<int, string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['token', 'wallet.user.username', 'wallet.user.email'];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with('wallet.user');
    }

    /**
     * @return array<string, string>
     */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        $email = self::transaction($record)->wallet?->user?->getAttribute('email');

        return [
            __('vendra-user::attributes.email') => is_string($email) ? $email : '',
        ];
    }

    public static function getNavigationBadge(): string
    {
        return (string) Number::format(Transaction::query()->count());
    }

    public static function getNavigationBadgeTooltip(): string
    {
        return __('vendra-transaction::navigation.navigation_badge_tooltip');
    }

    public static function getBreadcrumb(): string
    {
        return __('vendra-transaction::navigation.transaction');
    }

    public static function getModelLabel(): string
    {
        return __('vendra-transaction::navigation.transaction');
    }

    public static function getNavigationLabel(): string
    {
        return __('vendra-transaction::navigation.transactions');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vendra-transaction::navigation.transactions');
    }

    public static function getRelations(): array
    {
        return [
            TransactionMetadataRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTransactions::route('/'),
            'create' => CreateTransaction::route('/create'),
            'view' => ViewTransaction::route('/{record}'),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return TransactionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TransactionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TransactionTable::configure($table);
    }

    private static function transaction(Model $record): Transaction
    {
        throw_unless($record instanceof Transaction, InvalidArgumentException::class, 'Transaction resources require a Transaction record.');

        return $record;
    }
}
