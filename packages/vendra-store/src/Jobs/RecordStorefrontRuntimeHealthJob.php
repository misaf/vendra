<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\Attributes\UniqueFor;
use Misaf\VendraStore\Actions\RecordStorefrontRuntimeHealthAction;
use Spatie\Multitenancy\Jobs\NotTenantAware;

/**
 * Records the container runtime's health on the storefront queue, where the
 * runtime socket is. Scheduled every minute; a backed-up queue drops the extra
 * dispatches instead of stacking probes.
 */
#[Timeout(60)]
#[Tries(1)]
#[UniqueFor(120)]
final class RecordStorefrontRuntimeHealthJob implements NotTenantAware, ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        $this->onQueue(ProvisionStorefrontJob::QUEUE);
    }

    public function handle(RecordStorefrontRuntimeHealthAction $recordHealth): void
    {
        $recordHealth->execute();
    }
}
