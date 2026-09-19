<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

final class TransactionUsers
{
    /**
     * @return class-string<Model>
     */
    public static function model(): string
    {
        /** @var class-string<Model> */
        return Config::string('auth.providers.users.model');
    }
}
