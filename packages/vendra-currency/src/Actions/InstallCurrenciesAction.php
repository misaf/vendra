<?php

declare(strict_types=1);

namespace Misaf\VendraCurrency\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Misaf\VendraCurrency\Models\Currency;
use Misaf\VendraCurrency\Support\CurrencyRegistry;

final class InstallCurrenciesAction
{
    /**
     * Install the given catalog codes, skipping anything unsupported or
     * already installed. The comparison is case-insensitive; the model
     * normalizes the stored code to uppercase.
     *
     * @param  list<mixed>  $codes
     */
    public function execute(array $codes): int
    {
        Validator::make(
            ['codes' => $codes],
            ['codes' => ['required', 'array', 'min:1']],
        )->validate();

        return DB::transaction(function () use ($codes): int {
            $installed = 0;

            foreach ($codes as $code) {
                if (! is_string($code) || ! CurrencyRegistry::isSupported($code)) {
                    continue;
                }

                if (Currency::query()->where('code', mb_strtoupper($code))->exists()) {
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
