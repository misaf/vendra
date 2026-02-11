<?php

declare(strict_types=1);

namespace App\Filament\User\Clusters\Billings\Deposit\DepositCluster;

use Filament\Clusters\Cluster;
use Misaf\VendraTransaction\Facades\TransactionService;

final class DepositCluster extends Cluster
{
    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'billings/deposit';

    public static function getNavigationGroup(): string
    {
        return __('navigation.my_billing');
    }

    public static function getNavigationLabel(): string
    {
        return __('navigation.my_deposit');
    }

    public static function getClusterBreadcrumb(): string
    {
        return __('navigation.my_billing');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return TransactionService::hasAnyActiveTransactionGateway();
    }

    public static function canAccess(): bool
    {
        return TransactionService::hasAnyActiveTransactionGateway();
    }
}
