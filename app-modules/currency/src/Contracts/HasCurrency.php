<?php

declare(strict_types=1);

namespace Termehsoft\Currency\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method HasMany currencies
 */
interface HasCurrency
{
    /**
     * Get the user that owns the profile.
     *
     * @return HasMany
     */
    public function currencies(): HasMany;
}
