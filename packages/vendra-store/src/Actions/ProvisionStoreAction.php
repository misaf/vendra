<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Misaf\VendraPermission\Actions\CreateRoleAction;
use Misaf\VendraStore\Jobs\CompleteStoreProvisioningJob;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Models\StoreDomain;
use Misaf\VendraSubscription\Contracts\SubscriptionSubscriber;
use Misaf\VendraSupport\Context\ContextKeys;
use Misaf\VendraSupport\Context\RequestJobContext;
use Misaf\VendraUser\Models\User;

/**
 * Creates a store and everything it needs to be usable: the administrator user,
 * the administrator role, and the queued work that finishes provisioning.
 *
 * The console and the reseller panel both call this — they differ only in which
 * reseller they resolve — so the flow exists once instead of being copied into
 * each panel.
 */
final readonly class ProvisionStoreAction
{
    public function __construct(
        private CreateStoreAction $createStoreAction,
        private CreateRoleAction $createRoleAction,
    ) {}

    /**
     * @param array{
     *     domain: string,
     *     email: string,
     *     name?: string,
     *     username?: string
     * } $data
     * @param  (Model&SubscriptionSubscriber)|null  $reseller
     * @return array{store: Store, user: User, password: string}
     */
    public function execute(array $data, bool $shouldSeed = false, ?string $password = null, ?SubscriptionSubscriber $reseller = null): array
    {
        $password ??= Str::password(length: 8, letters: true, numbers: true, symbols: false);
        $domain = StoreDomain::normalizeDomain(Arr::get($data, 'domain'));
        $name = Arr::get($data, 'name', Str::headline(Str::before($domain, '.')));
        $username = Arr::get($data, 'username', $this->usernameFromEmail(Arr::get($data, 'email')));

        $result = DB::transaction(function () use ($data, $domain, $name, $username, $password, $reseller, $shouldSeed): array {
            $result = $this->createStoreAction->execute(
                name: $name,
                domain: $domain,
                username: $username,
                email: Arr::get($data, 'email'),
                password: $password,
                reseller: $reseller,
                shouldSeed: $shouldSeed,
            );

            $role = $this->createRoleAction->execute(
                tenant: Arr::get($result, 'store'),
                name: Config::string('vendra-permission.admin_role'),
                /*
                | The tenant administrator role always lives on the
                | tenant-facing guard. Guard::getDefaultName() resolves
                | against the ambient default guard, which Filament switches
                | per panel — provisioning from the console panel would
                | otherwise mint the role for the `console` guard, where the
                | tenant administration actions never look for it.
                */
                guardName: 'web',
            );

            Arr::get($result, 'store')->execute(fn () => Arr::get($result, 'user')->assignRole($role));

            return [
                ...$result,
                'password' => $password,
            ];
        });

        new RequestJobContext(
            traceId: RequestJobContext::resolveTraceId(),
            operation: 'store_provision_dispatch',
            tenantId: Arr::get($result, 'store')->id,
            metadata: [ContextKeys::RESELLER_ID => Arr::get($result, 'store')->reseller_id],
        )->scope(
            fn () => dispatch(new CompleteStoreProvisioningJob(Arr::get($result, 'store')->id))->afterCommit(),
        );

        return $result;
    }

    private function usernameFromEmail(string $email): string
    {
        $username = Str::of($email)
            ->before('@')
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9_]+/', '_')
            ->trim('_')
            ->limit(12, '')
            ->toString();

        return Str::length($username) >= 3 ? $username : 'admin';
    }
}
