<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Filament\Clusters\Resources\TransactionGateways;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable;
use Misaf\VendraSupport\Filament\Clusters\SalesCluster;
use Misaf\VendraSupport\Filament\Concerns\InteractsWithTranslatedGlobalSearch;
use Misaf\VendraSupport\Filament\Navigation\NavigationPriority;
use Misaf\VendraTransaction\Filament\Clusters\Resources\TransactionGateways\Pages\CreateTransactionGateway;
use Misaf\VendraTransaction\Filament\Clusters\Resources\TransactionGateways\Pages\EditTransactionGateway;
use Misaf\VendraTransaction\Filament\Clusters\Resources\TransactionGateways\Pages\ListTransactionGateways;
use Misaf\VendraTransaction\Filament\Clusters\Resources\TransactionGateways\Pages\ViewTransactionGateway;
use Misaf\VendraTransaction\Filament\Clusters\Resources\TransactionGateways\Schemas\TransactionGatewayForm;
use Misaf\VendraTransaction\Filament\Clusters\Resources\TransactionGateways\Schemas\TransactionGatewayInfolist;
use Misaf\VendraTransaction\Filament\Clusters\Resources\TransactionGateways\Tables\TransactionGatewayTable;
use Misaf\VendraTransaction\Models\TransactionGateway;

final class TransactionGatewayResource extends Resource
{
    use InteractsWithTranslatedGlobalSearch {
        getGloballySearchableAttributes as getTranslatedGloballySearchableAttributes;
    }
    use Translatable;

    protected static ?string $model = TransactionGateway::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?int $navigationSort = NavigationPriority::TransactionGateways->value;

    protected static ?string $slug = 'transaction-gateways';

    protected static ?string $cluster = SalesCluster::class;

    /**
     * @return array<int, string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return [
            ...self::getTranslatedGloballySearchableAttributes(),
            'slug',
        ];
    }

    /**
     * @return array<int, string>
     */
    protected static function translatableGlobalSearchAttributes(): array
    {
        return ['name'];
    }

    public static function getDefaultTranslatableLocale(): string
    {
        return app()->getLocale();
    }

    public static function getBreadcrumb(): string
    {
        return __('vendra-transaction::navigation.transaction_gateway');
    }

    public static function getModelLabel(): string
    {
        return __('vendra-transaction::navigation.transaction_gateway');
    }

    public static function getNavigationLabel(): string
    {
        return __('vendra-transaction::navigation.transaction_gateways');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vendra-transaction::navigation.transaction_gateways');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TransactionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTransactionGateways::route('/'),
            'create' => CreateTransactionGateway::route('/create'),
            'view' => ViewTransactionGateway::route('/{record}'),
            'edit' => EditTransactionGateway::route('/{record}/edit'),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return TransactionGatewayForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TransactionGatewayInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TransactionGatewayTable::configure($table);
    }
}
