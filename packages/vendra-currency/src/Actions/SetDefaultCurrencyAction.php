<?php

declare(strict_types=1);

namespace Misaf\VendraCurrency\Actions;

use Illuminate\Support\Facades\DB;
use Misaf\VendraCurrency\Models\Currency;

final class SetDefaultCurrencyAction
{
    public function execute(Currency $currency): void
    {
        DB::transaction(function () use ($currency): void {
            Currency::query()
                ->lockForUpdate()
                ->get(['id']);

            $currency->update([
                'active' => true,
                'is_default' => true,
            ]);
        });
    }
}
