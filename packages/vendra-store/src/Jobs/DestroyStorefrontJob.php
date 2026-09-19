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
use Misaf\VendraStore\Models\StorefrontDeployment;
use Misaf\VendraStore\Support\StorefrontReference;
use Spatie\Multitenancy\Jobs\NotTenantAware;
use Throwable;

/**
 * Addressed by slug because the deployment row is deleted with the store
 * before the job runs.
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

    public function handle(StorefrontProvisioner $provisioner): void
    {
        /*
         | The force delete freed the slug immediately. A deployment holding it
         | now belongs to a newer store, and the container is that store's.
         */
        if (StorefrontDeployment::query()->where('slug', $this->slug)->exists()) {
            return;
        }

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
     * Log the leaked container's slug so an administrator can remove it by hand.
     */
    public function failed(?Throwable $exception): void
    {
        Log::error('Destroying a storefront failed; its container may still be running.', [
            'slug' => $this->slug,
            'exception' => $exception?->getMessage(),
        ]);
    }
}
