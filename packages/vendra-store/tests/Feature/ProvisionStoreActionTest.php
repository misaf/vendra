<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Misaf\VendraStore\Actions\ProvisionStoreAction;
use Misaf\VendraStore\Jobs\CompleteStoreProvisioningJob;
use Misaf\VendraSupport\Tenancy\Events\TenantProvisioned;
use Misaf\VendraTenant\Enums\TenantProvisioningStatus;

beforeEach(function (): void {
    Event::fake([TenantProvisioned::class]);
    Queue::fake();
});

it('hashes a provided administrator password', function (): void {
    $result = resolve(ProvisionStoreAction::class)->execute([
        'name' => 'Acme',
        'domain' => 'acme.test',
        'username' => 'admin_acme',
        'email' => 'admin@acme.test',
    ], password: 'secret-password');

    expect(Arr::get($result, 'password'))->toBe('secret-password')
        ->and(Hash::check('secret-password', Arr::get($result, 'user')->password))->toBeTrue();
});

it('generates a random administrator password when none is provided', function (): void {
    $result = resolve(ProvisionStoreAction::class)->execute([
        'name' => 'Acme',
        'domain' => 'acme.test',
        'username' => 'admin_acme',
        'email' => 'admin@acme.test',
    ]);

    expect(Arr::get($result, 'password'))->toHaveLength(8)
        ->and(Hash::check(Arr::get($result, 'password'), Arr::get($result, 'user')->password))->toBeTrue();
});

it('assigns the administrator role for the user guard when another guard is active', function (): void {
    Config::set('auth.defaults.guard', 'console');

    $result = resolve(ProvisionStoreAction::class)->execute([
        'name' => 'Acme',
        'domain' => 'acme.test',
        'username' => 'admin_acme',
        'email' => 'admin@acme.test',
    ]);

    expect(Arr::get($result, 'user')->roles)->toHaveCount(1)
        ->and(Arr::get($result, 'user')->roles->sole()->guard_name)->toBe('web');
});

it('stamps the domain with the newly provisioned tenant even when another tenant is current', function (): void {
    $first = resolve(ProvisionStoreAction::class)->execute([
        'name' => 'First',
        'domain' => 'first.test',
        'username' => 'admin_first',
        'email' => 'admin@first.test',
    ]);

    switchToTestTenant(Arr::get($first, 'store'));

    $second = resolve(ProvisionStoreAction::class)->execute([
        'name' => 'Second',
        'domain' => 'second.test',
        'username' => 'admin_second',
        'email' => 'admin@second.test',
    ]);

    $domain = Arr::get($second, 'store')->execute(
        fn () => Arr::get($second, 'store')->storeDomains()->first(),
    );

    expect($domain)->not->toBeNull()
        ->and($domain->name)->toBe('second.test')
        ->and($domain->store_id)->toBe(Arr::get($second, 'store')->getKey());
});

it('queues durable tenant provisioning after creating inactive records', function (): void {
    $result = resolve(ProvisionStoreAction::class)->execute([
        'name' => 'Acme',
        'domain' => 'acme.test',
        'username' => 'admin_acme',
        'email' => 'admin@acme.test',
    ], shouldSeed: true);

    expect(Arr::get($result, 'store')->active)->toBeFalse()
        ->and(Arr::get($result, 'store')->provisioning_status)->toBe(TenantProvisioningStatus::Pending)
        ->and(Arr::get($result, 'store')->provisioning_should_seed)->toBeTrue();

    Queue::assertPushed(
        CompleteStoreProvisioningJob::class,
        fn (CompleteStoreProvisioningJob $job): bool => $job->tenantId === Arr::get($result, 'store')->id,
    );
    Event::assertNotDispatched(TenantProvisioned::class);
});
