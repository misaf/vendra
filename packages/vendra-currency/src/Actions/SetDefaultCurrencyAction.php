<?php

declare(strict_types=1);

namespace Misaf\VendraCurrency\Actions;

use Illuminate\Support\Facades\DB;
use Misaf\VendraCurrency\Models\Currency;
use Misaf\VendraSupport\Tenancy\TenantAwareness;

final class SetDefaultCurrencyAction
{
    public function execute(Currency $currency): void
    {
        DB::transaction(function () use ($currency): void {
            TenantAwareness::constrainToTenantOf(Currency::query(), $currency)
                ->lockForUpdate()
                ->get(['id']);

            $currency->update([
                'active' => true,
                'is_default' => true,
            ]);
        });
    }
}
