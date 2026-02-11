<?php

declare(strict_types=1);

namespace App\Filament\User\Clusters\Billings\Withdrawal\Pages;

use App\Filament\User\Clusters\Billings\Withdrawal\WithdrawalCluster\WithdrawalCluster;
use App\Livewire\User\Payment\Perfectmoney\Widgets\WithdrawalOverview;
use Filament\Clusters\Cluster;
use Filament\Pages\Page;
use Misaf\VendraTransaction\Facades\TransactionService;
use Misaf\VendraTransaction\Models\TransactionGateway;

final class Perfectmoney extends Page
{
    /**
     * @var class-string<Cluster>|null
     */
    protected static ?string $cluster = WithdrawalCluster::class;

    protected string $view = 'filament.user.pages.billings.perfectmoney.withdrawal';

    protected static ?string $slug = 'perfectmoney';

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
            WithdrawalOverview::class,
        ];
    }
}
