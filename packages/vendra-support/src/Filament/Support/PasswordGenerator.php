<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Support;

final class PasswordGenerator
{
    private const string CHARACTERS = '123456789abcdefghijklmnopqrstuvwxyz';

    private const int DEFAULT_LENGTH = 8;

    public static function generate(?int $length = null): string
    {
        $length ??= self::DEFAULT_LENGTH;
        $maxIndex = mb_strlen(self::CHARACTERS) - 1;

        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= self::CHARACTERS[random_int(0, $maxIndex)];
        }

        return $password;
    }
}
