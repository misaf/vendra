<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Actions;

use Illuminate\Support\Arr;
use Misaf\VendraStore\Enums\StorefrontDeploymentStatus;
use Misaf\VendraStore\Enums\StorefrontDesiredState;
use Misaf\VendraStore\Jobs\ProvisionStorefrontJob;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Models\StorefrontDeployment;
use Misaf\VendraStore\Models\StorefrontImage;
use Misaf\VendraStore\Support\StorefrontConfigurationMap;
use Misaf\VendraStore\Support\StorefrontRuntimeConfiguration;

/**
 * The row is written even without a configured runtime, so reconciliation can
 * pick it up later; the job is only dispatched when it can succeed.
 */
final readonly class RequestStorefrontDeploymentAction
{
    public function __construct(private StorefrontRuntimeConfiguration $runtime) {}

    /**
     * The caller validates the deployment identity and active image first.
     *
     * @param  array<string, mixed>  $form
     */
    public function execute(Store $store, string $domain, array $form): StorefrontDeployment
    {
        $configuration = StorefrontConfigurationMap::toConfiguration([
            ...StorefrontConfigurationMap::sampleForm($store->name, 'contact@'.$domain),
            ...$form,
        ]);

        $storefrontImage = StorefrontImage::query()->findOrFail(Arr::integer($form, 'storefront_image_id'));

        $deployment = StorefrontDeployment::query()->create([
            'store_id' => $store->id,
            'storefront_image_id' => $storefrontImage->id,
            'slug' => Arr::string($form, 'storefront_slug'),
            'domain' => $domain,
            'configuration' => $configuration,
            'status' => StorefrontDeploymentStatus::Pending,
            'desired_state' => StorefrontDesiredState::Running,
        ]);

        if ($this->runtime->isConfigured()) {
            dispatch(new ProvisionStorefrontJob($deployment->id))->afterCommit();
        }

        return $deployment;
    }
}
