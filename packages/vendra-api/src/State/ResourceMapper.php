<?php

declare(strict_types=1);

namespace Misaf\VendraApi\State;

use Illuminate\Database\Eloquent\Model;

interface ResourceMapper
{
    public function map(Model $model): object;
}
