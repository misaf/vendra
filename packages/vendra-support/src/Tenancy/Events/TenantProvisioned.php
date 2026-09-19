<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Tenancy\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Database\Eloquent\Model;

final readonly class TenantProvisioned implements ShouldDispatchAfterCommit
{
    public function __construct(
        public Model $tenant,
        public bool $shouldSeed = false,
    ) {}
}
