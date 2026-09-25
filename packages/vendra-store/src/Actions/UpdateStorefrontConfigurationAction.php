<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Actions;

use Illuminate\Support\Facades\DB;
use Misaf\VendraStore\Jobs\ProvisionStorefrontJob;
use Misaf\VendraStore\Models\StorefrontDeployment;
use Misaf\VendraStore\Support\StorefrontConfigurationMap;
use Misaf\VendraStore\Support\StorefrontRuntimeConfiguration;

final readonly class UpdateStorefrontConfigurationAction
{
    public function __construct(private StorefrontRuntimeConfiguration $runtime) {}

    /** @param array<string, mixed> $form */
    public function execute(StorefrontDeployment $deployment, array $form): void
    {
        DB::transaction(function () use ($deployment, $form): void {
            $deployment = StorefrontDeployment::query()->lockForUpdate()->findOrFail($deployment->id);
            $configuration = StorefrontConfigurationMap::updateEditable($deployment->configuration, $form);

            if ($configuration === $deployment->configuration) {
                return;
            }

            $deployment->update(['configuration' => $configuration]);

            if ($deployment->desired_state->expectsRunning() && $deployment->storeMayServe() && $this->runtime->isConfigured()) {
                dispatch(new ProvisionStorefrontJob($deployment->id, force: true))->afterCommit();
            }
        });
    }
}
