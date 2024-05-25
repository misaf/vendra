<?php

declare(strict_types=1);

namespace App\Models\User\Traits;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToUser
{
    /**
     * Get the user that owns the profile.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
