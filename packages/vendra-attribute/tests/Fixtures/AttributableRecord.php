<?php

declare(strict_types=1);

namespace Misaf\VendraAttribute\Tests\Fixtures;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Misaf\VendraAttribute\Concerns\HasAttributeValues;

#[Unguarded]
#[Table(name: 'attributable_records')]
final class AttributableRecord extends Model
{
    use HasAttributeValues;
    use HasFactory;
}
