<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Actions;

use Misaf\VendraStore\Services\StorefrontContainerRuntime;
use Misaf\VendraStore\Support\StorefrontRuntimeHealth;
use Misaf\VendraStore\Support\StorefrontRuntimeHealthReport;
use Misaf\VendraStore\Support\StorefrontSettings;
use Throwable;

/**
 * Probes the container runtime and the storefront network, and records what it
 * found for the panels. Runs on the storefront worker, the only process that can
 * reach the runtime.
 */
final readonly class RecordStorefrontRuntimeHealthAction
{
    public function __construct(
        private StorefrontContainerRuntime $runtime,
        private StorefrontSettings $settings,
        private StorefrontRuntimeHealth $health,
    ) {}

    public function execute(): StorefrontRuntimeHealthReport
    {
        $status = $this->runtime->status();
        $network = null;
        $networkError = null;

        if ($status->reachable) {
            try {
                $network = $this->runtime->findNetwork($this->settings->network);
            } catch (Throwable $exception) {
                report($exception);

                $networkError = $exception->getMessage();
            }
        }

        $report = new StorefrontRuntimeHealthReport(
            status: $status,
            networkName: $this->settings->network,
            network: $network,
            networkError: $networkError,
            checkedAt: now()->toImmutable(),
        );

        $this->health->put($report);

        return $report;
    }
}
