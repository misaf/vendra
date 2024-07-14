<?php

declare(strict_types=1);

namespace App\Models\Transaction\Services;

final class TransactionService
{
    public static function generateReferenceCode()
    {
        return fake()->randomNumber(9, true);
    }
}
