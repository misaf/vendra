<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Queue;
use Misaf\VendraStore\Actions\AddStoreDomainAliasAction;
use Misaf\VendraStore\Actions\MakeStoreDomainPrimaryAction;
use Misaf\VendraStore\Actions\RemoveStoreDomainAliasAction;
use Misaf\VendraStore\Enums\StorefrontRuntimeState;
use Misaf\VendraStore\Jobs\ProvisionStorefrontJob;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Models\StoreDomain;
use Misaf\VendraStore\Models\StorefrontDeployment;
use Misaf\VendraStore\Support\StorefrontObservation;
use Misaf\VendraStore\Support\StorefrontProvisionRequest;

it('adds an active alias domain and redeploys the storefront to route it', function (): void {
    Queue::fake();

    $store = Store::factory()->create();
    StoreDomain::factory()->for($store)->primary()->create(['name' => 'main.test']);
    $deployment = StorefrontDeployment::factory()->for($store)->create(['domain' => 'main.test']);

    $alias = resolve(AddStoreDomainAliasAction::class)->execute($store, 'alias.test');

    expect($alias->active)->toBeTrue()
        ->and($alias->is_primary)->toBeFalse()
        ->and($store->primaryDomain()->value('name'))->toBe('main.test')
        ->and(StorefrontProvisionRequest::for($deployment->refresh())->aliases)->toBe(['alias.test']);

    Queue::assertPushed(
        ProvisionStorefrontJob::class,
        fn (ProvisionStorefrontJob $job): bool => $job->deploymentId === $deployment->id && $job->force,
    );
});

it('promotes an alias to primary and keeps the previous primary as an alias', function (): void {
    Queue::fake();

    $store = Store::factory()->create();
    $previous = StoreDomain::factory()->for($store)->primary()->create(['name' => 'main.test']);
    $alias = StoreDomain::factory()->for($store)->active()->create(['name' => 'alias.test']);
    $deployment = StorefrontDeployment::factory()->for($store)->create(['domain' => 'main.test']);

    resolve(MakeStoreDomainPrimaryAction::class)->execute($store, $alias);

    expect($alias->fresh()?->is_primary)->toBeTrue()
        ->and($previous->fresh()?->is_primary)->toBeFalse()
        ->and($previous->fresh()?->active)->toBeTrue()
        ->and($deployment->fresh()?->domain)->toBe('alias.test')
        ->and($deployment->fresh()?->aliasDomains())->toBe(['main.test']);

    Queue::assertPushed(
        ProvisionStorefrontJob::class,
        fn (ProvisionStorefrontJob $job): bool => $job->deploymentId === $deployment->id && $job->force,
    );
});

it('refuses to make another store\'s domain primary', function (): void {
    $store = Store::factory()->create();
    StoreDomain::factory()->for($store)->primary()->create(['name' => 'main.test']);
    $foreign = StoreDomain::factory()->for(Store::factory()->create())->active()->create(['name' => 'foreign.test']);

    expect(fn () => resolve(MakeStoreDomainPrimaryAction::class)->execute($store, $foreign))
        ->toThrow(InvalidArgumentException::class);
});

it('removes an alias as trashed history and redeploys the storefront without it', function (): void {
    Queue::fake();

    $store = Store::factory()->create();
    StoreDomain::factory()->for($store)->primary()->create(['name' => 'main.test']);
    $alias = StoreDomain::factory()->for($store)->active()->create(['name' => 'alias.test']);
    $deployment = StorefrontDeployment::factory()->for($store)->create(['domain' => 'main.test']);

    resolve(RemoveStoreDomainAliasAction::class)->execute($store, $alias);

    $removed = StoreDomain::query()->withoutGlobalScopes()->withTrashed()->find($alias->getKey());

    expect($removed?->trashed())->toBeTrue()
        ->and($removed?->active)->toBeFalse()
        ->and($deployment->aliasDomains())->toBe([]);

    Queue::assertPushed(
        ProvisionStorefrontJob::class,
        fn (ProvisionStorefrontJob $job): bool => $job->deploymentId === $deployment->id && $job->force,
    );
});

it('refuses to remove the primary domain as an alias', function (): void {
    $store = Store::factory()->create();
    $primary = StoreDomain::factory()->for($store)->primary()->create(['name' => 'main.test']);

    expect(fn () => resolve(RemoveStoreDomainAliasAction::class)->execute($store, $primary))
        ->toThrow(InvalidArgumentException::class);
});

it('reports drift when the container routes a different set of aliases', function (): void {
    $observed = new StorefrontObservation(
        state: StorefrontRuntimeState::Running,
        aliases: ['old.test'],
    );

    expect($observed->isServingAliasesOtherThan(['alias.test']))->toBeTrue()
        ->and($observed->isServingAliasesOtherThan(['old.test']))->toBeFalse();
});

it('treats a container with no aliases label as no evidence rather than drift', function (): void {
    $observed = new StorefrontObservation(state: StorefrontRuntimeState::Running);

    expect($observed->isServingAliasesOtherThan(['alias.test']))->toBeFalse();
});
