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
use Misaf\VendraStore\Contracts\StorefrontProvisioner;
use Misaf\VendraStore\Support\StorefrontReference;
use Spatie\Multitenancy\Jobs\NotTenantAware;
use Throwable;

/**
 * Removes one storefront's container, off the request lifecycle.
 *
 * Queued for the same reason deployment is: only the storefront worker holds a
 * runtime socket, and an operator closing a store should not wait on a container
 * to stop. It runs on the same queue for that reason.
 *
 * Addressed by slug, not by deployment id, and that is the point of it. This job
 * is dispatched while a store is being force-deleted, so the deployment row it
 * would otherwise look up is removed by the cascade before the worker ever picks
 * the job up. Carrying the slug — the platform's stable handle, and what the
 * container name derives from — is what lets it outlive the record.
 *
 * There is nothing to record afterwards: the row is already gone, so this
 * reaches the provisioner port directly rather than through an action that would
 * have no state left to write.
 */
#[Timeout(120)]
#[Tries(5)]
#[UniqueFor(3600)]
final class DestroyStorefrontJob implements NotTenantAware, ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public function __construct(public readonly string $slug)
    {
        $this->onQueue(ProvisionStorefrontJob::QUEUE);
    }

    /**
     * Removing an absent container is a success: the provisioner treats a 404 as
     * the outcome that was asked for, so a retry after a partial failure — and a
     * second dispatch for a storefront already gone — both settle quietly.
     */
    public function handle(StorefrontProvisioner $provisioner): void
    {
        $provisioner->destroy(new StorefrontReference($this->slug));
    }

    /** @return list<int> */
    public function backoff(): array
    {
        return [5, 30, 120];
    }

    public function uniqueId(): string
    {
        return $this->slug;
    }

    /**
     * A storefront that could not be removed is a leaked container, and by now
     * there is no row left to record that against — so it is named in the log,
     * with the slug an operator needs to finish the job by hand.
     */
    public function failed(?Throwable $exception): void
    {
        Log::error('Destroying a storefront failed; its container may still be running.', [
            'slug' => $this->slug,
            'exception' => $exception?->getMessage(),
        ]);
    }
}
