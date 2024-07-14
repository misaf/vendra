<?php

declare(strict_types=1);

namespace App\Casts;

use Ariaieboy\Jalali\Jalali;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

final class DateCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (null !== $value && 'fa' === app()->getLocale()) {
            return Jalali::forge($value)->__toString();
        }

        return $value;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (null !== $value && 'fa' === app()->getLocale()) {
            if ($value instanceof Carbon) {
                return Jalali::fromCarbon($value)->__toString();
            }

            return Jalali::fromFormat('Y-m-d H:i:s', $value)->__toString();
        }

        return $value;
    }
}
