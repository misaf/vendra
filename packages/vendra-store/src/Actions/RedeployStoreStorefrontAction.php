<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Actions;

use Misaf\VendraStore\Enums\StorefrontDesiredState;
use Misaf\VendraStore\Jobs\ProvisionStorefrontJob;
use Misaf\VendraStore\Models\StorefrontDeployment;

final class RedeployStoreStorefrontAction
{
    public function execute(StorefrontDeployment $deployment): void
    {
        $deployment->markDesiredState(StorefrontDesiredState::Running);

        dispatch(new ProvisionStorefrontJob($deployment->id, force: true))->afterCommit();
    }
}
