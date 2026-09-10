<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Actions;

use Illuminate\Database\Eloquent\Model;
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
use Spatie\Permission\Guard;

/**
 * Creates a store and everything it needs to be usable: the owner user, the
 * administrator role, and the queued work that finishes provisioning.
 *
 * The console and the reseller panel both call this — they differ only in which
 * owner they resolve — so the flow exists once instead of being copied into
 * each panel.
 */
final class ProvisionStoreAction
{
    public function __construct(
        private readonly CreateStoreAction $createStoreAction,
        private readonly CreateRoleAction $createRoleAction,
    ) {}

    /**
     * @param array{
     *     domain: string,
     *     email: string,
     *     name?: string,
     *     username?: string
     * } $data
     * @param  (Model&SubscriptionSubscriber)|null  $owner
     * @return array{store: Store, user: User, password: string}
     */
    public function execute(array $data, bool $shouldSeed = false, ?string $password = null, ?SubscriptionSubscriber $owner = null): array
    {
        $password ??= Str::password(length: 8, letters: true, numbers: true, symbols: false);
        $domain = StoreDomain::normalizeDomain($data['domain']);
        $name = $data['name'] ?? Str::headline(Str::before($domain, '.'));
        $username = $data['username'] ?? $this->usernameFromEmail($data['email']);

        $result = DB::transaction(function () use ($data, $domain, $name, $username, $password, $owner, $shouldSeed): array {
            $result = $this->createStoreAction->execute(
                name: $name,
                domain: $domain,
                username: $username,
                email: $data['email'],
                password: $password,
                owner: $owner,
                shouldSeed: $shouldSeed,
            );

            $role = $this->createRoleAction->execute(
                tenant: $result['store'],
                name: Config::string('vendra-permission.admin_role'),
                guardName: Guard::getDefaultName(User::class),
            );

            $result['store']->execute(fn () => $result['user']->assignRole($role));

            return [
                ...$result,
                'password' => $password,
            ];
        });

        (new RequestJobContext(
            traceId: RequestJobContext::resolveTraceId(),
            operation: 'store_provision_dispatch',
            tenantId: $result['store']->id,
            metadata: [ContextKeys::RESELLER_ID => $result['store']->reseller_id],
        ))->scope(
            fn () => CompleteStoreProvisioningJob::dispatch($result['store']->id)->afterCommit(),
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

        return Str::length($username) >= 3 ? $username : 'owner';
    }
}
