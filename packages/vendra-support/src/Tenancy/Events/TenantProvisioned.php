<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Tenancy\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Database\Eloquent\Model;

/**
 * Fired after a tenant has been provisioned. Modules subscribe to run their
 * own tenant-scoped seeders (see TenantSeeders) or other provisioning side
 * effects, without the provisioning module needing to know they exist.
 */
final readonly class TenantProvisioned implements ShouldDispatchAfterCommit
{
    public function __construct(
        public Model $tenant,
        public bool $shouldSeed = false,
    ) {}
}
