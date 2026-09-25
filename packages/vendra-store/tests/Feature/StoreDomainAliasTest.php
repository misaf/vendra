<?php

declare(strict_types=1);

use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Validator;
use Misaf\VendraStore\Actions\AddStoreDomainAliasAction;
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
        ->and($deployment->aliasDomains())->toBeEmpty();

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

it('allows only one live primary domain per store', function (): void {
    $store = Store::factory()->create();
    StoreDomain::factory()->for($store)->primary()->create(['name' => 'first.test']);

    expect(fn () => StoreDomain::factory()->for($store)->primary()->create(['name' => 'second.test']))
        ->toThrow(UniqueConstraintViolationException::class);
});

it('rejects domains that collide with an administration host or a retained deployment', function (string $domain): void {
    StorefrontDeployment::factory()->create(['domain' => 'held.test']);

    expect(Validator::make(['domain' => $domain], ['domain' => StoreDomain::activeDomainRules()])->fails())->toBeTrue();
})->with(['admin.shop.test', 'held.test']);

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
