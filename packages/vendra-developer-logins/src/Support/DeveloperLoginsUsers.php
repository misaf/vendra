<?php

declare(strict_types=1);

namespace Misaf\VendraDeveloperLogins\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use LogicException;

final class DeveloperLoginsUsers
{
    /**
     * @return class-string<Authenticatable&Model>
     *
     * @throws LogicException
     */
    public static function model(): string
    {
        $model = Config::string('auth.providers.users.model');

        throw_unless(
            is_a($model, Model::class, true) && is_a($model, Authenticatable::class, true),
            LogicException::class,
            "The users provider model [{$model}] must be an authenticatable Eloquent model.",
        );

        return $model;
    }
}
