<?php

declare(strict_types=1);

namespace Termehsoft\Order\Services;

final class OrderService
{
    public static function generateReferenceCode()
    {
        return fake()->randomNumber(9, true);
    }
}
