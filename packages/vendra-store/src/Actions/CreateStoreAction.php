<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Support\StoreQuota;
use Misaf\VendraSubscription\Contracts\SubscriptionSubscriber;
use Misaf\VendraSubscription\Exceptions\SubscriptionLimitException;
use Misaf\VendraSupport\Exceptions\EntitlementExceededException;
use Misaf\VendraTenant\Enums\TenantProvisioningStatus;
use Misaf\VendraUser\Actions\CreateUserAction;
use Misaf\VendraUser\Models\User;

/**
 * The reseller is typed as a subscription subscriber so the store package does
 * not depend on the reseller package.
 */
final readonly class CreateStoreAction
{
    public function __construct(
        private CreateUserAction $createUserAction,
        private StoreQuota $storeQuota,
    ) {}

    /**
     * The caller normalizes and validates the domain first. A reseller's store
     * needs a plan with the custom domain feature to use a custom domain.
     *
     * @param  (Model&SubscriptionSubscriber)|null  $reseller
     * @return array{store: Store, user: User}
     *
     * @throws EntitlementExceededException
     * @throws SubscriptionLimitException
     */
    public function execute(
        string $name,
        string $domain,
        string $username,
        string $email,
        string $password,
        ?SubscriptionSubscriber $reseller = null,
        bool $shouldSeed = false,
    ): array {
        return DB::transaction(function () use (
            $name,
            $domain,
            $username,
            $email,
            $password,
            $reseller,
            $shouldSeed,
        ): array {
            $resellerId = null;

            if ($reseller !== null) {
                $lockedReseller = $this->storeQuota->lockAndAssertCanCreateStore($reseller);
                $this->storeQuota->assertCanUseDomain($lockedReseller, $domain);
                $resellerId = $lockedReseller->getKey();
            }

            $createdStore = Store::query()->create([
                'reseller_id' => $resellerId,
                'name' => $name,
                'active' => false,
                'provisioning_status' => TenantProvisioningStatus::Pending,
                'provisioning_should_seed' => $shouldSeed,
            ]);

            $createdStore->execute(fn () => $createdStore->storeDomains()->create([
                'name' => $domain,
                'active' => true,
                'is_primary' => true,
            ]));

            $createdUser = $this->createUserAction->execute(
                tenant: $createdStore,
                username: $username,
                email: $email,
                password: $password,
                isVerified: true,
            );

            $createdUser->tenants()->syncWithoutDetaching([$createdStore->getKey()]);

            return [
                'store' => $createdStore,
                'user' => $createdUser,
            ];
        }, attempts: 5);
    }
}
