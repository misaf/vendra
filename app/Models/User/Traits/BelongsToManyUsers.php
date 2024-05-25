<?php

declare(strict_types=1);

namespace App\Models\User\Traits;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait BelongsToManyUser
{
    /**
     * Get the user that owns the profile.
     *
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
