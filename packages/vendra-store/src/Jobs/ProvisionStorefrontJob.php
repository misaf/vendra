<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\Attributes\UniqueFor;
use Misaf\VendraStore\Actions\DeployStoreStorefrontAction;
use Misaf\VendraStore\Models\StorefrontDeployment;
use Spatie\Multitenancy\Jobs\NotTenantAware;
use Throwable;

#[Timeout(300)]
#[Tries(5)]
#[UniqueFor(3600)]
final class ProvisionStorefrontJob implements NotTenantAware, ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * The only queue whose worker holds the container-runtime socket.
     *
     * The socket is root-equivalent, so Horizon deliberately does not run this queue.
     */
    public const string QUEUE = 'storefronts';

    private const int MAX_PASSES = 3;

    public function __construct(
        public readonly int $deploymentId,
        public readonly bool $force = false,
    ) {
        $this->onQueue(self::QUEUE);
    }

    public function handle(DeployStoreStorefrontAction $deploy): void
    {
        $deployment = StorefrontDeployment::query()->findOrFail($this->deploymentId);
        $force = $this->force;

        /*
         | A domain replace or suspension that lands while this runs dispatches a
         | job the unique lock discards, and this run placed the old intent. It
         | converges onto the new one here instead, a bounded number of times.
         */
        for ($pass = 0; $pass < self::MAX_PASSES; $pass++) {
            $intent = $deployment->intentFingerprint();

            $deploy->execute($deployment, force: $force);

            $deployment = StorefrontDeployment::query()->findOrFail($this->deploymentId);

            if ($deployment->intentFingerprint() === $intent) {
                return;
            }

            $force = true;
        }
    }

    /** @return list<int> */
    public function backoff(): array
    {
        return [5, 30, 120, 300];
    }

    public function uniqueId(): string
    {
        return (string) $this->deploymentId;
    }

    /**
     * Record the failure once retries are exhausted, not on every attempt.
     */
    public function failed(?Throwable $exception): void
    {
        StorefrontDeployment::query()->find($this->deploymentId)?->markFailed(
            $exception === null ? 'Storefront provisioning failed.' : $exception->getMessage(),
        );
    }
}
