<?php

declare(strict_types=1);

namespace App\Models\Currency\Traits;

use App\Models\Currency\Currency;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToCurrency
{
    /**
     * Get the user that owns the profile.
     *
     * @return BelongsTo
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
}
