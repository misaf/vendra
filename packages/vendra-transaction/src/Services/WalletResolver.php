<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Misaf\VendraSupport\Capabilities\CurrencyIntegration;
use Misaf\VendraTransaction\Models\Wallet;

/**
 * Finds the wallet a balance belongs in, creating it on first use.
 *
 * Split out of the former TransactionService, which had grown to cover wallets,
 * gateways, limits, and token generation behind one injection point — anything
 * that needed a wallet also received the gateway registry.
 */
final class WalletResolver
{
    /**
     * The wallet holding the given user's balance in the given currency,
     * created on first use.
     */
    public function walletFor(Model $user, string $currencyCode): Wallet
    {
        return Wallet::query()->firstOrCreate([
            'user_id' => $user->getKey(),
            'currency_code' => Str::upper($currencyCode),
        ]);
    }

    /**
     * The wallet for the given user in the application's default currency,
     * created on first use.
     */
    public function defaultWalletFor(Model $user): Wallet
    {
        return $this->walletFor($user, CurrencyIntegration::defaultCode());
    }
}
