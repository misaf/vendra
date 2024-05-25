<?php

declare(strict_types=1);

namespace App\Models\Currency\Traits;

use App\Models\Currency\Currency;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
