<?php

declare(strict_types=1);

namespace Termehsoft\Transaction\Services;

final class TransactionService
{
    public static function generateReferenceCode()
    {
        return fake()->randomNumber(9, true);
    }
}
