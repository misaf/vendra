<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Tenancy\Listeners;

use Illuminate\Support\Facades\Artisan;
use Misaf\VendraSupport\Tenancy\Events\TenantProvisioned;
use Misaf\VendraSupport\Tenancy\TenantSeeders;
use RuntimeException;

final readonly class RunTenantSeeders
{
    public function __construct(private TenantSeeders $seeders) {}

    public function handle(TenantProvisioned $event): void
    {
        if (! $event->shouldSeed) {
            return;
        }

        $tenant = $event->tenant->getKey();

        foreach ($this->seeders->ordered() as $command) {
            $exitCode = Artisan::call($command, [
                'tenant' => $tenant,
                'seeders' => ['all'],
            ]);

            if ($exitCode !== 0) {
                throw new RuntimeException(sprintf(
                    'Seed command [%s] failed with exit code [%d].',
                    $command,
                    $exitCode,
                ));
            }
        }
    }
}
