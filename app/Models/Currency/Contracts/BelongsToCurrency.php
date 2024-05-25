<?php

declare(strict_types=1);

namespace App\Models\Currency\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Znck\Eloquent\Relations\BelongsToThrough;

interface BelongsToCurrency
{
    /**
     * Get the user that owns the profile.
     *
     * @return BelongsTo|BelongsToThrough
     */
    public function currency(): BelongsTo|BelongsToThrough;
}
