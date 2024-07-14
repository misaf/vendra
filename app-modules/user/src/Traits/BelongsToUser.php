<?php

declare(strict_types=1);

namespace Termehsoft\User\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Termehsoft\User\Models\User;

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
