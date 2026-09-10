<?php

declare(strict_types=1);

namespace Misaf\VendraActivityLog\Tests\Fixtures;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A model that does NOT implement the marker contract and therefore must never
 * be logged, even while activity logging is enabled.
 */
#[Fillable(['name', 'description'])]
#[Table(name: 'activity_log_widgets')]
final class PlainWidget extends Model
{
    use HasFactory;
}
