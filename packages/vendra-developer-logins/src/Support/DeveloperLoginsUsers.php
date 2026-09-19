<?php

declare(strict_types=1);

namespace Misaf\VendraDeveloperLogins\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

final class DeveloperLoginsUsers
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
