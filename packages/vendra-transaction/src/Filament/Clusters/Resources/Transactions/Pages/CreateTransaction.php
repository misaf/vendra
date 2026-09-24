<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Misaf\VendraTransaction\Actions\CreateTransactionAction;
use Misaf\VendraTransaction\Enums\TransactionTypeEnum;
use Misaf\VendraTransaction\Facades\WalletResolver;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\TransactionResource;
use Misaf\VendraTransaction\Models\Transaction;
use Misaf\VendraTransaction\Models\TransactionGateway;
use Misaf\VendraTransaction\Models\Wallet;
use Misaf\VendraTransaction\Rules\WithinTransactionLimit;
use Misaf\VendraTransaction\Support\TransactionUsers;

final class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data): Transaction {
            $currencyCode = Arr::string($data, 'currency_code');
            $transactionType = Arr::get($data, 'transaction_type');
            $transactionType = $transactionType instanceof TransactionTypeEnum ? $transactionType : TransactionTypeEnum::from(is_string($transactionType) ? $transactionType : '');
            $amount = Arr::get($data, 'amount');
            $amount = is_numeric($amount) ? (int) $amount : 0;

            $wallet = self::firstOrCreateWalletFor(Arr::get($data, 'user_id'), $currencyCode);
            $counterpartyWallet = filled(Arr::get($data, 'counterparty_user_id'))
                ? self::firstOrCreateWalletFor(Arr::get($data, 'counterparty_user_id'), $currencyCode)
                : null;

            Validator::make(
                ['data' => ['amount' => $amount]],
                ['data.amount' => [new WithinTransactionLimit($wallet, $transactionType)]],
            )->validate();

            return resolve(CreateTransactionAction::class)->execute(
                transactionGateway: TransactionGateway::query()->whereKey(Arr::get($data, 'transaction_gateway_id'))->firstOrFail(),
                wallet: $wallet,
                transactionType: $transactionType,
                amount: $amount,
                counterpartyWallet: $counterpartyWallet,
            );
        });
    }

    private static function firstOrCreateWalletFor(mixed $userId, string $currencyCode): Wallet
    {
        return WalletResolver::firstOrCreateWalletFor(TransactionUsers::model()::query()->whereKey($userId)->firstOrFail(), $currencyCode);
    }
}
