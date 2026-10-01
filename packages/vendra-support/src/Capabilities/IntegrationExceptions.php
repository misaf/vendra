<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Capabilities;

use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Throwable;

final class IntegrationExceptions
{
    public static function report(Throwable $exception): void
    {
        if ($exception instanceof QueryException) {
            $errorInfo = $exception->errorInfo ?? [];

            // Optional providers can be registered before their tables are migrated.
            if (in_array(Arr::get($errorInfo, 0, null), ['42S02', '42P01'], true)
                || ((Arr::get($errorInfo, 0, null)) === 'HY000'
                    && (Arr::get($errorInfo, 1, null)) === 1
                    && str_contains($exception->getPrevious()?->getMessage() ?? '', 'no such table:'))) {
                return;
            }
        }

        report($exception);
    }
}
