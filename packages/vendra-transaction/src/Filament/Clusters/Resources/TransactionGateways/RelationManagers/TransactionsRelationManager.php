<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Filament\Clusters\Resources\TransactionGateways\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Number;
use Misaf\VendraTransaction\Enums\TransactionTypeEnum;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Tables\TransactionTable;
use Misaf\VendraTransaction\Models\TransactionGateway;

final class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    protected static bool $isBadgeDeferred = true;

    public static function getModelLabel(): string
    {
        return __('vendra-transaction::navigation.transaction');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vendra-transaction::navigation.transactions');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('vendra-transaction::navigation.transactions');
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): string
    {
        $transactionCount = $ownerRecord instanceof TransactionGateway
            ? $ownerRecord->transactions()->count()
            : 0;

        return (string) Number::format($transactionCount);
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    /**
     * @return array<string|int, Tab>
     */
    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make()
                ->badge(fn (): string => (string) Number::format($this->getOwnerRecord()->transactions()->count()))
                ->deferBadge(),
        ];

        foreach (TransactionTypeEnum::cases() as $type) {
            $tabs[$type->value] = Tab::make()
                ->badge(fn (): string => (string) Number::format($this->getOwnerRecord()->transactions()->ofType($type)->count()))
                ->deferBadge()
                ->label($type->getLabel())
                ->modifyQueryUsing(fn (Builder $query) => $query->ofType($type));
        }

        return $tabs;
    }

    public function table(Table $table): Table
    {
        return TransactionTable::configure($table);
    }
}
