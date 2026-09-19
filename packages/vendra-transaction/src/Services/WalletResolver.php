<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Misaf\VendraSupport\Capabilities\CurrencyIntegration;
use Misaf\VendraTransaction\Models\Wallet;

final class WalletResolver
{
    public function walletFor(Model $user, string $currencyCode): Wallet
    {
        return Wallet::query()->firstOrCreate([
            'user_id' => $user->getKey(),
            'currency_code' => Str::upper($currencyCode),
        ]);
    }

    public function defaultWalletFor(Model $user): Wallet
    {
        return $this->walletFor($user, CurrencyIntegration::defaultCode());
    }
}
