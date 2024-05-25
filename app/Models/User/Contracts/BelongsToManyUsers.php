<?php

declare(strict_types=1);

namespace App\Models\User\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

interface BelongsToManyUsers
{
    public function users(): BelongsToMany;
}
