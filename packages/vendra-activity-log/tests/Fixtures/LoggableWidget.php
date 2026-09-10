<?php

declare(strict_types=1);

namespace Misaf\VendraActivityLog\Tests\Fixtures;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Misaf\VendraSupport\Contracts\ShouldLogActivity;

/**
 * A model that opts into activity logging solely by implementing the marker
 * contract, without depending on the activity-log package.
 */
#[Fillable(['name', 'description'])]
#[Table(name: 'activity_log_widgets')]
final class LoggableWidget extends Model implements ShouldLogActivity
{
    use HasFactory;
}
