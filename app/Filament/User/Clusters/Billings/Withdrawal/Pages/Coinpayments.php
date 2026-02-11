<?php

declare(strict_types=1);

namespace App\Filament\User\Clusters\Billings\Withdrawal\Pages;

use App\Filament\User\Clusters\Billings\Withdrawal\WithdrawalCluster\WithdrawalCluster;
use App\Livewire\User\Payment\Coinpayments\Widgets\WithdrawalOverview;
use Filament\Clusters\Cluster;
use Filament\Pages\Page;
use Misaf\VendraTransaction\Facades\TransactionService;
use Misaf\VendraTransaction\Models\TransactionGateway;

final class Coinpayments extends Page
{
    /**
     * @var class-string<Cluster>|null
     */
    protected static ?string $cluster = WithdrawalCluster::class;

    protected string $view = 'filament.user.pages.billings.coinpayments.withdrawal';

    protected static ?string $slug = 'coinpayments';

    public static function getNavigationLabel(): string
    {
        $name = TransactionGateway::whereJsonContainsLocale('slug', app()->getLocale(), 'coinpayments', '=')
            ->value('name');

        return is_string($name) ? $name : __('Coinpayments');
    }

    public static function getNavigationSort(): ?int
    {
        $position = TransactionGateway::whereJsonContainsLocale('slug', app()->getLocale(), 'coinpayments', '=')
            ->value('position');

        return is_numeric($position) ? (int) $position : 0;
    }

    public static function shouldRegisterNavigation(): bool
    {
        $hasActiveGateway = TransactionService::hasActiveTransactionGateway('coinpayments');

        if ( ! $hasActiveGateway) {
            return false;
        }

        $user = filament()->auth()->user();

        return $user->hasTag('deposit_crypto');
    }

    public static function canAccess(): bool
    {
        $hasActiveGateway = TransactionService::hasActiveTransactionGateway('coinpayments');

        if ( ! $hasActiveGateway) {
            return false;
        }

        $user = filament()->auth()->user();

        return $user->hasTag('deposit_crypto');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            WithdrawalOverview::class,
        ];
    }
}
