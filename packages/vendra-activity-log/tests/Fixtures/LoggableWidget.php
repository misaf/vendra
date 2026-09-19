<?php

declare(strict_types=1);

namespace Misaf\VendraActivityLog\Tests\Fixtures;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Misaf\VendraSupport\Contracts\ShouldLogActivity;

#[Fillable(['name', 'description'])]
#[Table(name: 'activity_log_widgets')]
final class LoggableWidget extends Model implements ShouldLogActivity
{
    use HasFactory;
}
