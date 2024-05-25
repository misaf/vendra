<?php

declare(strict_types=1);

namespace App\Models\Currency\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;

interface HasCurrency
{
    /**
     * Get the user that owns the profile.
     *
     * @return HasMany
     */
    public function currencies(): HasMany;
}
