<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Support;

use Illuminate\Support\Facades\Cache;
use Throwable;

final class StorefrontRuntimeHealth
{
    private const string CACHE_KEY = 'vendra-store:storefront-runtime-health';

    public function latest(): ?StorefrontRuntimeHealthReport
    {
        $report = Cache::get(self::CACHE_KEY);

        if (! is_array($report)) {
            return null;
        }

        try {
            return StorefrontRuntimeHealthReport::fromArray($report);
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }
    }

    public function put(StorefrontRuntimeHealthReport $report): void
    {
        Cache::forever(self::CACHE_KEY, $report->toArray());
    }
}
