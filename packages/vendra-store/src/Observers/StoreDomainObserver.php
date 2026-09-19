<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Observers;

use Misaf\VendraStore\Models\StoreDomain;
use Misaf\VendraStore\Support\StorefrontOrigins;

final class StoreDomainObserver
{
    public function saved(StoreDomain $domain): void
    {
        StorefrontOrigins::forget();
    }

    public function deleted(StoreDomain $domain): void
    {
        StorefrontOrigins::forget();
    }
}
