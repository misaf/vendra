<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Filament\Pages;

use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Misaf\VendraStore\Actions\ProvisionStoreAction;
use Misaf\VendraStore\Actions\RequestStorefrontDeploymentAction;
use Misaf\VendraSubscription\Contracts\SubscriptionSubscriber;
use Misaf\VendraSubscription\Exceptions\SubscriptionLimitException;

/**
 * Creating a store is the same operation in every panel: provision the store,
 * hand the administrator their credentials, and request the storefront.
 *
 * Only the billing reseller is resolved differently — the console picks one from
 * the form, the reseller panel uses the authenticated reseller's own — so that
 * is the single hook subclasses fill in. The two pages previously carried
 * identical copies of everything below it, which is how they drift.
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
                ->title(__('console.store_limit_reached'))
                ->body($exception->getMessage())
                ->send();

            throw new Halt;
        }

        Notification::make()
            ->success()
            ->title(__('console.store_created'))
            ->body(__('console.administrator_credentials', [
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
     * The reseller this store is billed to, or null for a store the platform owns
     * directly.
     *
     * @param  array<string, mixed>  $data
     * @return (Model&SubscriptionSubscriber)|null
     */
    abstract protected function resolveReseller(array $data): ?SubscriptionSubscriber;

    /**
     * Whether a storefront was asked for.
     *
     * Forms that make the storefront mandatory omit the toggle entirely, so an
     * absent key means yes; only an explicit "off" skips it.
     *
     * @param  array<string, mixed>  $data
     */
    protected function shouldRequestStorefront(array $data): bool
    {
        return false !== (Arr::get($data, 'create_storefront', true));
    }
}
