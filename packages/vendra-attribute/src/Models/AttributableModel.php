<?php

declare(strict_types=1);

namespace Misaf\VendraAttribute\Models;

use Illuminate\Database\Eloquent\Model;
use Misaf\VendraAttribute\Concerns\HasAttributeValues;

abstract class AttributableModel extends Model
{
    use HasAttributeValues;
}
