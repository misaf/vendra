<?php

declare(strict_types=1);

namespace Termehsoft\Currency\Traits;

use Termehsoft\Currency\Models\Currency;
use Termehsoft\Currency\Models\CurrencyCategory;
use Znck\Eloquent\Relations\BelongsToThrough;

trait BelongsToCurrencyCategoryThroughCurrency
{
    /**
     * Get the user that owns the profile.
     *
     * @return BelongsToThrough
     */
    public function currencyCategory(): BelongsToThrough
    {
        return $this->belongsToThrough(CurrencyCategory::class, Currency::class);
    }
}
