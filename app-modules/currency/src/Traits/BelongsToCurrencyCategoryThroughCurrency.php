<?php

declare(strict_types=1);

namespace App\Models\Currency\Traits;

use App\Models\Currency\Currency;
use App\Models\Currency\CurrencyCategory;
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
