<?php

declare(strict_types=1);

namespace App\Filament\User\Clusters\Billings\Deposit\Pages;

use App\Filament\User\Clusters\Billings\Deposit\DepositCluster\DepositCluster;
use App\Livewire\User\Payment\Perfectmoney\Widgets\DepositOverview;
use Filament\Clusters\Cluster;
use Filament\Pages\Page;
use Misaf\VendraTransaction\Facades\TransactionService;
use Misaf\VendraTransaction\Models\TransactionGateway;

final class Perfectmoney extends Page
{
    /**
     * @var class-string<Cluster>|null
     */
    protected static ?string $cluster = DepositCluster::class;

    protected string $view = 'filament.user.pages.billings.perfectmoney.deposit';

    protected static ?string $slug = 'perfectmoney';

    public static function getNavigationGroup(): string
    {
        return __('navigation.my_deposit');
    }

    public static function getNavigationLabel(): string
    {
        $name = TransactionGateway::whereJsonContainsLocale('slug', app()->getLocale(), 'perfectmoney', '=')
            ->value('name');

        return is_string($name) ? $name : __('Perfectmoney');
    }

    public static function getNavigationSort(): ?int
    {
        $position = TransactionGateway::whereJsonContainsLocale('slug', app()->getLocale(), 'perfectmoney', '=')
            ->value('position');

        return is_numeric($position) ? (int) $position : 0;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return TransactionService::hasActiveTransactionGateway('perfectmoney');
    }

    public static function canAccess(): bool
    {
        return TransactionService::hasActiveTransactionGateway('perfectmoney');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            DepositOverview::class,
        ];
    }
}
