<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Misaf\VendraStore\Actions\ReconcileStoreStorefrontAction;
use Misaf\VendraStore\Actions\StartStoreStorefrontAction;
use Misaf\VendraStore\Actions\UpdateStorefrontConfigurationAction;
use Misaf\VendraStore\Contracts\StorefrontProvisioner;
use Misaf\VendraStore\Enums\StorefrontDeploymentStatus;
use Misaf\VendraStore\Enums\StorefrontDesiredState;
use Misaf\VendraStore\Enums\StorefrontReconciliationOutcome;
use Misaf\VendraStore\Jobs\ProvisionStorefrontJob;
use Misaf\VendraStore\Jobs\ReconcileStorefrontJob;
use Misaf\VendraStore\Models\StorefrontDeployment;
use Misaf\VendraStore\Models\StorefrontImage;
use Misaf\VendraStore\Support\StorefrontObservation;
use Misaf\VendraStore\Support\StorefrontProvisionRequest;
use Misaf\VendraStore\Support\StorefrontProvisionResult;

const RECONCILE_IMAGE = 'ghcr.io/misaf/vendra-storefront-florist@sha256:abc123';

beforeEach(function (): void {
    Config::set('container.drivers.docker.host', 'unix:///var/run/docker.sock');
    Config::set('vendra-store.storefront.network', 'traefik-public');
    // Keep the health gate short so unhealthy containers are not polled for minutes.
    Config::set('vendra-store.storefront.health_timeout', 1);
});

/**
 * A configuration complete enough to pass validation on a redeploy.
 *
 * Kept local, since sibling test helpers only exist once their file loads.
 *
 * @return array<string, mixed>
 */
function reconcilableConfiguration(): array
{
    return [
        'slug' => 'acme-flowers',
        'domain' => 'acme.test',
        'siteUrl' => 'https://acme.test',
        'businessType' => 'Florist',
        'priceCurrency' => 'IRR',
        'name' => ['en' => 'Acme Flowers'],
        'address' => ['locality' => 'Tehran', 'country' => 'IR'],
        'contact' => [
            'mobilePhone' => '09120000000',
            'officePhone' => '02100000000',
            'email' => 'contact@acme.test',
            'hoursOpen' => '08:00',
            'hoursClose' => '21:00',
            'mapQuery' => '35.7,51.4',
        ],
        'social' => [
            'whatsappPhone' => '+989120000000',
            'telegramUsername' => 'acmeflowers',
            'instagramUsername' => 'acmeflowers',
        ],
    ];
}

/**
 * @param  array<string, mixed>  $attributes
 */
function reconcilable(array $attributes = []): StorefrontDeployment
{
    $storefrontImage = StorefrontImage::query()->firstOrCreate(
        ['image' => RECONCILE_IMAGE],
        ['active' => true],
    );

    return StorefrontDeployment::factory()->create([
        'storefront_image_id' => $storefrontImage->id,
        'slug' => 'acme-flowers',
        'domain' => 'acme.test',
        'status' => StorefrontDeploymentStatus::Ready,
        'desired_state' => StorefrontDesiredState::Running,
        'image' => RECONCILE_IMAGE,
        'configuration' => reconcilableConfiguration(),
        ...$attributes,
    ]);
}

function reconcile(StorefrontDeployment $deployment): StorefrontReconciliationOutcome
{
    return resolve(ReconcileStoreStorefrontAction::class)->execute($deployment);
}

describe('a storefront meant to be running', function (): void {
    it('leaves a healthy one serving the configured image completely alone', function (): void {
        $engine = fakeExistingStorefront();

        expect(reconcile(reconcilable()))->toBe(StorefrontReconciliationOutcome::InSync)
            ->and($engine->calls)->toBeEmpty();
    });

    it('starts a stopped storefront instead of rebuilding it', function (): void {
        $deployment = reconcilable();
        $engine = fakeExistingStorefront(
            ['Status' => 'exited', 'ExitCode' => 0],
            encodedConfiguration: StorefrontProvisionRequest::for($deployment)->encodedConfiguration(),
        );

        expect(reconcile($deployment))->toBe(StorefrontReconciliationOutcome::Started)
            ->and($engine->calls)->toBe(['start'])
            ->and($engine->calls)->not->toContain('remove');
    });

    it('applies configuration saved while stopped when the storefront is started', function (string $state): void {
        Queue::fake();
        $deployment = reconcilable(['desired_state' => StorefrontDesiredState::Stopped]);
        $engine = fakeExistingStorefront(
            ['Status' => $state, 'ExitCode' => 0],
            encodedConfiguration: StorefrontProvisionRequest::for($deployment)->encodedConfiguration(),
        );

        resolve(UpdateStorefrontConfigurationAction::class)->execute($deployment, ['storefront_mobile_phone' => '09129999999']);
        Queue::assertNotPushed(ProvisionStorefrontJob::class);

        resolve(StartStoreStorefrontAction::class)->execute($deployment->refresh());
        new ReconcileStorefrontJob($deployment->id)->handle(resolve(ReconcileStoreStorefrontAction::class));

        expect($engine->calls)->toContain('remove', 'containers/create');
        assertDockerRequestSent(function ($request): bool {
            if (! Str::endsWith($request->path, '/containers/create')) {
                return false;
            }

            $configuration = collect(Arr::get($request->body, 'Env'))->first(fn (string $value): bool => Str::startsWith($value, 'STOREFRONT_CONFIG_BASE64='));
            $decoded = json_decode(base64_decode(Str::after($configuration, 'STOREFRONT_CONFIG_BASE64=')), true, flags: JSON_THROW_ON_ERROR);

            return Arr::get($decoded, 'contact.mobilePhone') === '09129999999';
        });
    })->with(['exited', 'created']);

    it('redeploys a stopped storefront whose image changed', function (): void {
        $engine = fakeExistingStorefront(['Status' => 'exited', 'ExitCode' => 0], image: 'ghcr.io/misaf/vendra-storefront-florist@sha256:older');

        expect(reconcile(reconcilable()))->toBe(StorefrontReconciliationOutcome::Redeployed)
            ->and($engine->calls)->toContain('remove', 'containers/create');
    });

    it('redeploys a running storefront whose configuration changed', function (): void {
        $deployment = reconcilable();
        $engine = fakeExistingStorefront(encodedConfiguration: base64_encode('{}'));

        expect(reconcile($deployment))->toBe(StorefrontReconciliationOutcome::Redeployed)
            ->and($engine->calls)->toContain('remove', 'containers/create');
    });

    it('deploys one the runtime does not have at all', function (): void {
        $engine = fakeExistingStorefront(present: false);

        expect(reconcile(reconcilable()))->toBe(StorefrontReconciliationOutcome::Deployed)
            ->and($engine->calls)->toContain('containers/create');
    });

    it('redeploys one serving an image other than the configured one', function (): void {
        $engine = fakeExistingStorefront(image: 'ghcr.io/misaf/vendra-storefront-florist@sha256:older');

        expect(reconcile(reconcilable()))->toBe(StorefrontReconciliationOutcome::Redeployed)
            ->and($engine->calls)->toContain('remove')
            ->and($engine->calls)->toContain('containers/create');
    });

    it('redeploys one that is running but failing its health check', function (): void {
        $engine = fakeExistingStorefront(['Status' => 'running', 'Health' => ['Status' => 'unhealthy']]);

        expect(reconcile(reconcilable()))->toBe(StorefrontReconciliationOutcome::Redeployed)
            ->and($engine->calls)->toContain('containers/create');
    });

    it('does not mistake an unreported health state for drift', function (): void {
        $engine = fakeExistingStorefront(['Status' => 'running']);

        expect(reconcile(reconcilable()))->toBe(StorefrontReconciliationOutcome::InSync)
            ->and($engine->calls)->toBeEmpty();
    });
});

describe('a storefront meant to be stopped', function (): void {
    it('stops one that is still running', function (): void {
        $engine = fakeExistingStorefront();
        $deployment = reconcilable(['desired_state' => StorefrontDesiredState::Stopped]);

        expect(reconcile($deployment))->toBe(StorefrontReconciliationOutcome::Stopped)
            ->and($engine->calls)->toBe(['stop']);
    });

    it('leaves an already stopped one alone rather than starting it', function (): void {
        $engine = fakeExistingStorefront(['Status' => 'exited', 'ExitCode' => 0]);
        $deployment = reconcilable(['desired_state' => StorefrontDesiredState::Stopped]);

        expect(reconcile($deployment))->toBe(StorefrontReconciliationOutcome::InSync)
            ->and($engine->calls)->toBeEmpty();
    });
});

it('never rewrites the intent it is converging towards', function (): void {
    fakeExistingStorefront(['Status' => 'exited', 'ExitCode' => 0]);
    $deployment = reconcilable(['desired_state' => StorefrontDesiredState::Stopped]);

    reconcile($deployment);

    expect($deployment->refresh()->desired_state)->toBe(StorefrontDesiredState::Stopped);
});

it('refuses to read an unreachable runtime as an absent storefront', function (): void {
    bindFakeDockerEngine(fn ($request, bool $stream) => $stream
        ? dockerStreamResponse('', 500)
        : dockerResponse(['message' => 'boom'], 500));

    expect(fn () => reconcile(reconcilable()))->toThrow(RuntimeException::class);
});

describe('the reconcile command', function (): void {
    beforeEach(function (): void {
        Queue::fake();
    });

    it('queues a convergence for every deployment, stopped ones included', function (): void {
        $running = reconcilable();
        $stopped = reconcilable([
            'slug' => 'beta-flowers',
            'domain' => 'beta.test',
            'desired_state' => StorefrontDesiredState::Stopped,
        ]);

        $this->artisan('vendra-store:reconcile')
            ->expectsOutput('2 storefront deployment(s) queued for reconciliation.')
            ->assertSuccessful();

        foreach ([$running, $stopped] as $deployment) {
            Queue::assertPushed(
                ReconcileStorefrontJob::class,
                fn (ReconcileStorefrontJob $job): bool => $job->deploymentId === $deployment->id,
            );
        }

        Queue::assertNotPushed(ProvisionStorefrontJob::class);
    });

    it('queues a forced rebuild only when redeployment is asked for by name', function (): void {
        $deployment = reconcilable();

        $this->artisan('vendra-store:redeploy')
            ->expectsOutput('1 storefront deployment(s) queued for redeployment.')
            ->assertSuccessful();

        Queue::assertPushed(
            ProvisionStorefrontJob::class,
            fn (ProvisionStorefrontJob $job): bool => $job->deploymentId === $deployment->id && $job->force,
        );
        Queue::assertNotPushed(ReconcileStorefrontJob::class);
    });

    it('leaves a deliberately stopped storefront out of a redeployment', function (): void {
        reconcilable(['desired_state' => StorefrontDesiredState::Stopped]);

        $this->artisan('vendra-store:redeploy')
            ->expectsOutput('0 storefront deployment(s) queued for redeployment.')
            ->assertSuccessful();

        Queue::assertNotPushed(ProvisionStorefrontJob::class);
    });
});

it('reports what a synchronous pass actually changed', function (): void {
    fakeExistingStorefront();
    reconcilable();

    $this->artisan('vendra-store:reconcile --sync')
        ->expectsOutput('1 storefront deployment(s) reconciled.')
        ->expectsOutput('  in sync: 1')
        ->assertSuccessful();
});

it('promotes a requested deployment to ready once it is serving the desired image', function (): void {
    fakeExistingStorefront();
    $deployment = reconcilable(['status' => StorefrontDeploymentStatus::Requested, 'deployed_at' => null]);

    expect(reconcile($deployment))->toBe(StorefrontReconciliationOutcome::InSync)
        ->and($deployment->refresh()->status)->toBe(StorefrontDeploymentStatus::Ready)
        ->and($deployment->deployed_at)->not->toBeNull();
});

it('converges again when the intent changed while a reconcile job was running', function (): void {
    $deployment = reconcilable(['desired_state' => StorefrontDesiredState::Stopped]);
    $provisioner = Mockery::mock(StorefrontProvisioner::class);
    $provisioner->expects('observe')->twice()->andReturnUsing(function () use ($deployment): StorefrontObservation {
        // A start lands while the first pass is still settling the stopped intent.
        $deployment->refresh()->markDesiredState(StorefrontDesiredState::Running);

        return StorefrontObservation::fromContainer(null);
    });
    $provisioner->expects('provision')->once()->andReturn(StorefrontProvisionResult::make(ready: true, reference: 'vendra-storefront-acme-flowers', imageDigest: null));
    app()->instance(StorefrontProvisioner::class, $provisioner);

    app()->call([new ReconcileStorefrontJob($deployment->id), 'handle']);

    expect($deployment->refresh()->status)->toBe(StorefrontDeploymentStatus::Ready);
});
