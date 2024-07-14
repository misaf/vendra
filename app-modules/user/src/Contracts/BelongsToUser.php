<?php

declare(strict_types=1);

namespace App\Models\User\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Znck\Eloquent\Relations\BelongsToThrough;

/**
 * @method BelongsTo|BelongsToThrough user
 */
interface BelongsToUser
{
    /**
     * @return BelongsTo|BelongsToThrough
     */
    public function user(): BelongsTo|BelongsToThrough;
}
