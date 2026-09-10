<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\Attributes\UniqueFor;
use Illuminate\Support\Facades\Log;
use Misaf\VendraStore\Actions\ReconcileStoreStorefrontAction;
use Misaf\VendraStore\Models\StorefrontDeployment;
use Spatie\Multitenancy\Jobs\NotTenantAware;
use Throwable;

/**
 * Converges one storefront off the request lifecycle.
 *
 * Runs on the storefront queue for the same reason provisioning does: observing
 * and correcting a container needs the runtime socket, and only that worker
 * holds one.
 */
#[Timeout(180)]
#[Tries(3)]
#[UniqueFor(3600)]
final class ReconcileStorefrontJob implements NotTenantAware, ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public function __construct(public readonly int $deploymentId)
    {
        $this->onQueue(ProvisionStorefrontJob::QUEUE);
    }

    public function handle(ReconcileStoreStorefrontAction $reconcile): void
    {
        $deployment = StorefrontDeployment::query()->find($this->deploymentId);

        // Deleted between selection and execution: there is nothing left to converge.
        if ($deployment instanceof StorefrontDeployment) {
            $reconcile->execute($deployment);
        }
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

    /**
     * A failed convergence is not a failed deployment.
     *
     * The storefront may be serving perfectly and merely unreadable, so the row's
     * status is left alone rather than marked Failed — that status means
     * provisioning gave up, and claiming it here would strand a healthy
     * storefront in the panel and feed it to `storefront:retry-failed`.
     */
    public function failed(?Throwable $exception): void
    {
        Log::error('Reconciling a storefront failed; its runtime state is unknown.', [
            'deployment_id' => $this->deploymentId,
            'exception' => $exception?->getMessage(),
        ]);
    }
}
