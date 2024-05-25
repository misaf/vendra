<?php

declare(strict_types=1);

namespace App\Models\Currency\Traits;

use App\Models\Currency\CurrencyCategory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToCurrencyCategory
{
    /**
     * Get the user that owns the profile.
     *
     * @return BelongsTo
     */
    public function currencyCategory(): BelongsTo
    {
        return $this->belongsTo(CurrencyCategory::class);
    }
}
