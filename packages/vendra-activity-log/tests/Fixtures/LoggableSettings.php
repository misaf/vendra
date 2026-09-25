<?php

declare(strict_types=1);

namespace Misaf\VendraActivityLog\Tests\Fixtures;

use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Spatie\LaravelSettings\Settings;

final class LoggableSettings extends Settings implements ShouldLogActivity
{
    public string $name;

    public int $limit;

    public static function group(): string
    {
        return 'activity_log_fixture';
    }
}
