<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Filament\Pages;

use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Misaf\VendraStore\Actions\ProvisionStoreAction;
use Misaf\VendraStore\Actions\RequestStorefrontDeploymentAction;
use Misaf\VendraStore\Models\StorefrontImage;
use Misaf\VendraStore\Support\StorefrontConfigurationMap;
use Misaf\VendraStore\Support\StorefrontConfigurationValidator;
use Misaf\VendraSubscription\Contracts\SubscriptionSubscriber;
use Misaf\VendraSubscription\Exceptions\SubscriptionLimitException;

/**
 * Panels only differ in how they resolve the billing reseller.
 */
abstract class CreateStorePage extends CreateRecord
{
    /**
     * @param  array<string, mixed>  $data
     *
     * @throws Halt
     */
    protected function handleRecordCreation(array $data): Model
    {
        $domain = Arr::get($data, 'domain', null);
        $email = Arr::get($data, 'email', null);

        throw_if(! is_string($domain) || ! is_string($email), InvalidArgumentException::class, 'Invalid store details provided.');

        if ($this->shouldRequestStorefront($data)) {
            $this->validateStorefrontRequest($data);
        }

        try {
            $result = resolve(ProvisionStoreAction::class)->execute(
                data: [
                    'domain' => $domain,
                    'email' => $email,
                ],
                reseller: $this->resolveReseller($data),
            );
        } catch (SubscriptionLimitException $exception) {
            Notification::make()
                ->danger()
                ->title(__('vendra-store::messages.store_limit_reached'))
                ->body($exception->getMessage())
                ->send();

            throw new Halt;
        }

        Notification::make()
            ->success()
            ->title(__('vendra-store::messages.store_created'))
            ->body(__('vendra-store::attributes.administrator_credentials', [
                'username' => Arr::get($result, 'user')->username,
                'password' => Arr::get($result, 'password'),
            ]))
            ->persistent()
            ->send();

        if ($this->shouldRequestStorefront($data)) {
            resolve(RequestStorefrontDeploymentAction::class)->execute(
                store: Arr::get($result, 'store'),
                domain: $domain,
                form: $data,
            );
        }

        return Arr::get($result, 'store');
    }

    /**
     * Validate the storefront before the store is provisioned.
     *
     * The image will not boot on an incomplete configuration, so errors surface
     * on the form instead of as a failed deployment.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    private function validateStorefrontRequest(array $data): void
    {
        Validator::make(StorefrontConfigurationMap::toConfiguration($data), StorefrontConfigurationValidator::deploymentRules())->validate();

        Validator::make($data, [
            'storefront_image_id' => [
                'required',
                'integer',
                Rule::exists(StorefrontImage::class, 'id')->where('active', true),
            ],
        ])->validate();
    }

    /**
     * Get the reseller billed for the store, or null for a platform store.
     *
     * @param  array<string, mixed>  $data
     * @return (Model&SubscriptionSubscriber)|null
     */
    abstract protected function resolveReseller(array $data): ?SubscriptionSubscriber;

    /**
     * Determine if a storefront was requested; a missing toggle means yes.
     *
     * @param  array<string, mixed>  $data
     */
    protected function shouldRequestStorefront(array $data): bool
    {
        return false !== (Arr::get($data, 'create_storefront', true));
    }
}
