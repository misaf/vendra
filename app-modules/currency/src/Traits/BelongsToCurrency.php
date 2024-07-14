<?php

declare(strict_types=1);

namespace Termehsoft\Currency\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Termehsoft\Currency\Models\Currency;

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
