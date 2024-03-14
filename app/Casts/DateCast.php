<?php

declare(strict_types=1);

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Morilog\Jalali\Jalalian;

final class DateCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (null !== $value && 'fa' === app()->getLocale()) {
            return Jalalian::forge($value)->__toString();
        }

        return $value;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (null !== $value && 'fa' === app()->getLocale()) {
            return Jalalian::fromFormat('Y-m-d H:i:s', $value)->__toString();
        }

        return $value;
    }
}
