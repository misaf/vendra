<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Actions;

use Misaf\VendraStore\Contracts\StorefrontProvisioner;
use Misaf\VendraStore\Enums\StorefrontDesiredState;
use Misaf\VendraStore\Models\StorefrontDeployment;
use Misaf\VendraStore\Support\StorefrontReference;

/**
 * Stops an already-deployed storefront.
 *
 * This is not a deployment: nothing is built, pulled, or replaced, so the
 * recorded status and image stay exactly as they were. What does change is the
 * recorded desired state, so the next reconciliation pass converges on this
 * intent rather than reversing it.
 */
final readonly class StopStoreStorefrontAction
{
    public function __construct(private StorefrontProvisioner $provisioner) {}

    public function execute(StorefrontDeployment $deployment): void
    {
        $this->provisioner->stop(StorefrontReference::for($deployment));

        $deployment->markDesiredState(StorefrontDesiredState::Stopped);
    }
}
