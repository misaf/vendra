<?php

declare(strict_types=1);

namespace App\Models\Order\Services;

final class OrderService
{
    public static function generateReferenceCode()
    {
        return fake()->randomNumber(9, true);
    }
}
