<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Models\StoreDomain;
use Misaf\VendraStore\Support\StoreQuota;
use Misaf\VendraSubscription\Contracts\SubscriptionSubscriber;
use Misaf\VendraTenant\Enums\TenantProvisioningStatus;
use Misaf\VendraUser\Actions\CreateUserAction;
use Misaf\VendraUser\Models\User;

/**
 * Creates one store, its first domain, and its owner user in a single
 * transaction.
 *
 * A store is either created directly from the console with no billing owner, or
 * by a reseller that owns it. Which panel the request came from is not recorded,
 * because it is not business state. The owner is typed as a subscription
 * subscriber rather than a concrete Reseller, so the store domain stays below
 * the reseller domain in the dependency graph.
 */
final class CreateStoreAction
{
    public function __construct(
        private readonly CreateUserAction $createUserAction,
        private readonly StoreQuota $storeQuota,
    ) {}

    /**
     * @param  (Model&SubscriptionSubscriber)|null  $owner  the reseller billed for
     *                                                      this store, or null for
     *                                                      a direct console store
     * @return array{store: Store, user: User}
     */
    public function execute(
        string $name,
        string $domain,
        string $username,
        string $email,
        string $password,
        ?SubscriptionSubscriber $owner = null,
        bool $shouldSeed = false,
    ): array {
        $domain = StoreDomain::normalizeDomain($domain);
        Validator::make(
            ['domain' => $domain],
            ['domain' => StoreDomain::activeDomainRules()],
        )->validate();

        return DB::transaction(function () use (
            $name,
            $domain,
            $username,
            $email,
            $password,
            $owner,
            $shouldSeed,
        ): array {
            $ownerId = null;

            if ($owner !== null) {
                /*
                 | Re-read the owner under a row lock before counting: two
                 | concurrent creations would otherwise both see the last free
                 | slot in the plan and both take it.
                 */
                $lockedOwner = $owner->newQuery()->lockForUpdate()->whereKey($owner->getKey())->first();

                if (! $lockedOwner instanceof Model || ! $lockedOwner instanceof SubscriptionSubscriber) {
                    throw (new ModelNotFoundException)->setModel($owner::class);
                }

                $this->storeQuota->assertCanCreateStore($lockedOwner);

                $ownerId = $lockedOwner->getKey();
            }

            $createdStore = Store::query()->create([
                'reseller_id' => $ownerId,
                'name' => $name,
                'active' => false,
                'provisioning_status' => TenantProvisioningStatus::Pending,
                'provisioning_should_seed' => $shouldSeed,
            ]);

            $createdStore->execute(fn () => $createdStore->storeDomains()->create([
                'name' => $domain,
                'active' => true,
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
