<?php

declare(strict_types=1);

namespace App\Casts;

use Brick\Money\Currency as MoneyCurrency;
use Brick\Money\Money;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Termehsoft\Currency\Models\Currency;

final class MoneyCast implements CastsAttributes
{
    public bool $withoutObjectCaching = true;

    public function get(Model $model, string $key, mixed $value, array $attributes)
    {
        return Money::ofMinor($value, new MoneyCurrency(
            $model->currency->iso_code,
            $model->currency->id,
            $model->currency->name,
            $model->currency->decimal_place,
        ));
    }

    public function set(Model $model, string $key, mixed $value, array $attributes)
    {
        $currency = Currency::where('slug', 'toman')->first();

        $value = Money::of($value, new MoneyCurrency(
            $currency->iso_code,
            $currency->id,
            $currency->name,
            $currency->decimal_place,
        ));

        if ( ! $value instanceof Money) {
            throw new InvalidArgumentException('The given value is not an Address instance.');
        }

        return $value->getMinorAmount()->toBigInteger();
    }
}
