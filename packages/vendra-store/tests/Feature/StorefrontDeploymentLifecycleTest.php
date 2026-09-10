<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Illuminate\Validation\ValidationException;
use Misaf\VendraStore\Actions\RequestStorefrontDeploymentAction;
use Misaf\VendraStore\Enums\StorefrontDeploymentStatus;
use Misaf\VendraStore\Exceptions\InvalidStorefrontTransitionException;
use Misaf\VendraStore\Filament\Schemas\StorefrontConfigurationFields;
use Misaf\VendraStore\Models\StorefrontDeployment;
use Misaf\VendraStore\Support\StorefrontConfigurationMap;

beforeEach(function (): void {
    Queue::fake();
    Config::set('container.drivers.docker.host', 'unix:///var/run/docker.sock');
});

describe('the form-to-configuration map', function (): void {
    it('names only fields the console form actually defines', function (): void {
        $defined = collect([
            ...StorefrontConfigurationFields::identityFields(optional: false),
            ...StorefrontConfigurationFields::contactFields(optional: false),
            ...StorefrontConfigurationFields::locationAndSocialFields(optional: false),
        ])->map(fn ($component): string => $component->getName())->all();

        expect(array_keys(StorefrontConfigurationMap::FIELDS))->each->toBeIn($defined);
    });

    it('maps every storefront field the form collects', function (): void {
        $defined = collect([
            ...StorefrontConfigurationFields::identityFields(optional: false),
            ...StorefrontConfigurationFields::contactFields(optional: false),
            ...StorefrontConfigurationFields::locationAndSocialFields(optional: false),
        ])->map(fn ($component): string => $component->getName())
            // slug identifies the deployment row itself rather than travelling
            // inside the encoded configuration.
            ->reject(fn (string $name): bool => in_array($name, ['storefront_image_id', 'storefront_slug'], true))
            ->all();

        expect($defined)->each->toBeIn(array_keys(StorefrontConfigurationMap::FIELDS));
    });
});

describe('requesting a deployment', function (): void {
    it('rejects an incomplete configuration at request time instead of on the queue', function (): void {
        $tenant = createTestTenant();
        $form = storefrontRequestData();
        unset($form['storefront_contact_email'], $form['storefront_locality']);

        expect(fn () => resolve(RequestStorefrontDeploymentAction::class)->execute($tenant, 'acme.test', $form))
            ->toThrow(ValidationException::class)
            ->and(StorefrontDeployment::query()->count())->toBe(0);
        Queue::assertNothingPushed();
    });
});

describe('the deployment state machine', function (): void {
    it('refuses to fail a deployment that never started provisioning', function (): void {
        $deployment = StorefrontDeployment::factory()->create([
            'status' => StorefrontDeploymentStatus::Pending,
        ]);

        expect(fn () => $deployment->markFailed('boom'))
            ->toThrow(InvalidStorefrontTransitionException::class, 'from [pending] to [failed]');
    });

    it('records a ready deployment with its container reference and digest', function (): void {
        $deployment = StorefrontDeployment::factory()->create([
            'status' => StorefrontDeploymentStatus::Pending,
        ]);

        $deployment->markProcessing();
        $deployment->markReady('vendra-storefront-acme', 'ghcr.io/misaf/vendra-storefront-florist@sha256:abc', 'sha256:abc');

        expect($deployment->refresh()->status)->toBe(StorefrontDeploymentStatus::Ready)
            ->and($deployment->container_name)->toBe('vendra-storefront-acme')
            ->and($deployment->image_digest)->toBe('sha256:abc')
            ->and($deployment->deployed_at)->not->toBeNull();
    });

    it('clears the previous failure when a retry starts', function (): void {
        $deployment = StorefrontDeployment::factory()->create([
            'status' => StorefrontDeploymentStatus::Failed,
            'error' => 'the container exited',
            'failed_at' => now(),
        ]);

        $deployment->markProcessing();

        expect($deployment->refresh()->status)->toBe(StorefrontDeploymentStatus::Processing)
            ->and($deployment->error)->toBeNull()
            ->and($deployment->failed_at)->toBeNull();
    });

    it('treats only ready and failed as settled', function (): void {
        expect(StorefrontDeploymentStatus::Ready->isSettled())->toBeTrue()
            ->and(StorefrontDeploymentStatus::Failed->isSettled())->toBeTrue()
            ->and(StorefrontDeploymentStatus::Requested->isSettled())->toBeFalse()
            ->and(StorefrontDeploymentStatus::Processing->isSettled())->toBeFalse()
            ->and(StorefrontDeploymentStatus::Pending->isSettled())->toBeFalse();
    });
});
