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
#[Timeout(300)]
#[Tries(3)]
#[UniqueFor(3600)]
final class ReconcileStorefrontJob implements NotTenantAware, ShouldBeUnique, ShouldQueue
{
    use Queueable;

    private const int MAX_PASSES = 3;

    public function __construct(public readonly int $deploymentId)
    {
        $this->onQueue(ProvisionStorefrontJob::QUEUE);
    }

    public function handle(ReconcileStoreStorefrontAction $reconcile): void
    {
        $deployment = StorefrontDeployment::query()->find($this->deploymentId);

        // An intent change dispatched while this ran was discarded by the unique lock, so converge again.
        for ($pass = 0; $pass < self::MAX_PASSES && $deployment instanceof StorefrontDeployment; $pass++) {
            $intent = $deployment->intentFingerprint();

            $reconcile->execute($deployment);

            $deployment = StorefrontDeployment::query()->find($this->deploymentId);

            if ($deployment?->intentFingerprint() === $intent) {
                return;
            }
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
     * storefront in the panel and feed it to `vendra-store:retry-failed`.
     */
    public function failed(?Throwable $exception): void
    {
        Log::error('Reconciling a storefront failed; its runtime state is unknown.', [
            'deployment_id' => $this->deploymentId,
            'exception' => $exception?->getMessage(),
        ]);
    }
}
