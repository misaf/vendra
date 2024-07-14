<?php

declare(strict_types=1);

namespace Termehsoft\User\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method BelongsTo userProfile
 */
interface BelongsToUserProfile
{
    /**
     * @return BelongsTo
     */
    public function userProfile(): BelongsTo;
}
