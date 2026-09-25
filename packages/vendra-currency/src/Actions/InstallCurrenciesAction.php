<?php

declare(strict_types=1);

namespace Misaf\VendraCurrency\Actions;

use Illuminate\Support\Facades\DB;
use Misaf\VendraCurrency\Models\Currency;
use Misaf\VendraCurrency\Support\CurrencyRegistry;
use Misaf\VendraSupport\Tenancy\TenantAwareness;

final class InstallCurrenciesAction
{
    /**
     * Unsupported or installed codes are skipped. The caller requires at least
     * one code. Outside a tenant, this installs platform currencies.
     *
     * @param  list<mixed>  $codes
     */
    public function execute(array $codes): int
    {
        return DB::transaction(function () use ($codes): int {
            $installed = 0;

            foreach ($codes as $code) {
                if (! is_string($code) || ! CurrencyRegistry::isSupported($code)) {
                    continue;
                }

                if (TenantAwareness::constrainToCurrentTenant(Currency::query())->where('code', mb_strtoupper($code))->exists()) {
                    continue;
                }

                Currency::query()->create([
                    'code' => $code,
                    'name' => CurrencyRegistry::nameFor($code),
                    'decimal_places' => CurrencyRegistry::minorUnitFor($code),
                    'type' => CurrencyRegistry::typeFor($code),
                ]);

                $installed++;
            }

            return $installed;
        });
    }
}
