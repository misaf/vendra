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

    /**
     * Label a user by username, then name, then the given key.
     */
    public static function label(?Model $user, ?int $userId = null): string
    {
        foreach (['username', 'name'] as $attribute) {
            $value = $user?->getAttribute($attribute);

            if (is_string($value)) {
                return $value;
            }
        }

        $userKey = $userId ?? $user?->getKey();

        return is_int($userKey) || is_string($userKey) ? "#{$userKey}" : '';
    }
}
