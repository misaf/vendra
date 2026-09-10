<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Services;

use Misaf\VendraAffiliate\Models\Affiliate;
use RuntimeException;

final class AffiliateCodeService
{
    /**
     * Unambiguous uppercase alphabet: no 0/O, 1/I/L to keep codes readable
     * when shared verbally or in print.
     */
    private const string CODE_CHARACTERS = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';

    private const int CODE_LENGTH = 8;

    private const int MAX_ATTEMPTS = 10;

    public function generate(): string
    {
        for ($attempt = 0; $attempt < self::MAX_ATTEMPTS; $attempt++) {
            $code = $this->randomCode();

            if (! Affiliate::withTrashed()->where('code', $code)->exists()) {
                return $code;
            }
        }

        throw new RuntimeException('Unable to generate a unique affiliate code.');
    }

    private function randomCode(): string
    {
        $maxIndex = mb_strlen(self::CODE_CHARACTERS) - 1;
        $code = '';

        for ($i = 0; $i < self::CODE_LENGTH; $i++) {
            $code .= self::CODE_CHARACTERS[random_int(0, $maxIndex)];
        }

        return $code;
    }
}
