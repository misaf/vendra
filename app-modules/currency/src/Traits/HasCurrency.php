<?php

declare(strict_types=1);

namespace Termehsoft\Currency\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Termehsoft\Currency\Models\Currency;

trait HasCurrency
{
    /**
     * Get the user that owns the profile.
     *
     * @return HasMany
     */
    public function currencies(): HasMany
    {
        return $this->hasMany(Currency::class);
    }
}
