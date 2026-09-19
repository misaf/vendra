<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\Attributes\UniqueFor;
use Misaf\VendraStore\Contracts\StorefrontProvisioner;
use Misaf\VendraStore\Models\StorefrontDeployment;
use Misaf\VendraStore\Support\StorefrontReference;
use Spatie\Multitenancy\Jobs\NotTenantAware;

/**
 * Skipped if the storefront was stopped before the job ran.
 */
#[Timeout(300)]
#[Tries(3)]
#[UniqueFor(3600)]
final class RestartStorefrontJob implements NotTenantAware, ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public function __construct(public readonly int $deploymentId)
    {
        $this->onQueue(ProvisionStorefrontJob::QUEUE);
    }

    public function handle(StorefrontProvisioner $provisioner): void
    {
        $deployment = StorefrontDeployment::query()->find($this->deploymentId);

        if (! $deployment instanceof StorefrontDeployment || ! $deployment->desired_state->expectsRunning()) {
            return;
        }

        $provisioner->restart(StorefrontReference::for($deployment));
    }

    /** @return list<int> */
    public function backoff(): array
    {
        return [10, 60, 300];
    }

    public function uniqueId(): string
    {
        return (string) $this->deploymentId;
    }
}
