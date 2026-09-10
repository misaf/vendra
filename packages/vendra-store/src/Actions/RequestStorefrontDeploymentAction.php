<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Misaf\VendraStore\Enums\StorefrontDeploymentStatus;
use Misaf\VendraStore\Enums\StorefrontDesiredState;
use Misaf\VendraStore\Jobs\ProvisionStorefrontJob;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Models\StorefrontDeployment;
use Misaf\VendraStore\Models\StorefrontImage;
use Misaf\VendraStore\Support\StorefrontConfigurationMap;
use Misaf\VendraStore\Support\StorefrontConfigurationValidator;
use Misaf\VendraStore\Support\StorefrontRuntimeConfiguration;

/**
 * Records that a store should have a storefront, and asks for it to be built.
 *
 * The row is written whether or not a container runtime is configured: a
 * deployment nobody can act on yet is still the store's intent, and
 * reconciliation picks it up once the estate is up. Dispatching a job certain to
 * fail would only fill the failed-jobs table instead.
 */
final readonly class RequestStorefrontDeploymentAction
{
    public function __construct(private StorefrontRuntimeConfiguration $runtime) {}

    /**
     * @param  array<string, mixed>  $form
     *
     * @throws ValidationException
     */
    public function execute(Store $store, string $domain, array $form): StorefrontDeployment
    {
        $configuration = StorefrontConfigurationMap::toConfiguration($form);

        /*
         | Validate here rather than leaving it to the provisioner. The image
         | refuses to boot on an incomplete configuration, so a field missed at
         | this point used to surface minutes later as a failed deployment and a
         | crash-looping container instead of as an error on the form.
         */
        Validator::make($configuration, StorefrontConfigurationValidator::deploymentRules())->validate();

        $selection = Validator::make($form, [
            'storefront_image_id' => [
                'required',
                'integer',
                Rule::exists(StorefrontImage::class, 'id')->where('active', true),
            ],
        ])->validate();
        $storefrontImage = StorefrontImage::query()->findOrFail(Arr::integer($selection, 'storefront_image_id'));

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
