<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Pages;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Widgets\Widget;
use Filament\Widgets\WidgetConfiguration;
use Illuminate\Database\Eloquent\Builder;
use Misaf\VendraTransaction\Enums\TransactionTypeEnum;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\TransactionResource;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Widgets\TransactionBonusOverviewWidget;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Widgets\TransactionCommissionOverviewWidget;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Widgets\TransactionDepositOverviewWidget;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Widgets\TransactionTransferOverviewWidget;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Widgets\TransactionWithdrawalOverviewWidget;
use Misaf\VendraTransaction\Models\Transaction;

final class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/list-records.breadcrumb').' '.__('vendra-transaction::navigation.transaction');
    }

    /**
     * @return array<string|int, Tab>
     */
    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make()
                ->badge(static fn (): int => Transaction::query()->count())
                ->deferBadge(),
        ];

        foreach ([TransactionTypeEnum::Deposit, TransactionTypeEnum::Withdrawal, TransactionTypeEnum::Commission, TransactionTypeEnum::Transfer, TransactionTypeEnum::Bonus] as $type) {
            $tabs[$type->value] = Tab::make()
                ->badge(static fn (): int => Transaction::query()->ofType($type)->count())
                ->deferBadge()
                ->label($type->getLabel())
                ->modifyQueryUsing(static fn (Builder $query): Builder => $query->where('transaction_type', $type));
        }

        return $tabs;
    }

    /**
     * @return array<Action|ActionGroup>
     */
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    /**
     * @return array<string, int>
     */
    public function getHeaderWidgetsColumns(): array
    {
        return [
            'sm' => 1,
            'md' => 2,
            'lg' => 3,
        ];
    }

    /**
     * @return array<class-string<Widget>|WidgetConfiguration>
     */
    protected function getHeaderWidgets(): array
    {
        return [
            TransactionDepositOverviewWidget::class,
            TransactionWithdrawalOverviewWidget::class,
            TransactionCommissionOverviewWidget::class,
            TransactionBonusOverviewWidget::class,
            TransactionTransferOverviewWidget::class,
        ];
    }
}
