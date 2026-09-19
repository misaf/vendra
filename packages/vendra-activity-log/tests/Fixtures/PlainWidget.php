<?php

declare(strict_types=1);

namespace Misaf\VendraActivityLog\Tests\Fixtures;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A model without the marker contract, which is never logged.
 */
#[Fillable(['name', 'description'])]
#[Table(name: 'activity_log_widgets')]
final class PlainWidget extends Model
{
    use HasFactory;
}
