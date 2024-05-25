<?php

declare(strict_types=1);

namespace App\Models\Currency\Contracts;

use Znck\Eloquent\Relations\BelongsToThrough;

interface BelongsToCurrencyCategoryThroughCurrency
{
    /**
     * Get the user that owns the profile.
     *
     * @return BelongsToThrough
     */
    public function currencyCategory(): BelongsToThrough;
}
