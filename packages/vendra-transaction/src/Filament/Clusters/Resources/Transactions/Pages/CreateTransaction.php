<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Pages;

use Filament\Resources\Pages\CreateRecord;
use Misaf\VendraTransaction\Facades\WalletResolver;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\TransactionResource;
use Misaf\VendraTransaction\Support\TransactionUsers;

final class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    /**
     * Resolves the selected user + currency pair into a wallet, provisioning
     * it on first use, so transactions can be created for users who have no
     * wallet yet.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $currencyCode = (string) $data['currency_code'];

        $data['wallet_id'] = WalletResolver::walletFor(
            TransactionUsers::model()::query()->findOrFail($data['user_id']),
            $currencyCode,
        )->id;

        if (! empty($data['counterparty_user_id'])) {
            $data['counterparty_wallet_id'] = WalletResolver::walletFor(
                TransactionUsers::model()::query()->findOrFail($data['counterparty_user_id']),
                $currencyCode,
            )->id;
        }

        unset($data['user_id'], $data['currency_code'], $data['counterparty_user_id']);

        return $data;
    }
}
